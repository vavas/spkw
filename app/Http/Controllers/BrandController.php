<?php
/**
 * Created by PhpStorm.
 * User: QA
 * Date: 11.03.2016
 * Time: 13:59
 */

namespace App\Http\Controllers;

use App\Brands;
use App\Campaign;
use App\Influencers;
use App\InfluencersCampaign;
use App\SocialAccount;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\URL;
use Webpatser\Uuid\Uuid;

class BrandController extends Controller
{

    function __construct( Brands $brands){

        $this->middleware('jwt.auth');
        $this->middleware('admin',['only' => ['getBrandList','createBrand']]);
        $this->middleware('brand',['only' => ['brandViewCampaigns']]);

        $this->brands = $brands;
    }



    /**
     * @SWG\GET(path="/get-brand/{identity}",
     *  tags={"Operations with Brands"},
     *  summary="Perform Brand",
     *  description="Action Get Brand",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining a brand ID (e64d0697-515b-4f8d-9041-168984931fde)",
     *     example="e64d0697-515b-4f8d-9041-168984931fde",
     *     required=true
     *   ),
     *     @SWG\Response(response="200", description="Return brand or error message")
     * )
     */
    public function getBrand($identity){

        if(Auth::user()->isBrand()) {
            $model = User::getUserByBrandIdentify($identity);
        }
        if(Auth::user()->isAdmin()) {
            $model = Brands::where('identity','=',$identity)->first();
        }

        if(!is_null($model)){
            return $this->response(true,$model);
        }
        return $this->response(false, ['message' => 'Brand not found']);
    }

    /**
     * @SWG\Post(path="/del-brand",
     *  tags={"Operations with Brands"},
     *  summary="Perform Delete Brand",
     *  description="Action Delete Brand",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="find object",
     *     description="JSON Object which Delete Brand.",
     *     required=true,
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="identity", type="string", example="81952d0a-7c2e-46cf-9c4e-70428c16fdb2",description="Brand unique identificator"),)
     *     ),
     *     @SWG\Response(response="200", description="Rerurn brand or error message")
     * )
     */
    public function delBrand(){
        $data = Input::all();

        if(!isset($data['identity'])){
            return $this->response(false,['message' => 'Brand not found']);
        }

        $model = Brands::where('identity','=',$data['identity'])->first();
        $user = User::where('email','=',$model->email)->first();

        if(!is_null($model)){
            $model->delete();
            $user->delete();
            return $this->response(true, ['message' => 'Brand removed']);
        }
        return $this->response(false,['message' => 'Brand not found']);
    }


    /**
     * @SWG\GET(path="/get-influencers-list",
     *   tags={"Operations with Influencer"},
     *   summary="Perform Influencer list",
     *   description="Return Influencer list",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="Filter Influencer query-string parameters",
     *     description="page=1&length=10&order_by=identity&order_direction=desc&
    search=&filters[socials][all]=false&filters[socials][youtube]=false& filters[interests][all]=false&filters[interests][8958920c-fcb3-4389-a37c-9632d1f6629d]=true&
    filters[gender][male]=true&
    filters[gender][female]=true&
    filters[age][all]=false&
    filters[age][a37_42]=false&
    filters[reach][all]=false&
    filters[reach][r5_10]=false",
     *     required=false,
     *     @SWG\Schema(
     *         type="string", example=""
     *     )
     *    ),
     *
     *   @SWG\Response(response="200", description="return Influencer list or Error")
     * )
     */
    public function getInfluencersList(){
        $data = Input::all();
        $Influencer = new Influencers();
        $user = Auth::user();
        list($Influencers,$recordsTotal,$recordsFiltered) =  $Influencer->getInfluencersList($data, $user);

        return $this->response(true,$Influencers,$recordsTotal,$recordsFiltered);
    }


