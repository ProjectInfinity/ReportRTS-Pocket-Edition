<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use pocketmine\command\CommandSender;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\MessageHandler;
use ProjectInfinity\ReportRTS\util\PermissionHandler;

class BroadcastMessage {

    private $plugin;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
    }

    public function handleCommand(CommandSender $sender, $args) {

        if(!$sender->hasPermission(PermissionHandler::canBroadcast)) {
            $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canBroadcast));
            return true;
        }

        if(count($args) < 2) {
            $sender->sendMessage(sprintf(MessageHandler::$generalError, "Not enough arguments."));
            return true;
        }

        $args[0] = null;
        $message = sprintf(MessageHandler::$broadcast, $sender->getName(), trim(implode(" ", $args)));

        $this->plugin->messageStaff($message);
        # Let console know too! Otherwise he gets lonely.
        $this->plugin->getLogger()->info($message);

        return true;
    }
}