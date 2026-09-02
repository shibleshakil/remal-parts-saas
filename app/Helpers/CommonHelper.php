<?php
namespace App\Helpers;

use DateTime;
use DateInterval;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\BusinessSetting;
use App\Models\WebsiteSettings;

class CommonHelper
{

    /**
     * overWrite the Env File values.
     * @param  String type
     * @param  String value
     * @return \Illuminate\Http\Response
     */
    public static function overWriteEnvFile($type, $val)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $val = '"' . trim($val) . '"';
            $envContents = file_get_contents($path);
            $pattern = "/^$type=/m";

            if (preg_match($pattern, $envContents)) {
                // Variable exists, replace its old value
                $envContents = preg_replace("/^$type=(.*)/m", "$type=$val", $envContents);
                file_put_contents($path, $envContents);
            } else {
                // Variable doesn't exist, append it
                file_put_contents($path, "\n$type=$val", FILE_APPEND);
            }
        }
    }


    public static function get_business_settings($name)
    {
        $config = null;
        $data = BusinessSetting::where(['type' => $name])->first();
        if (isset($data)) {
            $config = json_decode($data['value'], true);
            if (is_null($config)) {
                $config = $data['value'];
            }
        }

        return $config;
    }

    public static function get_website_settings($section)
    {
        $config = null;
        $data = WebsiteSettings::where(['section' => $section])->first();
        if (isset($data)) {
            $config = json_decode($data['value'], true);
            if (is_null($config)) {
                $config = $data['value'];
            }
        }

        return $config;
    }


    public static function apiErrorProcessor($validator)
    {
        $err_keeper = '';
        foreach ($validator->errors()->getMessages() as $index => $error) {
            $err_keeper .= $error[0] . "\n";
        }
        return $err_keeper;
    }

    public static function timezoneList(){
        $timezone_list = DateTimeZone::listIdentifiers(
            DateTimeZone::ALL
        );

        return $timezone_list;
    }

    public static function getUserTimeZone(){
        $timezone = 'GMT';
        if (\session()->has('timezone')) {
            $timezone = \session()->get('timezone');
        }

        if ($timezone == "GMT+00:00") {
            return 'GMT';
        }

        if (self::isValidTimeZone($timezone)) {
            return $timezone;
        }

        return 'GMT';

    }

    public static function isValidTimeZone($timezone)
    {
        try {
            new DateTimeZone($timezone);
            return true;
        } catch (\Exception $e) {
            return false;
        }

    }

    public static function dateTimeAsUsersLocal($date){
        $userTimezone = self::getUserTimeZone();
        // Create a DateTime object from the input string
        $inputDateTime = new DateTime($date);

        // Set the desired time zone (GMT+6)
        $inputDateTime->setTimezone(new DateTimeZone($userTimezone));

        // Get the current date in the same time zone
        $currentDate = new DateTime('now', new DateTimeZone($userTimezone));

        // Check if the date is today
        $isToday = $inputDateTime->format('Y-m-d') === $currentDate->format('Y-m-d');

        // Output the formatted date
        if ($isToday) {
            return 'Today ' . $inputDateTime->format('H:i') .' ('. $userTimezone . ')';
        } else {
            return $inputDateTime->format('d-M H:i') .' ('. $userTimezone . ')';
        }

    }

    public static function dateTimeWithYearAsUsersLocal($date){
        $userTimezone = self::getUserTimeZone();
        // Create a DateTime object from the input string
        $inputDateTime = new DateTime($date);

        // Set the desired time zone (GMT+6)
        $inputDateTime->setTimezone(new DateTimeZone($userTimezone));

        // Get the current date in the same time zone
        $currentDate = new DateTime('now', new DateTimeZone($userTimezone));

        // Check if the date is today
        $isToday = $inputDateTime->format('Y-m-d') === $currentDate->format('Y-m-d');

        // Output the formatted date
        if ($isToday) {
            return 'Today ' . $inputDateTime->format('H:i') .' ('. $userTimezone . ')';
        } else {
            return $inputDateTime->format('d-M-Y H:i') .' ('. $userTimezone . ')';
        }

    }

    public static function dateTimeAsSystem($dateString){
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $dateString, 'UTC');
        $date->setTimezone('Europe/London');
        $formattedDate = $date->format('D, M d, Y g:i A (e)');
        return $formattedDate;

    }


   public static function userHasAccess(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

}
