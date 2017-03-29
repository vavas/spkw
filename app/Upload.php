<?php
/**
 * Created by PhpStorm.
 * User: QA
 * Date: 23.03.2016
 * Time: 12:59
 */

namespace App;


use Illuminate\Support\Facades\URL;
use AWS;
use SoapBox\Formatter\Parsers;
use Route;
class Upload
{

    public static function Upload(){

        $file = [];

        if(isset($_FILES['file'])){
            $file = $_FILES['file'];
        }

        if(empty($file)){
            return false;
        }

        $path = $_SERVER['DOCUMENT_ROOT'].'/uploads/';

        if(!file_exists($path)){
            mkdir($path,0777);
        }

        $extension = preg_split('#\.#',$file['name']);
        $extension = '.'.$extension[1];
        $fileName = md5(time()).$extension;

        if(move_uploaded_file($file['tmp_name'],$path.$fileName)){

            $s3 = AWS::createClient('s3');
            $result = $s3->putObject(array(
                'Bucket'     => 'sparkwoo-uploads',
                'Key'        => 'uploads/' . $fileName,
                'ACL' => 'public-read',
                'SourceFile' => public_path().'/uploads/'.$fileName,
            ));

            return $result['ObjectURL'];
        }

        return false;
    }

    public static function UploadBase64($fileBase64)
    {
        $imgdata = base64_decode($fileBase64);
        $f = finfo_open();
        $mime_type = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);
        switch($mime_type)
        {
            case 'image/gif'    : $extension = '.gif';   break;
            case 'image/png'    : $extension = '.png';   break;
            case 'image/jpeg'   : $extension = '.jpg';   break;
            case 'video/mp4'    : $extension = '.mp4';   break;

            default :
                throw new ApplicationException('The file uploaded was not a valid image file.');
                break;
        }

        $path = $_SERVER['DOCUMENT_ROOT'].'/uploads/';
        if(!file_exists($path)){
            mkdir($path,0777);
        }
        $fileName = md5(time()). $extension;
        if(file_put_contents(public_path() . '/uploads/' . $fileName , $imgdata)) {
            return URL::to('/uploads/' . $fileName);
        }
        return false;
    }

}