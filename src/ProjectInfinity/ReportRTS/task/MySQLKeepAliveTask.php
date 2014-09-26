<?php

namespace ProjectInfinity\ReportRTS\task;

use mysqli;
use pocketmine\scheduler\PluginTask;
use ProjectInfinity\ReportRTS\ReportRTS;

class MySQLKeepAliveTask extends PluginTask {

    /** @var mysqli */
    private $database;

    public function __construct(ReportRTS $plugin, \mysqli $database) {

        parent::__construct($plugin);
        $this->database = $database;
    }

    public function onRun($currentTick) {
        $this->database->ping();
    }
}