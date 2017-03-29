<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\States;
use App\Upload;

use Thujohn\Twitter\Facades\Twitter;
use Google_Client;
use Google_Service_YouTube;
use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\URL;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('jwt.auth');
//        $this->middleware('jwt.auth', ['except' => ['index']]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('welcome');

    }


    /**
     * @SWG\Post(path="/upload",
     *  tags={"Auxiliary operations"},
     *  summary="Perform upload image",
     *  description="Action upload image",
     *  produces={"application/json"},
     *  consumes={"multipart/form-data"},
     *     @SWG\Parameter(
     *     in="form",
     *     paramType="body",
     *     type="file",
     *     name="file",
     *     description="the image being loaded",
     *     allowMultiple = "false",
     *     ),
     *     @SWG\Response(response="200", description="Returns a new brand or errors")
     * )
     */
    public function upload(){

        $uploadUrl = Upload::Upload();

        if($uploadUrl){
            return $this->response(true,['url' => $uploadUrl]);
        }

        return $this->response(false,['message' => 'Upload failed']);
    }

    public function csv()
    {
        return view('welcome');
    }


    /**
     * @SWG\GET(path="/get-states-list",
     *  tags={"Auxiliary operations"},
     *  summary="Perform states",
     *  description="Action Get States list",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  @SWG\Response(response="200", description="Rerurn States list or error message")
     * )
     */
    public function getStatesList(){

        $states = States::getStatesList();

        if(is_null($states)){
            return $this->response(false,['mesage' => 'To get a list of failed']);
        }
        return $this->response(true,$states);
    }

    /**
     * @param $status
     * @param array $message
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
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
