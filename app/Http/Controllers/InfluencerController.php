<?php
/**
 * Created by PhpStorm.
 * User: QA
 * Date: 24.03.2016
 * Time: 12:53
 */

namespace App\Http\Controllers;

use App\Campaign;
use App\Influencers;
use App\InfluencersCampaign;
use Webpatser\Uuid\Uuid;
use Auth;
use App\User;
use Illuminate\Support\Facades\Input;


class InfluencerController  extends Controller
{

    function __construct(){
        $this->middleware('jwt.auth');
    }

    /**
     * @SWG\Get(path="/show-invitation/{identity}",
     *   tags={"Operations with Influencer"},
     *   summary="Perform show invitation",
     *   description="Influencer can view your invitations. Parameter - Influencer ID",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining a Influencer ID (e64d0697-515b-4f8d-9041-168984931fde)",
     *     example="e64d0697-515b-4f8d-9041-168984931fde",
     *     required=true
     *   ),
     *   @SWG\Response(response="200", description="Gets an array of objects (prompt) or an error")
     * )
     */
    public function showInvitation($identity){

        $model = new InfluencersCampaign();

        $invitation = $model->getInvitation($identity);

        if(is_null($invitation)){
            return $this->response(false,['message' => 'You have no invitations']);
        }
        return $this->response(true,$invitation);
    }

    /**
     * @SWG\Get(path="/show-influencer-campaign/{identity}",
     *   tags={"Operations with Influencer"},
     *   summary="Perform show influencer campaign",
     *   description="Influencer can view campaigns in which it participates. Parameter - Influencer ID",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining a Influencer ID (e64d0697-515b-4f8d-9041-168984931fde)",
     *     example="e64d0697-515b-4f8d-9041-168984931fde",
     *     required=true
     *   ),
     *   @SWG\Response(response="200", description="Gets an array of objects (campaigns) or an error")
     * )
     */
    public function showInfluencerCampaign($identity){
        $model = new InfluencersCampaign();

        $campaign = $model->getCampaingListByInfluencer($identity);
        
        if(is_null($campaign)){
            return $this->response(false,['message' => 'You have no active campaigns']);
        }
        return $this->response(true,$campaign);
    }

    /**
     * @SWG\Get(path="/influencer-accept-invited/{identity}",
     *   tags={"Operations with Influencer"},
     *   summary="Perform to accept the invitation campaign",
     *   description="Influencer takes the invitation campaign. Parameter - id invitations",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining a invitation ID (e64d0697-515b-4f8d-9041-168984931fde)",
     *     example="e64d0697-515b-4f8d-9041-168984931fde",
     *     required=true
     *   ),
     *   @SWG\Response(response="200", description="Receives the message about successful adoption of prompt or error")
     * )
     */
    public function influencerAcceptInvited($identity){
        $model = InfluencersCampaign::where('identity','=',$identity)->first();

        if(is_null($model) or $model->app_influencer != 'invitation'){
            return $this->response(false,['message' => 'The invitation is not really']);
        }

        $model->app_influencer = 'approved';
        $model->status = config('constants.INFLUENCER_CAMPAIGN_STATUS_APPLICATION_ACCEPTED');
        $model->save();

        return $this->response(true,['message' => 'The invitation was accepted']);
    }

    /**
     * @SWG\Get(path="/influencer-reject-invited/{identity}",
     *   tags={"Operations with Influencer"},
     *   summary="Perform to reject the invitation campaign",
     *   description="Influencer rejects the invitation campaign. Parameter - id invitations",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining a invitations ID (e64d0697-515b-4f8d-9041-168984931fde)",
     *     example="e64d0697-515b-4f8d-9041-168984931fde",
     *     required=true
     *   ),
     *   @SWG\Response(response="200", description="Receives the success message or reject the invitation a mistake")
     * )
     */
    public function influencerRejectInvited($identity){
        $model = InfluencersCampaign::where('identity','=',$identity)->first();

        if(is_null($model) or $model->app_influencer != 'invitation'){
            return $this->response(false,['message' => 'The invitation is not really']);
        }

        $model->app_influencer = 'rejected';
        $model->status = config('constants.INFLUENCER_CAMPAIGN_STATUS_APPLICATION_DECLINED');
        $model->save();

        return $this->response(true,['message' => 'Invitation declined']);
    }


