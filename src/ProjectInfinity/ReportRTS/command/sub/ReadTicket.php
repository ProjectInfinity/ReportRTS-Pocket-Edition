<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use ProjectInfinity\ReportRTS\data\Ticket;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\MessageHandler;
use ProjectInfinity\ReportRTS\util\PermissionHandler;
use ProjectInfinity\ReportRTS\util\ToolBox;

class ReadTicket {

    private $plugin;
    private $data;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
        $this->data = $plugin->getDataProvider();
    }

    public function handleCommand(CommandSender $sender, $args) {

        # Not enough arguments to be anything but "/ticket read".
        if(count($args) < 2) {
            return $this->viewPage($sender, 1);
        }

        switch(strtoupper($args[1])) {
            case "P":
            case "PAGE":
                if(count($args) < 3) return $this->viewPage($sender, 1);
                return $this->viewPage($sender, ToolBox::isNumber($args[2]) ?  (int) $args[2] : 1);

            case "H":
            case "HELD":
                if(count($args) < 3) return $this->viewHeld($sender, 1);
                return $this->viewHeld($sender, ToolBox::isNumber($args[2]) ? (int) $args[2] : 1);

            case "C":
            case "CLOSED":
                if(count($args) < 3) return $this->viewClosed($sender, 1);
                return $this->viewClosed($sender, ToolBox::isNumber($args[2]) ? (int) $args[2] : 1);

            case "SELF":
                return $this->viewSelf($sender);

            default:
                # Defaults to this if an action is not found. In this case we need to figure out what the user is trying to do.
                if(ToolBox::isNumber($args[1])) return $this->viewId($sender, (int) $args[1]);
                $sender->sendMessage(sprintf(MessageHandler::$generalError, "No valid action specified."));
                break;
        }
        return true;
    }

    /**
     * View the specified page. Defaults to 1.
     * @param CommandSender $sender
     * @param $page
     * @return bool
     */
    private function viewPage(CommandSender $sender, $page) {

        if(!$sender->hasPermission(PermissionHandler::canReadAll)) {
            $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::isStaff));
            return true;
        }

        if($page < 0) $page = 1;
        $a = $page * $this->plugin->ticketPerPage;
        # Compile a response to the user.
        $sender->sendMessage(TextFormat::AQUA."--------- ".count($this->plugin->getTickets())." Tickets -".TextFormat::YELLOW." Open ".TextFormat::AQUA."---------");
        if(count($this->plugin->getTickets()) == 0) $sender->sendMessage(MessageHandler::$noTickets);

        # (page * ticketPerPage) - ticketPerPage = Sets the start location of the "cursor".
        for($i = ($page * $this->plugin->ticketPerPage) - $this->plugin->ticketPerPage; $i < $a && $i < count($this->plugin->getTickets()); $i++) {
            /* @var $ticket Ticket */
            if($i < 0) $i = 1;
            $ticket = array_values($this->plugin->getTickets())[$i];

            if($ticket == null) {
                $sender->sendMessage(sprintf(MessageHandler::$generalError, "Ticket object is NULL!"));
                continue;
            }
            # Check if plugin hides tickets from offline players and if the player is offline.
            if($this->plugin->ticketHideOffline && !ToolBox::isOnline($sender->getName())) {
                $a++;
                continue;
            }

            $substring = ToolBox::shortenMessage($ticket->getMessage());
            # If the ticket is claimed, we should specify so by altering the text and colour of it.
            $substring = ($ticket->getStatus() == 1) ? TextFormat::LIGHT_PURPLE."Claimed by ".$ticket->getStaffName() : TextFormat::GRAY.$substring;
            # Send final message.
            $sender->sendMessage(TextFormat::GOLD."#".$ticket->getId()." ".ToolBox::timeSince($ticket->getTimestamp())." by ".
                (ToolBox::isOnline($ticket->getName()) ? TextFormat::GREEN : TextFormat::RED).$ticket->getName().TextFormat::GOLD." - ".$substring);
        }
        return true;
    }

    /**
     * View the specified page of held tickets.
     * @param CommandSender $sender
     * @param $page
     * @return bool
     */
    private function viewHeld(CommandSender $sender, $page) {

        if(!$sender->hasPermission(PermissionHandler::canReadAll)) {
            $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canReadAll));
            return true;
        }

        # Set cursor start position.
        $i = ($page * $this->plugin->ticketPerPage) - $this->plugin->ticketPerPage;

        $heldCount = $this->data->countTickets(2);
        $data = $this->data->getTickets($i, $this->plugin->ticketPerPage, 2);

        $sender->sendMessage(TextFormat::AQUA."--------- ".$heldCount." Tickets -".TextFormat::YELLOW." Held ".TextFormat::AQUA."---------");
        if($heldCount == 0) {
            $sender->sendMessage(MessageHandler::$holdNoTickets);
            return true;
        }

        # Loop tickets if any.
        foreach($data as $ticket) {
            $online = ToolBox::isOnline($ticket->getName()) ? TextFormat::GREEN : TextFormat::RED;
            $substring = ToolBox::shortenMessage($ticket->getMessage());

            $sender->sendMessage(TextFormat::GOLD."#".$ticket->getId()." ".date("d-m-Y h:i:s", $ticket->getTimestamp())." by ".$online.$ticket->getName().TextFormat::GOLD." - ".TextFormat::GRAY.$substring);
        }

        return true;
    }

    /**
     * View closed tickets.
     * @param CommandSender $sender
     * @param $page
     * @return bool
     */
    private function viewClosed(CommandSender $sender, $page) {

        if(!$sender->hasPermission(PermissionHandler::canReadAll)) {
            $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canReadAll));
            return true;
        }

        # Set cursor start position.
        $i = ($page * $this->plugin->ticketPerPage) - $this->plugin->ticketPerPage;

        $count = $this->data->countTickets(3);
        $data = $this->data->getTickets($i, $this->plugin->ticketPerPage, 3);

        $sender->sendMessage(TextFormat::AQUA."--------- ".$count." Tickets -".TextFormat::YELLOW." Closed ".TextFormat::AQUA."---------");
        if($count == 0) {
            $sender->sendMessage(MessageHandler::$noTickets);
            return true;
        }

        # Loop tickets if any.
        foreach($data as $ticket) {
            $online = ToolBox::isOnline($ticket->getName()) ? TextFormat::GREEN : TextFormat::RED;
            $substring = ToolBox::shortenMessage($ticket->getMessage());

            $sender->sendMessage(TextFormat::GOLD."#".$ticket->getId()." ".date("d-m-Y h:i:s", $ticket->getTimestamp())." by ".$online.$ticket->getName().TextFormat::GOLD." - ".TextFormat::GRAY.$substring);
        }

        return true;
    }

    /**
     * Views the 5 most recent unresolved tickets of
     * the player sending the command.
     * @param CommandSender $sender
     * @return bool
     */
    private function viewSelf(CommandSender $sender) {

        if(!$sender->hasPermission(PermissionHandler::canReadSelf)) {
            $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canReadSelf));
            return true;
        }

        $open = 0;
        $i = 0;
        foreach($this->plugin->getTickets() as $ticket) if(strtoupper($sender->getName()) == strtoupper($ticket->getName())) $open++;

        $sender->sendMessage(TextFormat::AQUA."--------- ".TextFormat::YELLOW."You have ".$open." unresolved tickets".TextFormat::AQUA."---------");
        if($open == 0) $sender->sendMessage(TextFormat::GOLD."You have no open tickets at this time.");

        foreach($this->plugin->getTickets() as $ticket) {
            $i++;
            if($i > 5) break;

            $substring = ToolBox::shortenMessage($ticket->getMessage());
            # If the ticket is claimed, we should specify so by altering the text and colour of it.
            $substring = ($ticket->getStatus() == 1) ? TextFormat::LIGHT_PURPLE."Claimed by ".$ticket->getStaffName() : TextFormat::GRAY.$substring;
            # Send final message.
            $sender->sendMessage(TextFormat::GOLD."#".$ticket->getId()." ".ToolBox::timeSince($ticket->getTimestamp())." by ".
                (ToolBox::isOnline($ticket->getName()) ? TextFormat::GREEN : TextFormat::RED).$ticket->getName().TextFormat::GOLD." - ".$substring);
        }


        return true;
    }

    /**
     * View a specific ticket.
     * @param CommandSender $sender
     * @param $id
     * @return bool
     */
    private function viewId(CommandSender $sender, $id) {

        if(!$sender->hasPermission(PermissionHandler::canReadAll)) {
            $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canReadAll));
            return true;
        }

        if(!ToolBox::isNumber($id)) {
            $sender->sendMessage(sprintf(MessageHandler::$generalError, "Ticket ID has to be a number."));
            return true;
        }

        $ticket = null;

        if(isset(ReportRTS::$tickets[$id]))
            $ticket = ReportRTS::$tickets[$id];
        else
            $ticket = $this->data->getTicket($id);

        if($ticket == null) {
            $sender->sendMessage(sprintf(MessageHandler::$ticketNotExists, $id));
            return true;
        }

        $online = ToolBox::isOnline($ticket->getName()) ? TextFormat::GREEN : TextFormat::RED;
        $date = date("d-m-Y h:i:s", $ticket->getTimestamp());

        $status = null;
        $statusColor = null;

        if($ticket->getStatus() == 0) {
            $status = "Open";
            $statusColor = TextFormat::YELLOW;
        }
        if($ticket->getStatus() == 1) {
            $status = "Claimed";
            $statusColor = TextFormat::RED;
        }
        if($ticket->getStatus() == 2) {
            $status = "On Hold";
            $statusColor = TextFormat::LIGHT_PURPLE;
        }
        if($ticket->getStatus() == 3) {
            $status = "Closed";
            $statusColor = TextFormat::GREEN;
        }

        # Compile response.
        $sender->sendMessage(TextFormat::AQUA."--------- ".TextFormat::YELLOW."Ticket #".$ticket->getId()." - ".$statusColor.$status.TextFormat::AQUA." ---------");
        $sender->sendMessage(TextFormat::YELLOW."Opened by ".$online.$ticket->getName().TextFormat::YELLOW." at ".TextFormat::GREEN.$date.
        TextFormat::YELLOW." at X:".TextFormat::GREEN.$ticket->getX().TextFormat::YELLOW.", Y:".TextFormat::GREEN.$ticket->getY().TextFormat::YELLOW.", Z:".
        TextFormat::GREEN.$ticket->getZ());
        $sender->sendMessage(TextFormat::GRAY.$ticket->getMessage());

        if($ticket->getStatus() == 1) {
            $time = round(microtime(true)) - $ticket->getStaffTimestamp();
            $sender->sendMessage(sprintf(TextFormat::LIGHT_PURPLE."Claimed for: %u hours, %u minutes, %u seconds by %s",
                $time/(60*60), ($time%(60*60))/(60), (($time%(60*60))%(60)), $ticket->getStaffName()));
        }

        if($ticket->getComment() != null and $ticket->getStatus() >= 2) $sender->sendMessage(TextFormat::YELLOW."Comment: ".TextFormat::DARK_GREEN.$ticket->getComment());

        return true;
    }
}