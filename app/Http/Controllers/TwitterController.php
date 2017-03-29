<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Thujohn\Twitter\Facades\Twitter;
use Session;
use Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\SocialAccount;

use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Config;

class TwitterController extends Controller
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



    public function login()
    {
        // your SIGN IN WITH TWITTER  button should point to this route
        $sign_in_twitter = true;
        $force_login = false;

        Twitter::reconfig(['token' => '', 'secret' => '']);

        $token = Twitter::getRequestToken(route('twitter.callback'));
        if (isset($token['oauth_token_secret'])) {
            $url = Twitter::getAuthorizeURL($token, $sign_in_twitter, $force_login);
            Session::put('oauth_state', 'start');
            Session::put('oauth_request_token', $token['oauth_token']);
            Session::put('oauth_request_token_secret', $token['oauth_token_secret']);

            return Redirect::to($url);

        }

//        return Redirect::route('twitter.error');
    }

    /**
     * @SWG\Post(path="/twitter/ios-verify",
     *  tags={"Operations with Social account for IOS"},
     *  summary="User add twitter account",
     *  description="Action add twitter account",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="find object",
     *     description="JSON Object which add twitter account.",
     *     required=true,
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="identity", type="string", example="303fff23-cc2c-4f26-8c77-18fd29ad6db8", description="User unique identificator"),
     *          @SWG\Property(property="name", type="string", example="JerronimoIN",
     *     description="Nickname in Twitter"),
     *          @SWG\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjI1LCJpc3MiOiJodHRwOlwvXC9wc2EubG9jXC9sb2dpbiIsImlhdCI6MTQ2NDM0NDk4MSwiZXhwIjoxNzc5NzA0OTgxLCJuYmYiOjE0NjQzNDQ5ODEsImp0aSI6IjZlYzJkMjk1OGE3YmQ4ZjFiZjQyYTM0MDI1ZWQyOWQ3In0.hx2vkMGU4mn-nYsMlXx1oBte11aif-O1cqyEMf",
     *     description="Twitter unique token"),
     *     )
     *     ),
     *     @SWG\Response(response="200", description="Return success or error message")
     * )
     */
    public function iosVerify(Request $request)
    {

        if ($request->input('token') && $request->input('name')) {
            $stack = \GuzzleHttp\HandlerStack::create();
            $profileOauth = new Oauth1([
                'consumer_key' => Config::get('constants.TWITTER_CONSUMER_KEY'),
                'consumer_secret' => Config::get('constants.TWITTER_CONSUMER_SECRET'),
                'oauth_token' => $request->input('token'),
                'token_secret' => ''
            ]);

            $stack->push($profileOauth);
            $client = new \GuzzleHttp\Client([
                'handler' => $stack
            ]);
            try {
                $profileResponse = $client->request('GET', 'https://api.twitter.com/1.1/users/show.json?screen_name=' .
                    $request->input('name'), [
                    'auth' => 'oauth'
                ]);
            } catch (\Exception $e) {
                return $this->response(false, ['message' => $e->getCode() . $e->getMessage()]);die;
            }
            $profile = json_decode($profileResponse->getBody(), true);
            if ($request->header('Authorization'))
            {
                $token = explode(' ', $request->header('Authorization'))[1];
                $socialAccount = new SocialAccount;
                $socialAccount->checkTemporary(config('constants.CAMPAIGN_SOCIAL_TWITTER'), $profile, $request->attributes->get('user'));

                $socialAccount->updateSocialUser(
                    config('constants.CAMPAIGN_SOCIAL_TWITTER'),
                    $profile,
                    $request->input('token'),
                    $request->attributes->get('user')
                );

                return $this->response(true, ['message' => 'Add twitter account!']);
            }
        } else {
            return $this->response(false, ['message' => 'Not all need data was send!']);
        }

    }

    public function callback()
    {
        // You should set this route on your Twitter Application settings as the callback
        // https://apps.twitter.com/app/YOUR-APP-ID/settings

        if (Session::has('oauth_request_token'))
        {
            $request_token = [
                'token'  => Session::get('oauth_request_token'),
                'secret' => Session::get('oauth_request_token_secret'),
            ];

            Twitter::reconfig($request_token);

            $oauth_verifier = false;

            if (Input::has('oauth_verifier'))
            {
                $oauth_verifier = Input::get('oauth_verifier');
            }

            // getAccessToken() will reset the token for you
            $token = Twitter::getAccessToken($oauth_verifier);

            if (!isset($token['oauth_token_secret']))
            {
                return Redirect::route('twitter.login')->with('flash_error', 'We could not log you in on Twitter.');
            }

            $credentials = Twitter::getCredentials();

            if (is_object($credentials) && !isset($credentials->error))
            {

                Session::put('access_token', $token);
                try {

                    //save data from twitter response to DB
                    $socialAccount = new SocialAccount;
                    $socialAccount->updateSocialUser(
                        config('constants.CAMPAIGN_SOCIAL_TWITTER'),
                        $credentials,
                        $token
                    );

                } catch (\Exception $e) {
                    var_dump($e->getCode() . $e->getMessage());die;
                }
                return Redirect::to('/frontend/#/onboard/step-one')->with('flash_notice', 'Congrats! You\'ve successfully signed in!');
            }
            return Redirect::route('twitter.error')->with('flash_error', 'Crab! Something went wrong while signing you up!');
        }
    }
}
