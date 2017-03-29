<?php
/**
 * Created by PhpStorm.
 * User: QA
 * Date: 10.03.2016
 * Time: 10:29
 */

namespace App\Http\Controllers;

use App\Brands;
use App\Influencers;
use App\Mailer;
use App\SocialAccount;
use Mail;
use App\User;
use Auth;
use Mandrill;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use \Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Webpatser\Uuid\Uuid;

class UserController extends Controller
{
    use AuthenticatesAndRegistersUsers, ThrottlesLogins;
    function __construct( User $user, Influencers $influencer ){

        $this->middleware('jwt.auth', ['except' => ['signUp','login','logout','confirmationEmail','resetPassword','forgotPassword','setNewPassword','test', 'createNewPassword', 'userProfile']]);
        $this->middleware('admin', ['only' => ['loginAs']]);

        $this->user = $user;
        $this->influencer = $influencer;

    }




    public function getUser(){
        if(Auth::Check()){
            return $this->response(true,Auth::user());
        }
        return $this->response(false);
    }

    /**
     * @SWG\Post(path="/sign-up",
     *   tags={"Operations with users"},
     *   summary="Perform Register User",
     *   description="register form data validate and save, User role - New",
     *   produces={"application/json"},
     *   consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="register object",
     *     description="JSON Object which register user",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="email", type="string", example="user@user.com"),
     *         @SWG\Property(property="password", type="string", example="12345678"),
     *         @SWG\Property(property="password_confirmation", type="string", example="12345678"),
     *         @SWG\Property(property="first_name", type="string", example="Steven"),
     *         @SWG\Property(property="last_name", type="string", example="Jobs")
     *     )
     *   ),
     *   @SWG\Response(response="200", description="Rerurn true or error message")
     * )
     */
    public function signUp(){
        $data = Input::all();

        /**
         * set up new user
         */
        $model = new User($data);

        $validate = $model->validate();
        if (!is_bool($validate)) {
            return $this->response(false,$validate);
        }

        $model->setUserAttributes();
        $model->identity = $this->getIdentity();

        /**
         * sey up influencers
         */

        $influencers = new Influencers($data);

        $validate = $influencers->validate();
        if (!is_bool($validate)) {
            return $this->response(false,$validate);
        }

        $model->save();
        $influencers->save();

        $influencers->authToken = $model->authToken;
        $influencers->identity = $model->identity;

        return $this->response(true,$influencers);
    }


    /**
     * @SWG\GET(path="/confirmation-email/{token}",
     *   tags={"Operations with users"},
     *   summary="Perform confirmation email",
     *   description="confirmation email User",
     *   produces={"application/json"},
     *   consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="token",
     *     description="Token is randomly generated and is disposable for each operation. Url, containing the token, sent to the email user",
     *     required=true
     *   ),
     *   @SWG\Response(response="200", description="Verified User email and activate User")
     * )
     */
    public function confirmationEmail($token){

        $user = User::where('authToken','=',$token)->first();

        if(is_null($user)){
            return $this->response(false,['message' => 'Your link is not valid']);
        }

        $token = JWTAuth::attempt(['email' => $user->email, 'password' => $user->remember_token]);
        if($token){
            $user->authToken = null;
            $user->role = 'influencer';
            $user->save();

            $user->accessToken = $token;
            return $this->response(true,$user);
        }

        return $this->response(false,['message' => 'Authorization error']);
    }

    /**
     * @SWG\Post(path="/reset-password",
     *   tags={"Operations with users"},
     *   summary="Perform Reset Password",
     *   description="Reset user password",
     *   produces={"application/json"},
     *   consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="reset object",
     *     description="JSON Object which reset user password",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="email", type="string", example="user@user.com")
     *     )
     *   ),
     *   @SWG\Response(response="200", description="Rerurn true or error message")
     * )
     */
    public function resetPassword(){
        $data = Input::all();

        $user = User::where('email','=',$data['email'])->first();

        if(is_null($user)){
            return $this->response(false, ['email' => 'Account does not exist']);
        }

        $password = $user->generate();

        $user->authToken = md5($user->generate());
        $user->password = bcrypt($password);
        $user->remember_token = $password;
        if($user->save()){

            // send verification email
            $mail = new Mailer();
            $mail->sendTemplate('reset-password', $user);
            $link = URL::to('forgot-password').'/'.$user->authToken;
        }
        return $this->response(false,['massage' => 'generate link failed']);
    }



