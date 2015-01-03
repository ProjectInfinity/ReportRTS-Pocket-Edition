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
        $result = $this->database->query("SELECT * FROM `reportrts_tickets` AS `ticket` INNER JOIN `reportrts_users` AS `user` ON ticket.userId = user.uid WHERE ticket.status < '2' ORDER BY ticket.id ASC");
        $temp = [];
        while($row = $result->fetch_assoc()) {
            $ticket = new Ticket($row['id'], $row['status'], $row['x'], $row['y'], $row['z'], $row['staffId'], $row['yaw'],
                $row['pitch'], $row['timestamp'], $row['staffTime'], $row['text'], $row['name'], $row['world'], null, $row['comment']);
            if($ticket->getStatus() > 0) $ticket->setStaffName($this->getUser(null, $ticket->getStaffId())['username']);
            $temp[$row['id']] = $ticket;
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
        $id = $sql->insert_id;
        $sql->close();
        return $id;
    }

    public function createTicket($sender, $world, Position $location, $yaw, $pitch, $message, $timestamp) {

        # Retrieve user data.
        $user = $this->getUser($sender);
        # Check if user exists.
        if($user['id'] == 0) {
            # Create the user since it does not exist.
            $this->createUser($sender);
            $user = $this->getUser($sender);
        }
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

    /**
     * Gets a number quoting the amount of tickets of current status.
     * If no status is specified it defaults to 4 which is a non-valid
     * status, and should be parsed as ALL tickets.
     * @param int $status
     * @return int
     */
    public function countTickets($status = 4) {

        $query = null;
        if($status < 4) {
            $query = $this->database->query("SELECT COUNT(*) FROM `reportrts_tickets` WHERE `status` = '$status'");
        } else {
            $query = $this->database->query("SELECT COUNT(*) FROM `reportrts_tickets`");
        }

        $result = $query->fetch_row();
        $query->close();

        return intval($result[0]);
    }

    public function getTickets($cursor, $limit, $status = 0) {

        $tickets = [];
        $query = $this->database->query("SELECT * FROM `reportrts_tickets` AS `ticket` INNER JOIN `reportrts_users` AS `user` ON ticket.userId = user.uid WHERE ticket.status = '".$status."' ORDER BY ticket.id ".($status > 2 ? "DESC" : "ASC")." LIMIT ".$cursor.",".$limit);

        while($row = $query->fetch_assoc()) {
            $tickets[$row['id']] = new Ticket($row['id'], $row['status'], $row['x'], $row['y'], $row['z'], $row['staffId'], $row['yaw'],
                $row['pitch'], $row['timestamp'], $row['staffTime'], $row['text'], $row['name'], $row['world'], null, $row['comment']);
        }

        $query->close();

        return $tickets;
}

    /**
     * @param $id
     * @return Ticket
     */
    public function getTicket($id) {
        if(!ToolBox::isNumber($id)) return null;
        $row = $this->database->query("SELECT * FROM `reportrts_tickets` AS `ticket` INNER JOIN `reportrts_users` AS `user` ON ticket.userId = user.uid WHERE ticket.id = '$id' LIMIT 1")->fetch_assoc();
        $ticket = new Ticket($row['id'], $row['status'], $row['x'], $row['y'], $row['z'], $row['staffId'], $row['yaw'],
            $row['pitch'], $row['timestamp'], $row['staffTime'], $row['text'], $row['name'], $row['world'], null, $row['comment']);
        if($ticket->getId() == null) return null;
        return $ticket;
    }

    public function getHandledBy($username, $cursor, $limit) {
        return $this->getTicketsBy($username, $cursor, $limit, false);
    }

    public function getOpenedBy($username, $cursor, $limit) {
        return $this->getTicketsBy($username, $cursor, $limit, true);
    }

    private function getTicketsBy($username, $cursor, $limit, $creator) {

        $user = $this->getUser($username);
        if($user['id'] === 0) {
            return false;
        }
        $result = null;

        # TODO: Make this more DRY.
        if($creator) {

            $stmt = $this->database->prepare("SELECT * FROM `reportrts_tickets` AS `ticket` INNER JOIN `reportrts_users` AS `user` ON `ticket`.userId = `user`.uid
            WHERE `ticket`.userId = ? ORDER BY `ticket`.timestamp DESC LIMIT ?, ?");
            $stmt->bind_param("iii", $user['id'], $cursor, $limit);
            $stmt->execute();
            $temp = $stmt->get_result();
            $result = [];
            if (!$temp) {
                echo "Database Error [{$this->database->errno}] {$this->database->error}".PHP_EOL;
                return null;
            }
            while($row = $temp->fetch_array(1)) {
                $ticket = new Ticket($row['id'], $row['status'], $row['x'], $row['y'], $row['z'], $row['staffId'], $row['yaw'],
                    $row['pitch'], $row['timestamp'], $row['staffTime'], $row['text'], $row['name'], $row['world'], null, $row['comment']);
                if($ticket->getStatus() > 0) $ticket->setStaffName($this->getUser(null, $ticket->getStaffId())['username']);
                $result[$row['id']] = $ticket;
            }

            $stmt->close();
            return $result;
        } else {

            $stmt = $this->database->prepare("SELECT * FROM `reportrts_tickets` AS `ticket` INNER JOIN `reportrts_users` AS `user` ON `ticket`.userId = `user`.uid
            WHERE `ticket`.staffId = ? ORDER BY `ticket`.staffTime DESC LIMIT ?, ?");
            $stmt->bind_param("iii", $user['id'], $cursor, $limit);
            $stmt->execute();
            $temp = $stmt->get_result();
            $result = [];
            if (!$temp) {
                echo "Database Error [{$this->database->errno}] {$this->database->error}".PHP_EOL;
                return null;
            }
            while($row = $temp->fetch_array(1)) {
                $ticket = new Ticket($row['id'], $row['status'], $row['x'], $row['y'], $row['z'], $row['staffId'], $row['yaw'],
                    $row['pitch'], $row['timestamp'], $row['staffTime'], $row['text'], $row['name'], $row['world'], null, $row['comment']);
                if($ticket->getStatus() > 0) $ticket->setStaffName($this->getUser(null, $ticket->getStaffId())['username']);
                $result[$row['id']] = $ticket;
            }
        }
        return $result;
    }

    public function getTop($limit) {

        if(!is_int($limit)) return [];

        $query = $this->database->query("SELECT `reportrts_users`.name, COUNT(`reportrts_tickets`.staffId) AS tickets FROM `reportrts_tickets`
        LEFT JOIN `reportrts_users` ON `reportrts_tickets`.staffId = `reportrts_users`.uid WHERE `reportrts_tickets`.status = 3
        GROUP BY `name` ORDER BY tickets DESC LIMIT ".$limit);

        $result = [];

        while($row = $query->fetch_assoc()) array_push($result, $row);

        return $result;
    }

    public function setTicketStatus($id, $username, $status, $comment, $notified, $timestamp) {

        if(!isset(ReportRTS::$tickets[$id])) {
            # Ticket is not of status OPEN(1).
            $ticket = $this->getTicket($id);
            if($ticket == null) return -3;
        } else {
            # Retrieve ticket from ticket array.
            $ticket = ReportRTS::$tickets[$id];
        }

        # Make sure username is alphanumeric.
        if(!ctype_alnum($username)) return -1;

        # Check if user exists. Array_filter might be necessary, we'll find out.
        $user = $this->getUser($username, 0 , true);
        if(empty($user)) {
            return -1;
        }

        # Make sure ticket statuses don't clash.
        if($ticket->getStatus() == $status or ($status == 2 && $ticket->getStatus() == 3)) return -2;

        $stmt = $this->database->prepare("UPDATE `reportrts_tickets` SET `status` = ?, `staffId` = ?, `staffTime` = ?, `comment` = ?, `notified` = ? WHERE `id` = ?");
        $stmt->bind_param('iiisii', $status, $user['id'], $timestamp, $comment, $notified, $id);
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

    public function setUserStatus($username, $status) {

        # User status has to be a boolean.
        if(!is_bool($status)) return 0;

        $status = $status ? 1 : 0;

        $stmt = $this->database->prepare("UPDATE `reportrts_users` SET  `banned` = ? WHERE `name` = ? LIMIT 1");
        $stmt->bind_param("is", $status, $username);
        $stmt->execute();
        $result = $stmt->affected_rows;
        $stmt->close();

        return $result;
    }

    /**
     * @param $username
     * @param $id;
     * @param $createIfNotExists
     * @return Array
     */
    public function getUser($username = null, $id = 0, $createIfNotExists = false) {
        $sql = null;
        if($username != null) {
            $sql = $this->database->prepare("SELECT * FROM `reportrts_users` WHERE `name` = ? LIMIT 1");
            $sql->bind_param("s", $username);
        }
        if($username == null and $id > 0) {
            $sql = $this->database->prepare("SELECT * FROM `reportrts_users` WHERE `uid` = ?");
            $sql->bind_param("i", $id);
        }

        $sql->execute();
        $sql->bind_result($id, $name, $banned);
        $sql->fetch();

        # Create the user if it does not exist.
        if($createIfNotExists === true and $id === 0) {
            $id = $this->createUser($username);
            $name = $username;
            $banned = 0;
        }

        $array = [
            "id" => (int) $id,
            "username" => $name,
            "isBanned" => ($banned == 1) ? true : false
        ];

        $sql->close();
        return $array;
    }

    public function reset() {
        $this->database->multi_query("TRUNCATE TABLE `reportrts_users`; TRUNCATE TABLE `reportrts_tickets`;");
    }

    public function resetNotifications() {
        $query = $this->database->query("UPDATE `reportrts_tickets` SET `notified` = 1 WHERE `notified` = 0");
        return $query;
    }
}