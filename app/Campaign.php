<?php
/**
 * Created by PhpStorm.
 * User: QA
 * Date: 16.03.2016
 * Time: 11:01
 */

namespace App;

use App\Http\Middleware\Influencer;
use Faker\Provider\DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Auth;

class Campaign extends Model
{
    protected $table = 'psa_campaign';

    protected $fillable = [
        'brand_id','title','social_network','type', 'visibility','submission_deadline','application_deadline','guidelines','media','interests',
        'compensation','minimum_reach','posting_date','condition','hashtag','mention','url','location_state','location_sity', 'status',
        'disclosure', 'details', 'campaign_image', 'other_compensation'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id','user_id','updated_at','created_at'
    ];

    protected $createRules = [
        'brand_id' => 'Required|String',
        'identity' => 'Required|String',
        'title' => 'Required|String',
        'details' => 'Required',
        'social_network' => 'Required|In:Twitter,Vine,Instagram,YouTube',
        'type' => 'Required|In:public,private',
        'submission_deadline' => 'Required|date',
        'application_deadline' => 'Required|date',
        'guidelines' => 'Required',
        'media' => 'Required|In:image,video,gif,brand provided',
        'interests' => 'Required',
        'compensation' => 'Required|integer',
        'minimum_reach' => 'Required|integer',
        'posting_date' => 'Required|date',
//        'condition' => 'Required|In:project,public',
        'status' => 'Required|In:open,closed',
        'disclosure' => 'Required|In:#Sp,#sp,#SP,#sponsored,#Ad,#ad,#AD,null'
    ];

    protected $rules = [
        'title' => 'Required|String',
        'details' => 'Required',
        'social_network' => 'Required|In:Twitter,Vine,Instagram,YouTube',
        'type' => 'Required|In:public,private',
        'submission_deadline' => 'Required|date',
        'application_deadline' => 'Required|date',
        'guidelines' => 'Required',
        'media' => 'Required|In:image,video,gif,brand provided',
        'interests' => 'Required',
        'compensation' => 'Required|integer',
        'minimum_reach' => 'Required|integer',
        'posting_date' => 'Required|date',
//        'condition' => 'Required|In:project,public',
        'status' => 'Required|In:open,closed',
        'disclosure' => 'Required|In:#Sp,#sp,#SP,#sponsored,#Ad,#ad,#AD,null',
        'url' => 'string',
    ];


    /**
     * create Compaing Validator
     * @param $data
     * @return array
     */
    public function createValidator($original = false){

        $data = ($original == true) ? $this->original : $this->attributes;
        if(isset($this->attributes['interests']) && !empty($this->attributes['interests'])) {
            unset($this->createRules['interests']);
        }
        $v = \Validator::make($data, $this->createRules);
        if($v->fails()){
            $error = $v->messages()->toArray();

            if(isset($this->guidelines['text'])) {

            } else if(isset($this->guidelines[0])) {
                foreach ($this->guidelines as $key => $guidline) {
                    if (empty($guidline['text'])) {
                        $error['guidelines'] = ['The guidline field is required.'];
                    }
                }
            }

            return $this->errorHandler($error);
        }

        if(is_array($this->guidelines)) {
            if(isset($this->guidelines['text'])) {

            } else if(isset($this->guidelines[0])) {
                foreach ($this->guidelines as $key => $guidline) {
                    if (empty($guidline['text'])) {
                        $error['guidelines'] = ['The guidline field is required.'];
                    }
                }
            }
        }
        if(isset($error)) {
            return $this->errorHandler($error);
        }

        return $v->passes();
    }

