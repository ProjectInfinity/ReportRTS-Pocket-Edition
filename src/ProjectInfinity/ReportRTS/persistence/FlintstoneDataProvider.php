<?php

namespace ProjectInfinity\ReportRTS\persistence;

use pocketmine\level\Position;

use ProjectInfinity\ReportRTS\ReportRTS;
use ProjectInfinity\ReportRTS\data\Ticket;
use ProjectInfinity\ReportRTS\lib\flintstone\Flintstone;
use ProjectInfinity\ReportRTS\util\ToolBox;

class FlintstoneDataProvider implements DataProvider {

    protected $plugin;
    protected $tickets;
    protected $users;

    /** @param ReportRTS $plugin */
    public function __construct(ReportRTS $plugin) {
        $this->plugin = $plugin;
        $this->tickets = Flintstone::load('tickets', ['dir' => $plugin->getDataFolder(), 'gzip' => true]);
        $this->users = Flintstone::load('users', ['dir' => $plugin->getDataFolder(), 'gzip' => true]);
        ReportRTS::$tickets = $this->load();
    }

    private function buildTicketFromData($key, $ticketData) {

        $ticket = new Ticket(intval($key), $ticketData['status'], $ticketData['x'], $ticketData['y'], $ticketData['z'], null,
            $ticketData['yaw'], $ticketData['pitch'], $ticketData['timestamp'], null, $ticketData['text'], $this->getUser(null, $ticketData['userId'])['username'],
            $ticketData['world'], null, null);

        if($ticket->getStatus() > 0)  {
            $ticket->setStaffName($this->getUser(null, $ticket->getStaffId())['username']);
            $ticket->setStaffTimestamp($ticketData['staffTime'] > 0 ? $ticketData['staffTime'] : null);
            $ticket->setComment(strlen($ticketData['comment']) > 0 ? $ticketData['comment'] : null);
        }

        return $ticket;
    }

    /** @return Ticket[] */
    private function load() {

        /** @var Ticket[] $tickets */
        $tickets = [];

        foreach($this->tickets->getKeys() as $key) {
            $ticket = $this->tickets->get($key);
            # Ticket is of incorrect status, let's skip it.
            if($ticket['status'] > 1) continue;

            $ticketClass = new Ticket(intval($key), $ticket['status'], $ticket['x'], $ticket['y'], $ticket['z'], null,
                $ticket['yaw'], $ticket['pitch'], $ticket['timestamp'], null, $ticket['text'], $this->getUser(null, $ticket['userId'])['username'],
                $ticket['world'], null, null);

            if($ticketClass->getStatus() > 0)  {
                $ticketClass->setStaffName($this->getUser(null, $ticket->getStaffId())['username']);
                $ticketClass->setStaffTimestamp($ticket['staffTime'] > 0 ? $ticket['staffTime'] : null);
                $ticketClass->setComment(strlen($ticket['comment']) > 0 ? $ticket['comment'] : null);
            }
            $tickets[$key] = $ticketClass;
        }

        return $tickets;

    }

    public function close() {
        Flintstone::unload('tickets');
        Flintstone::unload('users');
    }

    public function reset() {
        $this->tickets->flush();
        $this->users->flush();
    }

    public function resetNotifications() {
        // TODO: Implement resetNotifications() method.
    }

    public function createUser($username) {

        $player = $this->plugin->getServer()->getPlayer($username);
        if($player == null and strtoupper($username) != "CONSOLE") return 0;

        $this->users->set($username, ['uid' => count($this->users->getKeys()) + 1, 'name' => $username, 'banned' => false]);

        return $this->users->get($username)['uid'];

    }

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
        $data = [
            'userId' => $user['id'],
            'staffId' => 0,
            'staffTime' => 0.00,
            'status' => 0,
            'notified' => 0,
            'timestamp' => $timestamp,
            'world' => $world,
            'x' => $x,
            'y' => $y,
            'z' => $z,
            'yaw' => $yaw,
            'pitch' => $pitch,
            'comment' => "",
            'text' => $message,
        ];

