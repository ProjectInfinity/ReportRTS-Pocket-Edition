<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\MessageHandler;
use ProjectInfinity\ReportRTS\util\PermissionHandler;
use ProjectInfinity\ReportRTS\util\ToolBox;

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

        # Check if ticket message is too short.
        if($this->plugin->ticketMinWords > (count($args) - 1)) {
            $sender->sendMessage(sprintf(MessageHandler::$ticketTooShort, $this->plugin->ticketMinWords));
            return true;
        }

        # Check if the sender has too many open tickets. If we do it here, we skip the DB calls.
        if(ToolBox::countOpenTickets($sender->getName()) >= $this->plugin->ticketMax && !(PermissionHandler::bypassTicketLimit)) {
            $sender->sendMessage(MessageHandler::$ticketTooMany);
            return true;
        }

        # Check if the sender is opening tickets too quickly. If we do it here we skip the DB calls.
        if($this->plugin->ticketDelay > 0) {
            if(!(PermissionHandler::bypassTicketLimit)) {
                $wait = ToolBox::timeDifference($sender->getName(), $this->plugin->ticketDelay);
                if($wait > 0) {
                    $sender->sendMessage(sprintf(MessageHandler::$ticketTooFast, $wait));
                    return true;
                }
            }
        }

        $username = $sender->getName();
        if(!($sender instanceof Player)) {
            # Sender is more than likely console.
            $data = $this->data->getUser($sender->getName());
            $userId = $data['id'];
            $location = $this->plugin->getServer()->getDefaultLevel()->getSpawnLocation();
        } else {
            $userId = $this->data->getUserId($username);
            $location = $sender->getPosition();
        }

        # TODO: Continue at OpenTicket.java#L79

        return true;
    }

}