    /**
     * Get all campaing (Admin)
     * @param $data
     * @return array
     */
    public function getCampaignList($data, $user = null, $brand_id = null)
    {
        $privateUsers = false;

        $request = Campaign::leftJoin('psa_brands', 'psa_brands.identity', '=', 'psa_campaign.brand_id');
        /* Filtering */

        /* * Search */
        $aColumns = array('psa_brands.brand_name', 'psa_campaign.title',
            'psa_campaign.social_network', 'psa_campaign.type', 'psa_campaign.submission_deadline',
            'psa_campaign.application_deadline', 'psa_campaign.guidelines', 'psa_campaign.media',
            'psa_campaign.interests', 'psa_campaign.compensation', 'psa_campaign.condition',
            'psa_campaign.minimum_reach', 'psa_campaign.posting_date',
        );
        if (isset($data['search']) && $data['search'] != "") {
            $request->where(function ($query) use ($aColumns, $data) {
                    for ($i = 0; $i < count($aColumns); $i++) {
                        $query->orWhere($aColumns[$i], 'like', '%' . $data['search'] . '%');
                    }
                });
            $searchQuery = $data['search']. '*';
        }

        if (isset($data['filters']) && $data['filters'] != "") {
            $filters = $data['filters'];
            if (isset($filters['socials']) && $filters['socials'] != "") {
                $socials = $filters['socials'];
                $whereSocials = [];
                $request->where(function ($query) use ($user, $brand_id) {
                    if($brand_id != null) {
                        $query->where(function ($query) {
                            $query->where('psa_campaign.status', '=', \Config::get('constants.CAMPAIGN_STATUS_OPEN'));
                            $query->where('psa_campaign.visibility', '=', \Config::get('constants.CAMPAIGN_VISIBILITY_PUBLISHED'));

                        });
                    }
                });

                foreach ($socials as $socialId => $val) {
                    if (($socialId == 'all') && ($val == 'true')) {
                        $request->where(function ($query) {
                            $query->orWhere('psa_campaign.social_network', '=', \Config::get('constants.CAMPAIGN_SOCIAL_YOUTUBE'));
                            $query->orWhere('psa_campaign.social_network', '=', \Config::get('constants.CAMPAIGN_SOCIAL_TWITTER'));
                            $query->orWhere('psa_campaign.social_network', '=', \Config::get('constants.CAMPAIGN_SOCIAL_INSTAGRAM'));
                            $query->orWhere('psa_campaign.social_network', '=', \Config::get('constants.CAMPAIGN_SOCIAL_VINE'));
                        });
                        break;
                    }
                    if ($val == 'true') {
                        $whereSocials[] = $socialId;
                    }
                }
                $request->where(function ($query) use ($whereSocials) {

                    foreach($whereSocials as $whereSocial) {
                        $query->orWhere('psa_campaign.social_network', '=', $whereSocial);
                    }
                });
            }

            if (isset($filters['interests']) && $filters['interests'] != "") {
                $interests = $filters['interests'];
                $request->leftJoin('psa_interest_campaign', 'psa_interest_campaign.campaign_id', '=', 'psa_campaign.identity');
                $request->where(function ($query) use ($interests) {
                    foreach ($interests as $interestId => $val) {
                        if (($interestId == 'all') && ($val == 'true')) {
                            $allInterests = Interests::where('status','=', \Config::get('constants.INTEREST_ACTIVE'))->get();
                            foreach($allInterests as $interest) {
                                $query->orWhere('psa_interest_campaign.interest_id', '=', $interest);
                            }
                            break;
                        }
                        if ($val == 'true') {
                            $query->orWhere('psa_interest_campaign.interest_id', '=', $interestId);
                        }
                    }
                });
            }
        }
        if(isset($brand_id)) {
            $request->where('psa_campaign.brand_id', '=', $brand_id);
        }
        if(isset($user) && $user->isInfluencer()) {
            $request->where('psa_campaign.visibility', '=', \Config::get('constants.CAMPAIGN_VISIBILITY_PUBLISHED'));
            $request->where('psa_campaign.type', '=', 'public');
        }

        /* Group */
        $request->groupBy('psa_campaign.id');

        /* Ordering */
        if (isset($data['order_by'])) {
            if(isset($data['order_direction']) && ($data['order_direction'] == 'desc')) {
                $direction = 'DESC';
            } else {
                $direction = 'ASC';
            }

            $request->orderBy('psa_campaign.'.$data['order_by'], $direction);
        }

        if(isset($data['length']) && isset($data['page'])) {
            $take = $data['length'];
            $skip = $data['length'] * ($data['page'] - 1);
            $paginate = $request->paginate($take);
            $campaigns = $request->take($take)->skip($skip)->get($this->fields);
        } else {
            $paginate = $request->paginate();
            $campaigns = $request->get($this->fields);
        }

        foreach($campaigns as $campaign) {
            $campaign->interests = $this->getCampaignInterests($campaign->identity);
            if(!empty($campaign->guidelines)) {
                $campaign->guidelines = unserialize($campaign->guidelines);
            }
        }

        $recordsFiltered = $paginate->total();

        $recordsTotal = count($campaigns);

        return array($campaigns, $recordsTotal, $recordsFiltered);

    }

