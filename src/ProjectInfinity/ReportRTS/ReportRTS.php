<?php

namespace ProjectInfinity\ReportRTS;

use pocketmine\plugin\PluginBase;

use ProjectInfinity\ReportRTS\command\ReportRTSCommand;
use ProjectInfinity\ReportRTS\command\TicketCommand;
use ProjectInfinity\ReportRTS\data\Ticket;
use ProjectInfinity\ReportRTS\listener\RTSListener;
use ProjectInfinity\ReportRTS\persistence\DataProvider;
use ProjectInfinity\ReportRTS\persistence\MySQLDataProvider;
use ProjectInfinity\ReportRTS\util\MessageHandler;

class ReportRTS extends PluginBase {

    public $ticketMax;
    public $ticketDelay;
    public $ticketMinWords;
    public $ticketPerPage;
    public $ticketPreventDuplicates;
    public $ticketNag;
    public $ticketNagHeld;
    public $ticketHideOffline;

    # Array containing all tickets.
    /** @var Ticket[]  */
    public static $tickets = array();
    # Array containing all configurable sub-commands.
    public $commands;
    # Array containing all online staff members (users with reportrts.staff).
    public $staff;
    # Array containing all waiting notifications.
    public $notifications;

    public $debug;

    /** @var  DataProvider */
    protected $provider;

    public function onEnable() {
        $this->getLogger()->info("Welcome to the Alpha for ReportRTS. Please report any bugs you may discover to https://github.com/ProjectInfinity/ReportRTS-Pocket-Edition/issues.
        This project is a large Bukkit project that is being ported to PocketMine, be patient.");
        $this->reloadSettings();

        # Set up MessageHandler.
        MessageHandler::load();

        # Register commands.
        $this->getCommand("ticket")->setExecutor(new TicketCommand($this));
        $this->getCommand("reportrts")->setExecutor(new ReportRTSCommand($this));

        # Register event listeners.
        $this->getServer()->getPluginManager()->registerEvents(new RTSListener($this), $this);
    }

    public function onDisable() {
        # Close data provider connection.
        $this->provider->close();
    }

    public function reloadSettings() {
        $this->saveDefaultConfig();
        $this->reloadConfig();

        # Shows debug information in the plugin if enabled.
        $this->debug = $this->getConfig()->get("debug");

        # Ticket configuration.
        $this->ticketMax = $this->getConfig()->get("ticket")["max"];
        $this->ticketDelay = $this->getConfig()->get("ticket")["delay"];
        $this->ticketMinWords = $this->getConfig()->get("ticket")["minimumWords"];
        $this->ticketPerPage = $this->getConfig()->get("ticket")["perPage"];
        $this->ticketPreventDuplicates = $this->getConfig()->get("ticket")["preventDuplicates"];
        $this->ticketNag = $this->getConfig()->get("ticket")["nag"];
        $this->ticketNagHeld = $this->getConfig()->get("ticket")["nagHeld"];
        $this->ticketHideOffline= $this->getConfig()->get("ticket")["hideOffline"];

        # Set up storage.
        $provider = $this->getConfig()->get("storage")["type"];
        unset($this->provider);
        switch(strtoupper($provider)) {

            case "MYSQL":
                if($this->debug) $this->getLogger()->info("Using MySQL data provider.");
                $provider = new MySQLDataProvider($this);
                break;

            default:
                # TODO: Handle if no matched storage type is found.
                break;
        }

        if(!isset($this->provider) or !($this->provider instanceof DataProvider)) {
            $this->provider = $provider;
        }

        # Set up ticket array. NOTE: This contains fake tickets until a storage system has been developed.
        self::$tickets = array();

        # Make sure the array is sorted correctly, later this should be done after loading all data from a database.
        ksort(ReportRTS::$tickets);

        # Command configuration.
        $this->commands = array();
        $this->commands['readTicket'] = strtoupper($this->getConfig()->get("command")["readTicket"]);
        $this->commands['openTicket'] = strtoupper($this->getConfig()->get("command")["openTicket"]);
        $this->commands['closeTicket'] = strtoupper($this->getConfig()->get("command")["closeTicket"]);
        $this->commands['reopenTicket'] = strtoupper($this->getConfig()->get("command")["reopenTicket"]);
        $this->commands['claimTicket'] = strtoupper($this->getConfig()->get("command")["claimTicket"]);
        $this->commands['assignTicket'] = strtoupper($this->getConfig()->get("command")["assignTicket"]);
        $this->commands['unclaimTicket'] = strtoupper($this->getConfig()->get("command")["unclaimTicket"]);
        $this->commands['teleportToTicket'] = strtoupper($this->getConfig()->get("command")["teleportToTicket"]);
        $this->commands['broadcastToStaff'] = strtoupper($this->getConfig()->get("command")["broadcastToStaff"]);
        $this->commands['listStaff'] = strtoupper($this->getConfig()->get("command")["listStaff"]);

        # Setup notification array.
        $this->notifications = array();

        # Setup staff array.
        $this->staff = array();

    }

    /** @return Ticket[] */
    public static function getTickets() {
        return self::$tickets;
    }

    public function messageStaff($message) {
        foreach($this->staff as $staff) {
            $player = $this->getServer()->getPlayer($staff);
            if($player == null) continue;
            $player->sendMessage($message);
        }
    }

    /** @param DataProvider $provider */
    public function setDataProvider(DataProvider $provider) {
        $this->provider = $provider;
    }

    /** @return DataProvider */
    public function getDataProvider() {
        return $this->provider;
    }
}
