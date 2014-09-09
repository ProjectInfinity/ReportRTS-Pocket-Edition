<?php

namespace ProjectInfinity\ReportRTS\util;

class PermissionHandler {

    private $isStaff = "reportrts.staff";

    public function isStaff() {
        return $this->isStaff;
    }
}