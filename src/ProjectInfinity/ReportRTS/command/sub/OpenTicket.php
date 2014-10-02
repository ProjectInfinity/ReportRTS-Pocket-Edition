<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use pocketmine\command\CommandSender;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\MessageHandler;
use ProjectInfinity\ReportRTS\util\PermissionHandler;

class OpenTicket {

    private $plugin;
    private $data;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
        $this->data = $plugin->getDataProvider();
    }

    public function handleCommand(CommandSender $sender, $args) {

        if(!PermissionHandler::canOpenTicket) {
            $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canOpenTicket));
            return true;
        }
        if(count($args) < 2) {
            $sender->sendMessage(sprintf(MessageHandler::$generalError, "You have to enter a message."));
            return true;
        }

        # TODO: Continue where it left off. OpenTicket.java#L44
        return true;
    }

}