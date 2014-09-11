<?php

namespace ProjectInfinity\ReportRTS\util;

class ToolBox {

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
        
        return $i;
    }

}