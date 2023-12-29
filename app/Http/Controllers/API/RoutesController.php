<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\API\merchantsController;
use App\Models\ApiKey;

use Illuminate\Http\Request;

class RoutesController extends Controller
{
     function handlePaymentRequest($apiKey, $live, $paymentId = null)
    {
        // Check if API key exists in the database
        $api = ApiKey::where('api_key', $apiKey)->first();
    
        if ($api) {
            // Set endpoint based on the "live" parameter
            $endpoint = $live ? 'https://finix.live-payments-api.com' : 'https://finix.sandbox-payments-api.com';
    
            // Call the appropriate function based on the presence of $paymentId
            if ($paymentId) {
                $result = merchantsController::fetchPayment(config("app.api_username"), config("app.api_password"), $paymentId, $endpoint);
            } else {
                $result = merchantsController::listPayments(config("app.api_username"), config("app.api_password"), config(), $endpoint, ['filter' => json_encode(['tags.api' => $apiKey])]);
            }
    
            return $result[0];
        } else {
            // API key not found
            return response()->json(['error' => 'Invalid API key'], 401);
        }
    }
    function handleMakePaymentRequest($apiKey, $live, $email, $exp_month, $exp_year, $name, $cardNumber, $cvv, $amount, $currency)
    {
        // Check if API key exists in the database
        $api = ApiKey::where('api_key', $apiKey)->first();
        $merchant = ApiUsers::where('api_key',$apiKey)->first();
        if ($api) {
            // Set endpoint based on the "live" parameter
            $endpoint = $live ? 'https://finix.live-payments-api.com' : 'https://finix.sandbox-payments-api.com';
            $id=merchantsController::createIdentityBuyerMinReq(config("app.api_username"),config("app.api_password"),"byersolomon@mail.com");
            $id=json_decode($id[0],true)['id'];
            $card=merchantsController::createPaymentInstramentMinReq(config("app.api_username"),config("app.api_password"),
            $card=json_decode($card[0],true)['id'];
            $exp_month,$exp_year,$id,$name,$cardNumber,$cvv,"PAYMENT_CARD"); 
            $result=merchantsController::makePaymentMinReq(config("app.api_username"),config("app.api_password"),$merchant->merchant,$currency,$amount,$card);
            
            return $result[0];
        } else {
            // API key not found
            return response()->json(['error' => 'Invalid API key'], 401);
        }
    }
    function handleRefundRequest($apiKey, $live, $paymentId, $refundAmount)
    {
        // Check if API key exists in the database
        $api = ApiKey::where('api_key', $apiKey)->first();
    
        if ($api) {
            // Set endpoint based on the "live" parameter
            $endpoint = $live ? 'https://finix.live-payments-api.com' : 'https://finix.sandbox-payments-api.com';
    
            // Call the appropriate function based on the presence of $paymentId
            if ($paymentId) {
                $result = merchantsController::createRefund(config("app.api_username"), config("app.api_password"), $paymentId, $refundAmount ,$endpoint);
            } 
    
            return $result[0];
        } else {
            // API key not found
            return response()->json(['error' => 'Invalid API key'], 401);
        }
    }
    function makePyament(){
        return $this->handleMakePaymentRequest(request('api_key'), request('live', false), request('email'),request('$exp_month'),request('$exp_year'),request('$name'),request('$cardNumber'),request('$cvv'),request('$amount'),request('currency'),);
    }
    function listPayments(){
        return $this->handlePaymentRequest(request('api_key'), request('live', false), null);
    }
    function fetchPayment(){
        return $this->handlePaymentRequest(request('api_key'), request('live', false), request('payment_id'));
    }
    function refundPayment(){
        return $this->handlePaymentRequest(request('api_key'), request('live', false), request('payment_id'),request('refund_amount'));
    }
}
