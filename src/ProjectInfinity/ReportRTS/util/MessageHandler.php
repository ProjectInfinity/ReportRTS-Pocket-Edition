<?php

namespace ProjectInfinity\ReportRTS\util;

use pocketmine\utils\TextFormat;

class MessageHandler {

    private static $colors;

    public static $generalError;

    public static function load() {
        MessageHandler::$generalError = '%red%An error occurred. Reference: %s';
        MessageHandler::$colors = array("%red%" => TextFormat::RED);
        MessageHandler::$colors = array("%black%" => TextFormat::BLACK);
        MessageHandler::$colors = array("%dark_blue%" => TextFormat::DARK_BLUE);
        MessageHandler::$colors = array("%dark_green%" => TextFormat::DARK_GREEN);
        MessageHandler::$colors = array("%dark_aqua%" => TextFormat::DARK_AQUA);
        // TODO: Finish above.
    }

    private function parseColors($message) {
        $message = str_replace("")
    }
}