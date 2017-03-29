<?php

namespace App;

use App\Http\Controllers\UserController;
use App\Influencers;
use Illuminate\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use DB;
use Webpatser\Uuid\Uuid;

class User extends Model implements AuthenticatableContract,AuthorizableContract
{
    use Authenticatable, Authorizable;



    protected $table = 'psa_users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password','password_confirmation','role'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id','password','remember_token','lastLog','created_at','updated_at'
    ];

    private $rules = [
            'email' => 'Required|Between:3,64|Email|Unique:psa_users',
            'password' => 'string|Confirmed'
        ];

    private $editRules = [
        //'email' => 'Required|Between:3,64|Unique:psa_users',
        'password' => 'Required|string|Confirmed|Between:8,64',
    ];

    private $brandRules = [
        'email' => 'Between:3,64|Email|Unique:psa_users',
        'first_name' => 'Min:3|Max:80',
        'last_name' => 'Min:3|Max:80',
        'role' => 'In:brand',
    ];

    private $loginRules = [
        'email' => 'Required|Between:3,64|Email',
        'password' => 'Required|Min:3|Max:80'
    ];

    public function influencer()
    {
        return $this->hasOne('App\Influencers', 'email', 'email');
    }

    public function brand()
    {
        return $this->hasOne('App\Brands', 'email', 'email');
    }

    public function setUserAttributes(){
        $this->remember_token = $this->password;
        $this->password = bcrypt($this->password);
        $this->authToken = md5($this->generate());
    }

    public function validate(){
        $v = \Validator::make($this->attributes, $this->rules);
        if($v->fails()){
            $error = $v->messages()->toArray();
            return $this->errorHandler($error);
        }
        return $v->passes();
    }

    /**
     * validate User data
     * @param $data
     * @return mixed
     */
    public function validateBrand(){
        $v = \Validator::make($this->attributes, $this->brandRules);
        if($v->fails()){
            $error = $v->messages()->toArray();
            return $this->errorHandler($error);
        }
        return $v->passes();
    }

    /**
     * validate User data
     * @param $data
     * @return mixed
     */
    public function editValidator(){
        $v = \Validator::make($this->attributes, $this->editRules);
        if($v->fails()){
            $error = $v->messages()->toArray();
            return $this->errorHandler($error);
        }
        return $v->passes();
    }

    /**
     * validate User data
     * @param $data
     * @return mixed
     */
    public function validateLogin($data){
        $v = \Validator::make($data, $this->loginRules);
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

    /**
     * Generate random string
     * @param int $length
     * @return string
     */
    public function generate($length = 8){
        $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ123456789';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }
        return $string;
    }

    /**
     * return admin status
     * @return bool
     */
    public function isAdmin() {
        return $this->role != 'admin'?false:true;
    }

    /**
     * return admin status
     * @return bool
     */
    public function isBrand() {
        return $this->role != 'brand'?false:true;
    }

    /**
     *
     * @return bool
     */
    public function isInfluencer() {
        return $this->role != 'influencer' ? false : true;
    }


    /**
     * return First Name + Last Name
     * @return string
     */
    public function getFirstLastName()
    {
        return $this->firstname . '&nbsp;' . $this->lastname;
    }

    /**
     * @param $user
     * @return bool
     */
    public function deleteUserInterests($user) {
        $result = \DB::table('psa_interest_influencer')->where('influencer_identity', '=', $user);
        return $result->delete();
    }

    public function getUserInterests($identity)
    {
        $interests = \DB::table('psa_interest_influencer')
            ->where('influencer_identity', '=', $identity)
            ->join('psa_interests','psa_interests.identity', '=', 'psa_interest_influencer.interest_identity')
            ->get();

        return $interests;

    }

    public function saveUserInterests($influencer, $data) {

        if ($influencer && $data['interests']) {

            $sql = "INSERT INTO `psa_interest_influencer` ";
            $sql.= " (`influencer_identity`, `interest_identity`) VALUES ";
            for ($i = 0; $i < count($data['interests']); $i++) {
                $delimiter = ( $i+1 !== count($data['interests']) ) ? ', ' : ' ';
                $sql.= sprintf(
                    " ('%s', '%s')%s",
                    $influencer,
                    is_array($data['interests'][$i]) ? $data['interests'][$i]['identity'] : $data['interests'][$i],
                    $delimiter
                );
            }

            try {
                return \DB::insert($sql);
            } catch (\Illuminate\Database\QueryException $e) {
                if($e instanceof \Illuminate\Database\QueryException) {
                    return ['message' => 'interest_id is invalid'];
                }
            }
        }
        return false;
    }

    /**
     * @param $data array
     */
    public function createTemporaryInfluencer($data)
    {
        $data['identity'] = $this->getIdentity();
        return DB::transaction(function() use ($data)
        {
            $newUser = new User();
            $newUser->identity = $data['identity'];
            $newUser->email = $data['identity'];
            $newUser->role = config('constants.USER_ROLE_INFLUENCER');
            $newUser->save();

            if( !$newUser && !$newInfluencer && !$newSocial ) {
                DB::rollBack();
                throw new \Exception('Influencer not created');
            }
            return true;
        });
    }

    /**
     * generate UIID4 token
     * @return string
     * @throws \Exception
     */
    protected function getIdentity(){
        $uids = Uuid::generate(4);
        return $uids->string;
    }

    /**
     * @param $query
     * @param string $identity
     * @return User|null
     */
    public function scopeGetUserByBrandIdentify($query, $identity)
    {
        return $query->join('psa_brands', 'psa_users.email', '=', 'psa_brands.email')
            ->where('psa_brands.identity', '=', $identity)->first();
    }

}

