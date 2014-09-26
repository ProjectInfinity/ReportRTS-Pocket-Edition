<?php

namespace ProjectInfinity\ReportRTS\persistence;

use ProjectInfinity\ReportRTS\ReportRTS;

interface DataProvider {

    /** @param ReportRTS $plugin */
    public function __construct(ReportRTS $plugin);

    public function close();

    public function createUser($username);
    public function createTicket($staffId, $world, $x, $y, $z, $message, $userId, $timestamp);

    public function countHeldTickets();
    public function countTickets();

    public function deleteEntry($table, $id);

    public function getUserId($username);
    public function getLastIdBy($username);
    public function getTickets($cursor, $limit, $status);
    public function getTicketById($id);
    public function getLocation($id);
    public function getUnnotifiedUsers();
    public function getEverything($table);
    public function getHandledBy($username);
    public function getOpenedBy($username);
    public function getStats();
    public function getUsername($userId);

    public function setTicketStatus($id, $username, $status, $comment, $notified, $timestamp);
    public function setNotificationStatus($id, $status);
    public function setUserStatus($username, $status);

    public function populateTicketArray();

    public function userExists($player);
    public function updateTicket($id);

    public function openTicket();
    # Add more functions as documented in SQLDB.java from line 383.
}