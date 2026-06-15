<?php
class User {
    private $conn;
    private $table_name = "users";

    public function __construct($db) { $this->conn = $db; }

    public function isLoginUnique($login) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE login = :login LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":login", $login);
        $stmt->execute();
        return $stmt->rowCount() == 0;
    }

    public function register($fio, $phone, $email, $login, $password) {
        if (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) return "Логин должен быть от 6 символов (только латиница и цифры).";
        if (strlen($password) < 8) return "Пароль должен быть не менее 8 символов.";
        if (!$this->isLoginUnique($login)) return "Этот логин уже занят.";

        $query = "INSERT INTO " . $this->table_name . " (fio, phone, email, login, password, role_id) VALUES (:fio, :phone, :email, :login, :password, 1)";
        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt->bindParam(":fio", $fio);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":login", $login);
        $stmt->bindParam(":password", $hashed_password);

        if ($stmt->execute()) return true;
        return "Ошибка записи в БД.";
    }

    public function login($login, $password) {
        $query = "SELECT id, fio, password, role_id FROM " . $this->table_name . " WHERE login = :login LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":login", $login);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) return $row;
        }
        return false;
    }
}
?>
