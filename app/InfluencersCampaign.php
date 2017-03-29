<?php
/**
 * Created by PhpStorm.
 * User: QA
 * Date: 24.03.2016
 * Time: 12:39
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use Webpatser\Uuid\Uuid;
use Config;
use Carbon\Carbon;
use App\Campaign;
use App\Influencers;

class InfluencersCampaign extends Model
{

    protected $table = 'psa_influencers_campaign';

    protected $fillable = [
        'influencer_id','campaign_id', 'compensation', 'consideration', 'message'
    ];

    protected $hidden = [
        'id','updated_at','created_at'
    ];

    protected $rules = [
        'influencer_id'  => 'Required|string',
        'campaign_id'  => 'Required|string',
    ];

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

    /**
     * INFLUENCER FUNCTION APPLY TO CAMPAIGN
     */
    public function applyInCampaign($campaignIdentity,$userIdentity){

        if($this->checkInfluencerExistence($campaignIdentity,$userIdentity)){
            return false;
        }

        $this->campaign_id = $campaignIdentity;
        $this->influencer_id = $userIdentity;
        $this->app_influencer = 'approved';
        $this->app_brand = 'invitation';
        $this->status = config('constants.INFLUENCER_CAMPAIGN_STATUS_APPLIED');
        $this->identity = $this->getIdentity();

        return true;
    }


    public function checkInfluencerExistence($campaignIdentity,$userIdentity){
        $check = $this->where('campaign_id','=',$campaignIdentity)
            ->where('influencer_id', '=',$userIdentity)
            ->first();

        if(is_null($check) or $check->app_brand == 'rejected'){
            return false;
        }
        return true;
    }

    /**
     * @return Campaign|null
     */
    public function getEndedCampaigns()
    {
        return $this
            ->leftJoin('psa_campaign', 'psa_influencers_campaign.campaign_id', '=', 'psa_campaign.identity')
            ->leftJoin('psa_users', 'psa_influencers_campaign.influencer_id', '=', 'psa_users.identity')
            ->leftJoin('psa_influencers', 'psa_users.email', '=', 'psa_influencers.email')
            ->where('psa_campaign.application_deadline','<=', Carbon::tomorrow())
            ->whereNull('psa_campaign.campaign_detail')
            ->get([
                'psa_influencers.paypal as recipient',
                'psa_influencers_campaign.compensation as payment_amount',
                'psa_influencers_campaign.influencer_id as customer_id',
                'psa_influencers_campaign.campaign_id as campaign_id'
            ])
            ;
    }

    /**
     * BRAND FUNCTION INVITE INFLUENCER
     */
    public function inviteToCampaign(){

        if($this->checkCampaignExistence()){
            return false;
        }
        $this->app_influencer = 'invitation';
        $this->app_brand = 'approved';
        $this->status = config('constants.INFLUENCER_CAMPAIGN_STATUS_INVITED');
        $this->identity = $this->getIdentity();


        return true;
    }

    public function checkCampaignExistence(){
        $check = $this->where('campaign_id','=',$this->campaign_id)
            ->where('influencer_id', '=',$this->influencer_id)
            ->first();
        if(is_null($check) or $check->app_influencer == 'rejected'){
            return false;
        }
        return true;
    }


    public function getInvitation($identity){

        return $this->join('psa_campaign','psa_influencers_campaign.campaign_id','=','psa_campaign.identity')
            ->where('influencer_id','=',$identity)
            ->where(function ($query) {
                $query->orWhere('psa_influencers_campaign.status', '=', config('constants.INFLUENCER_CAMPAIGN_STATUS_INVITED'));
                $query->orWhere('psa_influencers_campaign.status', '=', config('constants.INFLUENCER_CAMPAIGN_STATUS_APPLIED'));
                $query->orWhere('psa_influencers_campaign.status', '=', config('constants.INFLUENCER_CAMPAIGN_STATUS_APPLICATION_DECLINED'));
            })
            ->get(['psa_influencers_campaign.*','psa_campaign.title']);
    }


    /**
     * generate UIID4 token
     */
    protected function getIdentity(){
        $uids = Uuid::generate(4);
        return $uids->string;
    }

    public function getInfluencerCampaignStatus($user_identity, $campaign_identity)
    {
        $influencerCampaign = $this->where('psa_influencers_campaign.campaign_id', '=', $campaign_identity)
            ->where('psa_influencers_campaign.influencer_id', '=', $user_identity)
            ->first();

        $result['status'] = config('constants.INFLUENCER_CAMPAIGN_STATUS_NULL');
        $result['reason'] = config('constants.INFLUENCER_CAMPAIGN_STATUS_NULL_REASON');
        return $result;
    }
}