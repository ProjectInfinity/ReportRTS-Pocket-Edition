<?php

namespace ProjectInfinity\ReportRTS\util;

use pocketmine\utils\TextFormat;

class MessageHandler {

    private static $colors;

    public static $generalError;
    public static $permissionError;

    public static function load() {
        self::$generalError = self::parseColors('%red%An error occurred. Reference: %s');
        self::$permissionError = self::parseColors('%yellow%You need permission %s to do that');
        self::$colors = (new \ReflectionClass(TextFormat::class))->getConstants();
    }

    /**
     * Iterates the color array and replaces the color codes from the provided String.
     * @param $message
     * @return String
     */
    private function parseColors($message) {
        $msg = $message;
        foreach(self::$colors as $color) {
            $key = "%".strtolower($color)."%";
            if(strpos($msg, $key) === true) {
                $msg = str_replace($key, $color, $msg);
            }
        }
        return $msg;
    }
}