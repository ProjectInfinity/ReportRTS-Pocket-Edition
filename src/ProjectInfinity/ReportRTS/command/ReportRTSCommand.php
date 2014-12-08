<?php

namespace ProjectInfinity\ReportRTS\command;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\util\MessageHandler;
use ProjectInfinity\ReportRTS\util\PermissionHandler;
use ProjectInfinity\ReportRTS\util\ToolBox;

class ReportRTSCommand implements CommandExecutor {

    private $plugin;
    private $data;
    private $dpType;
    private $dpHost;
    private $dpPort;
    private $dpUser;
    private $dpPass;
    private $dpDb;

    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
        $this->data = $plugin->getDataProvider();
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        if(count($args) == 0) return false;

        # TODO: Add all missing logic.
        switch(strtoupper($args[0])) {

            case "RELOAD":
                if(!$sender->hasPermission(PermissionHandler::canReload)) {
                    $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canReload));
                    return true;
                }
                $this->plugin->reloadSettings();
                break;

            case "BAN":
                if(!$sender->hasPermission(PermissionHandler::canBan)) {
                    $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canBan));
                    return true;
                }
                if(count($args) < 2) {
                    $sender->sendMessage(sprintf(MessageHandler::$generalError, "Please specify a player."));
                    return true;
                }

                # Attempt to get the target that you wish to ban.
                $target = $this->plugin->getServer()->getPlayer($args[1]);
                if($target === null) {
                    # User is not online, let's see if they exist in the database.
                    $target = $this->data->getUser($args[1]);
                    if($target['id'] == 0) {
                        $sender->sendMessage(sprintf(MessageHandler::$userNotExists, $args[1]));
                        return true;
                    }
                }

                # Check if target was gotten through getPlayer or using getUser.
                if($target instanceof Player) {
                    $result = $this->data->setUserStatus($target->getName(), true);
                } else {
                    $result = $this->data->setUserStatus($target['username'], true);
                }

                # Check if user status was set.
                if($result < 1) {
                    $sender->sendMessage(sprintf(MessageHandler::$generalError, "No affected users. This shouldn't happen."));
                    return true;
                }

                $this->plugin->messageStaff(sprintf(MessageHandler::$userBanned, $args[1]));

                break;

            case "UNBAN":
                if(!$sender->hasPermission(PermissionHandler::canBan)) {
                    $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canBan));
                    return true;
                }
                if(count($args) < 2) {
                    $sender->sendMessage(sprintf(MessageHandler::$generalError, "Please specify a player."));
                    return true;
                }

                # Attempt to get the target that you wish to unban.
                $target = $this->plugin->getServer()->getPlayer($args[1]);
                if($target === null) {
                    # User is not online, let's see if they exist in the database.
                    $target = $this->data->getUser($args[1]);
                    if($target['id'] == 0) {
                        $sender->sendMessage(sprintf(MessageHandler::$userNotExists, $args[1]));
                        return true;
                    }
                }

                # Check if target was gotten through getPlayer or using getUser.
                if($target instanceof Player) {
                    $result = $this->data->setUserStatus($target->getName(), false);
                } else {
                    $result = $this->data->setUserStatus($target['username'], false);
                }

                # Check if user status was set.
                if($result < 1) {
                    $sender->sendMessage(sprintf(MessageHandler::$generalError, "No affected users. This shouldn't happen."));
                    return true;
                }

                $this->plugin->messageStaff(sprintf(MessageHandler::$userUnbanned, $args[1]));

                break;

            case "RESET":
                if(!$sender->isOp()) {
                    $sender->sendMessage(sprintf(MessageHandler::$generalError, "You need to be OP to do that."));
                    return true;
                }

                $this->data->reset();

                $sender->sendMessage(TextFormat::RED."[ReportRTS] You deleted all tickets and users.");
                $this->plugin->getLogger()->alert($sender->getName()." deleted all tickets and users from ReportRTS.");

                break;

            case "STATS":
                if(!$sender->hasPermission(PermissionHandler::canSeeStats)) {
                    $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canSeeStats));
                    return true;
                }

                $data = $this->data->getTop(10);
                # Check if data is empty. This may happen when the passed $limit variable is not an integer.
                if(count($data) == 0) {
                    $sender->sendMessage(sprintf(MessageHandler::$generalError, "getTop() returned an empty array."));
                    return true;
                }

                $sender->sendMessage(TextFormat::YELLOW."---- Top 10 ----");
                $sender->sendMessage(TextFormat::YELLOW."<Placing>. <Player> : <Resolved Tickets>");
                $i = 0;
                foreach($data as $array => $value) {
                    $i++;
                    $sender->sendMessage(TextFormat::YELLOW.$i.". ". $value['name']." : ".$value['tickets']);
                }

                break;

            case "FIND":
            case "SEARCH":
                if(!$sender->hasPermission(PermissionHandler::canSearch)) {
                    $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canSearch));
                    return true;
                }
                if(count($args) < 3) {
                    $sender->sendMessage(sprintf(MessageHandler::$generalError, "Syntax is /rts search <Player> <Closed/Opened> <Page>"));
                    return true;
                }

                $action = $args[2];
                $player = $args[1];
                # If page is specified, we should use that as opposed to the default.
                if(count($args) === 4)
                    $page = $args[3];
                else
                    $page = 1;

                if($page < 1) $page = 1;

                # Set cursor start position.
                $i = ($page * $this->plugin->ticketPerPage) - $this->plugin->ticketPerPage;

                $tickets = null;

                if(strtoupper($action) === "CLOSED") {
                    $tickets = $this->data->getHandledBy($player, $i, $this->plugin->ticketPerPage);
                }
                if(strtoupper($action) === "OPENED") {
                    $tickets = $this->data->getOpenedBy($player, $i, $this->plugin->ticketPerPage);
                }

                # The player specified does not exist!
                if($tickets === false) {
                    $sender->sendMessage(sprintf(MessageHandler::$userNotExists, $player));
                    return true;
                }

                # No tickets were returned somehow or the user specified an invalid action.
                if($tickets === null) {
                    $sender->sendMessage(sprintf(MessageHandler::$generalError, "Tickets are null! /rts search <Player> <Closed/Opened> <Page>"));
                    return true;
                }

                # Send a message explaining which page we're on.
                $sender->sendMessage(TextFormat::AQUA."--------- Page ".$page." -".TextFormat::YELLOW." ".strtoupper($action)." ".TextFormat::AQUA."---------");

                foreach($tickets as $ticket) {
                    $substring = ToolBox::shortenMessage($ticket->getMessage());
                    # If the ticket is claimed, we should specify so by altering the text and colour of it.
                    $substring = ($ticket->getStatus() == 1) ? TextFormat::LIGHT_PURPLE."Claimed by ".$ticket->getStaffName() : TextFormat::GRAY.$substring;
                    # Send final message.
                    $sender->sendMessage(TextFormat::GOLD."#".$ticket->getId()." ".ToolBox::timeSince($ticket->getTimestamp())." by ".
                        (ToolBox::isOnline($ticket->getName()) ? TextFormat::GREEN : TextFormat::RED).$ticket->getName().TextFormat::GOLD." - ".$substring);
                }
                break;

            case "HELP":
                if(!PermissionHandler::canSeeHelp) {
                    $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canSeeHelp));
                    return true;
                }

                # Start messaging the player.
                $sender->sendMessage("====[ ".TextFormat::GOLD."ReportRTS Help ".TextFormat::GREEN."]====");
                $sender->sendMessage(TextFormat::GOLD."/ticket read <status> <page> ".TextFormat::RESET."-".TextFormat::YELLOW." See ticket details");
                $sender->sendMessage(TextFormat::GOLD."/ticket claim <id> ".TextFormat::RESET."-".TextFormat::YELLOW." Claim a ticket, no toe stepping");
                $sender->sendMessage(TextFormat::GOLD."/ticket unclaim <id> ".TextFormat::RESET."-".TextFormat::YELLOW." Unlaim a ticket");
                $sender->sendMessage(TextFormat::GOLD."/ticket close <id> <comment> ".TextFormat::RESET."-".TextFormat::YELLOW." Closes a ticket, optional comment");
                $sender->sendMessage(TextFormat::GOLD."/ticket hold <id> <reason> ".TextFormat::RESET."-".TextFormat::YELLOW." Put ticket on hold, optional reason");
                $sender->sendMessage(TextFormat::GOLD."/ticket open <message> ".TextFormat::RESET."-".TextFormat::YELLOW." Opens a ticket");
                $sender->sendMessage(TextFormat::GOLD."/ticket reopen <id> ".TextFormat::RESET."-".TextFormat::YELLOW." Reopens specified ticket");
                $sender->sendMessage(TextFormat::GOLD."/ticket staff ".TextFormat::RESET."-".TextFormat::YELLOW." See online staff");
                $sender->sendMessage(TextFormat::GOLD."/ticket tp <id> ".TextFormat::RESET."-".TextFormat::YELLOW." Teleport to ticket");
                $sender->sendMessage(TextFormat::GOLD."/ticket broadcast ".TextFormat::RESET."-".TextFormat::YELLOW." Message online staff");
                $sender->sendMessage(TextFormat::GOLD."/reportrts <action>".TextFormat::RESET."-".TextFormat::YELLOW." General ReportRTS command.");
                break;

            case "DUTY":
                if(!($sender instanceof Player)) {
                    $sender->sendMessage(TextFormat::RED."Only players can change duty status.");
                    return true;
                }
                if(!$sender->hasPermission(PermissionHandler::isStaff)) {
                    $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::isStaff));
                    return true;
                }

                # Show current duty status if there are less than 2 arguments.
                if(count($args) < 2) {
                    if(($staff = array_search($sender->getName(), $this->plugin->staff)) !== false)
                        $sender->sendMessage(TextFormat::GREEN."You are currently on duty.");
                    else
                        $sender->sendMessage(TextFormat::RED."You are currently off duty.");
                    return true;
                }

                $duty = strtoupper($args[1]);
                if(!$duty === "ON" and !$duty === "OFF") {
                    $sender->sendMessage(TextFormat::RED."Syntax is /rts duty on|off");
                }

                if($duty === "ON") {
                    if(($staff = array_search($sender->getName(), $this->plugin->staff)) === false) {
                        array_push($this->plugin->staff, $sender->getName());
                        $sender->sendMessage(TextFormat::GREEN."You are now on duty.");
                        return true;
                    }
                    $sender->sendMessage(TextFormat::YELLOW."You are already on duty.");
                }
                if($duty === "OFF") {
                    if(($staff = array_search($sender->getName(), $this->plugin->staff)) !== false) {
                        unset($this->plugin->staff[$staff]);
                        $sender->sendMessage(TextFormat::GREEN."You are no longer on duty.");
                        return true;
                    }
                    $sender->sendMessage(TextFormat::YELLOW."You are already off duty.");
                }
                break;

            case "NOTIF":
            case "NOTIFICATIONS":
                if(!$sender->hasPermission(PermissionHandler::canManageNotifications)) {
                    $sender->sendMessage(sprintf(MessageHandler::$permissionError, PermissionHandler::canManageNotifications));
                    return true;
                }
                # Display a message with pending notifications if no arguments are given.
                if(count($args) <= 1) {
                    $sender->sendMessage(TextFormat::YELLOW."There are currently ".count($this->plugin->notifications)." pending notifications.");
                    $sender->sendMessage(TextFormat::YELLOW."Reset them using /rts notif reset");
                    return true;
                }
                # Incorrect syntax. Inform the user.
                if(strtoupper($args[1]) !== "RESET") {
                    $sender->sendMessage(TextFormat::RED."Syntax incorrect! The correct syntax is /rts notif reset");
                    return true;
                }

                if(!$this->data->resetNotifications()) {
                    $sender->sendMessage(sprintf(MessageHandler::$generalError, "Failed to reset notifications!"));
                    return true;
                }

                $sender->sendMessage(TextFormat::GREEN."You have marked all notifications as read.");

                # Clean up after ourselves.
                unset($this->plugin->notifications);
                $this->plugin->notifications = [];
                break;

            case "SETUP":
                if(!$sender->isOp()) {
                    $sender->sendMessage(sprintf(MessageHandler::$generalError, "You have to be OP to use this command."));
                    return true;
                }
                if(count($args) <= 1) {
                    $sender->sendMessage(TextFormat::GREEN."Welcome to the ReportRTS setup! Below you will see valid actions for this command. /rts <action> <value>");
                    $sender->sendMessage("Actions: TYPE, HOST, PORT, DATABASE, USERNAME, PASSWORD");
                    $sender->sendMessage("When you are done, type /rts setup save. Type /rts setup to view this message again.");
                    return true;
                }

                switch(strtoupper($args[1])) {

                    case "TYPE":
                        if(count($args) < 3 or $args[2] === null or $args[2] == "") {
                            $sender->sendMessage(TextFormat::RED."Type cannot be empty or null! Supported types are: MYSQL");
                            return true;
                        }
                        if(strtoupper($args[2]) !== "MYSQL") {
                            $sender->sendMessage(TextFormat::RED."No valid type was specified. Valid types are: MYSQL");
                            return true;
                        }
                        $this->dpType = "MYSQL";
                        $this->plugin->getConfig()->setNested("storage.type", "mysql");
                        $this->plugin->saveConfig();
                        $this->plugin->reloadConfig();

                        $sender->sendMessage(TextFormat::GREEN."Type has been set to ".$this->dpType.". Continue with /rts setup HOST <value>");
                        break;

                    case "HOST":
                        if(count($args) < 3 or $args[2] === null or $args[2] == "" or $this->dpType !== "MYSQL") {
                            $sender->sendMessage(TextFormat::RED."Host cannot be empty, null or specified on unsupported types. Default is likely localhost or 127.0.0.1");
                            return true;
                        }

                        $this->dpHost = $args[2];
                        $this->plugin->getConfig()->setNested("storage.host", $this->dpHost);
                        $this->plugin->saveConfig();
                        $this->plugin->reloadConfig();

                        $sender->sendMessage(TextFormat::GREEN."Host has been set to ".$this->dpHost.". Continue with /rts setup PORT <value>");
                        break;

                    case "PORT":
                        if(count($args) < 3 or !is_int((int)$args[2]) or $this->dpType !== "MYSQL") {
                            $sender->sendMessage(TextFormat::RED."Port cannot be null, not a number or specified on unsupported types. Default is likely 3306 for MySQL");
                            return true;
                        }

                        $this->dpPort = intval($args[2]);
                        $this->plugin->getConfig()->setNested("storage.port", $this->dpPort);
                        $this->plugin->saveConfig();
                        $this->plugin->reloadConfig();

                        $sender->sendMessage(TextFormat::GREEN."Port has been set to ".$this->dpPort.". Continue with /rts setup USERNAME <value>");
                        break;

                    case "USERNAME":
                        if(count($args) < 3 or $args[2] === null or $args[2] == "" or $this->dpType !== "MYSQL") {
                            $sender->sendMessage(TextFormat::RED."Username cannot be empty, null or specified on unsupported types.");
                            return true;
                        }

                        $this->dpUser = $args[2];
                        $this->plugin->getConfig()->setNested("storage.username", $this->dpUser);
                        $this->plugin->saveConfig();
                        $this->plugin->reloadConfig();

                        $sender->sendMessage(TextFormat::GREEN."Username has been set to ".$this->dpUser.". Continue with /rts setup PASSWORD <value>");
                        break;

                    case "PASSWORD":
                        if(count($args) < 3 or $args[2] === null or $args[2] == "" or $this->dpType !== "MYSQL") {
                            $sender->sendMessage(TextFormat::RED."Password cannot be empty, null or specified on unsupported types.");
                            return true;
                        }

                        $this->dpPass = $args[2];
                        $this->plugin->getConfig()->setNested("storage.password", $this->dpPass);
                        $this->plugin->saveConfig();
                        $this->plugin->reloadConfig();

                        $sender->sendMessage(TextFormat::GREEN."Password has been set to ".$this->dpPass.". Continue with /rts setup DATABASE <value>");
                        break;

                    case "DATABASE":
                        if(count($args) < 3 or $args[2] === null or $args[2] == "" or $this->dpType !== "MYSQL") {
                            $sender->sendMessage(TextFormat::RED."Database cannot be empty, null or specified on unsupported types.");
                            return true;
                        }

                        $this->dpDb = $args[2];
                        $this->plugin->getConfig()->setNested("storage.database", $this->dpDb);
                        $this->plugin->saveConfig();
                        $this->plugin->reloadConfig();

                        $sender->sendMessage(TextFormat::GREEN."Database has been set to ".$this->dpDb.". Finish by typing /rts setup SAVE");
                        break;

                    case "SAVE":

                        if($this->dpType === "MYSQL") {
                            $failed = false;

                            if(!isset($this->dpHost)) {
                                $failed = true;
                                $sender->sendMessage(TextFormat::RED."Hostname is not set. /rts setup HOST <value>");
                            }

                            if(!isset($this->dpDb)) {
                                $failed = true;
                                $sender->sendMessage(TextFormat::RED."Database is not set. /rts setup DATABASE <value>");
                            }

                            if(!isset($this->dpPort)) {
                                $failed = true;
                                $sender->sendMessage(TextFormat::RED."Port is not set. /rts setup PORT <value>");
                            }

                            if(!isset($this->dpPass)) {
                                $failed = true;
                                $sender->sendMessage(TextFormat::RED."Password is not set. /rts setup PASSWORD <value>");
                            }

                            if(!isset($this->dpUser)) {
                                $failed = true;
                                $sender->sendMessage(TextFormat::RED."Username is not set. /rts setup USERNAME <value>");
                            }

                            if($failed) {
                                $sender->sendMessage(TextFormat::RED."Some settings are not correct. Make sure they are all correct.");
                                return true;
                            }

                            $this->plugin->reloadSettings();
                        }

                        break;

                    default:
                        $sender->sendMessage("Valid actions: TYPE, HOST, PORT, DATABASE, USERNAME, PASSWORD");
                        $sender->sendMessage("When you are done, type /rts setup save. Type /rts setup to view this message again.");
                        return true;
                }

                break;

            default:
                $sender->sendMessage(MessageHandler::$generalError);
                return false;
        }
        return true;
    }
}