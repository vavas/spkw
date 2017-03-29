<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    protected $table = 'psa_version';
    protected $primaryKey = 'bundle_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'minimum_version','current_version','store_url'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'bundle_id', 'created_at', 'updated_at'
    ];

    protected $rules = [
        'minimum_version' => 'string',
        'current_version' => 'string',
        'store_url' => 'string',
    ];

    public function validator(){
        $v = \Validator::make($this->attributes, $this->rules);
        if($v->fails()){
            $error = $v->messages()->toArray();
            return $this->errorHandler($error);
        }
        return $v->passes();
    }
}
