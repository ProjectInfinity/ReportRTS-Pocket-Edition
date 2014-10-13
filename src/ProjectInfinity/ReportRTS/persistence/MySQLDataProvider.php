<?php

namespace ProjectInfinity\ReportRTS\persistence;

use pocketmine\level\Position;
use ProjectInfinity\ReportRTS\data\Ticket;
use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\task\MySQLKeepAliveTask;
use ProjectInfinity\ReportRTS\util\ToolBox;

class MySQLDataProvider implements DataProvider {

    /** @var  ReportRTS */
    protected $plugin;

    /** @var  \mysqli */
    protected $database;

    /** @param ReportRTS $plugin */
    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
        $config = $this->plugin->getConfig()->get("storage");

        if(!isset($config["host"])  or !isset($config["username"]) or !isset($config["password"])
        or !isset($config["database"])) {
            $this->plugin->getLogger()->critical("Your MySQL settings are invalid! Please check your config.yml");
            # Do as SimpleAuth and provide a dummy provider?
        }
        $this->database = new \mysqli($config["host"], $config["username"], $config["password"], $config["database"], isset($config["port"]) ? $config["port"] : 3306);
        if($this->database->connect_error) {
            $this->plugin->getLogger()->critical("Could not connect to MySQL! Cause: ".$this->database->connect_error);
            # Do as SimpleAuth and provide a dummy provider?
            return;
        }

        # Set up tickets table.
        $resource = $this->plugin->getResource("mysql_tickets.sql");
        $this->database->query(stream_get_contents($resource));
        # Set up users table.
        $resource = $this->plugin->getResource("mysql_users.sql");
        $this->database->query(stream_get_contents($resource));

        # Make sure connection stays alive.
        $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask(new MySQLKeepAliveTask($this->plugin, $this->database), 600);

        $this->plugin->getLogger()->info("Connected using MySQL");

