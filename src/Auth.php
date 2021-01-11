<?php

namespace MDP\Auth;

use MDP\Auth\Exceptions\AuthDbConnectionNotSet;
use MDP\Auth\Exceptions\UserExistsException;
use PDO;
use PDOStatement;

class Auth
{
    /** @var PDO $connection */
    private $connection;
    /** @var string $usersTableName */
    private $usersTableName = "users";
    /** @var string $loginField */
    private $loginField = "email";
    /** @var string $usernameField */
    private $usernameField = "username";
    /** @var string $passwordField */
    private $passwordField = "password";
    /** @var string $emailField */
    private $emailField = "email";

    public function __construct(PDO $pdo = null)
    {
        $this->connection = $pdo;
    }

    public function user(): Authenticatable
    {
        return session()->get('user');
    }

    public function login(Authenticatable $user): bool
    {
        return session()->put('user', $user);
    }

    public function logout(): bool
    {
        return session()->forget('user');
    }

    public function isUserLoggedIn(): bool
    {
        return session()->has('user') &&
            (session()->get('user') instanceof Authenticatable);
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     * @throws AuthDbConnectionNotSet
     */
    public function check(string $username, string $password): bool
    {
        if (!$this->connection) {
            throw new AuthDbConnectionNotSet();
        }
        $sql = "SELECT * FROM {$this->usersTableName} 
                    WHERE {$this->loginField} = '{$username}'";
        /** @var PDOStatement $stmt */
        $stmt = $this->connection->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return !!count(array_filter($results, function (array $result) use ($password) {
            return password_verify($password, $result[$this->passwordField]);
        }));
    }

    /**
     * @param string $username
     * @param string $email
     * @param string $password
     * @return bool
     * @throws AuthDbConnectionNotSet
     * @throws UserExistsException
     */
    public function register(string $username, string $email, string $password): bool
    {
        if (!$this->connection) {
            throw new AuthDbConnectionNotSet();
        }
        $sql1 = "SELECT * FROM {$this->usersTableName} WHERE {$this->emailField} = '{$email}'";
        $stmt = $this->connection->query($sql1);
        if ($stmt->fetch()) {
            throw new UserExistsException();
        }
        $sql2 = "INSERT INTO {$this->usersTableName} ({$this->usernameField}, {$this->emailField}, {$this->passwordField}) VALUES (:{$this->usernameField}, :{$this->emailField}, :{$this->passwordField})";
        $stmt2 = $this->connection->prepare($sql2);
        return $stmt2->execute([
            ":{$this->usernameField}" => $username,
            ":{$this->emailField}" => $email,
            ":{$this->passwordField}" => $this->hash($password),
        ]);
    }

    /**
     * @param string $password
     * @return false|string|null
     */
    private function hash(string $password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * @param array $fields
     * @return bool
     */
    public function setUsersTableFields(array $fields): bool
    {
        $output = false;
        foreach ([
                     'usernameField' => 'setUsernameField',
                     'passwordField' => 'setPasswordField',
                     'emailField' => 'setEmailField'
                 ] as $field => $setter) {
            if (array_key_exists($field, $fields)) {
                $this->$setter($fields[$field]);
                $output = true;
            }
        }
        return $output;
    }

    /**
     * @param string $tableName
     */
    public function setUsersTableName(string $tableName): void
    {
        $this->usersTableName = $tableName;
    }

    /**
     * @param string $loginField
     */
    public function setLoginField(string $loginField): void
    {
        $this->loginField = $loginField;
    }

    /**
     * @param string $passwordField
     */
    public function setPasswordField(string $passwordField): void
    {
        $this->passwordField = $passwordField;
    }

    /**
     * @param string $emailField
     */
    public function setEmailField(string $emailField): void
    {
        $this->emailField = $emailField;
    }

    /**
     * @param string $usernameField
     */
    public function setUsernameField(string $usernameField): void
    {
        $this->usernameField = $usernameField;
    }

    /**
     * @return bool
     * @throws AuthDbConnectionNotSet
     */
    private function usersTableExists(): bool
    {
        if (!$this->connection) {
            throw new AuthDbConnectionNotSet();
        }
        $sql = "SELECT 1 FROM {$this->usersTableName}";
        $stmt = $this->connection->query($sql);
        try {
            $results = $stmt->fetchAll();
            if (is_array($results)) {
                return true;
            }
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
