<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use pocketmine\command\CommandSender;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\MessageHandler;
use ProjectInfinity\ReportRTS\util\PermissionHandler;

class ListStaff {

    private $plugin;
    private $data;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
        $this->data = $plugin->getDataProvider();
    }

    public function handleCommand(CommandSender $sender, $args) {

        if(!$sender->hasPermission(PermissionHandler::canSeeStaff)) {
            $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canSeeStaff));
            return true;
        }

        $staff = "";

        foreach($this->plugin->staff as $staff) {
            $player = $this->plugin->getServer()->getPlayer($staff);
            if($player == null) continue;

        }


        return true;
    }
}