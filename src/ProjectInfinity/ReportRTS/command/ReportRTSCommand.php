<?php

namespace ProjectInfinity\ReportRTS\command;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
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

                break;

            case "STATS":

                break;

            case "FIND":
            case "SEARCH":

                break;

            case "HELP":

                break;

            case "DUTY":

                break;

            default:
                $sender->sendMessage(MessageHandler::$generalError);
                return false;
        }
    }
}