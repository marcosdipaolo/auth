<?php declare(strict_types=1);

use MDP\Auth\Auth;
use MDP\Auth\Exceptions;

class GeneralTest extends \PHPUnit\Framework\TestCase
{
    public Auth $auth;
    public PDO $db;
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->db = new \PDO("sqlite:" . __DIR__ . "/database.sqlite");
        $this->createUsersTable();
        $this->auth = new Auth($this->db);
        $this->auth->enableTimestamps();
    }

    public function testAuthInstance(): void
    {
        /**
         * @var mixed $auth
         */
        $auth = new Auth();
        $this->assertInstanceOf(Auth::class, $auth);
        $this->assertInstanceOf(Auth::class, $auth);
    }

    private function createUsersTable(): void
    {
        $this->db->exec(
            "CREATE TABLE users(
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )"
        );
    }

    /**
     * @throws Exceptions\UserExistsException
     * @throws Exceptions\AuthDbConnectionNotSet
     */
    public function testUserRegister(): void
    {
        $user = $this->auth->register("username", "an@email.com", "12345678");
    }
}