        $id = count($this->tickets->getKeys()) + 1;
        $this->tickets->set((String) $id, $data);

        return $id;
    }

    /**
     * Gets a number quoting the amount of tickets of current status.
     * If no status is specified it defaults to 4 which is a non-valid
     * status, and should be parsed as ALL tickets.
     * @param int $status
     * @return int
     */
    public function countTickets($status = 4) {

        $i = 0;

        if($status < 4) {

            foreach($this->tickets->getKeys() as $key) {
                if($this->tickets->get($key)['status'] != $status) continue;
                $i++;
            }

        } else {
            $i = count($this->tickets->getKeys());
        }

        return $i;
    }

    /**
     * @param int $cursor
     * @param int $limit
     * @param int $status
     * @return Ticket[]
     */
    public function getTickets($cursor, $limit, $status = 0) {

        $tickets = [];

        $i = 0;
        $l = 0;

        $keys = $this->tickets->getKeys();
        if($status == 3) arsort($keys);

        foreach($keys as $key) {
            $ticket = $this->tickets->get($key);

            # Return array because we have reached the limit.
            if($l >= $limit) return $tickets;

            ## Debug line for pagination ##
            #echo PHP_EOL."i:".$i."/".$cursor." l:".$l."/".$limit.PHP_EOL;

            # Ticket is of incorrect status, let's skip it.
            if($ticket['status'] != $status) continue;

            # Increment the counter because the cursor is higher than the counter.
            if($cursor > $i) {
                $i++;
                continue;
            }

            $tickets[$key] = $this->buildTicketFromData($key, $ticket);
            if($status == 3) arsort($tickets);

            if($i < $limit) $i++;
            $l++;

        }
        return $tickets;
    }

    /**
     * @param $id
     * @return Ticket
     */
    public function getTicket($id) {

        if(!ToolBox::isNumber($id) or array_key_exists($id, $this->tickets->getKeys())) return null;

        return $this->buildTicketFromData($id, $this->tickets->get((String) $id));

    }

    private function getTicketsBy($username, $cursor, $limit, $creator) {

        $user = $this->getUser($username);
        if ($user['id'] === 0) {
            return false;
        }

        $return = null;

        if($creator) {

            $result = [];
            $i = 0;
            $l = 0;
            foreach($this->tickets->getKeys() as $key) {

                $ticket = $this->tickets->get($key);

                # Return array because we have reached the limit.
                if($l >= $limit) return $result;

                ## Debug line for pagination ##
                #echo PHP_EOL."i:".$i."/".$cursor." l:".$l."/".$limit.PHP_EOL;

                # Continue because the ticket is not opened by the user we are looking for.
                if($ticket['userId'] != $user['id']) continue;

                # Increment the counter because the cursor is higher than the counter.
                if($cursor > $i) {
                    $i++;
                    continue;
                }

                $result[intval($key)] = $this->buildTicketFromData($key, $ticket);

                if($i < $limit) $i++;
                $l++;

            }

        } else {

            $result = [];

            $i = 0;
            $l = 0;

            $invertedKeys = $this->tickets->getKeys();
            arsort($invertedKeys);
            foreach($invertedKeys as $key) {

                $ticket = $this->tickets->get($key);

                # Continue if ticket is NOT closed.
                if($ticket['status'] < 3) continue;

                # Return array because we have reached the limit.
                if($l >= $limit) {
                    arsort($result);
                    return $result;
                }

                ## Debug line for pagination ##
                #echo PHP_EOL."i:".$i."/".$cursor." l:".$l."/".$limit.PHP_EOL;

                # Continue because the ticket is not closed by the user we are looking for.
                if($ticket['staffId'] != $user['id']) continue;

                # Increment the counter because the cursor is higher than the counter.
                if($cursor > $i) {
                    $i++;
                    continue;
                }

                $result[intval($key)] = $this->buildTicketFromData($key, $ticket);

                if($i < $limit) $i++;
                $l++;

            }
        }
        return $result;
    }

    /**
     * @param $username
     * @param $cursor
     * @param $limit
     * @return Ticket[]|mixed
     */
    public function getHandledBy($username, $cursor, $limit) {
        return $this->getTicketsBy($username, $cursor, $limit, false);
    }

    /**
     * @param $username
     * @param $cursor
     * @param $limit
     * @return Ticket[]|mixed
     */
    public function getOpenedBy($username, $cursor, $limit) {
        return $this->getTicketsBy($username, $cursor, $limit, true);
    }

    /**
     * @param int $limit
     * @return Array
     */
    public function getTop($limit) {

        if(!is_int($limit)) return [];

        $result = [];

        foreach($this->tickets->getKeys() as $key) {
            $ticket = $this->tickets->get($key);

            # We only want tickets that are closed.
            if($ticket['status'] != 3) continue;

            $user = $this->getUser(null, $ticket['staffId']);

            # Ensure that array key exists, we don't want any errors.
            if(!array_key_exists($user['username'], $result)) $result[$user['username']] = 0;

            $result[$user['username']] = $result[$user['username']] + 1;
        }

        arsort($result);
        # Sorts the array by value, descending.
        return array_slice($result, 0, $limit, true);
    }

    /**
     * @param $username
     * @param $id
     * @param $createIfNotExists
     * @return Array
     */
    public function getUser($username = null, $id = 0, $createIfNotExists = false) {

        if($username != null) {
            # Create user if it does not exist.
            if(!array_key_exists($username, $this->users->getKeys()) and $createIfNotExists === true) $this->createUser($username);
            $user = $this->users->get($username);
            return [
                "id" => (int) $user['uid'],
                "username" => $user['name'],
                "isBanned" => $user['banned'] == 1 ? true : false
            ];
        }
        if($username == null and $id > 0) {
            foreach($this->users->getKeys() as $key) {
                # UID didn't match ID, continue.
                if($this->users->get($key)['uid'] === $id) {
                    # We found our user! Let's return it.
                    $user = [
                        'uid' => $id,
                        'name' => $this->users->get($key)['name'],
                        'banned' => $this->users->get($key)['banned'] == 1 ? true : false
                    ];
                    return [
                        "id" => (int) $user['uid'],
                        "username" => $user['name'],
                        "isBanned" => $user['banned'] == 1 ? true : false
                    ];
                }
            }
        }

        return [
            "id" => 0,
            "username" => "",
            "banned" => false
        ];
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

        return $this->tickets->replace((String) $id, [
            'userId' => $this->getUser($ticket->getName())['id'],
            'staffId' => $user['id'],
            'staffTime' => $timestamp,
            'status' => $status,
            'notified' => 0,
            'timestamp' => $timestamp,
            'world' => $ticket->getWorld(),
            'x' => $ticket->getX(),
            'y' => $ticket->getY(),
            'z' => $ticket->getZ(),
            'yaw' => $ticket->getYaw(),
            'pitch' => $ticket->getPitch(),
            'comment' => $comment,
            'text' => $ticket->getMessage(),
        ]) ? 1 : 0;

    }

    public function setNotificationStatus($id, $status) {

        # If -1 is returned then either the ID is not a number or the status is not a boolean.
        if(!ToolBox::isNumber($id) or !is_bool($status)) return -1;

        $id = intval($id);
        $status = $status ? 1 : 0 ;

        $ticket = $this->tickets->get((String) $id);
        $ticket['notified'] = $status;
        $this->tickets->replace((String) $id, $ticket);
        $ticket = $this->tickets->get((String) $id);

        return $status == $ticket['notified'] ? 0 : 1;

    }

    public function setUserStatus($username, $status) {

        # User status has to be a boolean.
        if(!is_bool($status)) return 0;

        $user = $this->users->get($username);

        # Return if status is the same as the one provided.
        if($status == $user['banned']) return 0;
        $user['banned'] = $status;

        $this->users->replace($username, $user);
        $user = $this->users->get($username);

        return $status == $user['banned'] ? 1 : 0;
    }
}