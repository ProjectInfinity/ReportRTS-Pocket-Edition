<?php

namespace ProjectInfinity\ReportRTS\persistence;

use pocketmine\level\Position;
use ProjectInfinity\ReportRTS\ReportRTS;

interface DataProvider {

    /** @param ReportRTS $plugin */
    public function __construct(ReportRTS $plugin);

    public function close();

    public function createUser($username);

    /**
     * @param $sender
     * @param $world
     * @param Position $location
     * @param $yaw
     * @param $pitch
     * @param $message
     * @param $timestamp
     * @return mixed
     */
    public function createTicket($sender, $world, Position $location, $yaw, $pitch, $message, $timestamp);

    /** @returns Integer */
    public function countHeldTickets();
    public function countTickets();

    public function deleteEntry($table, $id);

    public function getUserId($username);
    public function getLastIdBy($username);

    /**
     * @param int $cursor
     * @param int $limit
     * @param int $status
     * @return \mysqli_result
     */
    public function getTickets($cursor, $limit, $status = 0);
    public function getTicketById($id);
    public function getLocation($id);
    public function getUnnotifiedUsers();
    public function getEverything($table);
    public function getHandledBy($username);
    public function getOpenedBy($username);
    public function getStats();
    public function getUsername($userId);

    /**
     * @param $username
     * @return Array
     */
    public function getUser($username);

    public function setTicketStatus($id, $username, $status, $comment, $notified, $timestamp);
    public function setNotificationStatus($id, $status);
    public function setUserStatus($username, $status);

    public function populateTicketArray();

    public function userExists($player);
    public function updateTicket($id);

    public function openTicket();
    # Add more functions as documented in SQLDB.java from line 383.
}