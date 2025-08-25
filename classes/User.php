<?php
class User {
    private $conn;
    private $table = "Users";

    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $createdAt;

    public function __construct($db) {
        if (!$db) {
            throw new Exception("Database connection is required");
        }
        $this->conn = $db;
    }

    public function register() {
        if (!$this->conn) {
            throw new Exception("Database connection not established");
        }

        $query = "INSERT INTO " . $this->table . "
                (id, name, email, password, role)
                VALUES
                (UUID(), :name, :email, :password, :role)";

        $stmt = $this->conn->prepare($query);

        // Clean and hash data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->role = htmlspecialchars(strip_tags($this->role));

        // Bind data
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role", $this->role);

        try {
            $stmt->execute();
            return true;
        } catch(PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry error
                return false;
            }
            throw $e;
        }
    }

    public function login() {
        if (!$this->conn) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT id, name, email, password, role, isVerified FROM " . $this->table . "
                WHERE email = :email";

        $stmt = $this->conn->prepare($query);

        $this->email = htmlspecialchars(strip_tags($this->email));

        $stmt->bindParam(":email", $this->email);

        $stmt->execute();

        return $stmt;
    }

    public function emailExists() {
        if (!$this->conn) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT id FROM " . $this->table . " WHERE email = :email";

        $stmt = $this->conn->prepare($query);

        $this->email = htmlspecialchars(strip_tags($this->email));

        $stmt->bindParam(":email", $this->email);

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function updateProfile() {
        if (!$this->conn) {
            throw new Exception("Database connection not established");
        }

        $query = "UPDATE " . $this->table . "
                SET name = :name
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->name = htmlspecialchars(strip_tags($this->name));

        // Bind data
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":id", $this->id);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            throw $e;
        }
    }

    public function getUser($id) {
        if (!$this->conn) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers() {
        if (!$this->conn) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT * FROM " . $this->table . "
                WHERE role != 'ADMIN'
                ORDER BY createdAt DESC
                LIMIT 10";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalUsers() {
        if (!$this->conn) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE role != 'ADMIN'";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getAllUsersWithPagination($limit, $offset) {
        if (!$this->conn) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT * FROM " . $this->table . "
                WHERE role != 'ADMIN'
                ORDER BY createdAt DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteUser($userId) {
        if (!$this->conn) {
            throw new Exception("Database connection not established");
        }

        // First check if user exists and is not an admin
        $query = "SELECT role FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $userId);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || $user['role'] === 'ADMIN') {
            return false;
        }

        // Delete user's jobs first
        $query = "UPDATE Job SET deleted = TRUE, deletedAt = NOW() WHERE userId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();

        // Then delete the user
        $query = "DELETE FROM " . $this->table . " WHERE id = :id AND role != 'ADMIN'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $userId);

        return $stmt->execute();
    }

    public function searchUsers($searchTerm) {
        if (!$this->conn) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT * FROM " . $this->table . "
                WHERE role != 'ADMIN'
                AND (name LIKE :search OR email LIKE :search)
                ORDER BY createdAt DESC";

        $searchTerm = "%$searchTerm%";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":search", $searchTerm);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsersByRole($role) {
        if (!$this->conn) {
            throw new Exception("Database connection not established");
        }

        $query = "SELECT * FROM " . $this->table . "
                WHERE role = :role
                ORDER BY createdAt DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":role", $role);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserStats() {
        $query = "SELECT
                    SUM(CASE WHEN role = 'USER' THEN 1 ELSE 0 END) as jobSeekers,
                    SUM(CASE WHEN role = 'HR' THEN 1 ELSE 0 END) as hrManagers
                 FROM " . $this->table . "
                 WHERE role != 'ADMIN'";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Convert null values to 0
        $stats['jobSeekers'] = (int)$stats['jobSeekers'];
        $stats['hrManagers'] = (int)$stats['hrManagers'];

        return $stats;
    }

    public function verifyUser($userId) {
        try {
            $this->conn->beginTransaction();

            // Update user verification status
            $query = "UPDATE " . $this->table . " SET isVerified = TRUE WHERE id = :id AND role = 'HR'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $userId);
            $stmt->execute();

            // Update company verification status
            $query = "UPDATE Company SET isVerified = TRUE WHERE userId = :userId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":userId", $userId);
            $stmt->execute();

            // Also verify all jobs from this company
            $query = "UPDATE Job j
                     JOIN Company c ON j.companyId = c.id
                     SET j.isVerifiedJob = TRUE
                     WHERE c.userId = :userId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":userId", $userId);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function blockUser($userId) {
        $query = "UPDATE " . $this->table . " SET blockedByAdmin = TRUE WHERE id = :id AND role != 'ADMIN'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $userId);
        return $stmt->execute();
    }

    public function unblockUser($userId) {
        $query = "UPDATE " . $this->table . " SET blockedByAdmin = FALSE WHERE id = :id AND role != 'ADMIN'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $userId);
        return $stmt->execute();
    }

    public function unverifyUser($userId) {
        try {
            $this->conn->beginTransaction();

            // Update user verification status
            $query = "UPDATE " . $this->table . " SET isVerified = FALSE WHERE id = :id AND role = 'HR'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $userId);
            $stmt->execute();

            // Update company verification status
            $query = "UPDATE Company SET isVerified = FALSE WHERE userId = :userId";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":userId", $userId);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
?>