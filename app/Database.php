<?php

declare(strict_types=1);

namespace Api;

require 'vendor/autoload.php';

use Dotenv\Dotenv;

class Database {

    public $hostMaster;
    public $port;
    public $db;
    public $user;
    public $pass;
    public $hostMasterSharded;
    public $portSharded;
    public $dbSharded;
    public $userSharded;
    public $passSharded;

    function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $this->hostMaster = $_ENV['POSTGRES_HOST'];

        $this->port = $_ENV['POSTGRES_PORT'];
        $this->db = $_ENV['POSTGRES_DB'];
        $this->user = $_ENV['POSTGRES_USER'];
        $this->pass = $_ENV['POSTGRES_PASSWORD'];

        $this->hostMasterSharded = $_ENV['POSTGRES_HOST_SHARDED'];

        $this->portSharded = $_ENV['POSTGRES_PORT_SHARDED'];
        $this->dbSharded = $_ENV['POSTGRES_DB_SHARDED'];
        $this->userSharded = $_ENV['POSTGRES_USER_SHARDED'];
        $this->passSharded = $_ENV['POSTGRES_PASSWORD_SHARDED'];
    }

    public function getConnection(bool $isSharded = false) {
        try {

            if (!$isSharded) {
                $pdo = new \PDO("pgsql:host=$this->hostMaster;port=$this->port;dbname=$this->db", $this->user, $this->pass);

            } else {
                $pdo = new \PDO("pgsql:host=$this->hostMasterSharded;port=$this->portSharded;dbname=$this->dbSharded", $this->userSharded, $this->passSharded);
            }
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            return $pdo;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            exit();
        }
    }
}
?>