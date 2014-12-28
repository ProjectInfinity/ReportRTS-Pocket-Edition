<?php

namespace ProjectInfinity\ReportRTS\task;

use pocketmine\scheduler\PluginTask;
use ProjectInfinity\ReportRTS\data\Ticket;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\MessageHandler;

class LoginTask extends PluginTask {

    private $plugin;
    private $data;
    private $ticket;

    public function __construct(ReportRTS $plugin, Ticket $ticket) {
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->data = $plugin->getDataProvider();
        $this->ticket = $ticket;
    }

    public function onRun($currentTick) {

        # Attempt to get player, this has the potential to fail.
        $player = $this->plugin->getServer()->getPlayer($this->ticket->getName());
        # Player is online, so we send him/her some messages.
        if ($player != null) {
            $player->sendMessage(MessageHandler::$ticketCloseOffline);
            $player->sendMessage(sprintf(MessageHandler::$ticketCloseText, $this->ticket->getMessage(), trim($this->ticket->getComment())));
        }

        $this->data->setNotificationStatus($this->ticket->getId(), true);
    }
}