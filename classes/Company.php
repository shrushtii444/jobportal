<?php
class Company {
    private $conn;
    private $table_name = "Company";

    public $id;
    public $userId;
    public $companyName;
    public $companyEmail;
    public $companyBio;
    public $companyWebsite;
    public $companyLogo;
    public $isVerified;
    public $createdAt;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getCompany($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt;
    }

    public function getCompanyByUserId($userId) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE userId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        return $stmt;
    }

    public function getAllCompanies() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY createdAt DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getVerifiedCompanies() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE isVerified = TRUE ORDER BY createdAt DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>