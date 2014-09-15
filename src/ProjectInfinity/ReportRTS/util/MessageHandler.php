<?php

namespace ProjectInfinity\ReportRTS\util;

use pocketmine\utils\TextFormat;

class MessageHandler {

    private static $colors;

    public static $generalError;

    public static function load() {
        MessageHandler::$generalError = '%red%An error occurred. Reference: %s';
        MessageHandler::$colors = array("%black%" => TextFormat::BLACK,
            "%dark_blue%" => TextFormat::DARK_BLUE, "%dark_green%" => TextFormat::DARK_GREEN,
            "%dark_aqua%" => TextFormat::DARK_AQUA, "%dark_red%" => TextFormat::DARK_RED,
            "%dark_purple%" => TextFormat::DARK_PURPLE, "%gold%" => TextFormat::GOLD,
            "%gray%" => TextFormat::GRAY, "%dark_gray%" => TextFormat::DARK_GRAY,
            "%blue%" => TextFormat::BLUE, "%green%" => TextFormat::GREEN,
            "%aqua%" => TextFormat::AQUA, "%red%" => TextFormat::RED,
            "%light_purple%" => TextFormat::LIGHT_PURPLE, "%yellow%" => TextFormat::YELLOW,
            "%white%" => TextFormat::WHITE, "%obfuscated%" => TextFormat::OBFUSCATED,
            "%bold%" => TextFormat::BOLD, "%strikethrough%" => TextFormat::STRIKETHROUGH,
            "%underline%" => TextFormat::UNDERLINE, "%italic%" => TextFormat::ITALIC,
            "%reset%" => TextFormat::RESET);
    }

    private function parseColors($message) {
        $message = str_replace("")
    }
}