    public function forgotPassword($token){
        $user = User::where('authToken','=',$token)->first();
        if(is_null($user)){
            return $this->response(false,['message' => 'Your link is not valid']);
        }

        Auth::loginUsingId($user->id, true);

        if(Auth::check()) {
            return redirect('/frontend/#/onboard/step-one')->withCookie(cookie('user', $user));
        }
        return $this->response(false,['message' => 'Auth false']);
    }

    /**
     * @SWG\Post(path="/edit-profile",
     *   tags={"Operations with users"},
     *   summary="Perform Edit Influencer of Brand Profile",
     *   description="Edit profile, Good for editing, you must set the fields according to the descriptions of the model and type of the edited account",
     *   produces={"application/json"},
     *   consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="register object",
     *     description="JSON Object which edit profile",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="first_name", type="string", example="Steven",description="For all types of profile"),
     *         @SWG\Property(property="last_name", type="string", example="Jobs",description="For all types of profile"),
     *
     *
     *         @SWG\Property(property="image_url", type="string", example="http://dev/uploads/093095da2b952dbcc90e4ce1b6080dd0.jpg",description="For influencers"),     *
     *         @SWG\Property(property="gender", type="string", example="Male",description="For influencers"),
     *         @SWG\Property(property="age", type="int", example="25",description="For influencers"),
     *         @SWG\Property(property="location_state", type="string", example="Washington",description="For influencers"),
     *         @SWG\Property(property="location_city", type="string", example="Seattle",description="For influencers"),
     *         @SWG\Property(property="interests", type="string", example="",description="For influencers"),
     *         @SWG\Property(property="paypal", type="string", example="user@user.com",description="For influencers"),
     *
     *         @SWG\Property(property="details", type="string", example="",description="For Brands"),
     *         @SWG\Property(property="submission", type="string", example="public",description="For Brands"),
     *         @SWG\Property(property="status", type="string", example="public",description="For Brands"),
     *         @SWG\Property(property="hashtag", type="string", example="#hashtag",description="For Brands"),
     *         @SWG\Property(property="url", type="string", example="http://my-url.com",description="For Brands"),     *
     *
     *         @SWG\Property(property="onboarding", type="boolean", example="1",description="after filling out the profile flag is set to 1 (true) or modified")
     *     )
     *   ),
     *   @SWG\Response(response="200", description="Rerurn true or error message")
     * )
     */


    public function editProfile(){

        $data = Input::all();
        $user = User::find(Auth::user()->id);
        $profile = $this->getProfile($user);
        $influencer = Influencers::where('email','=',$profile->email)->first();
        if(is_null($profile)){
            return $this->response(false,['message' => 'Profile not found']);
        }

        $profile->setRawAttributes($data);
        if($user->isInfluencer() && isset($profile->media)){
            $validate = $profile->editValidatorSocials();
        } else {
            $validate = $profile->editValidator();
        }

        if (!is_bool($validate)) {
            return $this->response(false,$validate);
        }
        $user->email = (isset($data['email'])) ? $data['email'] : $user->email;
        $user->save();
        if($user->role == config('constants.USER_ROLE_INFLUENCER')) {
            if(isset($data['interests'])) {
                $user->deleteUserInterests($user->identity);
                $user->saveUserInterests($user->identity, $data);
            }
            $profile = $profile->getInfluencer($user, Auth::user());
        } else {
            $profile = $this->getProfile($user);
        }
        $profile->interests = $user->getUserInterests($user->identity);
        $profile->age = (int) $profile->age;
        return $this->response(true,$profile);
    }

