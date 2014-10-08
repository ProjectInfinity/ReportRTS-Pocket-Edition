<?php

namespace ProjectInfinity\ReportRTS\util;

use pocketmine\utils\TextFormat;

class MessageHandler {

    private static $colors;

    public static $generalError;
    public static $permissionError;

    public static $noTickets;

    public static $holdNoTickets;

    public static $ticketTooShort;
    public static $ticketTooMany;
    public static $ticketTooFast;
    public static $ticketDuplicate;
    public static $ticketOpenedUser;
    public static $ticketOpenedStaff;
    public static $ticketNotOpen;
    public static $ticketClaim;
    public static $ticketClaimUser;
    public static $ticketClaimText;

    public static function load() {
        self::$colors = (new \ReflectionClass(TextFormat::class))->getConstants();
        self::$generalError = self::parseColors('%red%An error occurred. Reference: %s');
        self::$permissionError = self::parseColors('%yellow%You need permission "%s" to do that');
        self::$noTickets = self::parseColors('%white%There are no tickets at this time.');
        self::$holdNoTickets = self::parseColors('%gold%There are no tickets on hold right now.');
        self::$ticketTooShort = self::parseColors('%red%Your ticket needs to contain at least %s words.');
        self::$ticketTooMany = self::parseColors('%red%You have too many open tickets, please wait before opening more.');
        self::$ticketTooFast = self::parseColors('%red%You need to wait %s seconds before attempting to open another ticket.');
        self::$ticketDuplicate = self::parseColors('%red%Your ticket has not been opened because it was detected as a duplicate.');
        self::$ticketOpenedUser = self::parseColors('%gold%You opened a ticket. A staff member should be with you soon.');
        self::$ticketOpenedStaff = self::parseColors('%green%A new ticket has been opened by %s, id assigned #%u.');
        self::$ticketNotOpen = self::parseColors('%red%Specified ticket is not open.');
        self::$ticketClaim = self::parseColors('%gold%%s is now handling ticket #%u.');
        self::$ticketClaimUser = self::parseColors('%gold%%s is now handling your ticket.');
        self::$ticketClaimText = self::parseColors('%gold%Ticket text: %yellow%%s');
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