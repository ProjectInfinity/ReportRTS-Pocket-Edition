<?php

namespace ProjectInfinity\ReportRTS\persistence;

use ProjectInfinity\ReportRTS\ReportRTS;

interface DataProvider {

    /** @param ReportRTS $plugin */
    public function __construct(ReportRTS $plugin);

    public function close();

    public function createUser($username);

    public function getUserId($username);

    # Add more functions as documented in SQLDB.java from line 103.
}