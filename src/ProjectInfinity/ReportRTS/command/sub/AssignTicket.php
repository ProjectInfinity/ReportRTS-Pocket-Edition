<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use pocketmine\command\CommandSender;
use pocketmine\IPlayer;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\MessageHandler;
use ProjectInfinity\ReportRTS\util\PermissionHandler;
use ProjectInfinity\ReportRTS\util\ToolBox;

class AssignTicket {

    private $plugin;
    private $data;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
        $this->data = $plugin->getDataProvider();
    }

    public function handleCommand(CommandSender $sender, $args) {

        if(!$sender->hasPermission(PermissionHandler::canAssign)) {
            $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canAssign));
            return true;
        }

        if(count($args) < 3) {
            $sender->sendMessage(sprintf(MessageHandler::$generalError, "You need to specify a ticket ID then a player."));
            return true;
        }

        if(!ToolBox::isNumber($args[1])) {
            $sender->sendMessage(sprintf(MessageHandler::$generalError, "Ticket ID must be a number. Provided: ".$args[1]));
            return true;
        }

        $ticketId = intval($args[1]);

        if(!isset(ReportRTS::$tickets[$ticketId]) or ReportRTS::$tickets[$ticketId]->getStatus() == 1) {
            # The ticket that the user is trying to claim is not in the array or is already claimed (not open).
            $sender->sendMessage(MessageHandler::$ticketNotOpen);
            return true;
        }
        $user = $this->data->getUser($args[2]);

        if($user['id'] == 0) {
            # Try to get the player from files.
            $player = $this->plugin->getServer()->getOfflinePlayer($args[2]);

            if($player->getFirstPlayed() === null) {
                $sender->sendMessage(sprintf(MessageHandler::$userNotExists, $player->getName()));
                return true;
            }
            # Create the user since it does not exist but is online.
            $this->data->createUser($player->getName());
            $user = $this->data->getUser($player->getName());
        }

        $ticket = ReportRTS::$tickets[$args[1]];

        $timestamp = round(microtime(true));

        if($resultCode = $this->data->setTicketStatus($ticketId, $user['username'], 1, null, 0, $timestamp) and $resultCode != 1) {

            if($resultCode == -1) {
                # Username is invalid or does not exist.
                $sender->sendMessage(sprintf(MessageHandler::$userNotExists, $user['username']));
                return true;
            }
            if($resultCode == -2) {
                # Ticket status incompatibilities.
                $sender->sendMessage(MessageHandler::$ticketStatusError);
                return true;
            }
            $sender->sendMessage(sprintf(MessageHandler::$generalError, "Unable to assign ticket #".$ticketId." to ".$args[2]));
            return true;
        }

        # Set ticket info in the ticket array too.
        $ticket->setStatus(1);
        $ticket->setStaffName($user['username']);
        $ticket->setStaffId($user['id']);
        $ticket->setStaffTimestamp($timestamp);
        ReportRTS::$tickets[$args[1]] = $ticket;

        $player = $this->plugin->getServer()->getPlayer($ticket->getName());
        if($player != null) {
            $player->sendMessage(sprintf(MessageHandler::$ticketAssignUser, $user['username']));
            $player->sendMessage(sprintf(MessageHandler::$ticketClaimText, $ticket->getMessage()));
        }

        # Let staff know about this change.
        $this->plugin->messageStaff(sprintf(MessageHandler::$ticketAssign, $user['username'], $ticketId));

        return true;
    }
}