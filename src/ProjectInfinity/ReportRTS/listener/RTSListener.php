<?php

namespace ProjectInfinity\ReportRTS\listener;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Sign;

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
        if(count($this->plugin->notifications) > 0) {
            # TODO: Do something because there are pending notifications!
            /** @var Ticket[] $found */
            $found = [];
            foreach($this->plugin->notifications as $ticket => $player) {
                if(strtoupper($player) != strtoupper($event->getPlayer()->getName())) continue;
                # L43#RTSListener.java
                # Store found ticket for later use.
                array_push($found, $ticket);
            }

            if(count($found) > 1) $event->getPlayer()->sendMessage(sprintf(MessageHandler::$ticketCloseMulti, count($found), "ticket ".$this->plugin->commands['readTicket']." self"));

            foreach($found as $ticket) {
                $this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new LoginTask($this->plugin, $ticket), 100);
            }
        }

        # Check if player is staff and add to array if true.
        if($event->getPlayer()->hasPermission(PermissionHandler::isStaff)) {
            array_push($this->plugin->staff, $event->getPlayer()->getName());
            array_unique($this->plugin->staff);
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event) {
        # TODO: This needs testing, if it works then remove this comment.
        if(($staff = array_search($event->getPlayer()->getName(), $this->plugin->staff)) !== false) {
            unset($$this->plugin->staff[$staff]);
        }
    }

    public function onSignChange(SignChangeEvent $event) {
        if($event->getBlock()->getID() != 323 && $event->getBlock()->getID() != 63 && $event->getBlock()->getID() != 68) return;
        $block = $event->getBlock();
        if(!($block instanceof Sign)) return;
        $sign = $block->getText();
        if($sign[0] != "[help]") return;

        // TODO: Process.
        $message = ToolBox::cleanSign($sign);
        if(strlen($message) == 0) {
            $event->getPlayer()->sendMessage(sprintf(MessageHandler::$generalError, "Sign syntax is invalid."));
            # Break the block because it is invalid.
            $event->getBlock()->level->useBreakOn($event->getBlock(), $item = null, null);
            return;
        }
        # TODO: Check if user has >= $ticketMax open.

    }

}