<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Interests extends Model
{
    protected $table = 'psa_interests';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = ['interest_name', 'status'];

    /**
     * The attributes excluded from the model's JSON form.
     * @var array
     */
    protected $hidden = [
        'id','created_at','updated_at'
    ];

    protected $createRules = [
        'identity' => 'Required|String',
        'interest_name' => 'Required|String',
        'status' => 'Required|In:active,inactive'
    ];

    protected $editRules = [
        'interest_name' => 'Required|String',
        'status' => 'Required|In:active,inactive'
    ];

    /**
     * create Interest Validator
     * @return array
     */
    public function createValidator()
    {
        $v = \Validator::make($this->attributes, $this->createRules);
        if($v->fails()){
            $error = $v->messages()->toArray();
            return $this->errorHandler($error);
        }
        return $v->passes();
    }

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

    protected function clearAttributes()
    {
        unset($this->identity);
    }

    public function getInterestsList($data){

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
