<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use pocketmine\command\CommandSender;
use ProjectInfinity\ReportRTS\ReportRTS;

class TeleportTicket {

    private $plugin;
    private $data;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
        $this->data = $plugin->getDataProvider();
    }

    public function handleCommand(CommandSender $sender, $args) {

        return true;
    }
}