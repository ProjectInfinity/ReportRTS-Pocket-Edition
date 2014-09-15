<?php

namespace ProjectInfinity\ReportRTS\util;

use pocketmine\utils\TextFormat;

class MessageHandler {

    private static $colors;

    public static $generalError;

    public static function load() {
        MessageHandler::$generalError = '%red%An error occurred. Reference: %s';
        MessageHandler::$colors = (new \ReflectionClass(TextFormat::class))->getConstants();
    }

    private function parseColors($message) {
        $message = str_replace("")
    }
}