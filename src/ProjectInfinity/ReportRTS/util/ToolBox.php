<?php

namespace ProjectInfinity\ReportRTS\util;

use pocketmine\utils\TextFormat;
use ProjectInfinity\ReportRTS\ReportRTS;

class ToolBox {

# Set up constants for relative time function.
const SECOND_MILLIS = 1000;
const MINUTE_MILLIS =  60000; # 60 * SECOND_MILLIS;
const HOUR_MILLIS = 3600000; # 60 * MINUTE_MILLIS;
const DAY_MILLIS = 86400000; # 24 * HOUR_MILLIS;

    /**
     * Returns a cleaned up String from the text provided, preferably from a sign.
     * @param $text
     * @return string
     */
    public static function cleanSign($text) {
        $result = "";
        foreach($text as $line) {
            if(strlen($line) > 0) $result .= trim($line);
        }
        return $result;
    }

    public static function countOpenTickets($player) {
        $i = 0;
        foreach(ReportRTS::$tickets as $ticket) {
            // TODO: This needs testing....
            if($ticket->getName() == $player) $i++;
        }
        return $i;
    }


    /**
     * Returns a float representing the amount of time in millis it took to execute command
     * from start to finish.
     * @param $start
     * @return float
     */
    public static function getTimeSpent($start) {
        return (float) number_format((microtime(true) * 1000)  - $start, 2, ".", "");
    }

    public static function timeSince($time) {

        # If time is given in seconds as opposed to milliseconds, convert.
        if($time < 1000000000000) $time *= 1000;

        $now = microtime(true) * 1000;
        if($time > $now || $time <= 0) return null;
        $diff = $now - $time;
        if($diff < self::MINUTE_MILLIS) {
            return TextFormat::GREEN . "just now" . TextFormat::GOLD;
        } else if($diff < 2 * self::MINUTE_MILLIS) {
            return TextFormat::GREEN . "1 minute ago" . TextFormat::GOLD; // a minute ago
        } else if($diff < 50 * self::MINUTE_MILLIS) {
            return "" . TextFormat::GREEN . round($diff / self::MINUTE_MILLIS) . " min ago" . TextFormat::GOLD;
        } else if($diff < 90 * self::MINUTE_MILLIS) {
            return TextFormat::GREEN . "1 hour ago" . TextFormat::GOLD;
        } else if ($diff < 24 * self::HOUR_MILLIS) {
            return "" . TextFormat::YELLOW . round($diff / self::HOUR_MILLIS) . " hours ago" . TextFormat::GOLD;
        } else if ($diff < 48 * self::HOUR_MILLIS) {
            return TextFormat::RED . "yesterday" . TextFormat::GOLD;
        } else {
            return "" . TextFormat::RED . round($diff / self::DAY_MILLIS) . " days ago" . TextFormat::GOLD;
        }
    }

    /**
     * Checks whether the provided String is a Integer or not
     * and returns a boolean representing the result.
     * @param $number
     * @return bool
     */
    public static function isNumber($number) {
        if(is_numeric($number)) $number = intval($number);
        if(!is_int($number) || $number <= 0) {
            return false;
        }
        return  true;
    }
}