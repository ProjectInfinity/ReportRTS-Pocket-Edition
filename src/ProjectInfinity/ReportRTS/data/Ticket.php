<?php

namespace ProjectInfinity\ReportRTS\data;

class Ticket {

    private $id, $status, $x, $y, $z, $staffId, $yaw, $pitch, $timestamp, $stafftstamp, $text, $name, $world, $staffName, $comment;

    public function __construct($id, $status, $x, $y, $z, $staffId = null, $yaw, $pitch, $timestamp, $stafftsstamp = null, $text, $name, $world, $staffName = null, $comment = null) {
        $this->id = $id;
        $this->status = $status;
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->staffId = $staffId;
        $this->yaw = $yaw;
        $this->pitch = $pitch;
        $this->timestamp = $timestamp;
        $this->stafftstamp = $stafftsstamp;
        $this->text = $text;
        $this->name = $name;
        $this->world = $world;
        $this->staffName = $staffName;
        $this->comment = $comment;
    }

    /**
     * Returns the message of the ticket.
     * @return String
     */
    public function getMessage() {
        return $this->text;
    }

    /**
     * Returns the name of the sender.
     * @return String
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Return the ID of the ticket.
     * @return Integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Returns the ticket status.
     * @return Integer
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Returns the timestamp when the ticket was created.
     * @return Float
     */
    public function getTimestamp() {
        return $this->timestamp;
    }

    /**
     * Returns the timestamp when the ticket was last interacted with by staff.
     * @return Float
     */
    public function getStaffTimestamp() {
        return $this->stafftstamp;
    }

    /**
     * Returns X where the ticket was created.
     * @return Float
     */
    public function getX() {
        return $this->x;
    }

    /**
     * Returns Y where the ticket was created.
     * @return Float
     */
    public function getY() {
        return $this->y;
    }

    /**
     * Returns Z where the ticket was created.
     * @return Float
     */
    public function getZ() {
        return $this->z;
    }

    /**
     * Returns Yaw where the ticket was created.
     * @return Float
     */
    public function getYaw() {
        return $this->yaw;
    }

    /**
     * Returns Pitch where the ticket was created.
     * @return Float
     */
    public function getPitch() {
        return $this->pitch;
    }

    /**
     * Returns the name of the world where the ticket was created.
     * @return String
     */
    public function getWorld() {
        return $this->world;
    }

    /**
     * Returns the name of the staff that handled the ticket, if any.
     * @return String
     */
    public function getStaffName() {
        return $this->staffName;
    }

    /**
     * Returns the comment that was made when handling the ticket, if any.
     * @return String
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * Sets the message of the ticket to $text.
     * @param $text
     */
    public function setMessage($text) {
        $this->text = $text;
    }

    /**
     * Sets the provided name as the user that opened the ticket.
     * @param $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Sets the provided ID as the user that opened the ticket.
     * @param $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setTimestamp($timestamp) {
        $this->timestamp = $timestamp;
    }

    public function setStaffTimestamp($staffTimestamp) {
        $this->stafftstamp = $staffTimestamp;
    }

    public function setX($x) {
        $this->x = $x;
    }

    public function setY($y) {
        $this->y = $y;
    }

    public function setZ($z) {
        $this->z = $z;
    }

    public function setYaw($yaw) {
        $this->yaw = $yaw;
    }

    public function setPitch($pitch) {
        $this->pitch = $pitch;
    }

    public function setWorld($world) {
        $this->world = $world;
    }

    public function setComment($comment) {
        $this->comment = $comment;
    }

}