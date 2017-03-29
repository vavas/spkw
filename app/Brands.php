<?php
/**
 * Created by PhpStorm.
 * User: QA
 * Date: 11.03.2016
 * Time: 13:49
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Brands extends Model
{
    protected $table = 'psa_brands';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email','first_name','last_name','brand_name','image_url','details','media','brand_media','submission','category',
        'status','hashtag','url'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id','updated_at','created_at'
    ];

    protected $createRules = [
        'email' => 'Required|email|Unique:psa_brands',
        'first_name' => 'Required|string',
        'last_name' => 'Required|string',
        'brand_name' => 'Required|string',
        'image_url' => 'Required|string',
        'identity' => 'Required|string',
    ];

    protected $editRules = [
        'first_name' => 'Required|string',
        'last_name' => 'Required|string',
        'image_url' => 'string',

        'details' => 'Min:3|Max:255',
        'media' => 'Min:3|Max:255|AlphaNum',
        'brand_media' => 'Min:3|Max:255|AlphaNum',
        'submission' => 'In:private,public',
        'category' => 'Min:3|Max:255|AlphaNum',
        'status' => 'In:project,public',
        'hashtag' => 'string',
        'url' => 'string',
    ];
    /**
     * rules array
     * @var array
     */
    protected $rules = [
    'details' => 'Min:3|Max:255',
    'media' => 'Min:3|Max:255|AlphaNum',
    'brand_media' => 'Min:3|Max:255|AlphaNum',
    'submission' => 'In:private,public',
    'category' => 'Min:3|Max:255|AlphaNum',
    'status' => 'In:project,public',
    'hashtag' => 'string',
    'url' => 'string',
];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * validate create brand data
     * @param $data
     * @return mixed
     */
    public function createValidator(){
        $v = \Validator::make($this->attributes, $this->createRules);
        if($v->fails()){
            $error = $v->messages()->toArray();
            return $this->errorHandler($error);
        }
        return $v->passes();
    }

    /**
     * validate brand data
     * @param $data
     * @return mixed
     */
    public function validator(){
        $v = \Validator::make($this->attributes, $this->rules);
        if($v->fails()){
            $error = $v->messages()->toArray();
            return $this->errorHandler($error);
        }
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


    public function getBrandList($data){

        if (!empty($data) and isset($data['order_by'])){
            $take = $data['length'];
            $skip = $take * ($data['page'] - 1);

            $result = $this->orderBy($data['order_by'],$data['order_direction'])
                ->take($take)
                ->skip($skip)
                ->get();
        } else {
            $result = $this->all();
        }


        $recordsFiltered = $this->count();
        $recordsTotal = count($result);

        return array($result,$recordsTotal,$recordsFiltered);
    }
}

