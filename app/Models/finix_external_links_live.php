<?php

namespace App\Models;

use App\Http\Controllers\API\fileController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class finix_external_links_live extends Model
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
   public static function authenticateGet($api_userID, $api_key)
{
    $perPage = 20; // Default items per page

    if (($api_userID > 1 || $api_userID === null) && ($api_key > 1 || $api_key === null)) {
        return false;
    }

    // Check if the API key is a sub key
    if ($api_key > 1 || $api_key === null) {
        return self::where('api_key', $api_key)
            ->where('api_user', $api_userID)
            ->paginate($perPage);
    } else {
        // If the API key is not a sub key, no need to query the database
        return self::where('api_user', $api_userID)
            ->paginate($perPage);
    }
}
public static function authenticateSearch($api_userID, $api_key, $search)
{
    $columns = \Schema::getColumnListing((new self())->getTable());
    $perPage = 20; // Default items per page

    if (($api_userID > 1 || $api_userID === null) && ($api_key > 1 || $api_key === null)) {
        return false;
    }

    // Check if the API key is a sub key
    if ($api_key > 1 || $api_key === null) {
        return self::where('api_key', $api_key)
            ->where('api_user', $api_userID)
            ->where(function ($query) use ($columns, $search) {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'like', "%{$search}%");
                }
            })
            ->paginate($perPage);
    } else {
        // If the API key is not a sub key, no need to query the database
        return self::where('api_user', $api_userID)
            ->where(function ($query) use ($columns, $search) {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'like', "%{$search}%");
                }
            })
            ->paginate($perPage);
    }
}


    use HasFactory;
    protected $table="finix_external_links_live";
    protected $guarded=['id'];
    public static $name='external_links';
    public static function updateFromIds_live($file_id,$id){
       self::fromArray([json_decode(fileController::fetchExternalFile(config("app.api_username"),config("app.api_password"),$file_id,$id,'https://finix.live-payments-api.com')[0])]);
    }
    public static function runUpdateWithID($id){
        $result= fileController::listAllexternalLinks(config("app.api_username"),config("app.api_password"),$id);
        $object=json_decode($result[0]);
        while(isset($object->_embedded)&&isset($object->_embedded->external_links)&&isset($object->page)&&isset($object->page->next_cursor)&&count($object->_embedded->external_links)>0){
            self::fromArray($object->_embedded->external_links);
         $nextArray=['after_cursor'=>$object->page->next_cursor];
         $result= fileController::listAllexternalLinks(config("app.api_username"),config("app.api_password"),$id,'https://finix.live-payments-api.com',$nextArray);
         $object=json_decode($result[0]);
        }
     }
public static function fromArray(array $array)
{
    foreach ($array as $data) {
        $data = (object)$data;

        $found = self::where('finix_id', $data->id)->first();

        if ($found == null) {
            $found = self::create([
                'finix_id' => $data->id ?? null,
                'link_id' => $data->id ?? null, // assuming 'id' is the link_id
                'expires_at' => $data->expires_at ?? null,
                'file_id' => $data->file_id ?? null,
                'type' => $data->type ?? null,
                'url' => $data->url ?? null,
                'user_id' => $data->user_id ?? null,
            ]);
        } else {
            $found->update([
                'finix_id' => $data->id ?? null,
                'link_id' => $data->id ?? null, // assuming 'id' is the link_id
                'expires_at' => $data->expires_at ?? null,
                'file_id' => $data->file_id ?? null,
                'type' => $data->type ?? null,
                'url' => $data->url ?? null,
                'user_id' => $data->user_id ?? null,
            ]);
        }

        // Save and refresh the model
        $found->save();
        $found->refresh();
    }
}
}
