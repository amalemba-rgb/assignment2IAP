<?php
class User {
    private $conn;
    private $table = 'assign';

    public $id;
    public $username;
    public $email;
    public $password;
    public $two_fa_code;
    public $email_verified;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Register User
    public function register() {
        $query = "INSERT INTO " . $this->table . " (username, email, password, two_fa_code) VALUES (:username, :email, :password, :two_fa_code)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $hashed_password = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':two_fa_code', $this->two_fa_code);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Verify 2FA Code
    public function verify2FA($code) {
        $query = "UPDATE " . $this->table . " SET email_verified = 1 WHERE two_fa_code = :code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $code);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Login User
    public function login($email, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['email_verified']) {
                return $user;
            } else {
                echo "Email not verified!";
            }
        }
        return false;
    }

    // Generate 2FA Code
    public function generate2FACode() {
        return rand(100000, 999999);
    }
}
