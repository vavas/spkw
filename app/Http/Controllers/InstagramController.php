<?php
/**
 * Created by PhpStorm.
 * User: user8
 * Date: 13.04.16
 * Time: 16:42
 */

namespace App\Http\Controllers;

use App\Http\Requests;
use App\SocialAccount;
use Session;
use Auth;
use Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Larabros\Elogram\Client;
use App\User;


class InstagramController extends Controller
{

    protected $clientId ;
    protected $clientSecret;
    protected $accessToken;
    protected $redirectUrl;
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('influencer');
        $this->clientId = config('constants.INSTAGRAM_CLIENT_ID');
        $this->clientSecret = config('constants.INSTAGRAM_CLIENT_SECRET');
        $this->redirectUrl = config('constants.INSTAGRAM_REDIRECT_URI');
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

        session_start();
        $client = new Client($this->clientId, $this->clientSecret, null, $this->redirectUrl);
        // If we don't have an authorization code then get one
        if (!Input::has('code')) {
            $options  = ['scope' => 'basic public_content follower_list'];
            $loginUrl = $client->getLoginUrl($options);
            return redirect((string)$loginUrl);
        } else {
            $token = $client->getAccessToken($_GET['code']);
            echo json_encode($token); // Save this for future use
        }
    }


    /**
     * @SWG\Post(path="/instagram/ios-verify",
     *  tags={"Operations with Social account for IOS"},
     *  summary="User add instagram account",
     *  description="Action add instagram account",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="find object",
     *     description="JSON Object which add instagram account.",
     *     required=true,
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="identity", type="string", example="303fff23-cc2c-4f26-8c77-18fd29ad6db8", description="User unique identificator"),
     *          @SWG\Property(property="name", type="string", example="JerronimoIN",
     *     description="Nickname in youtube"),
     *          @SWG\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjI1LCJpc3MiOiJodHRwOlwvXC9wc2EubG9jXC9sb2dpbiIsImlhdCI6MTQ2NDM0NDk4MSwiZXhwIjoxNzc5NzA0OTgxLCJuYmYiOjE0NjQzNDQ5ODEsImp0aSI6IjZlYzJkMjk1OGE3YmQ4ZjFiZjQyYTM0MDI1ZWQyOWQ3In0.hx2vkMGU4mn-nYsMlXx1oBte11aif-O1cqyEMf",
     *     description="instagram unique token"),
     *     )
     *     ),
     *     @SWG\Response(response="200", description="Return success or error message")
     * )
     * @param Request $request
     * @return response
     */
    public function iosVerify(Request $request)
    {
        if ($request->get('token')) {

            $accessToken = '{"access_token": "' . $request->get('token') . '"}';
            $client = new Client($this->clientId, $this->clientSecret, $accessToken, $this->redirectUrl);
            try {
                $response = $client->request('GET', 'users/self');
            } catch (\Exception $e) {
                return $this->response(false, ['message' => $e->getCode() . $e->getMessage()]);die;
            }
            if ($request->header('Authorization')){
                $socialAccount = new SocialAccount;
                $socialAccount->checkTemporary(config('constants.CAMPAIGN_SOCIAL_INSTAGRAM'), $response, $request->attributes->get('user'));

                $socialAccount->updateSocialUser(
                    config('constants.CAMPAIGN_SOCIAL_INSTAGRAM'),
                    $response,
                    $request->get('token'),
                    $request->attributes->get('user')
                );

                return $this->response(true, ['message' => 'Add instagram account!']);
            }
        } else {
            return $this->response(false, ['message' => 'Not all need data was send!']);
        }
    }

    public function callback()
    {
        session_start();
        $client = new Client($this->clientId, $this->clientSecret, null, $this->redirectUrl);
        if (Input::has('code')) {
            $token = $client->getAccessToken(Input::get('code'));
            $client->setAccessToken($token);
            Session::put('instagram', $token);
            return redirect('instagram/callback');
        } else {
           if(Session::has('instagram')) {
               $token = Session::get('instagram');
               $this->accessToken = json_encode(['access_token' => $token->getToken()]);
               $client = new Client($this->clientId, $this->clientSecret, $this->accessToken, $this->redirectUrl);

               try {
                   $response = $client->request('GET', 'users/self');
                   //save data from instagram response to DB
                   $socialAccount = new SocialAccount();
                   $socialAccount->updateSocialUser(
                       config('constants.CAMPAIGN_SOCIAL_INSTAGRAM'),
                       $response,
                       $token->getToken()
                   );
               } catch (\Exception $e) {
                 var_dump($e->getCode() . $e->getMessage());die;
               }
               return Redirect::to('/frontend/#/onboard/step-one');
           }
        }
    }

    public function response($status, $message = []){
        if(!$status){
            return response(json_encode([
                'status' => false,
                'errors' => $message
            ]), 403);
        }

        return response(json_encode([
            'status' => true,
            'data' => $message
        ]), 200);
    }
}