<?php

namespace ProjectInfinity\ReportRTS;

use pocketmine\plugin\PluginBase;

class ReportRTS extends PluginBase {

    public $ticketMax;
    public $ticketDelay;
    public $ticketMinWords;
    public $ticketPerPage;
    public $ticketPreventDuplicates;
    public $ticketNag;
    public $ticketNagHeld;
    public $ticketHideOffline;

    public $debug;

    public function onEnable() {
        $this->getLogger()->info("Welcome to the Alpha for ReportRTS. Please report any bugs you may discover to https://github.com/ProjectInfinity/ReportRTS/issues.
        This project is a large Bukkit project that is being ported to PocketMine.");
        $this->reloadSettings();
    }

    public function reloadSettings() {
        $this->saveDefaultConfig();
        $this->reloadConfig();

        # Ticket configuration.
        $this->ticketMax = $this->getConfig()->get("ticket.max");
        $this->ticketDelay = $this->getConfig()->get("ticket.delay");
        $this->ticketMinWords = $this->getConfig()->get("ticket.minimumWords");
        $this->ticketPerPage = $this->getConfig()->get("ticket.perPage");
        $this->ticketPreventDuplicates = $this->getConfig()->get("ticket.preventDuplicates");
        $this->ticketNag = $this->getConfig()->get("ticket.nag");
        $this->ticketNagHeld = $this->getConfig()->get("ticket.nagHeld");
        $this->ticketHideOffline= $this->getConfig()->get("ticket.hideOffline");

        # Shows debug information in the plugin if enabled.
        $this->debug = $this->getConfig()->get("debug");
    }
}
