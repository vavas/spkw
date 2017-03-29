<?php

namespace App\Http\Controllers;

use App\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Requests;

class VersionController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['only' => ['modifyVersion']]);
    }


    /**
     * @SWG\POST(path="/modify-version/{identity}",
     *   tags={"Operations with Version"},
     *   summary="Perform modify Version",
     *   description="modify version params",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     type="string",
     *     name="identity",
     *     description="obtaining a Bundle ID (com.sparkwoo.aphone)",
     *     example="com.sparkwoo.aphone",
     *     required=true
     *     ),
     *     @SWG\Parameter(
     *     in="body",
     *     name="version params",
     *     description="JSON Object which modify version",
     *     required=true,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="minimum_version", type="string", example="1.0.0", description="Minimum Version"),
     *         @SWG\Property(property="current_version", type="string", example="1.1.0", description="Current Version"),
     *         @SWG\Property(property="store_url", type="string", example="iTunes URL", description="Store URL")
     *     )
     *    ),
     *   @SWG\Response(response="200", description="The modify version or Error")
     * )
     */
    public function modifyVersion($bundle_id)
    {
        $data = Input::all();
        $model = Version::find($bundle_id);

        if(is_null($model)){
            return $this->response(false,['message' => 'Bundle ID not found']);
        }
        $model->setRawAttributes($data);
        $validate = $model->validator();
        if(!is_bool($validate)){
            return $this->response(false,$validate);
        }
        $model->save();

        return $this->response(true,Version::find($bundle_id));
    }

    /**
     * @SWG\GET(path="/get-version",
     *   tags={"Operations with Version"},
     *   summary="Perform Version information",
     *   description="Return Version information",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="body",
     *     name="App Version and Bundle ID, query-string parameters",
     *     description="app_version=1.0.0&bundle_id=com.sparkwoo.aphone",
     *     required=true,
     *     @SWG\Schema(
     *         type="string", example=""
     *     )
     *    ),
     *
     *   @SWG\Response(response="200", description="return Version information or Error")
     * )
     */
    public function getVersion()
    {
        $data = Input::all();
        if (isset($data['app_version']) && $data['app_version'] != ""
            && isset($data['bundle_id']) && $data['bundle_id'] != ""
        ) {
            $model = Version::find($data['bundle_id']);

            if(is_null($model)){
                return $this->response(false,['message' => 'Bundle ID not found']);
            }

            if (version_compare($data['app_version'], $model->minimum_version) < 0) {
                return $this->response(true,['message' => 'require update', 'forced_update' => true, 'store_url' => $model->store_url]);
            }

            if ((version_compare($data['app_version'], $model->minimum_version) >= 0) &&
                (version_compare($data['app_version'], $model->current_version) < 0)
            ) {
                return $this->response(true,['message' => 'optional update available', 'store_url' => $model->store_url]);
            }
            if (version_compare($data['app_version'], $model->current_version) == 0) {
                return $this->response(true,['message' => 'version of the app matches the current version details in Admin']);
            }


        } else {
            return $this->response(false,['message' => 'Bundle ID and Minimum Version is required']);
        }
        return $this->response(false,$model);
    }
}
