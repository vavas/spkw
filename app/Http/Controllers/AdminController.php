<?php
/**
 * Created by PhpStorm.
 * User: QA
 * Date: 15.03.2016
 * Time: 16:51
 */

namespace App\Http\Controllers;

use App\Brands;
use App\Providers\AppServiceProvider;
use App\SocialAccount;
use App\Upload;
use App\Mailer;
use Auth;
use App\User;
use App\Interests;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\URL;
use Tymon\JWTAuth\Facades\JWTAuth;
use Webpatser\Uuid\Uuid;
use Illuminate\Http\Request;
use SoapBox\Formatter\Parsers;
use Storage;

class AdminController extends Controller
{
    function __construct(){
        $this->middleware('jwt.auth', ['except' => ['createTemporaryAccount']]);
        $this->middleware('admin', ['except' => ['getInterestList', 'createTemporaryAccount']]);
    }

    public function getUserList(){
        $users = User::all();

        if(!is_null($users)){
            return $this->response(true,$users);
        }
        return $this->response(false,['message' => 'Not Found']);
    }

    /**
     * @SWG\GET(path="/get-brand-list?page={page}&length={length}&order_by={order_by}&order_direction={}",
     *   tags={"Operations with Admin"},
     *   summary="Perform Brands List (with collations)",
     *   description=" Get the list of brands with the collation",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="page",
     *     description="Page number",
     *     example="1",
     *     required=true
     *   ),
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="length",
     *     description="the number of items shown",
     *     example="10",
     *     required=true
     *   ),
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="order_by",
     *     description="sort by",
     *     example="brand_name",
     *     required=true
     *   ),
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="order_direction",
     *     description="to display in order",
     *     example="DESC",
     *     required=true
     *   ),
     *   @SWG\Response(response="200", description="Brands List")
     * )
     */

    /**
     * @SWG\GET(path="/get-brand-list",
     *   tags={"Operations with Admin"},
     *   summary="Perform Brands List (without collations)",
     *   description="Getting a list of brands without collation",
     *   produces={"application/json"},
     *   @SWG\Response(response="200", description="Brands List")
     * )
     */
    public function getBrandList(){
        $model = new Brands();

        $data = Input::all();

        list($brands,$recordsTotal,$recordsFiltered) = $model->getBrandList($data);

        return $this->response(true,$brands,$recordsTotal,$recordsFiltered);
    }


    /**
     * @SWG\Post(path="/create-brand",
     *  tags={"Operations with Admin"},
     *  summary="Perform update Brand",
     *  description="Action create Brand",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="edit object",
     *     description="JSON Object which create Brand.",
     *     required=true,
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="brand_name", type="string", example="New Brand",description="Brand Name"),
     *          @SWG\Property(property="first_name", type="string", example="John",description="User Name"),
     *          @SWG\Property(property="last_name", type="string", example="Smith",description="User Last Name"),
     *          @SWG\Property(property="email", type="string", example="john_smith@google.com",description="User email"),
     *          @SWG\Property(property="image_url", type="string", example="http://dev/uploads/093095da2b952dbcc90e4ce1b6080dd0.jpg",description="Brand Logo url (After Upload Action)")
     *     )
     *     ),
     *     @SWG\Response(response="200", description="Rerurn true or error message, The user is sent an e-mail")
     * )
     */


    public function createBrand(){
        $data = Input::all();

        //create new user - status Brand
        $user = new User($data);
        $user->identity = $this->getIdentity();
        $user->authToken = md5($user->generate());
        $password = $user->generate();
        $user->password = bcrypt($password);
        $user->remember_token = $password;
        $user->role = 'brand';

        //validate User data
        $validator = $user->validateBrand();
        if(!is_bool($validator)){
            return $this->response(false,$validator);
        }

        //create new brand
        $model = new Brands($data);
        $model->identity = $this->getIdentity();

        $validate = $model->createValidator();
        if(!is_bool($validate)){
            return $this->response(false,$validate);
        }

        //save new brand & new user. Create link to next step
        if($model->save() &&  $user->save()){

            $link = URL::to('forgot-password').'/'.$user->authToken;
            return $this->response(true,['message' => 'This link will be sent to the mail user '. $link .'. The token to validate - ' . $user->authToken . ' temporary password ' . $password]);
        }
        return $this->response(false,['mesage' => 'preservation failed']);
    }

