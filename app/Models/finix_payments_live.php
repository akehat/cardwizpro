<?php

namespace App\Models;

use App\Http\Controllers\API\merchantsController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class finix_payments_live extends Model
{
    use HasFactory;
    public static $name='transfers';
    public static function updateFromId_live($id){
       self::fromArray([json_decode(merchantsController::fetchPayment(config("app.api_username"),config("app.api_password"),$id,'https://finix.live-payments-api.com')[0])]);
    }
    protected $table="finix_payments_live";
    protected $guarded=['id'];
    public static function runUpdate(){
        $result= merchantsController::listPayments(config("app.api_username"),config("app.api_password"));
        $object=json_decode($result[0]);
        while(isset($object->_embedded)&&isset($object->_embedded->transfers)&&isset($object->page)&&isset($object->page->next_cursor)&&count($object->_embedded->transfers)>0){
            self::fromArray($object->_embedded->transfers);
         $nextArray=['after_cursor'=>$object->page->next_cursor];
         $result= merchantsController::listPayments(config("app.api_username"),config("app.api_password"),'https://finix.live-payments-api.com',$nextArray);
         $object=json_decode($result[0]);
        }
     }
    public static function fromArray($array){
        foreach ($array as $value) {
            $value=(object)$value;
            $found=finix_payments::where('finix_id',$value->id)->first();
            if($found==null){
               $found=finix_payments::create([
                    'finix_id'=>$value->id??null,
                    'created_at_finix'=>$value->created_at??null,
                    'updated_at_finix'=>$value->updated_at??null,
                    'additional_buyer_charges'=>$value->additional_buyer_charges??null,
                    'additional_healthcare_data'=>$value->additional_healthcare_data??null,
                    'additional_purchase_data'=>$value->additional_purchase_data??null,
                    'address_verification'=>$value->address_verification??null,
                    'amount'=>$value->amount??null,
                    'amount_requested'=>$value->amount_requested??null,
                    'application'=>$value->application??null,
                    'currency'=>$value->currency??null,
                    'destination'=>$value->destination??null,
                    'externally_funded'=>$value->externally_funded??null,
                    'failure_code'=>$value->failure_code??null,
                    'failure_message'=>$value->failure_message??null,
                    'fee'=>$value->fee??null,
                    'idempotency_id'=>$value->idempotency_id??null,
                    'merchant'=>$value->merchant??null,
                    'merchant_identity'=>$value->merchant_identity??null,
                    'messages'=>json_encode($value->messages??[])??null,
                    'parent_transfer'=>$value->parent_transfer??null,
                    'parent_transfer_trace_id'=>$value->parent_transfer_trace_id??null,
                    'raw'=>$value->raw??null,
                    'ready_to_settle_at'=>$value->ready_to_settle_at??null,
                    'receipt_last_printed_at'=>$value->receipt_last_printed_at??null,
                    'security_code_verification'=>$value->security_code_verification??null,
                    'source'=>$value->source??null,
                    'split_transfers'=>json_encode($value->split_transfers??[])??null,
                    'state'=>$value->state??null,
                    'statement_descriptor'=>$value->statement_descriptor??null,
                    'subtype'=>$value->subtype??null,
                    'tags'=>json_encode($value->tags??[])??null,
                    'trace_id'=>$value->trace_id??null,
                    'type'=>$value->type??null,
                    'fee_type'=>$value->fee_type??null
                ]);
            }else{
                $found->update([
                    'finix_id'=>$value->id??null,
                    'created_at_finix'=>$value->created_at??null,
                    'updated_at_finix'=>$value->updated_at??null,
                    'additional_buyer_charges'=>$value->additional_buyer_charges??null,
                    'additional_healthcare_data'=>$value->additional_healthcare_data??null,
                    'additional_purchase_data'=>$value->additional_purchase_data??null,
                    'address_verification'=>$value->address_verification??null,
                    'amount'=>$value->amount??null,
                    'amount_requested'=>$value->amount_requested??null,
                    'application'=>$value->application??null,
                    'currency'=>$value->currency??null,
                    'destination'=>$value->destination??null,
                    'externally_funded'=>$value->externally_funded??null,
                    'failure_code'=>$value->failure_code??null,
                    'failure_message'=>$value->failure_message??null,
                    'fee'=>$value->fee??null,
                    'idempotency_id'=>$value->idempotency_id??null,
                    'merchant'=>$value->merchant??null,
                    'merchant_identity'=>$value->merchant_identity??null,
                    'messages'=>json_encode($value->messages??[])??null,
                    'parent_transfer'=>$value->parent_transfer??null,
                    'parent_transfer_trace_id'=>$value->parent_transfer_trace_id??null,
                    'raw'=>$value->raw??null,
                    'ready_to_settle_at'=>$value->ready_to_settle_at??null,
                    'receipt_last_printed_at'=>$value->receipt_last_printed_at??null,
                    'security_code_verification'=>$value->security_code_verification??null,
                    'source'=>$value->source??null,
                    'split_transfers'=>json_encode($value->split_transfers??[])??null,
                    'state'=>$value->state??null,
                    'statement_descriptor'=>$value->statement_descriptor??null,
                    'subtype'=>$value->subtype??null,
                    'tags'=>json_encode($value->tags??[])??null,
                    'trace_id'=>$value->trace_id??null,
                    'type'=>$value->type??null,
                    'fee_type'=>$value->fee_type??null
                ]);
            }
            $found->save();
            $found->refresh();
        }
    }
    public static function makePayment($merchant,$currency,$amount_in_cents,$card,$userID,$api_userID,$apikeyID=0){
        $islive=true;
        $endpoint=$islive?'https://finix.live-payments-api.com':'https://finix.sandbox-payments-api.com';
        $payment=merchantsController::makePaymentMinReq(config("app.api_username"),config("app.api_password"),$merchant,$currency,$amount_in_cents,$card,$endpoint,[],['tags'=>["userID"=>"userID_".$userID,"api_userID"=>"api_userID_".$api_userID,"apikeyID"=>"apikeyID_".$apikeyID]]);
        if($payment[1]>=200&&$payment[1]<300){
        $value=(object)json_decode($payment[0]);
        $paymentMade=finix_payments::create([
            'finix_id'=>$value->id??null,
            'created_at_finix'=>$value->created_at??null,
            'updated_at_finix'=>$value->updated_at??null,
            'additional_buyer_charges'=>$value->additional_buyer_charges??null,
            'additional_healthcare_data'=>$value->additional_healthcare_data??null,
            'additional_purchase_data'=>$value->additional_purchase_data??null,
            'address_verification'=>$value->address_verification??null,
            'amount'=>$value->amount??null,
            'amount_requested'=>$value->amount_requested??null,
            'application'=>$value->application??null,
            'currency'=>$value->currency??null,
            'destination'=>$value->destination??null,
            'externally_funded'=>$value->externally_funded??null,
            'failure_code'=>$value->failure_code??null,
            'failure_message'=>$value->failure_message??null,
            'fee'=>$value->fee??null,
            'idempotency_id'=>$value->idempotency_id??null,
            'merchant'=>$value->merchant??null,
            'merchant_identity'=>$value->merchant_identity??null,
            'messages'=>json_encode($value->messages??[])??null,
            'parent_transfer'=>$value->parent_transfer??null,
            'parent_transfer_trace_id'=>$value->parent_transfer_trace_id??null,
            'raw'=>$value->raw??null,
            'ready_to_settle_at'=>$value->ready_to_settle_at??null,
            'receipt_last_printed_at'=>$value->receipt_last_printed_at??null,
            'security_code_verification'=>$value->security_code_verification??null,
            'source'=>$value->source??null,
            'split_transfers'=>json_encode($value->split_transfers??[])??null,
            'state'=>$value->state??null,
            'statement_descriptor'=>$value->statement_descriptor??null,
            'subtype'=>$value->subtype??null,
            'tags'=>json_encode($value->tags??[])??null,
            'trace_id'=>$value->trace_id??null,
            'type'=>$value->type??null,
            'fee_type'=>$value->fee_type??null,
            'api_user'=>$api_userID??null,
            'is_live'=>$islive??null,
            'api_key'=>''.$apikeyID??null
        ]);
        $paymentMade->save();
        $paymentMade->refresh();
            return ['worked'=>true,"responce"=>$payment[0],"ref"=>$paymentMade];
        }else{
            return ['worked'=>false,"responce"=>$payment[0]];
        }
    }
    public static function makeRefund($id,$amount_in_cents,$api_userID,$apikeyID=0){
        $islive=true;
        $endpoint=$islive?'https://finix.live-payments-api.com':'https://finix.sandbox-payments-api.com';
        $exists=null;
        if(!empty($api_userID)){
        $exists=self::where('finix_id',$id)->where('api_user', $api_userID)->first();
        }else if(!empty($apikeyID)&&$apikeyID!=0){
        $exists=self::where('finix_id',$id)->where('api_key', $api_userID)->first();
        }
        if($exists!==null){
            merchantsController::createRefund(config("app.api_username"),config("app.api_password"),$id,['tags'=>["api_userID"=>"api_userID_".$api_userID,"apikeyID"=>"apikeyID_".$apikeyID,'refund'=>'made']],$amount_in_cents,$endpoint);
        }
    }
}