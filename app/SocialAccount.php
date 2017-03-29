<?php
/**
 * Created by PhpStorm.
 * User: QA
 * Date: 11.03.2016
 * Time: 10:48
 */

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use DB;
use App\User as UserModel;
use App\SocialAccount as UserSocialAccount;

class SocialAccount  extends Model
{
    protected $table = 'psa_social_account';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_identity', 'twitter_verified', 'twitter_id', 'twitter_token', 'twitter_token_secret', 'twitter_screen_name', 'twitter_image', 'twitter_onboard',
        'youtube_verified', 'youtube_id', 'youtube_token', 'youtube_name', 'youtube_image',
        'youtube_subscribers', 'youtube_views', 'youtube_onboard',
        'instagram_verified', 'instagram_id', 'instagram_token', 'instagram_name', 'instagram_image', 'instagram_onboard',
        'total_reach'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id', 'updated_at', 'created_at'
    ];

    protected $responseFields = [
        'twitter_image', 'twitter_followers', 'twitter_verified', 'twitter_screen_name', 'twitter_onboard',
        'instagram_verified', 'instagram_image', 'instagram_followers', 'instagram_onboard',
        'youtube_verified', 'youtube_image', 'youtube_subscribers', 'youtube_views', 'youtube_onboard',
        'total_reach'
    ];

    /**
     * rules array
     * @var array
     */
    protected $createRules = [
        'social' => 'Required|In:Twitter,Vine,Instagram,YouTube',
        'username' => 'Required|Min:3|Max:80',
        'user_id' => 'Required|Min:3|Max:80|Unique:psa_social_account',
        'followers' => 'Required|Integer'
    ];

    /**
     * rules array
     * @var array
     */
    protected $updateRules = [
        'social' => 'In:Twitter,Vine,Instagram,YouTube',
        'username' => 'Min:3|Max:80',
        'user_id' => 'Min:3|Max:80',
        'followers' => 'Integer'
    ];

    protected $tmpAccRules = [
        'twitter_screen_name' => 'required_without:instagram_name|Unique:psa_social_account',
        'instagram_name' => 'required_without:twitter_screen_name|Unique:psa_social_account',
    ];


    /**
     * validate account data
     * @param $data
     * @return mixed
     */
    public function createValidator($data){
        $v = \Validator::make($data, $this->createRules);
        if($v->fails()){
            $error = $v->messages()->toArray();
            $return = [];
            foreach($error as $key => $value){
                $return[$key] = $value[0];
            }
            return $return;
        }
        return $v->passes();
    }

    /**
     * validate account data
     * @param $data
     * @return mixed
     */
    public function updateValidator($data){
        $v = \Validator::make($data, $this->updateRules);
        if($v->fails()){
            $error = $v->messages()->toArray();
            $return = [];
            foreach($error as $key => $value){
                $return[$key] = $value[0];
            }
            return $return;
        }
        $account = self::where('user_id','=',$data['user_id'])->first();
        if(!is_null($account) and $data['id'] != $account->id){
            return ['user_id' => 'The user id has already been taken.'];
        }
        return $v->passes();
    }

    public function createTmpValidator($data) {
        $v = \Validator::make($data, $this->tmpAccRules);
        if($v->fails()){
            $error = $v->messages()->toArray();
            $return = [];
            foreach($error as $key => $value){
                $return[$key] = $value[0];
            }
            return $return;
        }
        return $v->passes();

    }

    public function updateSocialUser($social_network, $data, $token, $currentUser)
    {
        $user = SocialAccount::where('user_identity','=',$currentUser->identity)->first();
        $user = (isset($user)) ? $user : new SocialAccount;
        $influencer = Influencers::where('email','=',$currentUser->email)->first();
        switch ($social_network) {
            case config('constants.CAMPAIGN_SOCIAL_YOUTUBE') :
                $checkDublicate = SocialAccount::where('youtube_id','=',$data['youtube_id'])->first();
                if(!is_null($checkDublicate) && ($checkDublicate->user_identity != $currentUser->identity)) {
                    return false;
                }
                $user->populateYoutubeFields($data, $token);
                $socialYoutube = new SocialAccountYoutube;
                $socialYoutube->user_identify = $currentUser->identity;
                $socialYoutube->number_subscribers = $data['youtube_subscribers'];
                $socialYoutube->number_views = $data['youtube_views'];
                $socialYoutube->number_videos = $data['youtube_videos'];
                $socialYoutube->save();
                $influencer->image_url = $data['youtube_image'];
                break;
            case config('constants.CAMPAIGN_SOCIAL_TWITTER') :
                $checkDublicate = SocialAccount::where('twitter_screen_name','=',$data['screen_name'])->first();
                if(!is_null($checkDublicate) && ($checkDublicate->user_identity != $currentUser->identity)) {
                    return false;
                }
                $user->populateTwitterFields($data, $token);
                $socialTwitter = new SocialAccountTwitter;
                $socialTwitter->user_identify = $currentUser->identity;
                $socialTwitter->followers_count = $data['followers_count'];
                $socialTwitter->save();
                $influencer->image_url = $data['profile_image_url'];
                break;
            case config('constants.CAMPAIGN_SOCIAL_INSTAGRAM') :
                /* @var $data \Larabros\Elogram\Http\Response */
                $instaUser = $data->get();
                $checkDublicate = SocialAccount::where('instagram_name','=',$instaUser['username'])->first();
                if(!is_null($checkDublicate) && ($checkDublicate->user_identity != $currentUser->identity)) {
                    return false;
                }
                $user->populateInstagramFields($instaUser, $token);
                $socialInstagram = new SocialAccountInstagram;
                $socialInstagram->user_identify = $currentUser->identity;
                $socialInstagram->media = $instaUser['counts']['media'];
                $socialInstagram->followed_by = $instaUser['counts']['followed_by'];
                $socialInstagram->follows = $instaUser['counts']['follows'];
                $socialInstagram->save();
                $influencer->image_url = $instaUser['profile_picture'];
                break;
        }
        $user->user_identity = $currentUser->identity;
        if(is_null($user->id)){
            $user->save();
        } else {
            $user->update();
        }
        $influencer->save();
        return true;
    }

    public function populateInstagramFields($data, $token)
    {
        $this->instagram_verified = true;
        $this->instagram_id = $data['id'];
        $this->instagram_token = $token;
        $this->instagram_name = $data['username'];
        $this->instagram_image = $data['profile_picture'];
        $this->instagram_followers = $data['counts']['follows'];
        $this->instagram_onboard = 1;
        $this->total_reach = $this->total_reach + $data['counts']['follows'];
        return true;
    }

    public function populateTwitterFields($data, $token)
    {
        $this->twitter_verified = ($data['verified']) ? 1 : 0;
        $this->twitter_id = $data['id_str'];
        $this->twitter_token = $token;
//        $this->twitter_token_secret = $token['oauth_token_secret'];
        $this->twitter_screen_name = $data['screen_name'];
        $this->twitter_image = $data['profile_image_url'];
        $this->twitter_followers = $data['followers_count'];
        $this->twitter_onboard = 1;
        $this->total_reach = $this->total_reach + $data['followers_count'];
        return true;
    }

    public function populateYoutubeFields($data, $token)
    {
        $this->youtube_id = $data['youtube_id'];
        $this->youtube_token = $token;
        $this->youtube_name = $data['youtube_name'];
        $this->youtube_image = $data['youtube_image'];
        $this->youtube_subscribers = $data['youtube_subscribers'];
        $this->youtube_views = $data['youtube_views'];
        $this->youtube_videos = $data['youtube_videos'];
        $this->youtube_channel_id = $data['youtube_channel_id'];
        $this->youtube_channel_url = $data['youtube_channel_url'];
        $this->youtube_onboard = 1;
        $this->total_reach = $this->total_reach + $data['youtube_subscribers'];
        return true;
    }

    public function checkTemporary($social_network, $profile, $currentUser)
    {
        switch ($social_network) {
            case config('constants.CAMPAIGN_SOCIAL_YOUTUBE') :
                break;
            case config('constants.CAMPAIGN_SOCIAL_TWITTER') :
                $social_user = SocialAccount::where('twitter_screen_name','=',$profile['screen_name'])->first();
                if(is_null($social_user)) {
                    return false;
                } else {
                    if($currentUser->identity == $social_user->user_identity) {
                        return false;
                    } else {
                        $temporary_model = UserTemporary::where('identity','=',$social_user->user_identity)->first();
                        if(!is_null($temporary_model)) {
                            return DB::transaction(function () use ($social_user, $currentUser, $temporary_model) {
                                DB::table('psa_influencers_campaign')->where('influencer_id', '=', $social_user->user_identity)->update(array('influencer_id' => $currentUser->identity));
                                $current_social_user = new UserSocialAccount();
                                $current_social_user->setRawAttributes($social_user->toArray());
                                $current_social_user->user_identity = $currentUser->identity;
                                $current_social_user->update();
                                $model = UserModel::where('identity', '=', $social_user->user_identity)->first();
                                if (!is_null($model)) {
                                    $model->delete();
                                }
                            });
                        }
                    }
                }

                return true;
                break;
            case config('constants.CAMPAIGN_SOCIAL_INSTAGRAM') :
                $instaUser = $profile->get();
                $social_user = SocialAccount::where('instagram_name','=',$instaUser['username'])->first();
                if(is_null($social_user)) {
                    return false;
                } else {
                    if($currentUser->identity == $social_user->user_identity) {
                        return false;
                    } else {

                        $temporary_model = UserTemporary::where('identity','=',$social_user->user_identity)->first();

                        if(!is_null($temporary_model)) {
                            return DB::transaction(function () use ($social_user, $currentUser, $temporary_model) {

                                DB::table('psa_influencers_campaign')->where('influencer_id', '=', $social_user->user_identity)->update(array('influencer_id' => $currentUser->identity));

                                $current_social_user = new UserSocialAccount();
                                $current_social_user->setRawAttributes($social_user->toArray());
                                $current_social_user->user_identity = $currentUser->identity;
                                $current_social_user->update();
                                $model = UserModel::where('identity', '=', $social_user->user_identity)->first();
                                if (!is_null($model)) {
                                    $model->delete();
                                }

                            });
                        }
                    }
                }
                break;
        }
    }
}