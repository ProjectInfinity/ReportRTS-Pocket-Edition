<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use Exception;
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

        if(!PermissionHandler::canReadAll) {
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

    private function viewHeld(CommandSender $sender, $page) {

        if(!PermissionHandler::canReadAll) {
            $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canReadAll));
            return true;
        }

        # Set cursor start position.
        $i = ($page * $this->plugin->ticketPerPage) - $this->plugin->ticketPerPage;
        $heldCount = 0;

        $result = null;
        try {
            $heldCount = $this->data->countHeldTickets();
            $data = $this->data->getTickets($i, $this->plugin->ticketPerPage, 2);
            $result = $data->fetch_assoc();
            $data->close();

        } catch(Exception $e) {
            $sender->sendMessage(sprintf(MessageHandler::$generalError, "Cannot read held tickets, check the console for errors."));
            $this->plugin->getLogger()->error($e->getMessage());
            $this->plugin->getLogger()->error("Line: ".$e->getLine()." in ".$e->getFile());
        }

        $sender->sendMessage(TextFormat::AQUA."--------- ".$heldCount." Tickets -".TextFormat::YELLOW." Held ".TextFormat::AQUA."---------");
        if($heldCount == 0) {
            $sender->sendMessage(MessageHandler::$holdNoTickets);
            return true;
        }

        # Loop tickets if any.
        while($row = $result) {
            $online = ToolBox::isOnline($result['name']) ? TextFormat::GREEN : TextFormat::RED;
            $substring = ToolBox::shortenMessage($result['text']);


            # TODO: Test below later when all required classes have been made.
            $sender->sendMessage(TextFormat::GOLD."#".$result['id']." ".date("d-m-Y h:i:s", $row['timestamp'])." by ".$online.$result['name'].TextFormat::GOLD." - ".TextFormat::GRAY.$substring);
        }
        return true;
    }
    private function viewClosed($sender, $page) {}
    private function viewSelf($sender) {}
    private function viewId($sender, $id) {}
}