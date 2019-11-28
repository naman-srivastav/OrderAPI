<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Exception;

class Order extends Model
{    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'orders';
    protected $fillable = [
        'id','origin_lat','origin_long','destination_lat' ,'destination_long','distance','status'
    ];
    
    const UNASSIGNED = "UNASSIGNED";
    const TAKEN = "TAKEN";
    
    /**
     * Function to get distance between origin and destination.
     * 
     * @param array $origin
     * @param array $destination
     * @return array
     */
    public function getDistance($origin , $destination){
        if(empty(config('config.DISTANCE_MATRIX_URL'))){
            return ["error" => "GOOGLE_MAP_URL_NOT_FOUND."];
        }
        if(empty(config('config.GOOGLE_MAP_KEY'))){
            return ["error" => "GOOGLE_MAP_KEY_NOT_FOUND"];
        }
        $lat1 = (float)array_get($origin,'0','');
        $long1 = (float)array_get($origin,'1','');
        $lat2 = (float)array_get($destination,'0','');
        $long2 = (float)array_get($destination,'1','');
        $url = config('config.DISTANCE_MATRIX_URL')."&origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&key=".config('config.GOOGLE_MAP_KEY');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        $response = json_decode($response, true);
        curl_close($ch);
        if(array_get($response,'status','') != "OK" || empty(array_get($response,'rows.0.elements.0.distance.value',''))){
            return ["error" => "DATA_NOT_FOUND"];
        }
        $data = [
            'origin_lat' => $lat1,
            'origin_long' => $long1,
            'destination_lat' => $lat1,
            'destination_long' => $long1,
            'distance' => array_get($response,'rows.0.elements.0.distance.value',''),
            'status' => self::UNASSIGNED,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        return $data;
    }
}
