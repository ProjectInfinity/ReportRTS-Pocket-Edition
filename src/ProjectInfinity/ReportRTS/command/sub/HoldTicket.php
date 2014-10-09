<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use pocketmine\command\CommandSender;

use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\MessageHandler;
use ProjectInfinity\ReportRTS\util\PermissionHandler;
use ProjectInfinity\ReportRTS\util\ToolBox;

class HoldTicket {

    private $plugin;
    private $data;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
        $this->data = $plugin->getDataProvider();
    }

    public function handleCommand(CommandSender $sender, $args) {

        if(!$sender->hasPermission(PermissionHandler::canHoldTicket)) {
            $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canHoldTicket));
            return true;
        }

        if(count($args) < 2 or !ToolBox::isNumber($args[1])) {
            $sender->sendMessage(sprintf(MessageHandler::$generalError, 'Correct syntax is: "/<command> <hold> <id>"'));
            return true;
        }

        $ticketId = intval($args[1]);
        # Get rid of arguments we do not want in the text.
        $args[0] = null;
        $args[1] = null;

        $reason = implode(" ", array_filter($args));

        if($resultCode = $this->data->setTicketStatus($ticketId, $sender->getName(), 2, $reason, 0, round(microtime(true) * 1000)) and $resultCode != 1) {
            if($resultCode == -1) {
                # Username is invalid or does not exist.
                $sender->sendMessage(sprintf(MessageHandler::$userNotExists, $sender->getName()));
                return true;
            }
            if($resultCode == -2) {
                # Ticket status incompatibilities.
                $sender->sendMessage(MessageHandler::$ticketStatusError);
                return true;
            }
            $sender->sendMessage(sprintf(MessageHandler::$generalError, "Unable to put ticket #".$ticketId." on hold."));
            return true;
        }

        # Check if ticket is open or not. You can't put a held or closed ticket on hold.
        if(!isset(ReportRTS::$tickets[$ticketId])) {
            $sender->sendMessage(MessageHandler::$ticketNotOpen);
            return true;
        }

        $player = $this->plugin->getServer()->getPlayer(ReportRTS::$tickets[$ticketId]->getName());
        if($player != null) {
            # User is online! Let's message them.
            $player->sendMessage(sprintf(MessageHandler::$ticketHoldUser, $sender->getName()));
            $player->sendMessage(sprintf(MessageHandler::$ticketHoldText, ReportRTS::$tickets[$ticketId]->getMessage(), trim($reason)));
        }

        # Remove the ticket from the array!
        unset(ReportRTS::$tickets[$ticketId]);

        # Let staff know that you put a ticket on hold.
        $this->plugin->messageStaff(sprintf(MessageHandler::$ticketHold, $ticketId, $sender->getName()));

        return true;
    }
}