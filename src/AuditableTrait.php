<?php

namespace fabian004\MyAuditPackage;

use Illuminate\Support\Facades\DB;

trait AuditableTrait
{
    public static function bootAuditableTrait()
    {
        static::created(function($item) 
        {
            $now = now(); 
            $id = $item->attributes["id"];
            DB::table('audits')->insert(
                [
                    'user_type' => auth()->user() ? get_class(auth()->user()) : 'App\Models\User',
                    'user_id' => auth()->user() ? auth()->user()->id : 0, 
                    'event' => "created", 
                    'auditable_type' => get_class($item), 
                    'auditable_id' => "$id", 
                    "old_values" =>"[]", 
                    "new_values" => json_encode($item->attributes), 
                    "url"=> isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"] : '',
                    "ip_address" => isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '', 
                    "user_agent" => isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : '', 
                    'created_at' => $now, 
                    'updated_at' => $now
                ]
            );
        });
        static::updating(function($item) 
        {
            $serverVars = [
                "HTTP_HOST" => $_SERVER["HTTP_HOST"],
                "REQUEST_URI" => $_SERVER["REQUEST_URI"],
                "REMOTE_ADDR" => $_SERVER["REMOTE_ADDR"],
                "HTTP_USER_AGENT" => $_SERVER["HTTP_USER_AGENT"]
            ];
            [$newFields,$oldFields] = self::getArrays($item->attributes,$item->original);
            $id = $item->attributes["id"];
            $now = now(); 
            DB::table('audits')->insert(
                [
                    'user_type' => 'App\Models\User', 
                    'user_id' => auth()->user() ? auth()->user()->id : 0, 
                    'event' => "updated", 
                    'auditable_type' => get_class($item), 
                    'auditable_id' => "$id", 
                    "old_values" => json_encode($oldFields), 
                    "new_values" => json_encode($newFields), 
                    "url"=> isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"] : '',
                    "ip_address" => isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '', 
                    "user_agent" => isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : '', 
                    'created_at' => $now, 
                    'updated_at' => $now
                ]
            );
            
        });
        
        static::deleting(function($item) 
        {
            $now = now(); 
            [$newFields,$oldFields] = self::getArrays($item->attributes,$item->original);
            $id = $item->attributes["id"];
            DB::table('audits')->insert(
                [
                    'user_type' => 'App\Models\User', 
                    'user_id' => auth()->user() ? auth()->user()->id : 0, 
                    'event' => "deleted", 
                    'auditable_type' => get_class($item), 
                    'auditable_id' => "$id", 
                    "old_values" => json_encode($oldFields),
                    "new_values" => json_encode($newFields), 
                    "url"=> isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"] : '',
                    "ip_address" => isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '', 
                    "user_agent" => isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : '', 
                    'created_at' => $now, 
                    'updated_at' => $now
                ]
            );
        });
    }
    protected static function getArrays($attributes, $original)
    {
        $newFields = [];
        $oldFields = [];

        foreach ($attributes as $key => $value1) 
        {
            if (!isset($original[$key])) {
                continue;
            }
            $value2 = $original[$key];
            if ($value1 != $value2) 
            {
                $newFields[$key] = $value1;
                $oldFields[$key] = $value2;
            }
        }

        return [$newFields, $oldFields];
    }

}