    /**
     * @SWG\Post(path="/invite-influencer-to-campaign",
     *   tags={"Operations with Brands"},
     *   summary="Perform invite Influencer to Campaign",
     *   description="The campaign invites influencer. Parameter - id of the Influencer",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="invite influencer object",
     *     description="JSON Object which invite Influencer",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="influencer_id", type="string", example="56503db8-af1d-4c9e-9a3c-95a5f851144d", description="Influencer unique identificator"),
     *         @SWG\Property(property="campaign_id", type="string", example="56503db8-af1d-4c9e-9a3c-95a5f851144d", description="Compaign unique identificator"),
     *         @SWG\Property(property="compensation", type="string", example="100", description="Compensation provided per user. This will be a dollar amount $USD"),
     *         @SWG\Property(property="consideration", type="string", example="consideration", description="A text entry for a product provided as consideration per user")
     *     )
     *    ),
     *   @SWG\Response(response="200", description="The campaign invites influencer")
     * )
     */
    public function inviteInfluencerToCampaign(){
        $data = Input::all();

        if(!(Auth::user()->isBrand() || Auth::user()->isAdmin())) {
            return $this->response(false,['message' => 'Only Admin or Brand can invite influencers']);
        }

        $InfluencerCampaign = new InfluencersCampaign($data);
        $validator = $InfluencerCampaign->validator();
        if(!is_bool($validator)){
            return $this->response(false,$validator);
        }
        $campaign = Campaign::where('identity', '=', $InfluencerCampaign->campaign_id)->first();
        $influencer = SocialAccount::where('user_identity', '=', $data['influencer_id'])->first();

        if($influencer->total_reach < $campaign->minimum_reach) {
            return $this->response(false,['message' => 'Influencer total reach(' .$influencer->total_reach .') less than
             Campaign minimum reach(' . $campaign->minimum_reach .')']);
        }

        $check = $InfluencerCampaign->inviteToCampaign();
        if(!$check){
            return $this->response(false,['message' => 'Your application registered previously. Expect the answer']);
        }

        //If Campaign temporarily stop accepting applicants
        if(!is_bool($campaign->isInvitable())) {
            return $this->response(false, ['message' => $campaign->isInvitable()]);
        }

        $InfluencerCampaign->save();

        return $this->response(true,['message' => 'Inluencer successfully invited. Expect the answer. Number invitations - '. $InfluencerCampaign->identity]);
    }

    /**
     * @SWG\Get(path="/brand-accept-application/{identity}",
     *   tags={"Operations with Brands"},
     *   summary="Perform to accept the application",
     *   description="The brand takes the request to the campaign. Parameter - id invitations",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining a invitation ID (e64d0697-515b-4f8d-9041-168984931fde)",
     *     example="e64d0697-515b-4f8d-9041-168984931fde",
     *     required=true
     *   ),
     *   @SWG\Response(response="200", description="To create a campaign brand. You must choose a brand to create a campaign")
     * )
     */
    public function brandAcceptApplication($identity){

        $model = InfluencersCampaign::where('identity','=',$identity)->first();

        if(is_null($model) or $model->app_brand != 'invitation'){
            return $this->response(false,['message' => 'The invitation is not really']);
        }

        $model->app_brand = 'approved';
        $model->status = 'application accepted';
        $model->save();

        return $this->response(true,['message' => 'The invitation was accepted']);
    }

    /**
     * @SWG\Get(path="/brand-reject-application/{identity}",
     *   tags={"Operations with Brands"},
     *   summary="Perform to reject the application campaign",
     *   description="Brand rejects the request to the campaign. Parameter - id invitations",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining a invitations ID (e64d0697-515b-4f8d-9041-168984931fde)",
     *     example="e64d0697-515b-4f8d-9041-168984931fde",
     *     required=true
     *   ),
     *   @SWG\Response(response="200", description="To create a campaign brand. You must choose a brand to create a campaign")
     * )
     */
    public function brandRejectApplication($identity){
        $model = InfluencersCampaign::where('identity','=',$identity)->first();

        if(is_null($model) or $model->app_brand != 'invitation'){
            return $this->response(false,['message' => 'The invitation is not really']);
        }

        $model->app_brand = 'rejected';
        $model->status = 'application declined';
        $model->save();

        return $this->response(true,['message' => 'Invitation declined']);
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
     * @SWG\Get(path="/brand-view-campaigns",
     *   tags={"Operations with Brands"},
     *   summary="Brand can view his own campaigns",
     *   description="Brand can view his own campaigns.",
     *   @SWG\Response(response="200", description="Gets campaigns list")
     * )
     */
    public function brandViewCampaigns()
    {
        $campaign = new Campaign();
        list($campaigns,$recordsTotal,$recordsFiltered) = $campaign->getBrandCampaigns();
        return $this->response(true,$campaigns,$recordsTotal,$recordsFiltered);
    }
}
