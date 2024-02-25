<?php

namespace MDP\Auth;

use MDP\Auth\Exceptions\AuthDbConnectionNotSet;
use MDP\Auth\Exceptions\UserExistsException;
use PDO;
use PDOStatement;

class Auth
{
    private PDO | NULL $connection;
    /** @var string $usersTableName */
    private string $usersTableName = "users";
    /** @var string $loginField */
    private string $loginField = "email";
    /** @var string $usernameField */
    private string $usernameField = "username";
    /** @var string $passwordField */
    private string $passwordField = "password";
    /** @var string $emailField */
    private string $emailField = "email";
    private DatabaseTimestampsConfig $timestampsConfig;

    public function __construct(PDO $pdo = null)
    {
        $this->connection = $pdo;
        $this->timestampsConfig = new DatabaseTimestampsConfig(false);
    }

    public function user(): ?Authenticatable
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
        $sql = /** @lang SQL */"SELECT * FROM {$this->usersTableName} 
                    WHERE {$this->loginField} = '{$username}'";
        /** @var PDOStatement $stmt */
        $stmt = $this->connection->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return !!count(array_filter($results, function (array $result) use ($password) {
            return password_verify($password, $result[$this->passwordField]);
        }));
    }

    public function enableTimestamps(string $createdAtFieldName = null, string $updatedAtFieldName = null): void {
        $this->timestampsConfig->enabled = true;
        if($createdAtFieldName) {
            $this->timestampsConfig->createdAtFieldName = $createdAtFieldName;
        }
        if($createdAtFieldName) {
            $this->timestampsConfig->updatedAtFieldName = $updatedAtFieldName;
        }
    }

    /**
     * @throws AuthDbConnectionNotSet
     * @throws UserExistsException
     */
    public function register(string $username, string $email, string $password): Authenticatable
    {
        if (!$this->connection) {
            throw new AuthDbConnectionNotSet();
        }
        $sql1 = "SELECT * FROM {$this->usersTableName} WHERE {$this->emailField} = '{$email}'";
        $stmt = $this->connection->query($sql1);
        if ($stmt->fetch()) {
            throw new UserExistsException();
        }
        $fieldNames = "{$this->usernameField}, {$this->emailField}, {$this->passwordField}";
        $values = ":{$this->usernameField}, :{$this->emailField}, :{$this->passwordField}";
        if ($this->timestampsConfig->enabled) {
            $fieldNames .= ", {$this->timestampsConfig->createdAtFieldName}, {$this->timestampsConfig->updatedAtFieldName}";
            $values .= ", :{$this->timestampsConfig->createdAtFieldName}, :{$this->timestampsConfig->updatedAtFieldName}";
        }
        $sql2 = "INSERT INTO {$this->usersTableName} ({$fieldNames}) VALUES ({$values})";
        $stmt2 = $this->connection->prepare($sql2);
        $bindings = [
            ":{$this->usernameField}" => $username,
            ":{$this->emailField}" => $email,
            ":{$this->passwordField}" => $this->hash($password),
            ":{$this->timestampsConfig->createdAtFieldName}" => date("Y-m-d H:i:s"),
            ":{$this->timestampsConfig->updatedAtFieldName}" => date("Y-m-d H:i:s"),
        ];
        if ($this->timestampsConfig->enabled) {
            $bindings = [
                ...$bindings,
                ":{$this->timestampsConfig->createdAtFieldName}" => date("Y-m-d H:i:s"),
                ":{$this->timestampsConfig->updatedAtFieldName}" => date("Y-m-d H:i:s")
            ];

        }
        $stmt2->execute($bindings);
        $stmt3 = $this->connection->query($sql1);
        $userArr = $stmt3->fetch();
        return new class (
            $userArr["id"],
            $userArr["username"],
            $userArr["email"],
            $userArr["password"]
        ) extends Authenticatable {
            public function __construct(
                public int | string $id,
                public string $username,
                public string $email,
                public string $password,
            )
            {}
        };
    }

    /**
     * @param string $password
     * @return string
     */
    private function hash(string $password): string
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
