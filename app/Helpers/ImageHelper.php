<?php
namespace App\Helpers;

use Carbon\Carbon;
use Image;


class ImageHelper
{
    public static function upload(string $dir, $old_file = null, $image = null)
    {
        if ($image != null) {
            $directoryPath = public_path($dir);

            if (!is_dir($directoryPath)) {
                mkdir($directoryPath, 0755, true);
            }


            // if ($image->getClientOriginalExtension() == 'gif') {
            //     $imageName = Carbon::now()->toDateString() . "-" . uniqid() . ".gif";
            //     $image->move($directoryPath, $imageName);

            // }else {
            //     $image = Image::make($image);
            //     $image = $image->encode('webp');
            //     $imageName = Carbon::now()->toDateString() . "-" . uniqid() . ".webp";
            //     $image->save($directoryPath . '/' . $imageName);
            // }

            if ($old_file) {
                if (file_exists($directoryPath . '/' . $old_file)) {
                    unlink($directoryPath . '/' . $old_file);
                }
            }


        } else {
            $imageName = null;
        }

        return $imageName;
    }


    // single image upload
    public static function handleUpdatedUploadedImage($file, $path, $data, $field)
    {
        $directoryPath = public_path($path);


        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }

        $name = time() . $field . '.' . $file->getClientOriginalExtension();
        $file->move(public_path() . $path, $name);
        if ($data && $data[$field] != null) {
            if (file_exists(public_path() . $path.'/' . $data[$field])) {
                unlink(public_path() . $path.'/' . $data[$field]);
            }
        }
        return $name;
    }

    // Api single image upload
    // public static function handleAPIUpdatedUploadedImage($file, $path, $data, $delete_path, $field)
    // {
    //     $imageByte = base64_decode($request->id_card);
    //     $idCardMimeType = Common::mime_content_type_from_buffer($imageByte);
    //     $imageName = time(). $field. Common::getFileExtensionFromMimeType($idCardMimeType);
    //     file_put_contents((public_path(). $path . $imageName), $imageByte);
    //     if ($data && $data[$field] != null) {
    //         if (file_exists(public_path() . $delete_path . $data[$field])) {
    //             unlink(public_path() . $delete_path . $data[$field]);
    //         }
    //     }
    //     return $imageName;
    // }

    public static function getFileExtensionFromMimeType($mimeType)
    {
        switch ($mimeType) {
            case 'image/jpeg':
                return '.jpg';
            case 'image/png':
                return '.png';
            case 'application/pdf':
                return '.pdf';
            // Add more cases for other file types if needed
            default:
                return '.txt'; // Default to .txt if the file type is unknown
        }
    }

    /**
     * Wrapper for mime_content_type function using a buffer instead of a file path.
     * Requires the FileInfo PHP extension.
     */
    public static function mime_content_type_from_buffer($buffer)
    {
        $tmpFile = tmpfile();
        fwrite($tmpFile, $buffer);
        $metaData = stream_get_meta_data($tmpFile);
        $filePath = $metaData['uri'];
        $mimeType = mime_content_type($filePath);
        fclose($tmpFile);
        return $mimeType;
    }

    // multi image upload
    public static function handleUploadMultipleFile($files, $path)
    {
        $directoryPath = public_path($path);
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }

        $fileNames = [];
        foreach ($files as $key => $file) {
            if ($file) {

                $name = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path() . $path, $name);

                array_push($fileNames, $name);
            }
        }

        return json_encode($fileNames);
    }

    // public static function get_time($timezone_name,$date,$targetTimezone){
    //     if ($timezone_name == '') {
    //         return $date;
    //     }

    //     if (!Common::isTimezoneValid($timezone_name)) {
    //         return $date;
    //     }

    //     $originalDate = Carbon::createFromFormat('Y-m-d H:i:s', $date, $timezone_name);

    //     $convertedDate = $originalDate->setTimezone($targetTimezone??'UTC');
    //     return $convertedDate;
    // }

    public static function humanReadableTime($tz, $time){
        date_default_timezone_set($tz);
        $currentDateTime = Carbon::now();
        $notificationTime = Carbon::parse($time);
        $humanReadableTime = $notificationTime->diffForHumans($currentDateTime);
        return $humanReadableTime;
    }

}
