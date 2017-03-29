<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Thujohn\Twitter\Facades\Twitter;
use Config;

use Google_Service_YouTube_VideoSnippet;
use Google_Service_YouTube_VideoStatus;
use Google_Service_YouTube_Video;
use Google_Service_YouTube;
use Google_Http_MediaFileUpload;
use Google_Client;


class UserTemporary extends Model
{
    protected $table = 'psa_users_temporary';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = ['identity'];

    /**
     * The attributes excluded from the model's JSON form.
     * @var array
     */
    protected $hidden = [
        'id','created_at','updated_at'
    ];

    protected $createRules = [
        'identity' => 'Required|String',
    ];

    protected $editRules = [
        'campaign_id' => 'Required|String',
    ];



    /**
     * validate edit interest data
     * @return mixed
     */
    public function editValidator(){
        $v = \Validator::make($this->attributes, $this->editRules);
        if($v->fails()){
            $error = $v->messages()->toArray();
            return $this->errorHandler($error);
        }
        $this->clearAttributes();
        return $v->passes();
    }

    /**
     * return array errors
     * @param $errors
     * @return array
     */
    protected function errorHandler($errors){
        $return = [];
        foreach($errors as $key => $value){
            $return[$key] = $value[0];
        }
        return $return;
    }






}
