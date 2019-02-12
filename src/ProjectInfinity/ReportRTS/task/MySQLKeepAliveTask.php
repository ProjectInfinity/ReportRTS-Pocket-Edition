<?php

namespace ProjectInfinity\ReportRTS\task;

use mysqli;
use pocketmine\scheduler\Task;
use ProjectInfinity\ReportRTS\ReportRTS;

class MySQLKeepAliveTask extends PluginTask {

    /** @var mysqli */
    private $database;

    public function __construct(ReportRTS $plugin, \mysqli $database) {

        $this->database = $database;
    }

    public function onRun(int $currentTick) {
        $this->database->ping();
    }
}
