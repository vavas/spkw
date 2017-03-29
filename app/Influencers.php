<?php
/**
 * Created by PhpStorm.
 * User: QA
 * Date: 22.03.2016
 * Time: 11:19
 */

namespace App;

use Faker\Provider\cs_CZ\DateTime;
use Illuminate\Database\Eloquent\Model;

class Influencers extends Model
{

    protected $table = 'psa_influencers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'first_name','last_name','image_url','gender','age','image','location_state','location_city',
        'paypal', 'birthday'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id','created_at','updated_at'
    ];


    protected $guarded = [
        'onboarding','identity','role','accessToken','authToken'
    ];

    /**
     * create new influencer rules
     * @var array
     */
    protected $createRules = [
        'email' => 'Required|Email',
        'first_name' => 'Required|string|Min:3|Max:80',
        'last_name' => 'Required|string|Min:3|Max:80',
        'image_url' => 'string',
    ];

    /**
     * rules array
     * @var array
     */
    protected $rules = [
        //'email' => 'Required|Email',
        'image_url' => 'string',
        'first_name' => 'Required|string|Min:3|Max:80',
        'last_name' => 'Required|string|Min:3|Max:80',
        'gender' => 'Required|In:Female,Male',
        'age' => 'Required|Integer|Min:18',
        'location_state' => 'Required|String|Min:3|Max:50',
        'location_city' => 'Required|String|Min:3|Max:50',
        'paypal' => 'Email',
    ];

    protected $onboardingRules = [
        'identity' => 'Required|string',
    ];

    protected $responseFields = [
        'first_name', 'last_name', 'image_url', 'gender', 'age', 'location_state', 'location_city',
        'paypal',
    ];

    protected $socialFields = [
        'twitter_id', 'twitter_image', 'twitter_followers', 'twitter_verified', 'twitter_screen_name', 'twitter_onboard',
        'instagram_id', 'instagram_name', 'instagram_verified', 'instagram_image', 'instagram_followers', 'instagram_onboard',
        'youtube_id', 'youtube_name', 'youtube_verified', 'youtube_image', 'youtube_subscribers', 'youtube_views', 'youtube_onboard', 'birthday',
        'total_reach'
    ];


    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function validate($original = false){
        $data = ($original == true) ? $this->original : $this->attributes;
        if(isset($this->attributes['interests']) && !empty($this->attributes['interests'])) {
            unset($this->createRules['interests']);
        }
        $v = \Validator::make($data, $this->createRules);
        if($v->fails()){
            $error = $v->messages()->toArray();
            return $this->errorHandler($error);
        }
        return $v->passes();
    }

    public function editValidator($role = false){
        $v = \Validator::make($this->attributes, $this->rules);
        if($v->fails()){
            $error = $v->messages()->toArray();
            return $this->errorHandler($error);
        }
        if($role == config('constants.USER_ROLE_INFLUENCER')) {
            $this->clearBrandAttributes();
        }
        $this->clearAttributes();
        return $v->passes();
    }

    public function editValidatorSocials(){

        $v = \Validator::make($this->attributes, $this->onboardingRules);
        if($v->fails()){
            $error = $v->messages()->toArray();
            return $this->errorHandler($error);
        }
        $this->clearInfluencerMainAttributes();
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
     * @param $data
     * @param null $user
     * @return array
     * @todo Think need refactor
     */
    public function getInfluencersList($data, $user = null){

        $aColumns = array('psa_social_account.twitter_screen_name', 'psa_social_account.youtube_name',
                'psa_social_account.instagram_name', 'psa_influencers.first_name', 'psa_influencers.last_name',
                'psa_social_account.total_reach'
        );
        $fields = ['identity', 'first_name','last_name','image_url','gender','age',
            'image','location_state','location_city'];
        if(isset($user) && ($user->role == config('constants.USER_ROLE_ADMIN'))) {

        }
        if(isset($user)) {
            switch ($user->role) {
                case \Config::get('constants.USER_ROLE_INFLUENCER'):
                    array_push($fields, 'psa_users.id');
                    break;
                case \Config::get('constants.USER_ROLE_BRAND'):
                    array_push($fields, 'psa_users.identity', 'psa_users.id');
                    break;
                case \Config::get('constants.USER_ROLE_ADMIN'):
                    array_push($fields, 'psa_influencers.email', 'psa_influencers.paypal',
                        'psa_users.created_at', 'psa_users.lastLog', 'psa_users.id');
                    break;
                default:
                    break;
            }
        }

        $result = \DB::table('psa_influencers');
        $result->join('psa_users','psa_users.email','=','psa_influencers.email')
            ->join('psa_social_account', 'psa_users.identity', '=', 'psa_social_account.user_identity');
        /* * Filtering */
        if (isset($data['filters']) && $data['filters'] != "") {
            $filters = $data['filters'];
            if (isset($filters['socials']) && $filters['socials'] != "") {
                $socials = $filters['socials'];
                $whereSocials = [];

                foreach ($socials as $socialId => $val) {
                    if (($socialId == 'all') && ($val == 'true')) {

                        $result->where(function ($query) {
                            $query->orWhere('psa_social_account.twitter_onboard', '=', 1);
                            $query->orWhere('psa_social_account.instagram_onboard', '=', 1);
                            $query->orWhere('psa_social_account.youtube_onboard', '=', 1);
                        });
                        break;
                    }
                    if ($val == 'true') {
                        $whereSocials[] = $socialId;
                    }
                }
                $result->where(function ($query) use ($whereSocials) {
                    foreach($whereSocials as $whereSocial) {
                        if(in_array($whereSocial . '_onboard', $this->socialFields)) {
                            $query->where('psa_social_account.' . $whereSocial . '_onboard', '=', 1);
                        }
                    }
                });
            }

            if (isset($filters['interests']) && $filters['interests'] != "") {
                $interests = $filters['interests'];
                $result->leftJoin('psa_interest_influencer', 'psa_interest_influencer.influencer_identity', '=', 'psa_users.identity');
                $result->where(function ($query) use ($interests) {
                    foreach ($interests as $interestId => $val) {
                        if (($interestId == 'all') && ($val == 'true')) {
                            break;
                        }
                        if ($val == 'true') {
                            $query->orWhere('psa_interest_influencer.interest_identity', '=', $interestId);
                        }
                    }
                });
            }

            if (isset($filters['gender']) && $filters['gender'] != "") {
                $genders = $filters['gender'];
                $result->where(function ($query) use ($genders) {
                    foreach ($genders as $genderId => $val) {
                        if (($genderId == 'all') && ($val == 'true')) {
                            $allGenders = array('male', 'female');
                            foreach($allGenders as $gender) {
                                $query->orWhere('psa_influencers.gender', '=', $gender);
                            }
                            break;
                        }
                        if ($val == 'true') {
                            $query->orWhere('psa_influencers.gender', '=', $genderId);
                        }
                    }
                });
            }

            if (isset($filters['age']) && $filters['age'] != "") {
                $ages = $filters['age'];
                $result->where(function ($query) use ($ages) {
                    foreach ($ages as $ageId => $val) {
                        if (($ageId == 'all') && ($val == 'true')) {
                            break;
                        }
                        $allAges = array('a18_24' => [18,24], 'a25_36' => [25,36], 'a37_42' => [37,42], 'a43_plus' => [43]);
                        if ($val == 'true') {
                            if($ageId == 'a43_plus') {
                                $query->orWhere('psa_influencers.age', '>=', 43);
                            } else {
                                $query->orWhereBetween('psa_influencers.age', [$allAges[$ageId][0], $allAges[$ageId][1]]);

                            }
                        }
                    }
                });
            }

            if (isset($filters['reach']) && $filters['reach'] != "") {
                $reaches = $filters['reach'];
                $result->where(function ($query) use ($reaches) {
                    foreach ($reaches as $reachId => $val) {
                        if (($reachId == 'all') && ($val == 'true')) {
                            break;
                        }
                        $allReaches = array('r0_5' => [0,5000], 'r5_10' => [5000,10000], 'r101_25' => [10001,25000], 'r251_50' => [25001,50000], 'r50_plus' => [50000]);
                        if ($val == 'true') {
                            if(isset($allReaches[$reachId])) {

                                if ($reachId == 'r50_plus') {
                                    $query->orWhereRaw('`psa_social_account`.`total_reach` >= ' . 50000);
                                } else {
                                    $query->orWhereRaw('`psa_social_account`.`total_reach` between ' . $allReaches[$reachId][0] . ' and ' . $allReaches[$reachId][1]);
                                }
                            }
                        }
                    }
                });
            }
            /* Group */
            $result->groupBy('psa_users.id');

            /* Ordering */
            if (isset($data['order_by'])) {
                if(isset($data['order_direction']) && ($data['order_direction'] == 'DESC')) {
                    $direction = 'DESC';
                } else {
                    $direction = 'ASC';
                }
                $result->orderBy($data['order_by'], $direction);
            }

            /* * Search */
            if (isset($data['search']) && $data['search'] != "") {
                $searchQuery = $data['search']. '*';
                $queryRaw = "MATCH (first_name, last_name) AGAINST ('$searchQuery' IN BOOLEAN MODE) 
                OR MATCH (psa_social_account.twitter_screen_name,
                 psa_social_account.youtube_name, psa_social_account.instagram_name) 
                 AGAINST ('$searchQuery' IN BOOLEAN MODE)";
                $result->whereRaw($queryRaw);
            }

            if(isset($data['length']) && isset($data['page'])) {
                $take = $data['length'];
                $skip = $data['length'] * ($data['page'] - 1);
                $paginate = $result->paginate($take);
                $influencers = $result->get(array_merge($fields, $this->socialFields));
            } else {
                $paginate = $result->paginate();
                $influencers = $result->get(array_merge($fields, $this->socialFields));
            }

        }else{
            $influencers = $this->join('psa_users','psa_users.email','=','psa_influencers.email')
                ->leftJoin('psa_social_account', 'psa_users.identity', '=', 'psa_social_account.user_identity')
//                ->get(array_merge($fields, $this->socialFields))->toSql();
                ->get(array_merge($fields, $this->socialFields));
            $paginate = $this->join('psa_users','psa_users.email','=','psa_influencers.email')
                ->leftJoin('psa_social_account', 'psa_users.identity', '=', 'psa_social_account.user_identity')
                ->paginate();
        }
        foreach($influencers as $influencer) {
            if(!is_null($influencer)) {
                $influencer->interests = $user->getUserInterests($influencer->identity);
            }

        }
        $recordsTotal = count($influencers);

        $recordsFiltered = $paginate->total();

        return array($influencers,$recordsTotal,$recordsFiltered);

    }

    public function getInfluencer($user, $currentUser = false){

        $profile = $user->influencer()
            ->leftJoin('psa_users', 'psa_influencers.email', '=' ,'psa_users.email')
            ->leftJoin('psa_social_account', 'psa_users.identity', '=', 'psa_social_account.user_identity')
            ->where('psa_users.identity', '=', $user->identity)->first(array_merge($this->responseFields, $this->socialFields));

        if(is_null($profile)){
            return $profile;
        }
        if(isset($currentUser)) {
            switch ($currentUser->role) {
                case \Config::get('constants.USER_ROLE_INFLUENCER'):
                        if($currentUser->id == $user->id) {
                            $profile->email = $user->email;
                            $profile->last_login = new \Datetime($user->lastLog);
                        } else {
                            $profile->paypal = null;
                        }

                    break;
                case \Config::get('constants.USER_ROLE_BRAND'):
                    $profile->paypal = null;
                    break;
                case \Config::get('constants.USER_ROLE_ADMIN'):
                    $profile->signup_date = $user->created_at;
                    $profile->email = $user->email;
                    $profile->last_login = new \Datetime($user->lastLog);
                    break;
                /* case 'new':
                     break;
                 case 'blocked':
                     break;*/
                default:
                    break;
            }
        }

        return $profile;
    }

    public function getAge($birhday = false)
    {
        if($birhday) {
            $this->birthday = $birhday;
        }
        //explode the date to get month, day and year
        $from = new \DateTime($this->birthday);
        $to   = new \DateTime('today');
        return $from->diff($to)->y;
    }

    public function getInfluencersCampaign($campaign_identity)
    {
        $influencers = InfluencersCampaign::join('psa_users', 'psa_influencers_campaign.influencer_id', '=', 'psa_users.identity')
            ->join('psa_influencers', 'psa_influencers.email', '=', 'psa_users.email')
            ->join('psa_social_account', 'psa_social_account.user_identity', '=', 'psa_users.identity')
            ->where('psa_influencers_campaign.campaign_id','=',$campaign_identity)
            ->where(function ($query) {
                $query->orWhere('psa_influencers_campaign.status', '=', config('constants.INFLUENCER_CAMPAIGN_STATUS_INVITED'));
                $query->orWhere('psa_influencers_campaign.status', '=', config('constants.INFLUENCER_CAMPAIGN_STATUS_APPLIED'));
                $query->orWhere('psa_influencers_campaign.status', '=', config('constants.INFLUENCER_CAMPAIGN_STATUS_APPLICATION_ACCEPTED'));
                $query->orWhere('psa_influencers_campaign.status', '=', config('constants.INFLUENCER_CAMPAIGN_STATUS_APPLICATION_DECLINED'));
            })
            ->groupBy('psa_users.identity')
            ->get(array('psa_social_account.*', 'psa_influencers_campaign.status',
                'psa_influencers.first_name', 'psa_influencers.last_name', 'psa_influencers_campaign.identity as invite_identity'));
        return $influencers;
    }
}
