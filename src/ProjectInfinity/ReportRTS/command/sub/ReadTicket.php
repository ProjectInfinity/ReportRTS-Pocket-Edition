<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\MessageHandler;
use ProjectInfinity\ReportRTS\util\PermissionHandler;
use ProjectInfinity\ReportRTS\util\ToolBox;

class ReadTicket {

    private $plugin;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
    }
    public function handleCommand(CommandSender $sender, $args) {

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

    /**
     * View the specified page. Defaults to 1.
     * @param CommandSender $sender
     * @param $page
     * @return bool
     */
    private function viewPage(CommandSender $sender, $page) {

        if(!PermissionHandler::canReadAll) {
            $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::isStaff));
            return true;
        }

        if($page < 0) $page = 1;

        # Compile a response to the user.
        $sender->sendMessage(TextFormat::AQUA."--------- ".count($this->plugin->getTickets())." Tickets -".TextFormat::YELLOW." Open ".TextFormat::AQUA."---------");
        if(count($this->plugin->getTickets()) == 0) $sender->sendMessage(MessageHandler::$noTickets);

        # TODO: Port pagination.

    }
    private function viewHeld($sender, $page) {}
    private function viewClosed($sender, $page) {}
    private function viewSelf($sender) {}
    private function viewId($sender, $id) {}
}