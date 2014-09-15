<?php

namespace ProjectInfinity\ReportRTS\command;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\MessageHandler;

class ReportRTSCommand implements CommandExecutor {

    private $plugin;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        if(count($args) == 0) return false;

        switch(strtoupper($args[0])) {


            default:
                $sender->sendMessage(MessageHandler::$generalError);
                return false;
        }
    }
}