        ReportRTS::$tickets = $this->load();
    }

    public function close() {
        $this->database->close();
    }

    /** @return Ticket[] */
    private function load() {
        $result = $this->database->query("SELECT * FROM `reportrts_tickets` AS `ticket` INNER JOIN `reportrts_users` AS `user` ON ticket.userId = user.id WHERE ticket.status = '0' ORDER BY ticket.id ASC");
        $temp = [];
        while($row = $result->fetch_array()) {
            var_dump($row['timestamp']);
            $ticket = new Ticket($row[0], $row['status'], $row['x'], $row['y'], $row['z'], $row['staffId'], $row['yaw'],
                $row['pitch'], $row['timestamp'], $row['staffTime'], $row['text'], $row['name'], $row['world'], null, $row['comment']);
            $temp[$row[0]] = $ticket;
        }

        $result->close();
        return $temp;
    }

    public function createUser($username) {
        $id = 0;

        $player = $this->plugin->getServer()->getPlayer($username);
        if($player == null and strtoupper($username) != "CONSOLE") return 0;

        $sql = $this->database->prepare("INSERT INTO `reportrts_users` (`name`, `banned`) VALUES (?, '0')");
        $sql->bind_param("s", $username);
        $sql->execute();
        $sql->close();
        return $id;
    }

    public function createTicket($sender, $world, Position $location, $yaw, $pitch, $message, $timestamp) {

        # Retrieve user data.
        $user = $this->getUser($sender);

        # Check if user is banned before processing further.
        if($user['isBanned'] == 1) {
            return -1;
        }

        $world = $location->getLevel()->getName(); $x = $location->getX(); $y = $location->getY(); $z = $location->getZ();
        $stmt = $this->database->prepare("INSERT INTO `reportrts_tickets` (`userId`, `timestamp`, `world`, `x`, `y`, `z`, `yaw`, `pitch`, `text`) VALUES(".$user['id'].",?,?,?,?,?,?,?,?)");
        $stmt->bind_param('isiiiiis', $timestamp, $world, $x, $y, $z, $yaw, $pitch, $message);
        $stmt->execute();
        if($stmt->affected_rows == 0) {
            $stmt->close();
            return 0;
        }
        $id = $stmt->insert_id;
        # We're done here, time to close up shop.
        $stmt->close();

        return $id;
    }

    public function countHeldTickets() {
        return 0;
    }

    public function countTickets()
    {
        // TODO: Implement countTickets() method.
    }

    public function deleteEntry($table, $id)
    {
        // TODO: Implement deleteEntry() method.
    }

    public function getUserId($username)
    {
        // TODO: Implement getUserId() method.
    }

    public function getLastIdBy($username)
    {
        // TODO: Implement getLastIdBy() method.
    }

    public function getTickets($cursor, $limit, $status = 0) {

        $result = null;

        switch($status) {

            # Ticket is unresolved.
            case 0:
                $result = $this->database->query("SELECT * FROM `reportrts_tickets` AS `ticket` INNER JOIN `reportrts_users` AS `user` ON ticket.userId = user.id WHERE ticket.status = '0' ORDER BY ticket.id ASC LIMIT ".$cursor.",".$limit);
                break;

            # Ticket is claimed.
            case 1:
                $result = $this->database->query("SELECT * FROM `reportrts_tickets` AS `ticket` INNER JOIN `reportrts_users` AS `user` ON ticket.userId = user.id WHERE ticket.status = '1' ORDER BY ticket.id ASC LIMIT ".$cursor.",".$limit);
                break;

            # Ticket is on hold.
            case 2:
                $result = $this->database->query("SELECT * FROM `reportrts_tickets` AS `ticket` INNER JOIN `reportrts_users` AS `user` ON ticket.userId = user.id WHERE ticket.status = '2' ORDER BY ticket.id ASC LIMIT ".$cursor.",".$limit);
                break;

            # Ticket is closed.
            case 3:
                $result = $this->database->query("SELECT * FROM `reportrts_tickets` AS `ticket` INNER JOIN `reportrts_users` AS `user` ON ticket.userId = user.id WHERE ticket.status = '3' ORDER BY ticket.id DESC LIMIT ".$cursor.",".$limit);
                break;
        }
        return $result;
    }

    /** @return Ticket */
    public function getTicket($id) {
        if(!ToolBox::isNumber($id)) return null;
        $ticket = $this->database->query("SELECT * FROM `reportrts_tickets` WHERE `id` = '$id' LIMIT 1")->fetch_row();

        return $ticket;
    }

    public function getLocation($id)
    {
        // TODO: Implement getLocation() method.
    }

    public function getUnnotifiedUsers()
    {
        // TODO: Implement getUnnotifiedUsers() method.
    }

    public function getEverything($table)
    {
        // TODO: Implement getEverything() method.
    }

    public function getHandledBy($username)
    {
        // TODO: Implement getHandledBy() method.
    }

    public function getOpenedBy($username)
    {
        // TODO: Implement getOpenedBy() method.
    }

    public function getStats()
    {
        // TODO: Implement getStats() method.
    }

    public function getUsername($userId)
    {
        // TODO: Implement getUsername() method.
    }

    public function setTicketStatus($id, $username, $status, $comment, $notified, $timestamp) {

        if(!isset(ReportRTS::$tickets[$id])) {
            # Ticket is not of status OPEN(1).
            $ticket = $this->getTicket($id);
            if($ticket == null) return -2;
        } else {
            # Retrieve ticket from ticket array.
            $ticket = ReportRTS::$tickets[$id];
        }

        # Make sure username is alphanumeric.
        if(!ctype_alnum($username)) return -1;

        # Check if user exists. Array_filter might be necessary, we'll find out.
        if(empty($this->getUser($username))) {
            return -1;
        }

        # Make sure ticket statuses don't clash.
        if($ticket->getStatus() == $status or ($status == 2 && $ticket->getStatus() == 3)) return -2;

        $stmt = $this->database->prepare("UPDATE `reportrts_tickets` SET `status` = ?, `staffId` = ?, `staffTime` = ?, `comment` = ?, `notified` = ? WHERE `id` = ?");
        $stmt->bind_param('iiisii', $status, $staffId, $timestamp, $comment, $notified, $id);
        $stmt->execute();
        $result = $stmt->affected_rows > 0 ? 1 : 0;
        $stmt->close();

        return $result;
    }

    public function setNotificationStatus($id, $status) {

        # If -1 is returned then either the ID is not a number or the status is not a boolean.
        if(!ToolBox::isNumber($id) or !is_bool($status)) return -1;

        $id = intval($id);
        $status = $status ? 1 : 0 ;

        $stmt = $this->database->prepare("UPDATE `reportrts_tickets` SET `notified` = ? WHERE `id` = ?");
        $stmt->bind_param('ii', $id, $status);
        $stmt->execute();
        $result = $stmt->affected_rows > 0 ? 1 : 0;
        $stmt->close();

        return $result;
    }

    public function setUserStatus($username, $status)
    {
        // TODO: Implement setUserStatus() method.
    }

    public function populateTicketArray()
    {
        // TODO: Implement populateTicketArray() method.
    }

    public function userExists($player)
    {
        // TODO: Implement userExists() method.
    }

    public function updateTicket($id)
    {
        // TODO: Implement updateTicket() method.
    }

    public function openTicket()
    {
        // TODO: Implement openTicket() method.
    }

    /**
     * @param $username
     * @return Array
     */
    public function getUser($username) {
        $sql = $this->database->prepare("SELECT * FROM `reportrts_users` WHERE `name` = ? LIMIT 1");
        $sql->bind_param("s", $username);
        $sql->execute();
        $sql->bind_result($id, $name, $banned);
        $sql->fetch();

        $array = [
            "id" => (int) $id,
            "username" => $name,
            "isBanned" => ($banned == 1) ? true : false
        ];

        $sql->close();
        return $array;
    }
}