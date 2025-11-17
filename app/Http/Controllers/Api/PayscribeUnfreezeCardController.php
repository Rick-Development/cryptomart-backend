<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\CardIssusing\UnfreezeCardHelper;
use Illuminate\Http\Request;

class PayscribeUnfreezeCardController extends Controller
{
    public function __construct(private UnfreezeCardHelper $unfreezeCardHelper){} 
    public function unfreezeCard(Request $request){
        $data = $request->validate(
            [
                'ref' => 'required | string'
            ]
        );

        $response = json_decode($this->unfreezeCardHelper->unfreezeCard($data), true);
        
        return $response;
    }
}