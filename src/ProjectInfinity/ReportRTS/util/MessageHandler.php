<?php

namespace ProjectInfinity\ReportRTS\util;

use pocketmine\utils\TextFormat;

class MessageHandler {

    private static $colors;

    public static $generalError;
    public static $permissionError;

    public static $noTickets;

    public static function load() {
        self::$colors = (new \ReflectionClass(TextFormat::class))->getConstants();
        self::$generalError = self::parseColors('%red%An error occurred. Reference: %s');
        self::$permissionError = self::parseColors('%yellow%You need permission "%s" to do that');
        self::$noTickets = self::parseColors('%white%There are no tickets at this time.');
    }

    /**
     * Iterates the color array and replaces the color codes from the provided String.
     * @param $message
     * @return String
     */
    private static function parseColors($message) {
        $msg = $message;
        foreach(self::$colors as $color => $value) {
            $key = "%".strtolower($color)."%";
            if(strpos($msg, $key) !== false) {
                $msg = str_replace($key, $value, $msg);
            }
        }
        return $msg;
    }
}