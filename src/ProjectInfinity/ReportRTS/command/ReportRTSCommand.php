<?php

namespace ProjectInfinity\ReportRTS\command;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;

class ReportRTSCommand implements CommandExecutor {

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        // TODO: Port command logic.
        $sender->sendMessage("Welp.");
        return false;
    }
}