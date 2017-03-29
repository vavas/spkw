<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function __construct()
    {
//        header('Access-Control-Allow-Origin: https://api-docs.sparkwoo.com');
//        header('Access-Control-Allow-Methods: POST,GET,PUT,DELETE,OPTIONS');
//        header('Access-Control-Allow-Headers: Content-Type, Authorization');
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