    /**
     * Get campaign by Brand
     * @param $data
     * @param $user
     * @return array
     */
    public function getCampaignListByBrand($data,$user){

        $brand = Brands::where('email','=',$user->email)->first();

        if(empty($data)){
            $result = $this->where('brand_id','=',$brand->identity)->get();
        }else {
            $take = $data['length'];
            $skip = $take * ($data['page'] - 1);

            $result = $this->where('brand_id','=',$brand->identity)
                ->orderBy($data['order_by'],$data['order_direction'])
                ->take($take)
                ->skip($skip)
                ->get();
        }

        $recordsFiltered = $this->where('brand_id','=',$brand->identity)->count();
        $recordsTotal = !empty($result)?count($result):0;

        return array($result,$recordsTotal,$recordsFiltered);
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

    public function isInvitable() {

        if(!isset($this)) {
            return 'Campaign not found';
        }
        if($this->status != \Config::get('constants.CAMPAIGN_STATUS_OPEN')) {
            return 'Campaign status needs to be opened';
        }

        if($this->visibility != \Config::get('constants.CAMPAIGN_VISIBILITY_PUBLISHED')) {
            return 'Campaign visibility needs to be published';
        }

        $application_deadline = new \DateTime($this->application_deadline);
        $current_date = new \DateTime();
        $diff = $application_deadline->diff($current_date);

        if($diff->invert == false) {
            return 'Deadline is out';
        }

        return true;
    }

    public function save_campaign_interests($campaign, $data) {

        if ($campaign && $data['interests']) {
            $sql = "INSERT INTO `psa_interest_campaign` ";
            $sql.= " (`campaign_id`, `interest_id`) VALUES ";

            for ($i = 0; $i < count($data['interests']); $i++) {
                $delimiter = ( $i+1 !== count($data['interests']) ) ? ', ' : ' ';
                $sql.= sprintf(
                    " ('%s', '%s')%s",
                    $campaign,
                    is_array($data['interests'][$i]) ? $data['interests'][$i]['identity'] :$data['interests'][$i],
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

    public function delete_campaign_interests($campaign) {
        $result = \DB::table('psa_interest_campaign')->where('campaign_id', '=', $campaign);
        return $result->delete();
    }

    /**
     * Update campaign detail
     * @param string $identity
     * @param string $filename
     * @return mixed
     */
    public function updateCampaignDetail($identity, $filename)
    {
        $result = Campaign::where('identity', '=', $identity)->first();
        $result->campaign_detail = $filename;
        
        return $result->save();
    }

    public function getCampaignInterests($identity)
    {
        $interests = \DB::table('psa_interest_campaign')
            ->where('campaign_id', '=', $identity)
            ->join('psa_interests','psa_interests.identity', '=', 'psa_interest_campaign.interest_id')
            ->get();
        $result = [];
        foreach($interests as $key => $interest) {
            $result[$key]['identity'] = $interest->interest_id;
            $result[$key]['interest_name'] = $interest->interest_name;
            $result[$key]['status'] = $interest->status;
        }
        return $result;

    }

    public function getBrandCampaigns()
    {
        $brand = Brands::where('email','=',Auth::user()->email)->first();

        if(empty($data)){

            $campaigns = Campaign::where('psa_campaign.brand_id','=',$brand->identity)
                ->groupBy('psa_campaign.identity')
                ->get(array('psa_campaign.*'));
                foreach($campaigns as $campaign) {
                    $campaign['influencers_to_review'] = InfluencersCampaign::join('psa_campaign', 'psa_influencers_campaign.campaign_id', '=', 'psa_campaign.identity')
                        ->where('psa_campaign.brand_id','=',$brand->identity)
                        ->where('psa_campaign.identity','=',$campaign->identity)
                        ->where(function ($query) {
                        $query->orWhere('psa_influencers_campaign.status', '=', config('constants.INFLUENCER_CAMPAIGN_STATUS_INVITED'));
                        $query->orWhere('psa_influencers_campaign.status', '=', config('constants.INFLUENCER_CAMPAIGN_STATUS_APPLIED'));
                    })
                        ->groupBy('psa_influencers_campaign.influencer_id')->paginate()->total();

                    $campaign['posts_to_review'] = InfluencersCampaign::join('psa_campaign', 'psa_influencers_campaign.campaign_id', '=', 'psa_campaign.identity')
                        ->join('psa_posts', 'psa_influencers_campaign.post_identity', '=', 'psa_posts.identity')
                        ->where(function ($query) {
                            $query->orWhere('psa_posts.status', '=', config('constants.POST_STATUS_CREATED'));
                            $query->orWhere('psa_posts.status', '=', config('constants.POST_STATUS_PENDING'));
                        })
                        ->where('psa_campaign.brand_id','=',$brand->identity)
                        ->where('psa_campaign.identity','=',$campaign->identity)
                        ->where('psa_influencers_campaign.status', '=', config('constants.INFLUENCER_CAMPAIGN_STATUS_APPLICATION_ACCEPTED'))

                        ->groupBy('psa_influencers_campaign.post_identity')->paginate()->total();

                }



        }else {
            $take = $data['length'];
            $skip = $take * ($data['page'] - 1);

            $campaigns = $this->where('brand_id','=',$brand->identity)
                ->orderBy($data['order_by'],$data['order_direction'])
                ->take($take)
                ->skip($skip)
                ->get();
        }


        $recordsFiltered = InfluencersCampaign::join('psa_campaign', 'psa_influencers_campaign.campaign_id', '=', 'psa_campaign.identity')
            ->where('psa_campaign.brand_id','=',$brand->identity)
            ->where(function ($query) {
                $query->orWhere('psa_influencers_campaign.status', '=', config('constants.INFLUENCER_CAMPAIGN_STATUS_INVITED'));
                $query->orWhere('psa_influencers_campaign.status', '=', config('constants.INFLUENCER_CAMPAIGN_STATUS_APPLIED'));
            })->paginate()->total();
        $recordsTotal = !empty($result)?count($campaigns):$recordsFiltered;

        return array($campaigns,$recordsTotal,$recordsFiltered);
    }

    public function otherCompensation($influencer_id, $campaign_id)
    {
        $result = InfluencersCampaign::where('influencer_id','=',$influencer_id)
            ->where('campaign_id','=',$campaign_id)
            ->where(function ($query) {
                $query->orWhere('status', '=', config('constants.INFLUENCER_CAMPAIGN_STATUS_INVITED'));
                $query->orWhere('status', '=', config('constants.INFLUENCER_CAMPAIGN_STATUS_APPLIED'));
                $query->orWhere('status', '=', config('constants.INFLUENCER_CAMPAIGN_STATUS_APPLICATION_ACCEPTED'));
            })
            ->first();
        if(is_null($result)){
            return false;
        }
        return $result;
    }

}