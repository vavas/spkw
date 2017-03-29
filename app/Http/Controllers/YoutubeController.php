<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Session;
use Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Google_Client;
use Google_Service_YouTube;
use App\SocialAccount;
use Config;

class YoutubeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('influencer');
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('home');
    }

    public function link()
    {

    }

    public function login()
    {
        $OAUTH2_CLIENT_ID = \Config::get('constants.GOOGLE_CLIENT_ID');
        $OAUTH2_CLIENT_SECRET = \Config::get('constants.GOOGLE_CLIENT_SECRET');

        $client = new Google_Client();
        $client->setClientId($OAUTH2_CLIENT_ID);
        $client->setClientSecret($OAUTH2_CLIENT_SECRET);

        /*
         * This OAuth 2.0 access scope allows for full read/write access to the
         * authenticated user's account and requires requests to use an SSL connection.
         */

        $client->setScopes('https://www.googleapis.com/auth/youtube.force-ssl');
//        $redirect = filter_var('http://' . $_SERVER['HTTP_HOST']);
        $redirect = filter_var(config('constants.GOOGLE_REDIRECT_URI'));

        $client->setRedirectUri($redirect);
        $client->setAccessType('offline');

        // Define an object that will be used to make all API requests.
        $youtube = new Google_Service_YouTube($client);

        if (isset($_GET['code'])) {
            if (strval(Session::get('state')) !== strval(Input::get('state'))) {
                die('The session state did not match.');
            }

            $client->authenticate($_GET['code']);
            Session::put('token', $client->getAccessToken());
            header('Location: ' . $redirect);
        }

        if (Session::get('token') !== null) {
            $client->setAccessToken(Session::get('token'));
        }

        $response = [];

        // Check to ensure that the access token was successfully acquired.

        if ($client->getAccessToken()) {

            if($client->isAccessTokenExpired()) {

                $state = mt_rand();
                $client->setState($state);
                Session::put('state', $state);
                $authUrl = $client->createAuthUrl();

                return redirect(filter_var($authUrl, FILTER_SANITIZE_URL));
            }
            return redirect((string) $redirect);
            // This code executes if the user enters an action in the form
            // and submits the form. Otherwise, the page displays the form above.
            $channelsResponse = $youtube->channels->listChannels('statistics', array(
                'mine' => 'true',
            ));

            foreach ($channelsResponse['items'] as $channel) {
                $response = $channel['statistics'];
            }

            Session::put('token', $client->getAccessToken());

        } else {
            // If the user hasn't authorized the app, initiate the OAuth flow
            $state = mt_rand();
            $client->setState($state);
            Session::put('state', $state);
            $authUrl = $client->createAuthUrl();

            return redirect((string)$authUrl);
        }
//        return $this->response(true,$response);
    }

    /**
     * @SWG\Post(path="/youtube/ios-verify",
     *  tags={"Operations with Social account for IOS"},
     *  summary="User add youtube account",
     *  description="Action add youtube account",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="find object",
     *     description="JSON Object which add youtube account.",
     *     required=true,
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="identity", type="string", example="303fff23-cc2c-4f26-8c77-18fd29ad6db8", description="User unique identificator"),
     *          @SWG\Property(property="name", type="string", example="JerronimoIN",
     *     description="Nickname in youtube"),
     *     @SWG\Property(property="token", type="object", example=
            @SWG\Property(access_token="ya29.CjLyAr5DS9cKn4RYCr1IIilf_EQBpuvD_2KjEG-Z9I0iO8wLVD1P-o_CqbYIqbuF_D31qg",
     *     token_type="Bearer",
     *     expires_in=3600,
     *     created=1464603099,
     *     refresh_token="1/HKSmLFXzqP0leUihZp2xUt3-5wkU7Gmu2Os_eBnzw74")
     *         ),
     *     )
     *     ),
     *     @SWG\Response(response="200", description="Return success or error message")
     * )
     */
    public function iosVerify(Request $request)
    {
        if ($request->input('token') && $request->input('name')) {

//            $token = '{"access_token":"ya29.CjLyAr5DS9cKn4RYCr1IIilf_EQBpuvD_2KjEG-Z9I0iO8wLVD1P-o_CqbYIqbuF_D31qg","token_type":"Bearer","expires_in":3600,"created":1464603099}';
            $token = json_encode($request->input('token'));
            $client = new Google_Client();
            $client->setClientId(Config::get('constants.GOOGLE_CLIENT_ID'));
            $client->setClientSecret(Config::get('constants.GOOGLE_CLIENT_SECRET'));
            $client->setRedirectUri(Config::get('constants.GOOGLE_REDIRECT_URI'));
            $client->setScopes(array('https://www.googleapis.com/auth/youtube.force-ssl'));
            $client->setAccessToken($token);

            $youtube = new Google_Service_YouTube($client);
            try {
                $channelsResponse = $youtube->channels->listChannels('id, snippet, statistics, contentDetails', array(
                    'mine' => 'true',
                ));
            } catch (\Exception $e) {
                return $this->response(false, ['message' => $e->getCode() . $e->getMessage()]);die;
            }
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

            if ($request->header('Authorization')) {
                $socialAccount = new SocialAccount;
                $socialAccount->checkTemporary(config('constants.CAMPAIGN_SOCIAL_YOUTUBE'), $mostPopularChannel, $request->attributes->get('user'));

                $socialAccount->updateSocialUser(
                    config('constants.CAMPAIGN_SOCIAL_YOUTUBE'),
                    $mostPopularChannel,
                    $token,
                    $request->attributes->get('user')
                );

                return $this->response(true, ['message' => 'Add google account!']);
            }
        } else {
            return $this->response(false, ['message' => 'Not all need data was send!']);
        }
    }

    public function callback()
    {

        $OAUTH2_CLIENT_ID = \Config::get('constants.GOOGLE_CLIENT_ID');
        $OAUTH2_CLIENT_SECRET = \Config::get('constants.GOOGLE_CLIENT_SECRET');

        $client = new Google_Client();
        $client->setClientId($OAUTH2_CLIENT_ID);
        $client->setClientSecret($OAUTH2_CLIENT_SECRET);

        /*
         * This OAuth 2.0 access scope allows for full read/write access to the
         * authenticated user's account and requires requests to use an SSL connection.
         */

        $client->setScopes('https://www.googleapis.com/auth/youtube.force-ssl');
//        $redirect = filter_var('http://' . $_SERVER['HTTP_HOST']);
        $redirect = filter_var(config('constants.GOOGLE_REDIRECT_URI'));

        $client->setRedirectUri($redirect);
        $client->setAccessType('offline');

        // Define an object that will be used to make all API requests.
        $youtube = new Google_Service_YouTube($client);

        if (isset($_GET['code'])) {

            if (strval(Session::get('state')) !== strval(Input::get('state'))) {
                die('The session state did not match.');
            }
            $client->authenticate($_GET['code']);
            Session::put('token', $client->getAccessToken());
            return redirect((string)$redirect);
        }

        if (Session::get('token') !== null) {
            $client->setAccessToken(Session::get('token'));
        }
        // Check to ensure that the access token was successfully acquired.

        if ($client->getAccessToken()) {
            if($client->isAccessTokenExpired()) {

                $state = mt_rand();
                $client->setState($state);
                Session::put('state', $state);
                $authUrl = $client->createAuthUrl();
                return redirect(filter_var($authUrl, FILTER_SANITIZE_URL));

            }

            // This code executes if the user enters an action in the form
            // and submits the form. Otherwise, the page displays the form above.
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
            Session::put('token', $client->getAccessToken());
            try {
                //save data from youtube response to DB
                $socialAccount = new SocialAccount;
                $socialAccount->updateSocialUser(
                    config('constants.CAMPAIGN_SOCIAL_YOUTUBE'),
                    $mostPopularChannel,
                    Session::get('token')
                );
                return Redirect::to('/frontend/#/onboard/step-one');
            } catch (\Exception $e) {
                $e->getCode() . $e->getMessage();
            }
        } else {
            // If the user hasn't authorized the app, initiate the OAuth flow
            $state = mt_rand();
            $client->setState($state);
            Session::put('state', $state);
            $authUrl = $client->createAuthUrl();

            return redirect((string)$authUrl);
        }
        return view('home');
    }
}