    /**
     * @SWG\Post(path="/edit-brand/{identity}",
     *  tags={"Operations with Admin"},
     *  summary="Perform edit Brand",
     *  description="Action edit Brand",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="brand identity",
     *     example="DESC",
     *     required=true
     *   ),
     *     @SWG\Parameter(
     *     in="body",
     *     name="edit object",
     *     description="JSON Object which edit Brand.",
     *     required=true,
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="brand_name", type="string", example="New Brand",description="Brand Name"),
     *          @SWG\Property(property="first_name", type="string", example="John",description="User Name"),
     *          @SWG\Property(property="last_name", type="string", example="Smith",description="User Last Name"),
     *          @SWG\Property(property="email", type="string", example="john_smith@google.com",description="User email"),
     *          @SWG\Property(property="image_url", type="string", example="http://dev/uploads/093095da2b952dbcc90e4ce1b6080dd0.jpg",description="Brand Logo url (After Upload Action)")
     *     )
     *     ),
     *     @SWG\Response(response="200", description="Returns a new brand or errors")
     * )
     */


    public function editBrand($identity, Brands $brands){
        $data = Input::all();
        $model = $brands->where('identity','=',$identity)->first();

        $model->setRawAttributes($data);
        $validate = $model->editValidator();
        if(!is_bool($validate)){
            return $this->response(false,$validate);
        }

        $user = User::join('psa_brands', 'psa_users.email', '=', 'psa_brands.email')
            ->where('psa_brands.identity', '=', $identity)->first(['psa_users.*']);
        $user->email = $data['email'];
        $user->save();
        $model->save();

        return $this->response(true,Brands::where('identity','=',$identity)->first());
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
     * @SWG\Post(path="/create-interest",
     *  tags={"Operations with Interests"},
     *  summary="Perform create Interest",
     *  description="Action create Interest",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="create object",
     *     description="JSON Object which create Interest.",
     *     required=true,
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="interest_name", type="string", example="New Interest",description="Interest Name"),
     *          @SWG\Property(property="status", type="string", example="active", description="Status - active or inactive")
     *     )
     *     ),
     *     @SWG\Response(response="200", description="Rerurn true or error message")
     * )
     */
    public function createInterest() {
        $data = Input::all();

        $model = new Interests($data);
        $model->identity = $this->getIdentity();

        $validate = $model->createValidator();

        if(!is_bool($validate)){
            return $this->response(false,$validate);
        }

        $model->save();

        return $this->response(true,$model);
    }

    /**
     * @SWG\Post(path="/edit-interest/{identity}",
     *   tags={"Operations with Interests"},
     *   summary="Perform Edit Interest",
     *   description="To Edit an interest",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining an interest ID (e64d0697-515b-4f8d-9041-168984931fde)",
     *     example="e64d0697-515b-4f8d-9041-168984931fde",
     *     required=true
     *   ),
     *     @SWG\Parameter(
     *     in="body",
     *     name="Edit Interest object",
     *     description="JSON Object which Edit Interest",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(property="interest_name", type="string", example="Edit Interest",description="Interest Name"),
     *          @SWG\Property(property="status", type="string", example="active", description="Status - active or inactive")
     *     )
     *    ),
     *   @SWG\Response(response="200", description="To create a Interest. You must choose an interest to edit this interest")
     * )
     */
    public function editInterest($identity) {
        $data = Input::all();
        $model = Interests::where('identity','=',$identity)->first();

        if(is_null($model)){
            return $this->response(false,['message' => 'Interest not found']);
        }

        $model->setRawAttributes($data);
        $validate = $model->editValidator();
        if(!is_bool($validate)){
            return $this->response(false,$validate);
        }

        $model->save();

        return $this->response(true,Interests::where('identity','=',$identity)->first());
    }

    /**
     * @SWG\Get(path="/get-interest-list",
     *   tags={"Operations with Interests"},
     *   summary="Perform to get interests list. Influencer and Brand can see only active interests. Admin can see active and inactive interests.",
     *   description="Get interests list",
     *   produces={"application/json"},
     *   @SWG\Response(response="200", description="Get Interests List")
     * )
     */
    public function getInterestList() {
        if(Auth::user()->isAdmin()){
            $interests = Interests::all();
        } else {
            $interests = Interests::where('status','=', \Config::get('constants.INTEREST_ACTIVE'))->get();
        }
        $model = new Interests();
        list($interests,$recordsTotal,$recordsFiltered) = $model->getInterestsList($interests);
        return $this->response(true, $interests, $recordsTotal, $recordsFiltered);
    }

    /**
     * @SWG\Get(path="/get-interest/{identity}",
     *   tags={"Operations with Interests"},
     *   summary="Perform to get specified interest",
     *   description="Get specified interest",
     *   produces={"application/json"},
     *      @SWG\Parameter(
     *          in="path",
     *          type="string",
     *          name="identity",
     *          description="obtaining an interest ID (e64d0697-515b-4f8d-9041-168984931fde)",
     *          example="e64d0697-515b-4f8d-9041-168984931fde",
     *          required=true
     *      ),
     *   @SWG\Response(response="200", description="Get specified Interest")
     * )
     */
    public function getInterest($identity) {
        $interests = Interests::where('identity','=',$identity)->first();

        if(is_null($interests)){
            return $this->response(false,['message' => 'Interest not found']);
        }

        return $this->response(true,$interests);
    }

    /**
     * @SWG\Post(path="/del-interest",
     *  tags={"Operations with Interests"},
     *  summary="Perform Delete Interest",
     *  description="Action Delete Interest",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="delete object",
     *     description="JSON Object which Delete Interest.",
     *     required=true,
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="identity", type="string", example="81952d0a-7c2e-46cf-9c4e-70428c16fdb2",description="Interest unique identificator"),)
     *     ),
     *     @SWG\Response(response="200", description="Rerurn true or error message")
     * )
     */
    public function delInterest() {
        $data = Input::all();
        if(!isset($data['identity'])){
            return $this->response(false,['message' => 'Interest not found']);
        }
        $model = Interests::where('identity','=',$data['identity'])->first();
        if(!is_null($model)) {
            $model->delete();
            return $this->response(true, ['message' => 'Interest removed']);
        }
        return $this->response(false,['message' => 'Interest not found']);
    }

    /**
     * @SWG\Post(path="/create-temporary-account",
     *  tags={"Operations with Admin"},
     *  summary="Perform create influencer temporary account",
     *  description="Action create influencer temporary account",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="create object",
     *     description="JSON Object which create influencer temporary account.",
     *     required=true,
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="first_name", type="string", example="First Name",description="First Name"),
     *          @SWG\Property(property="last_name", type="string", example="Last Name",description="Last Name"),
     *          @SWG\Property(property="email", type="string", example="temporary_influencer@test.com",description="Email Address"),
     *          @SWG\Property(property="location", type="string", example="Location",description="Location"),
     *          @SWG\Property(property="twitter_screen_name", type="string", example="Twitter Username",description="Twitter Username"),
     *          @SWG\Property(property="twitter_id", type="string", example="123312123",description="Twitter User ID (id_str)"),
     *          @SWG\Property(property="twitter_followers", type="string", example="10000",description="Twitter Followers (number)"),
     *          @SWG\Property(property="twitter_verified", type="string", example="true",description="Twitter Verified: True or False"),
     *          @SWG\Property(property="instagram_name", type="string", example="Instagram Username",description="Instagram Username"),
     *          @SWG\Property(property="instagram_id", type="string", example="312123312",description="Instagram User ID"),
     *          @SWG\Property(property="instagram_followers", type="string", example="20000",description="Instagram Followers (number)"),
     *          @SWG\Property(property="instagram_verified", type="string", example="false",description="Instagram Verified: True or False")
     *
     *     )
     *     ),
     *     @SWG\Response(response="200", description="Rerurn true or error message")
     * )
     */
    public function createTemporaryAccount(Request $request)
    {
        $data = Input::all();
        $file = $request->file('file');
        if($file && ($file->getClientOriginalExtension() === 'csv')) {
            Storage::put(
                'uploads/' . $file->getClientOriginalName(),
                file_get_contents($file->getRealPath())
            );
            $contents = Storage::get('uploads/' . $file->getClientOriginalName());
//            $csv = new Parsers\CsvParser($contents);
            $errors = [];
        }
        $model = new User($data);
        $socialAcc = new SocialAccount($data);
        $validateSocial = $socialAcc->createTmpValidator($data);
        if (!is_bool($validateSocial)) {
            return $this->response(false,$validateSocial);
        }
        $result = $model->createTemporaryInfluencer($data);
        if(is_array($result) && isset($result['error'])) {
           return $this->response(false, $result['error']);
        }
        return $this->response(true, ['message' => 'successfully imported']);
    }

}