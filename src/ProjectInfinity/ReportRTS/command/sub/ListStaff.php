<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\MessageHandler;
use ProjectInfinity\ReportRTS\util\PermissionHandler;

class ListStaff {

    private $plugin;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
    }

    public function handleCommand(CommandSender $sender) {

        if(!$sender->hasPermission(PermissionHandler::canSeeStaff)) {
            $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canSeeStaff));
            return true;
        }

        $list = "";

        foreach($this->plugin->staff as $staff) {
            $player = $this->plugin->getServer()->getPlayer($staff);
            if($player == null) continue;
            if($this->plugin->vanish and $sender instanceof Player) {
                if(!$sender->canSee($player)) continue;
            }
            $list .= $player->getDisplayName().MessageHandler::$separator;
        }

        # No staff is online.
        if(strlen($list) == 0) {
            $sender->sendMessage(MessageHandler::$noStaff);
            return true;
        }

        $sender->sendMessage(sprintf(MessageHandler::$staffList, substr($list, 0, strlen($list) - strlen(MessageHandler::$separator))));
        return true;
    }
}