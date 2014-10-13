<?php

namespace ProjectInfinity\ReportRTS\util;

use pocketmine\utils\TextFormat;

class MessageHandler {

    private static $colors;

    public static $generalError;
    public static $permissionError;

    public static $noTickets;
    public static $noStaff;

    public static $holdNoTickets;

    public static $ticketTooShort;
    public static $ticketTooMany;
    public static $ticketTooFast;
    public static $ticketDuplicate;
    public static $ticketOpenedUser;
    public static $ticketOpenedStaff;
    public static $ticketNotOpen;
    public static $ticketNotClaimed;
    public static $ticketClose;
    public static $ticketCloseMulti;
    public static $ticketCloseUser;
    public static $ticketCloseText;
    public static $ticketCloseOffline;
    public static $ticketClaim;
    public static $ticketClaimUser;
    public static $ticketClaimText;
    public static $ticketUnclaim;
    public static $ticketUnclaimUser;
    public static $ticketHold;
    public static $ticketHoldUser;
    public static $ticketHoldText;
    public static $ticketStatusError;

    public static $userNotExists;

    public static $staffList;

    public static $separator;

    public static function load() {
        self::$colors = (new \ReflectionClass(TextFormat::class))->getConstants();
        self::$generalError = self::parseColors('%red%An error occurred. Reference: %s');
        self::$permissionError = self::parseColors('%yellow%You need permission "%s" to do that');
        self::$noTickets = self::parseColors('%white%There are no tickets at this time.');
        self::$noStaff = self::parseColors('%yellow%There are no staff members online.');
        self::$holdNoTickets = self::parseColors('%gold%There are no tickets on hold right now.');
        self::$ticketTooShort = self::parseColors('%red%Your ticket needs to contain at least %s words.');
        self::$ticketTooMany = self::parseColors('%red%You have too many open tickets, please wait before opening more.');
        self::$ticketTooFast = self::parseColors('%red%You need to wait %s seconds before attempting to open another ticket.');
        self::$ticketDuplicate = self::parseColors('%red%Your ticket has not been opened because it was detected as a duplicate.');
        self::$ticketOpenedUser = self::parseColors('%gold%You opened a ticket. A staff member should be with you soon.');
        self::$ticketOpenedStaff = self::parseColors('%green%A new ticket has been opened by %s, id assigned #%u.');
        self::$ticketNotOpen = self::parseColors('%red%Specified ticket is not open.');
        self::$ticketNotClaimed = self::parseColors('%red%You may only unclaim tickets that are claimed.');
        self::$ticketClose = self::parseColors('%gold%Ticket #%u was closed by %s.');
        self::$ticketCloseMulti = self::parseColors('%gold%While you were gone, %u tickets were closed.  Use /%s to check your currently open tickets.');
        self::$ticketCloseUser = self::parseColors('%gold%%s completed your ticket.');
        self::$ticketCloseText = self::parseColors('%gold%Ticket text: %yellow%%s %gold%Comment: %yellow%%s');
        self::$ticketCloseOffline = self::parseColors('%gold%One of your tickets have been closed while you were offline.');
        self::$ticketClaim = self::parseColors('%gold%%s is now handling ticket #%u.');
        self::$ticketClaimUser = self::parseColors('%gold%%s is now handling your ticket.');
        self::$ticketUnclaim = self::parseColors('%gold%%s is no longer handling ticket #%u.');
        self::$ticketUnclaimUser = self::parseColors('%gold%%s is no longer handling your ticket.');
        self::$ticketClaimText = self::parseColors('%gold%Ticket text: %yellow%%s');
        self::$ticketHold = self::parseColors('%gold%Ticket #%u was put on hold by %s');
        self::$ticketHoldText = self::parseColors('%gold%Your ticket was put on hold by %s');
        self::$ticketHoldText = self::parseColors('%gold%Ticket text: %yellow%%s %gold%Reason: %yellow%%s');
        self::$userNotExists = self::parseColors('%red%The specified user %s does not exist or contains invalid characters.');
        self::$ticketStatusError = self::parseColors('%red%Unable to set ticket status. Check that the status of the ticket does not collide.');
        self::$separator = self::parseColors('%yellow%, ');
        self::$staffList = self::parseColors('%aqua%Staff online: %yellow%%s');
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