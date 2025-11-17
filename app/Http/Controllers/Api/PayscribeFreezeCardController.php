<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\CardIssusing\FreezeCaard;
use App\Http\Helpers\Payscribe\CardIssusing\FreezeCardHelper;
use Illuminate\Http\Request;

class PayscribeFreezeCardController extends Controller
{
    public function __construct(private FreezeCardHelper $freezeCardHelper){}

    public function freezeCard(Request $request){
        $data = $request->validate(
            [
                'ref' => 'required | string'
            ]
        );
        $response = json_decode($this->freezeCardHelper->freezeCard($data), true);
        return $response;
    }
}