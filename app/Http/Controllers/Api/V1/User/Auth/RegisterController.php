<?php

namespace App\Http\Controllers\Api\V1\User\Auth;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Http\Helpers\Response;
use App\Models\UserAuthorization;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Traits\User\RegisteredUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Http\Resources\User\UserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Providers\Admin\BasicSettingsProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Notifications\User\Auth\SendAuthorizationCode;
use App\Services\QuidaxService;

class RegisterController extends Controller
{
    use RegistersUsers, RegisteredUsers;

    protected $basic_settings;
    protected $quidax;

    public function __construct(QuidaxService $quidax)
    {
        $this->quidax = $quidax;
        $this->basic_settings = BasicSettingsProvider::get();
        $this->middleware(function($request, $next) {
            if($this->basic_settings->user_registration == false) return Response::error([__("Currently user registration is not available")], [], 400);
            return $next($request);
        });
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = $this->validator($request->all());
        if($validator->fails()) {
            return Response::error($validator->errors()->all(),[]);
        }

        $validated = $validator->validate();
        $basic_settings             = $this->basic_settings;

        $validated['email_verified']    = ($basic_settings->email_verification == true) ? false : true;
        $validated['sms_verified']      = ($basic_settings->sms_verification == true) ? false : true;
        $validated['kyc_verified']      = ($basic_settings->kyc_verification == true) ? false : true;
        $validated['password']          = Hash::make($validated['password']);
        $validated['username']          = make_username($validated['firstname'],$validated['lastname']);

        if(User::where("username",$validated['username'])->exists()) return Response::error([__('User already exists!')],[],400);

        $validated['account_no'] = generate_unique_number('users','account_no', 14);

        $validated['address']       = [
            'country' => $validated['country'] ?? "",
        ];

        $validated['account_type'] = $validated['account_type'] ?? "";
        $validated['company_name'] = $validated['company_name'] ?? "";
        try {
            // Call Quidax API
            $quidax_response = $this->quidax->createSubAccount([
                'email'      => $validated['email'],
                'first_name' => $validated['firstname'],
                'last_name'  => $validated['lastname'],
            ]);

            if (!isset($quidax_response['data'])) {
                throw new \Exception("Invalid Quidax response");
            }

            $quidax_data = $quidax_response['data'];

            // Merge Quidax details into user payload
            $validated['quidax_id']           = $quidax_data['id'] ?? null;
            $validated['quidax_sn']           = $quidax_data['sn'] ?? null;
            $validated['quidax_display_name'] = $quidax_data['display_name'] ?? null;
            $validated['quidax_reference']    = $quidax_data['reference'] ?? null;

            event(new Registered($user = $this->create($validated)));

            // return response()->json([
            //     'status'  => 'success',
            //     'message' => 'User registered successfully',
            //     'user'    => $user,
            // ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Registration failed! Please try again.',
                'error'   => $e->getMessage(), // log internally
            ], 500);
        }


        // get user with all information
        try{
            $user = User::find($user->id);
        }catch(Exception $e) {
            return Response::error([__('Failed to fetch user information. Please try again')],[],500);
        }

        try{
            $this->createUserWallets($user);
            $token = $user->createToken("auth_token")->accessToken;
        }catch(Exception $e) {
            return Response::error([__('Failed to generate user token! Please try again')],[],500);
        }
        if ($basic_settings->email_verification == true) {
            $auth_token  = generate_unique_string("user_authorizations","token",200);
            $data = [
                'user_id'       => $user->id,
                'code'          => generate_random_code(),
                'token'         => $auth_token,
                'created_at'    => now(),
            ];
            DB::beginTransaction();
            try{
                UserAuthorization::where("user_id",$user->id)->delete();
                DB::table("user_authorizations")->insert($data);
                try{
                    $user->notify(new SendAuthorizationCode((object) $data));
                }catch(Exception $e){}
                DB::commit();
            }catch(Exception $e) {
                DB::rollBack();
                $error = ['Something went worng! Please try again.'];
                return Response::error($error);
            }
            $status     = false;
        }else{
            $status     = true;
            $auth_token      = '';
        }
        if ($basic_settings->email_verification == 1 && $basic_settings->email_notification == 1) {
            $message =  ['Please check email and verify your account'];
        } else {
            $message =  ['Registration successful'];
        }


        $data = [
            'email_verification'    => $basic_settings->email_verification,
            'kyc_verification'    => $basic_settings->kyc_verification,
            'token' => $token,
            'image_path' => get_files_public_path('user-profile'),
            'default_image' => get_files_public_path('default'),
            'user_info' => new UserResource($user),
            'authorization' => [
                'status'    => $status,
                'token'     => $auth_token,
            ],
        ];
        return Response::success($message, $data);
    }
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data) {

        $basic_settings = $this->basic_settings;
        $password_rule = "required|confirmed|string|min:6";
        if($basic_settings->secure_password) {
            $password_rule = ["required","confirmed",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()];
        }

        $agree_policy = $this->basic_settings->agree_policy == 1 ? 'required|in:on' : 'nullable';

        return Validator::make($data,[
            // 'account_type'      => 'required|in:personal,business',
            'firstname'         => 'required|string|max:60',
            'lastname'          => 'required|string|max:60',
            'email'             => 'required|string|email|max:150|unique:users,email',
            'country'           => 'required|string|max:50',
            // 'company_name'      => "required_if:account_type," . GlobalConst::BUSINESS_ACCOUNT,
            'password'          => $password_rule,
            'agree'             => $agree_policy,
        ]);
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard("api");
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create($data);
    }


}
