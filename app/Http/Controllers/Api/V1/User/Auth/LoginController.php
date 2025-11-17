<?php

namespace App\Http\Controllers\Api\V1\User\Auth;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Http\Helpers\Response;
use App\Models\UserAuthorization;
use App\Traits\User\LoggedInUsers;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\User\UserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Notifications\User\Auth\SendAuthorizationCode;
use App\Http\Controllers\Api\V1\User\Auth\AuthorizationController;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    protected $request_data;

    use AuthenticatesUsers, LoggedInUsers;

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        \Log::info($request->all());
        $this->request_data = $request;

        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors()->all(), []);
        }

        $validated = $validator->validate();
        if (!User::where($this->username(), $validated['username'])->exists()) {
            return Response::error([__('User doesn\'t exists!')], [], 404);
        }

        $user = User::where($this->username(), $validated['username'])->first();
        if (!$user)
            return Response::error([__('User doesn\'t exists')]);

        if (Hash::check($validated['password'], $user->password)) {
            if ($user->status != GlobalConst::ACTIVE)
                return Response::error([__("Your account is temporary banded. Please contact with system admin")]);

            // User authenticated
            $token = $user->createToken("auth_token")->accessToken;
            return $this->authenticated($request, $user, $token);
        }

        return Response::error([__('username didn\'t match')]);
    }


    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        $request->merge(['status' => true]);
        $request->merge([$this->username() => $request->credentials]);
        return $request->only($this->username(), 'password', 'status');
    }


    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        $request = $this->request_data->all();
        $credentials = $request['username'];
        if (filter_var($credentials, FILTER_VALIDATE_EMAIL)) {
            return "email";
        }
        return "username";
    }


    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard("api");
    }


    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user, $token)
    {
        $basic_settings = BasicSettings::first();

        // Reset 2FA flag
        $user->update([
            'two_factor_verified' => false,
        ]);

        // Try refreshing wallets
        try {
            $this->refreshUserWallets($user);
        } catch (Exception $e) {
            return Response::error(
                [__('Login Failed! Failed to refresh wallet! Please try again')],
                [],
                500
            );
        }

        // Log the login attempt
        $this->createLoginLog($user);

        // 🔥 FIXED: Only send authorization code if verification is required AND user has NOT verified yet
        if ($basic_settings->email_verification == true && $user->email_verified == false) {
            $auth_token = generate_unique_string("user_authorizations", "token", 200);
            $data = [
                'user_id' => $user->id,
                'code' => generate_random_code(),
                'token' => $auth_token,
                'created_at' => now(),
            ];

            DB::beginTransaction();
            try {
                // Remove old codes for this user
                UserAuthorization::where("user_id", $user->id)->delete();

                // Insert the new code/token
                DB::table("user_authorizations")->insert($data);

                // Try notifying user (fail silently but you may want to log it)
                try {
                    $user->notify(new SendAuthorizationCode((object) $data));
                } catch (Exception $e) {
                    // log the error instead of swallowing silently
                    \Log::warning("Authorization email failed for user {$user->id}: " . $e->getMessage());
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw new Exception(__("Something went wrong! Please try again"));
            }

            // User still needs to complete verification
            $status = false;
        } else {
            // User is good to go (already verified or verification not required)
            $status = true;
            $auth_token = '';
        }

        // Return the login response
        return Response::success(
            [__('User successfully logged in')],
            [
                'token' => $token,
                'user_info' => UserResource::make($user),
                'authorization' => [
                    'status' => $status,
                    'token' => $auth_token,
                ],
            ],
            200
        );
    }

}