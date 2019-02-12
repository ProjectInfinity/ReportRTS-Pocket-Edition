<?php

namespace ProjectInfinity\ReportRTS\listener;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Sign;

use pocketmine\utils\TextFormat;
use ProjectInfinity\ReportRTS\data\Ticket;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\task\LoginTask;
use ProjectInfinity\ReportRTS\util\MessageHandler;
use ProjectInfinity\ReportRTS\util\PermissionHandler;
use ProjectInfinity\ReportRTS\util\ToolBox;

class RTSListener implements Listener {

    private $plugin;
    private $data;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
        $this->data = $plugin->getDataProvider();
    }

    public function onPlayerJoin(PlayerJoinEvent $event) {

        if($this->plugin->isDefault) {
            if($event->getPlayer()->isOp()) {
                $event->getPlayer()->sendMessage(TextFormat::RED."You need to set up ReportRTS! Use /rts setup <action> <argument>");
                $this->plugin->getServer()->dispatchCommand($event->getPlayer(), "reportrts setup");
            }
            return;
        }

        if(count($this->plugin->notifications) > 0) {
            /** @var Ticket[] $found */
            $found = [];
            foreach($this->plugin->notifications as $ticket) {
                if(strtoupper($ticket->getName()) != strtoupper($event->getPlayer()->getName())) continue;
                # Store found ticket for later use.
                array_push($found, $ticket);
            }

            if(count($found) > 1) $event->getPlayer()->sendMessage(sprintf(MessageHandler::$ticketCloseMulti, count($found), "ticket ".$this->plugin->commands['readTicket']." self"));

            foreach($found as $ticket) {
                $this->plugin->getScheduler()->scheduleDelayedTask(new LoginTask($this->plugin, $ticket), 100);
            }
        }

        # Check if player is staff and add to array if true.
        if($event->getPlayer()->hasPermission(PermissionHandler::isStaff)) {
            array_push($this->plugin->staff, $event->getPlayer()->getName());
            $this->plugin->staff = array_unique($this->plugin->staff);
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event) {

        if(($staff = array_search($event->getPlayer()->getName(), $this->plugin->staff)) !== false) unset($this->plugin->staff[$staff]);
    }

    public function onSignChange(SignChangeEvent $event) {
        if($event->getBlock()->getID() != 63 && $event->getBlock()->getID() != 68) return;
        $block = $event->getBlock();
        if(!($block instanceof Sign)) return;
        $sign = $block->getText();
        if($sign[0] != "[help]") return;

        $message = ToolBox::cleanSign($sign);
        if(strlen($message) == 0) {
            $event->getPlayer()->sendMessage(sprintf(MessageHandler::$generalError, "Sign syntax is invalid."));
            # Break the block because it is invalid.
            $event->getBlock()->level->useBreakOn($event->getBlock(), $item = null, null);
            return;
        }

        # Fire command.
        $this->plugin->getServer()->dispatchCommand($event->getPlayer(), "ticket ".$this->plugin->commands['openTicket']." ".$message);
    }
}
