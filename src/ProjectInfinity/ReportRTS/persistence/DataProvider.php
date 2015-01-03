<?php

namespace ProjectInfinity\ReportRTS\persistence;

use pocketmine\level\Position;
use ProjectInfinity\ReportRTS\data\Ticket;
use ProjectInfinity\ReportRTS\ReportRTS;

interface DataProvider {

    /** @param ReportRTS $plugin */
    public function __construct(ReportRTS $plugin);

    public function close();

    public function reset();
    public function resetNotifications();

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

    /**
     * @param int $cursor
     * @param int $limit
     * @param int $status
     * @return Ticket[]
     */
    public function getTickets($cursor, $limit, $status = 0);

    /**
     * @param $id
     * @return Ticket
     */
    public function getTicket($id);

    /**
     * @param $username
     * @param $cursor
     * @param $limit
     * @return Ticket[]|mixed
     */
    public function getHandledBy($username, $cursor, $limit);
    /**
     * @param $username
     * @param $cursor
     * @param $limit
     * @return Ticket[]|mixed
     */
    public function getOpenedBy($username, $cursor, $limit);

    /**
     * @param int $limit
     * @return Array
     */
    public function getTop($limit);

    /**
     * @param $username
     * @param $id
     * @param $createIfNotExists
     * @return Array
     */
    public function getUser($username = null, $id = 0, $createIfNotExists = false);

    public function setTicketStatus($id, $username, $status, $comment, $notified, $timestamp);
    public function setNotificationStatus($id, $status);
    public function setUserStatus($username, $status);
}