    /**
     * @SWG\Get(path="/apply-to-campaign/{identity}",
     *   tags={"Operations with Influencer"},
     *   summary="Perform apply Influencer to Campaign",
     *   description="The user submits a request in the campaign. Parameter - id of the Campaign",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining a campaign ID (e64d0697-515b-4f8d-9041-168984931fde)",
     *     example="e64d0697-515b-4f8d-9041-168984931fde",
     *     required=true
     *   ),
     *   @SWG\Response(response="200", description="Receives the message about successful application in the campaign or an error message")
     * )
     */
    public function applyToCampaign($identity){
        $campaign = Campaign::where('identity','=',$identity)->first();

        if(is_null($campaign)){
            return $this->response(false,['message' => 'Campaign not found']);
        }

        $InfluencerCampaign = new InfluencersCampaign();
        $check = $InfluencerCampaign->applyInCampaign($identity,Auth::user()->identity);

        if(!$check){
            return $this->response(false,['message' => 'Your application registered previously. Expect the answer']);
        }
        $InfluencerCampaign->save();

        return $this->response(true,['message' => 'You have successfully applied to participate in the campaign ' . $campaign->title .'. Await approval of the administrator of the campaign.
                                        The number of applications -'. $InfluencerCampaign->identity]);
    }

    /**
     * generate UIID4 token
     */
    protected function getIdentity(){
        $uids = Uuid::generate(4);
        return $uids->string;
    }

    /**
     * @param $status
     * @param array $message
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function response($status, $message = [],$recordsTotal = null,$recordsFiltered = null){
        if(!$status){
            return response(json_encode([
                'status' => false,
                'errors' => $message
            ], JSON_NUMERIC_CHECK), 403);
        }

        $return =[
            'status' => true,
            'data' => $message
        ];

        if(!is_null($recordsTotal) and !is_null($recordsFiltered)){
            $return['recordsTotal'] = $recordsTotal;
            $return['recordsFiltered'] = $recordsFiltered;
        }

        return response(json_encode($return), 200);
    }

    /**
     * @SWG\GET(path="/get-influencer/{identity}",
     *   tags={"Operations with Influencer"},
     *   summary="Perform view influencer profile",
     *   description="view influencer profile",
     *   produces={"application/json"},
     *   consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="User identity (3627510e-2821-4ef1-b669-8244c46350f6)",
     *     example="3627510e-2821-4ef1-b669-8244c46350f6",
     *     required=true
     *   ),
     *   @SWG\Response(response="200", description="User can see influencers profile")
     * )
     */
    public function getInfluencer($identity)
    {
        $user = User::where('identity','=',$identity)->first();
        $currentUser = Auth::user();
        if(is_null($user)){
            return $this->response(false,['message' => 'User not found']);
        }
        $influencer = new Influencers();
        $profile = $influencer->getInfluencer($user, $currentUser);

        return $this->response(true,$profile);
    }

    /**
     * @SWG\Get(path="/show-influencer-campaign-status/{identity}",
     *   tags={"Operations with Influencer"},
     *   summary="Perform show influencer campaign status",
     *   description="Influencer can view his campaign status. Parameter - Campaign ID",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining a Campaign ID (e64d0697-515b-4f8d-9041-168984931fde)",
     *     example="e64d0697-515b-4f8d-9041-168984931fde",
     *     required=true
     *   ),
     *   @SWG\Response(response="200", description="Gets objects (campaign) or an error")
     * )
     */
    public function showInfluencerCampaignStatus($identity)
    {
        $campaign = Campaign::where('identity','=',$identity)->first();
        $user = Auth::user();
        if(is_null($campaign)){
            return $this->response(false,['message' => 'Campaign not found']);
        }
        $model = new InfluencersCampaign();
        $status = $model->getInfluencerCampaignStatus($user->identity, $campaign->identity);

        return $this->response(true,$status);
    }

    public function editInfluencer($identity){
        $data = Input::all();
        $user = User::where('identity','=',$identity)->first();
        $currentUser = Auth::user();
        if(is_null($user)){
            return $this->response(false,['message' => 'User not found']);
        }
        $influencer = new Influencers();
        $profile = $influencer->getInfluencer($user, $currentUser);

        $profile->setRawAttributes($data);

        $validate = $profile->validate(true);

        if(!is_bool($validate)){
            return $this->response(false,$validate);
        }

        $profile->save();

//        $model->delete_campaign_interests($identity);
//
//        $result = $model->save_campaign_interests($identity, $data);
//
//        if(!$result) {
//            return $this->response(false, ['interests' => 'interest_id is invalid']);
//        } else if(isset($result['message'])) {
//            return $this->response(false, $result['message']);
//        }
//
//        $model->interests = $model->getCampaignInterests($identity);
        return $this->response(true);
    }

}