    /**
     * @SWG\GET(path="/user-profile/{identity}",
     *   tags={"Operations with users"},
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
    public function userProfile($identity){
        $user = User::where('identity','=',$identity)->first();

        if(is_null($user)){
            return $this->response(false,['message' => 'User not found']);
        }

        $profile = $this->getProfile($user);
        if(!is_null($profile)) {
            $profile->interests = $user->getUserInterests($user->identity);
        }
        if(is_null($profile)){
            return $this->response(false,['message' => 'Profile not found']);
        }
        $profile->age = (int) $profile->age;
        return $this->response(true,$profile);
    }


    /**
     * @SWG\Post(path="/change-password",
     *   tags={"Operations with users"},
     *   summary="Perform change password",
     *   description="Edit form data validate and save,",
     *   produces={"application/json"},
     *   consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="change password object",
     *     description="JSON Object which edit profile",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="password_old", type="string", example="12345678"),
     *         @SWG\Property(property="password", type="string", example="12345678"),
     *         @SWG\Property(property="password_confirmation", type="string", example="12345678")
     *     )
     *   ),
     *   @SWG\Response(response="200", description="Rerurn true or error message")
     * )
     */
    public function changePassword(){
        $data = Input::all();

        $model = User::find(Auth::user()->id);

        $check = [
            'email' => Auth::user()->email,
            'password' => isset($data['password_old'])?$data['password_old']:''
        ];
        if(!Auth::validate($check)){
            return $this->response(false,['password_old' => 'Incorrect password']);
        }

        $model->setRawAttributes($data);
        $validate = $model->editValidator();
        if (!is_bool($validate)) {
            return $this->response(false,$validate);
        }
        $model->password = bcrypt($model->password);
        $model->save();
        return $this->response(true,$model);
    }

    /**
     * @SWG\Post(path="/login",
     *   tags={"Operations with users"},
     *   summary="Perform login",
     *   description="Login and password to log in. If User !Guest - redirect to home",
     *   produces={"application/json"},
     *   consumes={"application/json"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="login object",
     *     description="JSON Object which represents filter conditions",
     *     required=true,
     *
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="email", type="string", example="user@user.com", required=false),
     *         @SWG\Property(property="password", type="string", example="12345678", required=true)
     *     )
     *   ),
     *   @SWG\Response(response="200", description="Rerurn Error or Object User")
     * )
     */
    public function login(){

        $data = Input::all();
        $user = new User();
        $validate = $user->validateLogin($data);

        if (!is_bool($validate)) {
            return $this->response(false,$validate);
        }
        $user = User::where('email','=',$data['email'])->first();
        $token = JWTAuth::attempt(['email' => $data['email'], 'password' => $data['password']]);
        if($token){
            Auth::attempt(['email' => $data['email'], 'password' => $data['password']]);
            $user = Auth::user();
            $user->authToken = null;
            $user->lastLog = \Carbon\Carbon::now();
            $user->save();
            $user->accessToken = $token;

            return $this->response(true,$user);
        }


        return $this->response(false, ['password' => 'Incorrect password']);
    }

    /**
     * @SWG\GET(path="/del-profile",
     *   tags={"Operations with users"},
     *   summary="Perform delete profile",
     *   description="delete User Profile",
     *   produces={"application/json"},
     *   @SWG\Response(response="200", description="delete User Profile")
     * )
     */
    public function delProfile(){
        $user = User::find(Auth::user()->id);
        $profile = $this->getProfile($user);
        $profile->delete();
        $user->delete();
        Auth::guard($this->getGuard())->logout();
        return $this->response(true, ['message' => 'Account removed']);
    }


    /**
     * @SWG\GET(path="/logout",
     *   tags={"Operations with users"},
     *   summary="Perform logout",
     *   description="logout User, redirect to home",
     *   produces={"application/json"},
     *   @SWG\Response(response="200", description="Logout User, redirect to Home")
     * )
     */
    public function logout(){
        Auth::guard($this->getGuard())->logout();
        Auth::logout();

        return $this->response(true);
    }


    /**
     * generate UIID4 token
     * @return string
     * @throws \Exception
     */
    protected function getIdentity(){
        $uids = Uuid::generate(4);
        return $uids->string;
    }


    /**
     * @SWG\GET(path="/login-as/{identity}",
     *   tags={"Operations with users"},
     *   summary="Perform login as another user",
     *   description="login as another user",
     *   produces={"application/json"},
     *   consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="User identity (3627510e-2821-4ef1-b669-8244c46350f6)",
     *     example="1",
     *     required=true
     *   ),
     *   @SWG\Response(response="200", description="Admin logged as user")
     * )
     */
    public function loginAs($identity) {
        $user = User::join('psa_brands', 'psa_users.email', '=', 'psa_brands.email')
            ->where('psa_brands.identity', '=', $identity)->first(['psa_users.*']);
        if(!is_null($user)){
            Session::put('logged_admin_id', Auth::user()->id);
            $token = JWTAuth::fromUser($user);
            if($token){
                $user->accessToken = $token;
                return $this->response(true,$user);
            }
        }
        return $this->response(false, ['error' => 'User not found']);
    }


}