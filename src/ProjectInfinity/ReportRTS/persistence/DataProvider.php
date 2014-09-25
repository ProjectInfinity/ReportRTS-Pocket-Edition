<?php

namespace ProjectInfinity\ReportRTS\persistence;

use ProjectInfinity\ReportRTS\ReportRTS;

interface DataProvider {

    /** @param ReportRTS $plugin */
    public function __construct(ReportRTS $plugin);

    public function close();

    public function createUser($username);

    public function countHeldTickets();
    public function countTickets();

    public function getUserId($username);
    public function getLastIdBy($username);

    public function setTicketStatus($id, $username, $status, $comment, $notified, $timestamp);
    public function setUserStatus($username, $status);

    public function populateTicketArray();

    public function openTicket();
    # Add more functions as documented in SQLDB.java from line 383.
}