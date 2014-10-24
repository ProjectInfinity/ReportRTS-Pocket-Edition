<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use pocketmine\command\CommandSender;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\MessageHandler;
use ProjectInfinity\ReportRTS\util\PermissionHandler;
use ProjectInfinity\ReportRTS\util\ToolBox;

class ReopenTicket {

    private $plugin;
    private $data;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
        $this->data = $plugin->getDataProvider();
    }

    public function handleCommand(CommandSender $sender, $args) {

        if(!$sender->hasPermission(PermissionHandler::canReopenTicket)) {
            $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canReopenTicket));
            return true;
        }

        if(count($args) < 2 or !ToolBox::isNumber($args[1])) {
            $sender->sendMessage(sprintf(MessageHandler::$generalError, 'Correct syntax is: "/<command> <reopen> <id>"'));
            return true;
        }

        $ticketId = intval($args[1]);

        if($resultCode = $this->data->setTicketStatus($ticketId, $sender->getName(), 0, "", 0, round(microtime(true))) and $resultCode != 1) {
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
            if($resultCode == -3) {
                # Ticket does not exist.
                $sender->sendMessage(sprintf(MessageHandler::$ticketNotExists, $ticketId));
                return true;
            }
            $sender->sendMessage(sprintf(MessageHandler::$generalError, "Unable to reopen ticket #".$ticketId));
            return true;
        }

        ReportRTS::$tickets[$ticketId] = $this->data->getTicket($ticketId);

        # Check if ticket has been assigned to the array.
        if(!isset(ReportRTS::$tickets[$ticketId])) {
            $sender->sendMessage(sprintf(MessageHandler::$generalError, "Something went wrong! Ticket did not enter the ticket array."));
            return true;
        }

        $this->plugin->messageStaff(sprintf(MessageHandler::$ticketReopen, $sender->getName(), $ticketId));
        $sender->sendMessage(sprintf(MessageHandler::$ticketReopenSelf, $ticketId));

        return true;
    }
}