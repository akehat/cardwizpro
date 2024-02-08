<?php

namespace App\Models;

use App\Http\Controllers\API\finixUsersController;
use App\Http\Controllers\API\merchantsController;
use Error;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class awaiting_users extends Model
{
public function scopeAccessible($query)
    {
        // Check if the authenticated user is an admin
        if (Auth::check() && Auth::user()->isAdmin()) {
            return $query; // No additional condition needed for admins
        }

        // If not an admin, add the additional condition
        return $query->where('api_user', Auth::user()->apiuser()->select('api_users.id')->first()->id);
    }
 public static function authenticateGetID($id, $api_userID , $api_key)
    {
        if(($api_userID > 1 || $api_userID === null) && ($api_key > 1 || $api_key === null)) return false;
        // Check if the API key is a sub key
        if ($api_key > 1 || $api_key === null) {
            return self::where(function ($query) use ($id) {
            $query->where('id', $id)
                  ->orWhere('finix_id', $id);
        })->where('api_key', $api_key)
                ->where('api_user', $api_userID)
                ->first();
        } else {
            // If the API key is not a sub key, no need to query the database
            return self::where('api_user', $api_userID)
                ->where(function ($query) use ($id) {
            $query->where('id', $id)
                  ->orWhere('finix_id', $id);
        })
                ->first();
        }
    }
    public static function authenticateGet( $api_userID , $api_key)
    {
        if(($api_userID > 1 || $api_userID === null) && ($api_key > 1 || $api_key === null)) return false;
        // Check if the API key is a sub key
        if ($api_key > 1 || $api_key === null) {
            return self::where('api_key', $api_key)
                ->where('api_user', $api_userID)
                ->first();
        } else {
            // If the API key is not a sub key, no need to query the database
            return self::where('api_user', $api_userID)
                ->first();
        }
    }
    use HasFactory;
    protected $table='awaiting_user';
    protected $guarded=['id'];
    public function checkReady(){
        try{
            verifications::runUpdate();
        }catch(Exception | Error $e){
            Log::info($e->getMessage());
        }
        return verifications::where('identity',$this->identity)->first() !== null;
    }
    public function checkStatus(){
        try{
            verifications::runUpdate();
        }catch(Exception | Error $e){
            Log::info($e->getMessage());
        }
        $verifications=verifications::where('identity',$this->identity)->first();
        if($verifications==null){
            return null;
        }
        else{
            return $verifications->state;
        }
    }
    public function completeSignup(){
        $user=finixUsersController::createAMerchantUser($this->identity);
        if($user[1]>=200&&$user[1]<300){
            $value=json_decode($user[0]);
            $userApi=ApiUser::where('user_id',$this->user_id)->first();
            $userApi->update([
                "username"=>$value->id,
                "password"=>$value->password
            ]);
            $userApi->save();
            $userApi->refresh();
            $userRef=$userApi->user();
            $userRef->update([
                'hasId'=>true
            ]);
            $userRef->save();
            $userRef->refresh();
            return true;
        }else{
            return $user[0];
        }

    }

    // Schema::create('awaiting_user', function (Blueprint $table) {
    //     $table->id();
    //     $table->timestamps();
    //     $table->string('identity')->default(0)->nullable();
    //     $table->integer('user_id')->nullable();
    // });

}
