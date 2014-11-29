<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use pocketmine\command\CommandSender;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\MessageHandler;
use ProjectInfinity\ReportRTS\util\PermissionHandler;
use ProjectInfinity\ReportRTS\util\ToolBox;

class CloseTicket {

    private $plugin;
    private $data;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
        $this->data = $plugin->getDataProvider();
    }

    public function handleCommand(CommandSender $sender, $args) {

        # TODO: Add close self.

        if(!$sender->hasPermission(PermissionHandler::canCloseTicket)) {
            $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canCloseTicket));
            return true;
        }

        if(count($args) < 2 or !ToolBox::isNumber($args[1])) {
            $sender->sendMessage(sprintf(MessageHandler::$generalError, 'Correct syntax is: "/<command> <close> <id> <comment>"'));
            return true;
        }

        $ticketId = intval($args[1]);

        $ticket = null;

        if(isset(ReportRTS::$tickets[$ticketId])) {
            $ticket = ReportRTS::$tickets[$ticketId];
        } else {
            $ticket = $this->data->getTicket($ticketId);
        }

        # Ticket does not exist.
        if($ticket === null) {
            $sender->sendMessage(sprintf(MessageHandler::$ticketNotExists, $ticketId));
            return true;
        }

        # Check if ticket is claimed and if the user that sent the command is the same user as the one that opened the ticket.
        $isClaimed = $ticket->getStatus() == 1 ? strtoupper($ticket->getStaffName()) != strtoupper($sender->getName()) : false;

        if($isClaimed && !$sender->hasPermission(PermissionHandler::bypassTicketClaim)) {
            $sender->sendMessage(sprintf(MessageHandler::$generalError, "Ticket #".$ticketId." is claimed by another player."));
            return true;
        }

        # Get rid of arguments we do not want in the text.
        $args[0] = null;
        $args[1] = null;

        $comment = implode(" ", array_filter($args));

        if($resultCode = $this->data->setTicketStatus($ticketId, $sender->getName(), 3, $comment, 0, round(microtime(true))) and $resultCode != 1) {
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
            $sender->sendMessage(sprintf(MessageHandler::$generalError, "Unable to close ticket #".$ticketId));
            return true;
        }


        $player = $this->plugin->getServer()->getPlayer($ticket->getName());
        if($player != null) {
            # User is online! Let's message them.
            $player->sendMessage(sprintf(MessageHandler::$ticketCloseUser, $sender->getName()));
            $player->sendMessage(sprintf(MessageHandler::$ticketCloseText, $ticket->getMessage(), trim($comment)));
        } else {
            $this->plugin->notifications[$ticketId] = $ticket;
        }

        # Let staff know about the ticket change.
        $this->plugin->messageStaff(sprintf(MessageHandler::$ticketClose, $ticketId, $sender->getName()));

        # Remove the ticket if it is located in the ticket array.
        if(isset(ReportRTS::$tickets[$ticketId])) {
            unset(ReportRTS::$tickets[$ticketId]);
        }

        return true;
    }
}