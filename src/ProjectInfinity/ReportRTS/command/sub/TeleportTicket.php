<?php

namespace ProjectInfinity\ReportRTS\command\sub;

use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\MessageHandler;
use ProjectInfinity\ReportRTS\util\PermissionHandler;
use ProjectInfinity\ReportRTS\util\ToolBox;

class TeleportTicket {

    private $plugin;
    private $data;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
        $this->data = $plugin->getDataProvider();
    }

    public function handleCommand(CommandSender $sender, $args) {

        if(!($sender instanceof Player)) {
            $sender->sendMessage("[ReportRTS] You need a body to teleport. Players only.");
        }

        if(!$sender->hasPermission(PermissionHandler::canTeleport)) {
            $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canTeleport));
            return true;
        }

        if(count($args) < 2 or !ToolBox::isNumber($args[1])) {
            $sender->sendMessage(sprintf(MessageHandler::$generalError, 'Correct syntax is: "/<command> <teleport> <id>"'));
            return true;
        }

        $id = intval($args[1]);

        $ticket = null;

        # Loads ticket from array or data-provider.
        if(isset(ReportRTS::$tickets[$id]))
            $ticket = ReportRTS::$tickets[$id];
        else
            $ticket = $this->data->getTicket($id);

        # Check if ticket exists or not.
        if($ticket == null) {
            $sender->sendMessage(sprintf(MessageHandler::$ticketNotExists, $id));
            return true;
        }

        $position = new Position($ticket->getX(), $ticket->getY(), $ticket->getZ(), $this->plugin->getServer()->getLevelByName($ticket->getWorld()));

        if($position->getLevel() == null) {
            $sender->sendMessage(TextFormat::RED."[ReportRTS] Level is null! Attempting to teleport to that ticket will cause an error.");
            return true;
        }

        $sender->teleport($position, $ticket->getYaw(), $ticket->getPitch());
        $sender->sendMessage(sprintf(MessageHandler::$ticketTeleport, $id));

        return true;
    }
}