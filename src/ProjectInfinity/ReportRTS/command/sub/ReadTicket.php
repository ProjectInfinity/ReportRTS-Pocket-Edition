<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\MessageHandler;
use ProjectInfinity\ReportRTS\util\ToolBox;

class ReadTicket {

    private $plugin;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
    }
    public function handleCommand($sender, $args) {

        # Not enough arguments to be anything but "/ticket read".
        if(count($args) < 2) {
            echo "less than 2 arguments";
            return $this->viewPage($sender, 1);
        }

        switch(strtoupper($args[1])) {
            case "P":
            case "PAGE":
                if(count($args) < 3) return $this->viewPage($sender, 1);
                return $this->viewPage($sender, ToolBox::isNumber($args[2]) ?  (int) $args[2] : 1);

            case "H":
            case "HELD":
                if(count($args) < 3) return $this->viewHeld($sender, 1);
                return $this->viewHeld($sender, ToolBox::isNumber($args[2]) ? (int) $args[2] : 1);

            case "C":
            case "CLOSED":
                if(count($args) < 3) return $this->viewClosed($sender, 1);
                return $this->viewClosed($sender, ToolBox::isNumber($args[2]) ? (int) $args[2] : 1);

            case "SELF":
                return $this->viewSelf($sender);

            default:
                # Defaults to this if an action is not found. In this case we need to figure out what the user is trying to do.
                if(ToolBox::isNumber($args[1])) return $this->viewId($sender, (int) $args[1]);
                $sender->sendMessage(sprintf(MessageHandler::$generalError, "No valid action specified."));
                break;
        }
    }

    private function viewPage($sender, $page) {}
    private function viewHeld($sender, $page) {}
    private function viewClosed($sender, $page) {}
    private function viewSelf($sender) {}
    private function viewId($sender, $id) {}
}