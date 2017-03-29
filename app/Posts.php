<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Thujohn\Twitter\Facades\Twitter;
use Config;

use Google_Service_YouTube_VideoSnippet;
use Google_Service_YouTube_VideoStatus;
use Google_Service_YouTube_Video;
use Google_Service_YouTube;
use Google_Http_MediaFileUpload;
use Google_Client;
use Auth;


class Posts extends Model
{
    protected $table = 'psa_posts';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = ['identity','influencer_id','campaign_id','text', 'status', 'image_url', 'video_url'];

    /**
     * The attributes excluded from the model's JSON form.
     * @var array
     */
    protected $hidden = [
        'id','created_at','updated_at'
    ];

    protected $createRules = [
        'identity' => 'Required|String',
        'campaign_id' => 'Required|String',
        'text' => 'Required|String',
        'image_url' => 'String',
        'video_url' => 'String',
    ];

    protected $editRules = [
        'campaign_id' => 'Required|String',
        'text' => 'Required|String',
        'image_url' => 'String',
        'video_url' => 'String',
    ];

    public $fieldsForFeeds = ['psa_campaign.social_network', 'psa_posts.id', 'psa_posts.influencer_id', 'psa_posts.text', 'psa_posts.image_url',
        'psa_posts.video_url', 'psa_social_account.twitter_onboard', 'psa_social_account.twitter_token', 'psa_social_account.twitter_token_secret',
        'psa_social_account.youtube_onboard', 'psa_social_account.youtube_token',
        'psa_social_account.instagram_onboard', 'psa_social_account.instagram_token',
        'psa_posts.posted', 'psa_posts.status', 'psa_posts'
    ];

    /**
     * create Interest Validator
     * @return array
     */
    public function createValidator($social_network)
    {
        switch($social_network) {

            case config('constants.CAMPAIGN_SOCIAL_INSTAGRAM'):
                if(isset($this->attributes['image_url'])) {
                    unset($this->createRules['video_url']);
                }
                if(isset($this->attributes['video_url'])) {
                    unset($this->createRules['image_url']);
                }
            case config('constants.CAMPAIGN_SOCIAL_TWITTER'):
                    unset($this->createRules['video_url']);
                    unset($this->createRules['image_url']);

            case config('constants.CAMPAIGN_SOCIAL_YOUTUBE'):
        }
        $v = \Validator::make($this->attributes, $this->createRules);
        if($v->fails()){
            $error = $v->messages()->toArray();
            return $this->errorHandler($error);
        }
        return $v->passes();
    }

