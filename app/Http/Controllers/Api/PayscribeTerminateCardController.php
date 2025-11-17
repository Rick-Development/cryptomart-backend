<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\CardIssusing\TerminateCardHelper;
use Illuminate\Http\Request;

class PayscribeTerminateCardController extends Controller
{
    public function __construct(private TerminateCardHelper $terminateCardHelper){}

    public function terminateCard(Request $request){
        $data = $request->validate(
            [
                'ref' => 'required | string'
            ]
        );

        $response = json_decode($this->terminateCardHelper->terminateCard($data), true);
        return $response;
    }
}