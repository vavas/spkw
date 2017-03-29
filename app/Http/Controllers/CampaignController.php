<?php
/**
 * Created by PhpStorm.
 * User: QA
 * Date: 16.03.2016
 * Time: 10:29
 */

namespace App\Http\Controllers;

use App\Brands;
use App\Influencers;
use App\Upload;
use App\User;
use App\InfluencersCampaign;
use Auth;
use App\Campaign;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Webpatser\Uuid\Uuid;
use Gate;

class CampaignController extends Controller
{

    function __construct( Campaign $campaign, Brands $brand ){
        $this->middleware('jwt.auth');

        $this->campaign = $campaign;
        $this->brand = $brand;
    }

    /**
     * @SWG\GET(path="/get-campaign-list",
     *   tags={"Operations with Campaign"},
     *   summary="Perform Campaign list",
     *   description="Return Campaign list",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="Filter Campaign query-string parameters",
     *     description="page=1& length=10& order_by=identity& order_direction=desc& filters[socials][all]=false& filters[socials][instagram]=true& filters[interests][all]=false&filters[interests][interest_id]=false",
     *     required=false,
     *     @SWG\Schema(
     *         type="string", example=""
     *     )
     *    ),
     *
     *   @SWG\Response(response="200", description="return Campaign list or Error")
     * )
     */
    public function getCampaignList(){

        $data = Input::all();


        $currentUser = Auth::user();

        if(Auth::user()->isBrand()){
            $brand = $this->brand->where('email','=',Auth::user()->email)->first();
            list($model,$recordsTotal,$recordsFiltered) =  $this->campaign->getCampaignList($data, $currentUser, $brand->identity);
        }else{
            list($model,$recordsTotal,$recordsFiltered) =  $this->campaign->getCampaignList($data, $currentUser);
//
        }

        return $this->response(true,$model,$recordsTotal,$recordsFiltered);
    }

    /**
     * @SWG\Post(path="/create-campaign",
     *   tags={"Operations with Campaign"},
     *   summary="Perform Create Campaign",
     *   description="To create a campaign brand",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="Create Campaign object",
     *     description="JSON Object which Create Campaign",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="brand_id", type="string", example="81952d0a-7c2e-46cf-9c4e-70428c16fdb2"),
     *         @SWG\Property(property="title", type="string", example="New Campaign"),
     *         @SWG\Property(property="social_network", type="string", example="Twitter"),
     *         @SWG\Property(property="type", type="string", example="public", description="Campaign type private or public"),
     *         @SWG\Property(property="visibility", type="string", example="draft", description="Visibility - draft or published"),
     *
     *         @SWG\Property(property="submission_deadline", type="date", example="2016-04-01"),
     *         @SWG\Property(property="application_deadline", type="date", example="2016-04-01"),
     *         @SWG\Property(property="guidelines", type="array", example={
                        @SWG\Property(text="guideline1"),
     *              }
     *         ),
     *         @SWG\Property(property="media", type="string", example="image",description="Campaign media dropdown 'image','video','gif','brand provided'"),
     *         @SWG\Property(property="interests", type="array", example={"8958920c-fcb3-4389-a37c-9632d1f6629d"}),
     *         @SWG\Property(property="compensation", type="integer", example="1"),
     *         @SWG\Property(property="minimum_reach", type="integer", example="100",description="the minimum number of subscribers"),
     *         @SWG\Property(property="posting_date", type="date", example="2016-04-01", ),
     *         @SWG\Property(property="status", type="string", example="open"),
     *         @SWG\Property(property="disclosure", type="string", example="#SP"),
     *         @SWG\Property(property="campaign_image", type="string", example="")
     *
     *     )
     *    ),
     *   @SWG\Response(response="200", description="To create a campaign brand. You must choose a brand to create a campaign")
     * )
     */
    public function createCampaign(){
        $data = Input::all();

        if(Auth::user()->isBrand()){
            $brand = Brands::where('email','=',Auth::user()->email)->first();
            $data['brand_id'] = $brand->identity;
        }

        $model = new Campaign($data);
        /**Check that posting deadline bigger that application/submission deadlines**/
        if (Gate::denies('check-deadline', [$model])) {
            return $this->response(false, [
                    'message' => sprintf('Post deadline should be bigger that Application, Submission deadlines')
                ]
            );
        }
        $model->identity = $this->getIdentity();

        $validate = $model->createValidator();

        if(!is_bool($validate)){
            return $this->response(false,$validate);
        }
        /**Check minimum reach campaign**/
        if (isset($data['minimum_reach'])) {
            $data['minimum_reach'] = preg_replace("/[^0-9]/","", $data['minimum_reach']);
        }

        $model->save();
        $model->delete_campaign_interests($model->identity);
        $result = $model->save_campaign_interests($model->identity, $data);


        $model->interests = $model->getCampaignInterests($model->identity);

        return $this->response(true,$model);
    }

