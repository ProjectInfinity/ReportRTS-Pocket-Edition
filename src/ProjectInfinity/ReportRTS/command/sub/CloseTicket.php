<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use pocketmine\command\CommandSender;
use pocketmine\Player;
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

        # Check if the user that opened the ticket is online.
        $online = ToolBox::isOnline(ReportRTS::$tickets[$ticketId]->getName());
        # Check if ticket is claimed and if the user that sent the command is the same user as the one that opened the ticket.
        $isClaimed = ReportRTS::$tickets[$ticketId]->getStatus() == 1 ? strtoupper(ReportRTS::$tickets[$ticketId]->getStaffName()) != strtoupper($sender->getName()) : false;

        # TODO: Continue L85#CloseTicket.java
        # Get rid of arguments we do not want in the text.
        $args[0] = null;
        $args[1] = null;

        $comment = implode(" ", array_filter($args));

        if($resultCode = $this->data->setTicketStatus($ticketId, $sender->getName(), 3, $comment, 0, round(microtime(true) * 1000)) and $resultCode != 1) {
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
            $sender->sendMessage(sprintf(MessageHandler::$generalError, "Unable to close ticket #".$ticketId));
            return true;
        }

        return true;
    }
}