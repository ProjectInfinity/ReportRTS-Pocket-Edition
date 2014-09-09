<?php

namespace ProjectInfinity\ReportRTS\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\PermissionHandler;

class RTSListener implements Listener {

    private $plugin;
    private $ph;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
        /* Setup permission handler so that there is a centralized class doing all the magic for permissions checkups.
        Much easier to maintain when changing nodes. */
        $this->ph = new PermissionHandler();
    }

    public function onPlayerJoin(PlayerJoinEvent $event) {
        if(count($this->plugin->notifications) > 0) {
            # TODO: Do something because there are pending notifications!
        }

        # Check if player is staff and add to array if true.
        if($event->getPlayer()->hasPermission($this->ph->isStaff())) {
            array_push($this->plugin->staff, $event->getPlayer()->getName());
            array_unique($this->plugin->staff);
        }
    }

}