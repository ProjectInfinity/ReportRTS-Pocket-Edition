<?php

namespace ProjectInfinity\ReportRTS\command;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use ProjectInfinity\ReportRTS\command\sub\ReadTicket;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\ToolBox;

class TicketCommand implements CommandExecutor {

    private $plugin;

    private $readCommand;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;

        # Set up sub-commands.
        $this->readCommand = new ReadTicket($plugin);
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

        return $result;
    }

}