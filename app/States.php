<?php
/**
 * Created by PhpStorm.
 * User: QA
 * Date: 23.03.2016
 * Time: 17:53
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class States extends Model
{

    protected $table = 'psa_states';

    public static function getStatesList(){
        return self::all('id', 'name as state');
    }
}