<?php

namespace ProjectInfinity\ReportRTS\util;

use pocketmine\utils\TextFormat;

class MessageHandler {

    private static $colors;

    public static $generalError;

    public static function load() {
        self::$generalError = '%red%An error occurred. Reference: %s';
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