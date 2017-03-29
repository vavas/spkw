<?php
/**
 * Created by PhpStorm.
 * User: QA
 * Date: 16.03.2016
 * Time: 10:29
 */

namespace App\Http\Controllers;

use App\Brands;
use App\Providers\AppServiceProvider;
use App\Upload;
use App\User;
use App\Posts;
use App\InfluencersCampaign;
use Auth;
use App\Campaign;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Webpatser\Uuid\Uuid;
use Thujohn\Twitter\Facades\Twitter;
use App\Policies\CampaignPolicy;
use Gate;

class PostController extends Controller
{

    function __construct(){
        $this->middleware('jwt.auth', ['except' => 'postFeeds']);
        $this->middleware('brand', ['only' => array('brandAcceptPost', 'brandRejectPost', 'schedulePost', 'brandPostsList')]);
    }

    /**
     * @SWG\Post(path="/create-post",
     *   tags={"Operations with Posts"},
     *   summary="Perform Create Post",
     *   description="To create a campaign post",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="Create Post object",
     *     description="JSON Object which Create Post",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="campaign_id", type="string", example="07fa523c-98bb-4951-8d6e-60646001fba9"),
     *         @SWG\Property(property="text", type="string", example="New Post"),
     *         @SWG\Property(property="image_url", type="string", example="https://s3-us-west-2.amazonaws.com/sparkwoo-uploads/uploads/9f9886f6bd8c9a4517537854d4917778.jpg"),
     *         @SWG\Property(property="video_url", type="string", example="http://dev/uploads/093095da2b952dbcc90e4ce1b6080dd0.mp4")
     *     )
     *    ),
     *   @SWG\Response(response="200", description="To create a campaign brand. You must choose a brand to create a campaign")
     * )
     */
    public function createPost(Campaign $campaign){

        $data = Input::all();
        $campaignData = $campaign->where('identity','=',$data['campaign_id'])->first();
        
        if (Gate::denies('create-post', [$campaignData, $data])) {
            return $this->response(false, [
                    'message' => sprintf('You should be upload %s', $campaignData->media)
                ]
            );
        }
        /**Check exist url in message**/
        if (Gate::denies('check-url-campaign', [$campaignData, $data['text']])) {
            return $this->response(false, [
                    'message' => sprintf('You should use %s in your message', $campaignData->url)
                ]
            );
        }
        /**Check exist all has tags in message**/
        if (Gate::denies('check-hash-tags-campaign', [$campaignData, $data['text']])) {
            return $this->response(false, [
                    'message' => sprintf('You should use all hash tags in your message')
                ]
            );
        }
        /**Check exist all mention in message**/
        if (Gate::denies('check-mention-campaign', [$campaignData, $data['text']])) {
            return $this->response(false, [
                    'message' => sprintf('You should use all mention in your message')
                ]
            );
        }

        if(is_null($campaign->first()->id)){
            return $this->response(false,['message' => 'Campaign not found']);
        }

        $model = InfluencersCampaign::where('campaign_id','=',$data['campaign_id'])
            ->where('influencer_id','=',Auth::user()->identity)
            ->first();
        if(is_null($model)){
            return $this->response(false,['message' => 'Influencer application accepted - false']);
        }

        $checkPost = Posts::checkExistPost(Auth::user()->identity, $data['campaign_id'])->exists();
        if ($checkPost) {
            return $this->response(false,['message' => 'Post for this campaign already exist']);
        }

        $posts = new Posts($data);

        $posts->identity = $this->getIdentity();
        $posts->influencer_id = Auth::user()->identity;
        $validate = $posts->createValidator($campaign->social_network);

        if(!is_bool($validate)){
            return $this->response(false,$validate);
        }
        if(isset($data['image'])) {
            $posts->image_url = $data['image'];
        }
        $posts->status = config('constants.POST_STATUS_CREATED');
        $posts->save();

        $model->post_identity = $posts->identity;
        $model->save();


        return $this->response(true,Posts::find($posts->id));
    }
    /**
     * @SWG\Post(path="/resubmit-post/{identity}",
     *   tags={"Operations with Posts"},
     *   summary="Perform Resubmit Post",
     *   description="To resubmit a campaign post",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining a post identity (da4fab22-51b6-4581-b396-5ae34dcb8c13)",
     *     example="da4fab22-51b6-4581-b396-5ae34dcb8c13",
     *     required=true
     *   ),
     *      @SWG\Parameter(
     *     in="body",
     *     name="Resubmit Post object",
     *     description="JSON Object which Resubmit Post",
     *     required=true,
     *
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="campaign_id", type="string", example="07fa523c-98bb-4951-8d6e-60646001fba9"),
     *         @SWG\Property(property="text", type="string", example="New Post"),
     *         @SWG\Property(property="image_url", type="string", example="http://dev/uploads/093095da2b952dbcc90e4ce1b6080dd0.jpg"),
     *         @SWG\Property(property="video_url", type="string", example="http://dev/uploads/093095da2b952dbcc90e4ce1b6080dd0.mp4")
     *     )
     *    ),
     *   @SWG\Response(response="200", description="To create a campaign brand. You must choose a brand to create a campaign")
     * )
     * @param  string $identity
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resubmitPost($identity)
    {
        $data = Input::all();
        $model = new Posts();
        $campaign = new Campaign();
        $post = $model->where('identity','=',$identity)->where('status', '=', config('constants.POST_STATUS_REJECTED'))->first();

        if(is_null($post)){
            return $this->response(false,['message' => 'Post not found']);
        }
        if(Auth::user()->identity != $post->influencer_id) {
            return $this->response(false,['message' => 'Influencer post not found']);
        }
        $campaignData = $campaign->where('identity','=',$data['campaign_id'])->first();

        if (Gate::denies('create-post', [$campaignData, $data])) {
            return $this->response(false, [
                    'message' => sprintf('You should be upload %s', $campaignData->media)
                ]
            );
        }
        /**Check exist url in message**/
        if (Gate::denies('check-url-campaign', [$campaignData, $data['text']])) {
            return $this->response(false, [
                    'message' => sprintf('You should use %s in your message', $campaignData->url)
                ]
            );
        }
        /**Check exist all has tags in message**/
        if (Gate::denies('check-hash-tags-campaign', [$campaignData, $data['text']])) {
            return $this->response(false, [
                    'message' => sprintf('You should use all hash tags in your message')
                ]
            );
        }
        /**Check exist all mention in message**/
        if (Gate::denies('check-mention-campaign', [$campaignData, $data['text']])) {
            return $this->response(false, [
                    'message' => sprintf('You should use all mention in your message')
                ]
            );
        }

