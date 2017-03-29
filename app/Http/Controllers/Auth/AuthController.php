<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Support\Facades\Session;
use Validator;
use Hash;
use Auth;
use Config;
use Firebase\JWT\JWT;
use App\SocialAccount;
use Illuminate\Http\Request;

use GuzzleHttp\Subscriber\Oauth\Oauth1;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Larabros\Elogram\Client;
use Google_Client;
use Google_Service_YouTube;
use Cookie;


class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';
    protected $instagramClientId ;
    protected $instagramClientSecret;
    protected $instagramAccessToken;
    protected $instagramRedirectUrl;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {

//        $this->middleware('guest', ['except' => array('logout')]);
        $this->middleware('influencer', ['only' => array('twitter','instagram', 'google', 'unlink')]);
        $this->instagramClientId = config('constants.INSTAGRAM_CLIENT_ID');
        $this->instagramClientSecret = config('constants.INSTAGRAM_CLIENT_SECRET');
        $this->instagramRedirectUrl = config('constants.INSTAGRAM_REDIRECT_URI');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    function login(){

    }

    /**
     * Unlink provider.
     */
    public function unlink(Request $request)
    {
        $provider = ($request->get('provider') != 'google') ? $request->get('provider') : config('constants.CAMPAIGN_SOCIAL_YOUTUBE');
        $user = User::find(Auth::user()->id);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found']);
        }
        $social = SocialAccount::where('user_identity','=',$user->identity)->first();

        switch($provider) {
            case config('constants.CAMPAIGN_SOCIAL_YOUTUBE'):
                $social->youtube_onboard = 0;
                break;
            case strtolower(config('constants.CAMPAIGN_SOCIAL_TWITTER')):
                $social->twitter_onboard = 0;
                break;
            case strtolower(config('constants.CAMPAIGN_SOCIAL_INSTAGRAM')):
                $social->instagram_onboard = 0;
                break;
            default:
                return response()->json(['status' => false, 'message' => 'Provider not found']);
        }
        $social->save();
        return response()->json(array('status' => true));
    }

    public function twitter(Request $request)
    {
        $stack = \GuzzleHttp\HandlerStack::create();
        // Part 1 of 2: Initial request from Satellizer.
        if (!$request->input('oauth_token') || !$request->input('oauth_verifier'))
        {
            $stack = \GuzzleHttp\HandlerStack::create();

            $requestTokenOauth = new Oauth1([
                'consumer_key' => Config::get('constants.TWITTER_CONSUMER_KEY'),
                'consumer_secret' => Config::get('constants.TWITTER_CONSUMER_SECRET'),
                'callback' => $request->input('redirectUri'),
                'token' => '',
                'token_secret' => ''
            ]);
            $stack->push($requestTokenOauth);
            $client = new \GuzzleHttp\Client([
                'handler' => $stack
            ]);

            // Step 1. Obtain request token for the authorization popup.
            $requestTokenResponse = $client->request('POST', 'https://api.twitter.com/oauth/request_token', [
                'auth' => 'oauth'
            ]);
            $oauthToken = array();
            parse_str($requestTokenResponse->getBody(), $oauthToken);
            // Step 2. Send OAuth token back to open the authorization screen.
            return response()->json($oauthToken);
        }
        // Part 2 of 2: Second request after Authorize app is clicked.
        else
        {
            $accessTokenOauth = new Oauth1([
                'consumer_key' => Config::get('constants.TWITTER_CONSUMER_KEY'),
                'consumer_secret' => Config::get('constants.TWITTER_CONSUMER_SECRET'),
                'token' => $request->input('oauth_token'),
                'verifier' => $request->input('oauth_verifier'),
                'token_secret' => ''
            ]);

            $stack->push($accessTokenOauth);
            $client = new \GuzzleHttp\Client([
                'handler' => $stack
            ]);
            // Step 3. Exchange oauth token and oauth verifier for access token.
            $accessTokenResponse = $client->request('POST', 'https://api.twitter.com/oauth/access_token', [
                'auth' => 'oauth'
            ]);

            $accessToken = array();
            parse_str($accessTokenResponse->getBody(), $accessToken);

            $profileOauth = new Oauth1([
                'consumer_key' => Config::get('constants.TWITTER_CONSUMER_KEY'),
                'consumer_secret' => Config::get('constants.TWITTER_CONSUMER_SECRET'),
                'oauth_token' => $accessToken['oauth_token'],
                'token_secret' => ''
            ]);

            $stack->push($profileOauth);
            $client = new \GuzzleHttp\Client([
                'handler' => $stack
            ]);
            // Step 4. Retrieve profile information about the current user.
            $profileResponse = $client->request('GET', 'https://api.twitter.com/1.1/users/show.json?screen_name=' . $accessToken['screen_name'], [
                'auth' => 'oauth'
            ]);
            $profile = json_decode($profileResponse->getBody(), true);

            // Step 5a. Link user accounts.
            if ($request->header('Authorization'))
            {
//                $token = explode(' ', $request->header('Authorization'))[1];
                $token = $accessToken['oauth_token'];
//                $payload = (array) JWT::decode($token, Config::get('app.token_secret'), array('HS256'));
                //save data from twitter response to DB
                $socialAccount = new SocialAccount;
                $socialAccount->checkTemporary(config('constants.CAMPAIGN_SOCIAL_TWITTER'), $profile, $request->attributes->get('user'));

                if($socialAccount->updateSocialUser(
                    config('constants.CAMPAIGN_SOCIAL_TWITTER'),
                    $profile,
                    $token,
                    $request->attributes->get('user')
                )) {
                    return response()->json(['token' => $token]);
                } else {
                    return $this->response(false,['message' => 'This influencer has been activated before']);
                };

            }
            // Step 5b. Create a new user account or return an existing one.
            else
            {
                $user = User::where('twitter', '=', $profile['id']);
                if ($user->first())
                {
                    return response()->json(['token' => $this->createToken($user->first())]);
                }
                $user = new User;
                $user->twitter = $profile['id'];
                $user->displayName = $profile['screen_name'];
                $user->save();
                return response()->json(['token' => $this->createToken($user)]);
            }
        }
    }

    /**
     * Login with Instagram.
     */
    public function instagram(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $params = [
            'code' => $request->input('code'),
            'client_id' => $request->input('clientId'),
            'client_secret' => Config::get('constants.INSTAGRAM_CLIENT_SECRET'),
            'redirect_uri' => $request->input('redirectUri'),
            'grant_type' => 'authorization_code',
        ];

        // Step 1. Exchange authorization code for access token.
        $accessTokenResponse = $client->request('POST', 'https://api.instagram.com/oauth/access_token', [
            'form_params' => $params
        ]);

        $accessToken = json_decode($accessTokenResponse->getBody(), true);

        // Step 2a. If user is already signed in then link accounts.
        if ($request->header('Authorization'))
        {
            $token = '{"access_token": "' . $accessToken['access_token'] . '"}';
//            var_dump($token);die;
            $client = new Client($this->instagramClientId, $this->instagramClientSecret, $token, $this->instagramRedirectUrl);

            $response = $client->request('GET', 'users/self');

            $socialAccount = new SocialAccount;
            $socialAccount->checkTemporary(config('constants.CAMPAIGN_SOCIAL_INSTAGRAM'), $response, $request->attributes->get('user'));
            if($socialAccount->updateSocialUser(
                config('constants.CAMPAIGN_SOCIAL_INSTAGRAM'),
                $response,
                $token,
                $request->attributes->get('user')
            )) {
                return response()->json(['token' => $token]);
            } else {
                return $this->response(false,['message' => 'This influencer has been activated before']);
            };

            return response()->json(['token' => $token]);
        }
        // Step 2b. Create a new user account or return an existing one.
        else
        {
            $user = User::where('instagram', '=', $accessToken['user']['id']);
            if ($user->first())
            {
                return response()->json(['token' => $this->createToken($user->first())]);
            }
            $user = new User;
            $user->instagram = $accessToken['user']['id'];
            $user->displayName =  $accessToken['user']['username'];
            $user->save();
            return response()->json(['token' => $this->createToken($user)]);
        }
    }

    /**
     * Login with Google.
     */
    public function google(Request $request)
    {

        $client = new Google_Client;
        if(!Session::get('youtube_credentials')) {
            $client->setClientId(Config::get('constants.GOOGLE_CLIENT_ID'));
            $client->setClientSecret(Config::get('constants.GOOGLE_CLIENT_SECRET'));
            $client->setRedirectUri(Config::get('constants.GOOGLE_REDIRECT_URI'));
//next two line added to obtain refresh_token
            $client->setAccessType('offline');
            $client->setApprovalPrompt('force');

            $client->setScopes(array('https://www.googleapis.com/auth/youtube.force-ssl'));
//            $client->setScopes(array('https://gdata.youtube.com'));

            if ($request->input('code')) {
                $credentials = $client->authenticate($request->input('code'));
                $array = get_object_vars(json_decode($credentials));
                // store and use $refreshToken to get new access tokens
//                $refreshToken = $array['refreshToken'];
//                $client->refreshToken($refreshToken);
//                $NewAccessToken = json_decode($client->getAccessToken());
//
//                $client->refreshToken($NewAccessToken->refresh_token);

                /*TODO: Store $credentials somewhere secure */
                Session::put('youtube', $client->getAccessToken());
                Session::put('youtube_credentials', $credentials);
                Session::put('youtube_refresh_token', $client->getRefreshToken());

                $youtube = new Google_Service_YouTube($client);
                $channelsResponse = $youtube->channels->listChannels('id, snippet, statistics, contentDetails', array(
                    'mine' => 'true',
                ));

                $mostPopularChannel = [];
                if(count($channelsResponse['items'])) {
                    $max = $channelsResponse['items'][0]['statistics']['viewCount'];
                    // get most popular youtube chanel (max views count among all channels)
                    for ($i = 0; $i < count($channelsResponse['items']); $i++) {
                        if ($channelsResponse['items'][$i]['statistics']['viewCount'] >= $max) {
                            $max = $channelsResponse['items'][$i]['statistics']['viewCount'];
                            $mostPopularChannel['youtube_channel_id'] = $channelsResponse['items'][$i]['id'];
                            $mostPopularChannel['youtube_channel_url'] = 'https://www.youtube.com/channel/' . $channelsResponse['items'][$i]['id'];
                            $mostPopularChannel['youtube_subscribers'] = $channelsResponse['items'][$i]['statistics']['subscriberCount'];
                            $mostPopularChannel['youtube_views'] = $max;
                            $mostPopularChannel['youtube_videos'] = $channelsResponse['items'][$i]['statistics']['videoCount'];
                            $mostPopularChannel['youtube_image'] = $channelsResponse['items'][$i]['snippet']['thumbnails']['default']['url'];
                            $mostPopularChannel['youtube_id'] = $channelsResponse['items'][$i]['contentDetails']['googlePlusUserId'];
                            $mostPopularChannel['youtube_name'] = $channelsResponse['items'][$i]['snippet']['title'];
                        }
                    }
                }
                try {

                    //save data from youtube response to DB
                    $socialAccount = new SocialAccount();

                    $socialAccount->updateSocialUser(
                        config('constants.CAMPAIGN_SOCIAL_YOUTUBE'),
                        $mostPopularChannel,
                        Session::get('youtube_credentials'),
                        $request->attributes->get('user')
                    );
                    return response()->json(['token' => Session::get('youtube_credentials')]);
                } catch (\Exception $e) {
                    var_dump($e->getCode() . $e->getMessage());die;
                }

            } else {
                $authUrl = $client->createAuthUrl();
                return redirect($authUrl);

            }
        } else {

            $client = new Google_Client();
            $client->setClientId(Config::get('constants.GOOGLE_CLIENT_ID'));
            $client->setClientSecret(Config::get('constants.GOOGLE_CLIENT_SECRET'));
            $client->setRedirectUri(Config::get('constants.GOOGLE_REDIRECT_URI'));
            $client->setScopes(array('https://www.googleapis.com/auth/youtube.force-ssl'));
            $client->setAccessToken(Session::get('youtube_credentials'));

            if($client->isAccessTokenExpired()) {
                $credentials = $client->authenticate($request->input('code'));
//                /*TODO: Store $credentials somewhere secure */
                Session::put('youtube', $client->getAccessToken());
                Session::put('youtube_credentials', $credentials);
//                $client->refreshToken(Session::get('youtube_refresh_token'));

            }

//            $client->setAccessToken(Session::get('youtube'));
            $youtube = new Google_Service_YouTube($client);

            $channelsResponse = $youtube->channels->listChannels('id, snippet, statistics, contentDetails', array(
                'mine' => 'true',
            ));

            $mostPopularChannel = [];
            if(count($channelsResponse['items'])) {
                $max = $channelsResponse['items'][0]['statistics']['viewCount'];
                // get most popular youtube chanel (max views count among all channels)
                for ($i = 0; $i < count($channelsResponse['items']); $i++) {
                    if ($channelsResponse['items'][$i]['statistics']['viewCount'] >= $max) {
                        $max = $channelsResponse['items'][$i]['statistics']['viewCount'];
                        $mostPopularChannel['youtube_channel_id'] = $channelsResponse['items'][$i]['id'];
                        $mostPopularChannel['youtube_channel_url'] = 'https://www.youtube.com/channel/' . $channelsResponse['items'][$i]['id'];
                        $mostPopularChannel['youtube_subscribers'] = $channelsResponse['items'][$i]['statistics']['subscriberCount'];
                        $mostPopularChannel['youtube_views'] = $max;
                        $mostPopularChannel['youtube_videos'] = $channelsResponse['items'][$i]['statistics']['videoCount'];
                        $mostPopularChannel['youtube_image'] = $channelsResponse['items'][$i]['snippet']['thumbnails']['default']['url'];
                        $mostPopularChannel['youtube_id'] = $channelsResponse['items'][$i]['contentDetails']['googlePlusUserId'];
                        $mostPopularChannel['youtube_name'] = $channelsResponse['items'][$i]['snippet']['title'];
                    }
                }
            }
            try {
                //save data from youtube response to DB

                $socialAccount = new SocialAccount;
                if($socialAccount->updateSocialUser(
                    config('constants.CAMPAIGN_SOCIAL_YOUTUBE'),
                    $mostPopularChannel,
                    Session::get('youtube_credentials'),
                    $request->attributes->get('user')
                )) {
                    return response()->json(['youtube' => Session::get('youtube_credentials')]);
                } else {
                    return $this->response(false,['message' => 'This influencer has been activated before']);
                };

            } catch (\Exception $e) {
                var_dump($e->getCode() . $e->getMessage());die;
            }
        }
        return response()->json(['token' => Session::get('youtube_credentials')]);
    }

}