    /**
     * validate edit interest data
     * @return mixed
     */
    public function editValidator(){
        $v = \Validator::make($this->attributes, $this->editRules);
        if($v->fails()){
            $error = $v->messages()->toArray();
            return $this->errorHandler($error);
        }
        $this->clearAttributes();
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

    protected function clearAttributes()
    {
        unset($this->identity);
    }

    public function getPostsForFeed()
    {
        $request = \DB::table('psa_posts');
        $request->join('psa_social_account', 'psa_social_account.user_identity', '=', 'psa_posts.influencer_id')
                ->join('psa_campaign', 'psa_campaign.identity', '=', 'psa_posts.campaign_id')
                ->where('psa_posts.posted', '=', 0)
                ->where('psa_posts.status', '=', 'approved');
        $posts = $request->get($this->fieldsForFeeds);
        return $posts;
    }

    /**
     * @param $query
     * @param string $userId
     * @param string $campaignId
     * @return Post|null
     */
    public function scopeCheckExistPost($query, $userId, $campaignId)
    {
        return $query->where('campaign_id','=',$campaignId)
            ->where('influencer_id','=',$userId);

    }

    public function postFeeds($posts)
    {

        session_start();
        foreach($posts as $post) {
            switch ($post->social_network) {
                case config('constants.CAMPAIGN_SOCIAL_YOUTUBE') :
                    // REPLACE this value with the path to the file you are uploading.
                    $client = new Google_Client();
                    $client->setClientId(Config::get('constants.GOOGLE_CLIENT_ID'));
                    $client->setClientSecret(Config::get('constants.GOOGLE_CLIENT_SECRET'));
                    $client->setRedirectUri(Config::get('constants.GOOGLE_REDIRECT_URI'));
                    $client->setAccessType('offline');
                    $client->setApprovalPrompt('force');

                    $client->setScopes(array('https://www.googleapis.com/auth/youtube.force-ssl'));
                    $client->setAccessToken($post->youtube_token);

                    $videoPath = public_path() . '/uploads/video.mp4';

                    $snippet = new Google_Service_YouTube_VideoSnippet();

                    $snippet->setTitle("How to publish status on linkedin in PHP");
                    $snippet->setDescription("tutorial going to solve your problems and its very easy to publish status on your LinkedIn wall. No need to go to LinkedIn and update your status from your website create a file on your web site which do this all stuff without going to LinkedIn. To get Code to do as we are doing in this video follow this link: http://www.phpgang.com/post-auto-status-on-linkedin-with-php_511.html");
                    $snippet->setTags(array("LinkedIn status", "PHP", "LinkedIn", "oauth"));

                    // Numeric video category. See
                    // https://developers.google.com/youtube/v3/docs/videoCategories/list
                    $snippet->setCategoryId("22");

                    // Set the video's status to "public". Valid statuses are "public",
                    // "private" and "unlisted".
                    $status = new Google_Service_YouTube_VideoStatus();
                    $status->privacyStatus = "public";

                    // Associate the snippet and status objects with a new video resource.
                    $video = new Google_Service_YouTube_Video();
                    $video->setSnippet($snippet);
                    $video->setStatus($status);

                    // Specify the size of each chunk of data, in bytes. Set a higher value for
                    // reliable connection as fewer chunks lead to faster uploads. Set a lower
                    // value for better recovery on less reliable connections.
                    $chunkSizeBytes = 1 * 1024 * 1024;

                    // Setting the defer flag to true tells the client to return a request which can be called
                    // with ->execute(); instead of making the API call immediately.
                    $client->setDefer(true);

                    $youtube = new Google_Service_YouTube($client);

                    // Create a request for the API's videos.insert method to create and upload the video.
                    $insertRequest = $youtube->videos->insert("status,snippet", $video);

                    // Create a MediaFileUpload object for resumable uploads.
                    $media = new Google_Http_MediaFileUpload(
                        $client,
                        $insertRequest,
                        'video/*',
                        null,
                        true,
                        $chunkSizeBytes
                    );
                    $media->setFileSize(filesize($videoPath));
                    // Read the media file and upload it chunk by chunk.
                    $status = false;
                    $handle = fopen($videoPath, "rb");
                    while (!$status && !feof($handle)) {
                        $chunk = fread($handle, $chunkSizeBytes);
                        $status = $media->nextChunk($chunk);
                    }
                    fclose($handle);
                    // If you want to make other calls after the file upload, set setDefer back to false
                    $client->setDefer(false);
                    if($media->getHttpResultCode() == 200) {
                        $model = Posts::find($post->id);
                        if(is_null($model)){
                            break;
                        }
                        $model->posted = 1;
                        $model->posted_at = new \DateTime();
                        $model->save();
                    }

                    break;
                case config('constants.CAMPAIGN_SOCIAL_TWITTER') :
                    $request_token = [
                        'token'  => $post->twitter_token,
                        'secret' => $post->twitter_token_secret,
                    ];
                    Twitter::reconfig($request_token);
                    $uploaded_media = Twitter::uploadMedia(['media' => file_get_contents($post->image_url)]);
                    $twitter = Twitter::postTweet(['status' => $post->text, 'media_ids' => $uploaded_media->media_id_string]);
                    if($twitter) {
                        $model = Posts::find($post->id);
                        if(is_null($model)){
                            break;
                        }
                        $model->posted = 1;
                        $model->posted_at = new \DateTime($twitter->created_at);
                        $model->save();
                    }
                    break;
                case config('constants.CAMPAIGN_SOCIAL_INSTAGRAM') :
                    var_dump($post);die;
                    break;
            }
        }
    }

    public function getBrandPosts()
    {
        $brand = Brands::where('email','=',Auth::user()->email)->first();
        $posts = InfluencersCampaign::join('psa_campaign', 'psa_influencers_campaign.campaign_id', '=', 'psa_campaign.identity')
            ->join('psa_posts', 'psa_influencers_campaign.post_identity', '=', 'psa_posts.identity')
            ->where('psa_campaign.brand_id','=',$brand->identity)
            ->where('psa_influencers_campaign.status', '=', config('constants.INFLUENCER_CAMPAIGN_STATUS_APPLICATION_ACCEPTED'))

            ->get(array('psa_posts.*'));
        return $posts;
    }

    /**
     * @param string $identity
     * @param string $userIdentity
     * @return mixed
     */
    public function getInfluencerPosts($identity, $userIdentity)
    {
        return Posts::where('influencer_id', '=', $userIdentity)->where('campaign_id', '=', $identity)->first();
    }



}