        $post->fill($data);
        $post->status = config('constants.POST_STATUS_CREATED');
        $post->image_url = isset($data['image_url']) ? $data['image_url'] : '';
        $post->video_url = isset($data['video_url']) ? $data['video_url'] : '';
        $post->text = $data['text'];
        $post->save();
        return $this->response(true,Posts::find($post->id));
    }

    /**
     * @SWG\GET(path="/brand-posts-list",
     *   tags={"Operations with Posts"},
     *   summary="Perform Posts list",
     *   description="Return Posts list",
     *   produces={"application/json"},
     *
     *   @SWG\Response(response="200", description="return Posts list or Error")
     * )
     */
    public function brandPostsList()
    {
        $posts = new Posts();
        $response = $posts->getBrandPosts();
        return $this->response(true,$response);
    }

    /**
     * @SWG\GET(path="/influencer-post-data/{identity}",
     *   tags={"Operations with Posts"},
     *   summary="Influencer Posts list",
     *   description="Return Posts list",
     *   produces={"application/json"},
     *
     *   @SWG\Response(response="200", description="return Posts list or Error")
     * )
     */
    public function influencerPostData($identity)
    {
        $posts = new Posts();
        $userIdentity = Auth::user()->identity;

        $response = $posts->getInfluencerPosts($identity, $userIdentity);
        
        return $this->response(true,$response);
    }


    /**
     * @SWG\Post(path="/del-post",
     *   tags={"Operations with Posts"},
     *   summary="Perform delete Post",
     *   description="To delete a campaign post",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="Delete Post object",
     *     description="JSON Object which delete Post",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="identity", type="string", example="56503db8-af1d-4c9e-9a3c-95a5f851144d", description="Post unique identificator"),
     *     )
     *    ),
     *   @SWG\Response(response="200", description="To create a campaign post. You must choose a campaign to create a post")
     * )
     */
    public function delPost(){
        $data = Input::all();

        if(empty($data['identity'])){
            return $this->response(false,['message' => 'Post not found']);
        }

        $model = Post::where('identity','=',$data['identity'])->first();

        if(is_null($model)){
            return $this->response(false,['message' => 'Post not found']);
        }
        $model->delete();
        return $this->response(true,['message' => 'Post removed']);
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
            ]), 403);
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
     * @SWG\Post(path="/brand-accept-post/{identity}",
     *   tags={"Operations with Posts"},
     *   summary="Perform to accept the post",
     *   description="Brand accept post. Parameter - identity of post",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining a post identity (da4fab22-51b6-4581-b396-5ae34dcb8c13)",
     *     example="da4fab22-51b6-4581-b396-5ae34dcb8c13",
     *     required=true
     *   ),

     *   @SWG\Response(response="200", description="To accept post. You must choose a post to create an acceptance")
     * )
     */
    public function brandAcceptPost(Campaign $modelCampaign, Posts $modelPosts, $identity)
    {
        $post = $modelPosts->where('identity','=',$identity)->first();
        if(is_null($post)){
            return $this->response(false,['message' => 'Post not found']);
        }
        $campaign = $modelCampaign->where('identity','=', $post->campaign_id)->first();
        $brand = Brands::where('email','=',Auth::user()->email)->first();

        if($campaign->brand_id != $brand->identity) {
            return $this->response(false,['message' => 'Campaign not found']);
        }

        $post->status = config('constants.POST_STATUS_APPROVED');
        $post->save();
        return $this->response(true,Posts::find($post->id));

    }

    /**
     * @SWG\POST(path="/brand-reject-post/{identity}",
     *   tags={"Operations with Posts"},
     *   summary="Perform to reject a post",
     *   description="Brand reject post. Parameter - identity of post",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining a post identity (da4fab22-51b6-4581-b396-5ae34dcb8c13)",
     *     example="da4fab22-51b6-4581-b396-5ae34dcb8c13",
     *     required=true
     *   ),
     *     @SWG\Parameter(
     *     in="body",
     *     name="Edit Post object",
     *     description="JSON Object which send reject reason",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="reason", type="string", example="Message",description="Reject reason"),
     *
     *     )
     *    ),
     *   @SWG\Response(response="200", description="To reject post. You must choose a post to reject")
     * )
     */
    public function brandRejectPost(Campaign $modelCampaign, Posts $modelPosts, $identity)
    {
        $data = Input::all();
        $post = $modelPosts->where('identity','=',$identity)->first();
        if(is_null($post)){
            return $this->response(false,['message' => 'Post not found']);
        }
        $campaign = $modelCampaign->where('identity','=', $post->campaign_id)->first();
        $brand = Brands::where('email','=',Auth::user()->email)->first();

        if($campaign->brand_id != $brand->identity) {
            return $this->response(false,['message' => 'Campaign not found']);
        }
        if($post->status == config('constants.POST_STATUS_APPROVED')) {
            return $this->response(false,['message' => 'Post already accepted']);
        }
        $post->reject_reason = $data['reason'];
        $post->status = config('constants.POST_STATUS_REJECTED');

        $post->save();
        return $this->response(true,Posts::find($post->id));
    }

    public function postFeeds()
    {
        $model = new Posts();
        $posts = $model->getPostsForFeed();
        $model->postFeeds($posts);
    }

    /**
     * @SWG\Post(path="/schedule-post/{identity}",
     *   tags={"Operations with Posts"},
     *   summary="Perform to schedule the post",
     *   description="Brand can schedule posts publishing . Parameter - identity of post",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining a post identity (da4fab22-51b6-4581-b396-5ae34dcb8c13)",
     *     example="da4fab22-51b6-4581-b396-5ae34dcb8c13",
     *     required=true
     *   ),
     *     @SWG\Parameter(
     *     in="body",
     *     name="scheduled datetime",
     *     description="JSON Object with scheduled datetime",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="publish_at", type="string", example="2016-05-30 12:52:28",description="Scheduled Datetime"),
     *          @SWG\Property(property="timezone", type="string", example="PDT",description="Pacific Time or Eastern Time"),
     *
     *     )
     *    ),

     *   @SWG\Response(response="200", description="Brand scheduled post publishing ")
     * )
     */
    public function schedulePost($identity)
    {

        $post = Posts::where('identity','=',$identity)
            ->where('status', '=', config('constants.POST_STATUS_APPROVED'))
            ->first();

        if(is_null($post)){
            return $this->response(false, ['message' => 'Post does not exist']);
        }
        if($post->posted == 1) {
            return $this->response(false, ['message' => 'Post already published']);
        }

        $campaign = Campaign::where('identity','=', $post->campaign_id)->first();
        $brand = Brands::where('email','=',Auth::user()->email)->first();

        if (is_null($brand)) {
            return $this->response(false, ['message' => 'Brand not found']);
        }

        if($campaign->brand_id != $brand->identity) {
            return $this->response(false,['message' => 'Campaign not found']);
        }

        $post->publish_at = Input::get('publish_at');
        $post->timezone = Input::get('timezone');
        $post->save();

        return $this->response(true, $post);

    }



}