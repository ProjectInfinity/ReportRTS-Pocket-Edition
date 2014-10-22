<?php

namespace ProjectInfinity\ReportRTS\command;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;

use ProjectInfinity\ReportRTS\command\sub\AssignTicket;
use ProjectInfinity\ReportRTS\command\sub\BroadcastMessage;
use ProjectInfinity\ReportRTS\command\sub\ClaimTicket;
use ProjectInfinity\ReportRTS\command\sub\CloseTicket;
use ProjectInfinity\ReportRTS\command\sub\HoldTicket;
use ProjectInfinity\ReportRTS\command\sub\ListStaff;
use ProjectInfinity\ReportRTS\command\sub\OpenTicket;
use ProjectInfinity\ReportRTS\command\sub\ReadTicket;
use ProjectInfinity\ReportRTS\command\sub\ReopenTicket;
use ProjectInfinity\ReportRTS\command\sub\TeleportTicket;
use ProjectInfinity\ReportRTS\command\sub\UnclaimTicket;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\ToolBox;

class TicketCommand implements CommandExecutor {

    private $plugin;

    private $readCommand;
    private $openCommand;
    private $closeCommand;
    private $claimCommand;
    private $holdCommand;
    private $unclaimCommand;
    private $staffCommand;
    private $teleportCommand;
    private $broadcastCommand;
    private $assignCommand;
    private $reopenCommand;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;

        # Set up sub-commands.
        $this->readCommand = new ReadTicket($plugin);
        $this->openCommand = new OpenTicket($plugin);
        $this->closeCommand = new CloseTicket($plugin);
        $this->claimCommand = new ClaimTicket($plugin);
        $this->holdCommand = new HoldTicket($plugin);
        $this->unclaimCommand = new UnclaimTicket($plugin);
        $this->staffCommand = new ListStaff($plugin);
        $this->teleportCommand = new TeleportTicket($plugin);
        $this->broadcastCommand = new BroadcastMessage($plugin);
        $this->assignCommand = new AssignTicket($plugin);
        $this->reopenCommand = new ReopenTicket($plugin);
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

        /** Argument checker, DO NOT LEAVE THIS UNCOMMENTED IN PRODUCTION */
        $i = -1;
        foreach($args as $arg) {
        $i++;
        $this->plugin->getLogger()->info("Position: " . $i . " | Actual Position: " . ($i + 1) . " | Argument: " . $arg);
        }
        /** LOOK ABOVE **/

        if(count($args) == 0) return false;
        $start = 0.00;
        if($this->plugin->debug) $start = microtime(true) * 1000;
        $result = false;

        # Oh snap gurl. Here comes the command statements. Wish this could be a switch though...

        /** Read ticket. **/
        if(strtoupper($args[0]) == $this->plugin->commands['readTicket']) {
            if($this->plugin->debug) $this->plugin->getLogger()->info($sender->getName()." ".get_class($this)." took ".ToolBox::getTimeSpent($start)."ms, ".$command->getName()." ".implode(" ", $args));
            $result = $this->readCommand->handleCommand($sender, $args);
        }
        /** Open a ticket. **/
        if(strtoupper($args[0]) == $this->plugin->commands['openTicket']) {
            if($this->plugin->debug) $this->plugin->getLogger()->info($sender->getName()." ".get_class($this)." took ".ToolBox::getTimeSpent($start)."ms, ".$command->getName()." ".implode(" ", $args));
            $result = $this->openCommand->handleCommand($sender, $args);
        }
        /** Closes a ticket. **/
        if(strtoupper($args[0]) == $this->plugin->commands['closeTicket']) {
            if($this->plugin->debug) $this->plugin->getLogger()->info($sender->getName()." ".get_class($this)." took ".ToolBox::getTimeSpent($start)."ms, ".$command->getName()." ".implode(" ", $args));
            $result = $this->closeCommand->handleCommand($sender, $args);
        }
        /** Claim a ticket. **/
        if(strtoupper($args[0]) == $this->plugin->commands['claimTicket']) {
            if($this->plugin->debug) $this->plugin->getLogger()->info($sender->getName()." ".get_class($this)." took ".ToolBox::getTimeSpent($start)."ms, ".$command->getName()." ".implode(" ", $args));
            $result = $this->claimCommand->handleCommand($sender, $args);
        }
        /** Teleport to a ticket. **/
        if(strtoupper($args[0]) == $this->plugin->commands['teleportToTicket']) {
            if($this->plugin->debug) $this->plugin->getLogger()->info($sender->getName()." ".get_class($this)." took ".ToolBox::getTimeSpent($start)."ms, ".$command->getName()." ".implode(" ", $args));
            $result = $this->teleportCommand->handleCommand($sender, $args);
        }
        /** Unclaim a ticket. **/
        if(strtoupper($args[0]) == $this->plugin->commands['unclaimTicket']) {
            if($this->plugin->debug) $this->plugin->getLogger()->info($sender->getName()." ".get_class($this)." took ".ToolBox::getTimeSpent($start)."ms, ".$command->getName()." ".implode(" ", $args));
            $result = $this->unclaimCommand->handleCommand($sender, $args);
        }
        /** Hold a ticket. **/
        if(strtoupper($args[0]) == $this->plugin->commands['holdTicket']) {
            if($this->plugin->debug) $this->plugin->getLogger()->info($sender->getName()." ".get_class($this)." took ".ToolBox::getTimeSpent($start)."ms, ".$command->getName()." ".implode(" ", $args));
            $result = $this->holdCommand->handleCommand($sender, $args);
        }
        /** List staff. **/
        if(strtoupper($args[0]) == $this->plugin->commands['listStaff']) {
            if($this->plugin->debug) $this->plugin->getLogger()->info($sender->getName()." ".get_class($this)." took ".ToolBox::getTimeSpent($start)."ms, ".$command->getName()." ".implode(" ", $args));
            $result = $this->staffCommand->handleCommand($sender);
        }
        /** Broadcast to staff **/
        if(strtoupper($args[0]) == $this->plugin->commands['broadcastToStaff']) {
            if($this->plugin->debug) $this->plugin->getLogger()->info($sender->getName()." ".get_class($this)." took ".ToolBox::getTimeSpent($start)."ms, ".$command->getName()." ".implode(" ", $args));
            $result = $this->broadcastCommand->handleCommand($sender, $args);
        }
        /** Assign a ticket. **/
        if(strtoupper($args[0]) == $this->plugin->commands['assignTicket']) {
            if($this->plugin->debug) $this->plugin->getLogger()->info($sender->getName()." ".get_class($this)." took ".ToolBox::getTimeSpent($start)."ms, ".$command->getName()." ".implode(" ", $args));
            $result = $this->assignCommand->handleCommand($sender, $args);
        }
        /** Reopen a ticket. **/
        if(strtoupper($args[0]) == $this->plugin->commands['reopenTicket']) {
            if($this->plugin->debug) $this->plugin->getLogger()->info($sender->getName()." ".get_class($this)." took ".ToolBox::getTimeSpent($start)."ms, ".$command->getName()." ".implode(" ", $args));
            $result = $this->reopenCommand->handleCommand($sender, $args);
        }
        return $result;
    }

}