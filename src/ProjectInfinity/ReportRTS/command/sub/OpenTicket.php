<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use ProjectInfinity\ReportRTS\data\Ticket;
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

        if(!($sender instanceof Player)) {
            # Sender is more than likely console.
            $location = $this->plugin->getServer()->getDefaultLevel()->getSpawnLocation();
            # Yaw & Pitch needs to be confirmed.
            $yaw = 0.0;
            $pitch = 0.0;
        } else {
            $location = $sender->getPosition();
            $yaw = $sender->yaw;
            $pitch = $sender->pitch;
        }

        $args[0] = null;
        $message = implode(" ", array_filter($args));

        if($this->plugin->ticketPreventDuplicates) {
            # TODO: Make this percentage based?
            foreach(ReportRTS::$tickets as $ticket) {
                if(strtolower($ticket->getName()) != strtolower($sender->getName())) continue;
                if(strtolower($ticket->getMessage()) != strtolower($message)) continue;
                $sender->sendMessage(MessageHandler::$ticketDuplicate);
                return true;
            }
        }

        $timestamp = round(microtime(true) * 1000);
        $ticketId = $this->data->createTicket($sender->getName(), $location->getLevel()->getName(), $location, $yaw, $pitch, $message, $timestamp);

        if($ticketId <= 0) {
            # Something went wrong. Let's see if they are banned.
            if($ticketId == -1) {
                $sender->sendMessage(sprintf(MessageHandler::$generalError, "You are banned from opening tickets."));
                return true;
            }
            $sender->sendMessage(sprintf(MessageHandler::$generalError, "Could not open ticket."));
            return true;
        }

        $sender->sendMessage(MessageHandler::$ticketOpenedUser);
        $this->plugin->getLogger()->info($sender->getName()." opened a ticket with the ID #".$ticketId.".");

        # Notify all staff members of the newly opened ticket.
        foreach($this->plugin->staff as $staff) {
            $player = $this->plugin->getServer()->getPlayer($staff);
            if($player == null) continue;
            $player->sendMessage(sprintf(MessageHandler::$ticketOpenedStaff, $sender->getName(), $ticketId));
        }

        array_push(ReportRTS::$tickets, new Ticket($ticketId, 0, $location->getX(), $location->getY(), $location->getZ(), null,
            $yaw, $pitch, $timestamp, null, $message, $sender->getName(), $location->getLevel()->getName(), null, null));

        return true;
    }
}