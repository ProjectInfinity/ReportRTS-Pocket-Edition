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

class ReportRTSCommand implements CommandExecutor {

    private $plugin;
    private $data;

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

                break;

            case "HELP":

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

            default:
                $sender->sendMessage(MessageHandler::$generalError);
                return false;
        }
        return true;
    }
}