    /**
     * @SWG\Post(path="/edit-campaign/{identity}",
     *   tags={"Operations with Campaign"},
     *   summary="Perform Edit Campaign",
     *   description="To Edit a campaign brand",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining a campaign ID (e64d0697-515b-4f8d-9041-168984931fde)",
     *     example="e64d0697-515b-4f8d-9041-168984931fde",
     *     required=true
     *   ),
     *     @SWG\Parameter(
     *     in="body",
     *     name="Edit Campaign object",
     *     description="JSON Object which Edit Campaign",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="title", type="string", example="New Campaign"),
     *         @SWG\Property(property="social_network", type="string", example="Twitter"),
     *         @SWG\Property(property="type", type="string", example="public", description="Campaign type private or public"),
     *         @SWG\Property(property="visibility", type="string", example="draft", description="Visibility - draft or published"),
     *         @SWG\Property(property="submission_deadline", type="date", example="2016-04-01"),
     *         @SWG\Property(property="application_deadline", type="date", example="2016-04-01"),
     *         @SWG\Property(property="guidelines", type="array", example={
     *                  @SWG\Property(text="guideline1"),
     *              }
     *         ),
     *         @SWG\Property(property="media", type="string", example="image",description="Campaign media dropdown 'image','video','gif','brand provided'"),
     *         @SWG\Property(property="interests", type="array", example={"Motorcycles"}),
     *         @SWG\Property(property="compensation", type="string", example="1"),
     *         @SWG\Property(property="minimum_reach", type="integer", example="100",description="the minimum number of subscribers"),
     *         @SWG\Property(property="posting_date", type="date", example="2016-04-01", ),
     *         @SWG\Property(property="hashtag", type="sring", example="#hashtag"),
     *         @SWG\Property(property="mention", type="sring", example=""),
     *         @SWG\Property(property="url", type="sring", example="http://my-campaign.com"),
    *          @SWG\Property(property="status", type="string", example="open"),
     *         @SWG\Property(property="disclosure", type="string", example="#SP"),
     *     )
     *    ),
     *   @SWG\Response(response="200", description="To create a campaign brand. You must choose a brand to create a campaign")
     * )
     */
    public function editCampaign($identity){

        $data = Input::all();

        $model = Campaign::where('identity','=',$identity)->first();
        $model->details = 'details';

        if(is_null($model)){
            return $this->response(false,['message' => 'Campaign not found']);
        }
        $user = User::find(Auth::user()->id);
        if(!$user->isAdmin() && ($model->visibility == \Config::get('constants.CAMPAIGN_VISIBILITY_PUBLISHED'))) {
            unset($data['visibility']);
        }
        $model->setRawAttributes($data);
        /**Check that posting deadline bigger that application/submission deadlines**/
        if (Gate::denies('check-deadline', [$model])) {
            return $this->response(false, [
                    'message' => sprintf('Post deadline should be bigger that Application, Submission deadlines')
                ]
            );
        }
        $validate = $model->createValidator(true);

        if(!is_bool($validate)){
            return $this->response(false,$validate);
        }

        $model->delete_campaign_interests($identity);

        $result = $model->save_campaign_interests($identity, $data);

        if(!$result) {
            return $this->response(false, ['interests' => 'interest_id is invalid']);
        } else if(isset($result['message'])) {
            return $this->response(false, $result['message']);
        }

        $model->interests = $model->getCampaignInterests($identity);
        return $this->response(true,$model);
    }

    /**
     * @SWG\GET(path="/get-campaign/{identity}",
     *   tags={"Operations with Campaign"},
     *   summary="Perform view Campaign",
     *   description="To view a campaign brand",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining a campaign ID (e64d0697-515b-4f8d-9041-168984931fde)",
     *     example="e64d0697-515b-4f8d-9041-168984931fde",
     *     required=true
     *   ),
     *   @SWG\Response(response="200", description="To create a campaign brand. You must choose a brand to create a campaign")
     * )
     */
    public function getCampaign($identity){

        $campaign = new Campaign();
        $model = $campaign->where('psa_campaign.identity','=',$identity)
            ->leftJoin('psa_brands', 'psa_brands.identity', '=', 'psa_campaign.brand_id')
            ->first($campaign->fields);

        if(is_null($model)){
            return $this->response(false,['message' => 'Campaign not found']);
        }
        if(Auth::user()->isBrand()) {
            $influencers = new Influencers();
            $model->influencers = $influencers->getInfluencersCampaign($identity);
        }
        $other_compensation = $campaign->otherCompensation(Auth::user()->identity, $model->identity);
        $model->compensation = (int) $model->compensation;

        return $this->response(true,$model);
    }

    /**
     * @SWG\Post(path="/del-campaign",
     *   tags={"Operations with Campaign"},
     *   summary="Perform delete Campaign",
     *   description="To delete a campaign brand",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="Delete Campaign object",
     *     description="JSON Object which delete Campaign",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="identity", type="string", example="56503db8-af1d-4c9e-9a3c-95a5f851144d", description="Compaign unique identificator"),
     *     )
     *    ),
     *   @SWG\Response(response="200", description="To create a campaign brand. You must choose a brand to create a campaign")
     * )
     */
    public function delCampaign(){
        $data = Input::all();
        $this->campaign->delete();
        return $this->response(true,['message' => 'Campaign removed']);
    }



    /**
     * generate UIID4 token
     */
    protected function getIdentity(){
        $uids = Uuid::generate(4);
        return $uids->string;
    }



}