<?php
/**
 * Created by PhpStorm.
 * User: QA
 * Date: 11.03.2016
 * Time: 10:41
 */

namespace App\Http\Controllers;

use App\SocialAccount;
use JWTAuth;
use Auth;
use Illuminate\Support\Facades\Input;


class AccountController extends Controller
{

    function __construct(){
        $this->middleware('jwt.auth');
    }

    /**
     * @SWG\Post(path="/add-social-account",
     *   tags={"Operations with Social Accounts"},
     *   summary="Perform Add Social Accounts",
     *   description="Add Influence Social Account",
     *   produces={"application/json"},
     *   consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="Add Account object",
     *     description="JSON Object which Add Account",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="Twitter", type="object",
     *          @SWG\Property(property="social", type="string", example="Twitter"),
     *          @SWG\Property(property="username", type="string", example="Steven_Jobs"),
     *          @SWG\Property(property="user_id", type="string", example="#123123123"),
     *          @SWG\Property(property="followers", type="int", example="5000"),
     *        )
     *     )
     *   ),
     *   @SWG\Response(response="200", description="Rerurn Error or true")
     * )
     */
    public function addSocialAccount(){
        $data = Input::all();

        foreach($data as $account){
            $model = new SocialAccount($data);

            $model->user_code = Auth::user()->id;

            $validate = $model->createValidator($data);
            if(!is_bool($validate)){
                return $this->response(false,$validate);
            }
            $model->save();
        }

        return $this->response(true,$model);
    }

    /**
     * @SWG\Post(path="/edit-social-account",
     *   tags={"Operations with Social Accounts"},
     *   summary="Perform Edit Social Accounts",
     *   description="Edit Influence Social Account",
     *   produces={"application/json"},
     *   consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="Add Account object",
     *     description="JSON Object which Edit Account",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="id", type="int", example="1"),
     *         @SWG\Property(property="social", type="string", example="Twitter"),
     *         @SWG\Property(property="username", type="string", example="Steven_Jobs"),
     *         @SWG\Property(property="user_id", type="string", example="#123123123"),
     *         @SWG\Property(property="followers", type="int", example="5000"),
     *     )
     *   ),
     *   @SWG\Response(response="200", description="Rerurn Error or true")
     * )
     */
    public function editSocialAccount(){
        $data = Input::all();
        /**
         * if not isset account id - return error
         */
        if(!isset($data['id'])){
            return $this->response(false, ['message' => 'Account not found']);
        }

        $model = SocialAccount::find($data['id']);
        /**
         * if not isset account by id - return error
         */
        if(is_null($model)){
            return $this->response(false, ['message' => 'Account not found']);
        }

        $validate = $model->updateValidator($data);
        if(!is_bool($validate)){
            return  $this->response(false,$validate);
        }
        $model->setRawAttributes($data);
        $model->save();
        return $this->response(true,SocialAccount::find($data['id']));
    }

    /**
     * @SWG\GET(path="/get-social-account-list-by-user",
     *   tags={"Operations with Social Accounts"},
     *   summary="Perform Social network list by User",
     *   description="Return Social network list by User",
     *   produces={"application/json"},
     *   @SWG\Response(response="200", description="Return Social network list by User or Error")
     * )
     */
    public function getSocialAccountListByUser(){

        $userId = Auth::user()->id;

        $model = SocialAccount::where('user_code', $userId)->get();;

        return $this->response(true,$model);
    }

    /**
     * @SWG\Post(path="/view-social-account",
     *   tags={"Operations with Social Accounts"},
     *   summary="Perform View Social Accounts",
     *   description="View Influence Social Account",
     *   produces={"application/json"},
     *   consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="View Account id",
     *     description="JSON Object which Delete Account",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="id", type="int", example="1")
     *     )
     *   ),
     *   @SWG\Response(response="200", description="Rerurn Error or true")
     * )
     */
    public function viewSocialAccount(){
        $data = Input::all();

        /**
         * if not isset account id - return error
         */
        if(!isset($data['id'])){
            return $this->response(false, ['message' => 'Account not found']);
        }

        $model = SocialAccount::find($data['id']);

        if(!is_null($model)){
            return $this->response(true,$model);
        }
        return $this->response(false, ['message' => 'Account not found']);
    }


    /**
     * @SWG\Post(path="/del-social-account",
     *   tags={"Operations with Social Accounts"},
     *   summary="Perform Delete Social Accounts",
     *   description="Delete Influence Social Account",
     *   produces={"application/json"},
     *   consumes={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="Delete Account id",
     *     description="JSON Object which Delete Account",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="id", type="int", example="1")
     *     )
     *   ),
     *   @SWG\Response(response="200", description="Rerurn Error or true")
     * )
     */
    public function delSocialAccount(){
        $data = Input::all();

        /**
         * if not isset account id - return error
         */
        if(!isset($data['id'])){
            return $this->response(false, ['message' => 'Account not found']);
        }

        $model = SocialAccount::find($data['id']);
        if(!is_null($model)){
            $model->delete();
            return $this->response(true, ['message' => 'Account removed']);
        }
        return $this->response(false, ['message' => 'Account not found']);
    }

    /**
     * @SWG\GET(path="/get-social-network-list",
     *   tags={"Operations with Social Accounts"},
     *   summary="Perform Social network list",
     *   description="Return Social network list",
     *   produces={"application/json"},
     *   @SWG\Response(response="200", description="The auxiliary function . Getting a list of social networks")
     * )
     */
    public function getSocialNetworkList(){
        $model = new SocialAccount();

        return $this->response(true,$model->socialNetwork);
    }

}