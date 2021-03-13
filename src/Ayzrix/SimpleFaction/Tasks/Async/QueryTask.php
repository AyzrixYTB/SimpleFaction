<?php

namespace Ayzrix\SimpleFaction\Tasks\Async;

use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\scheduler\AsyncTask;

class QueryTask extends AsyncTask {

    private $text;
    private $hostname;
    private $user;
    private $password;
    private $database;

    public function __construct(string $text) {
        $this->text = $text;
        $this->hostname = Utils::getIntoConfig("mysql_address");
        $this->user = Utils::getIntoConfig("mysql_user");
        $this->password = Utils::getIntoConfig("mysql_password");
        $this->database = Utils::getIntoConfig("mysql_db");
    }

    public function onRun() {
        $db = new \MySQLi($this->hostname, $this->user, $this->password, $this->database);
        $db->query($this->text);
    }
}