<?php
class Job {
    private $conn;
    private $table_name = "Job";

    public $id;
    public $companyId;
    public $title;
    public $description;
    public $requirements;
    public $location;
    public $salary_min;
    public $salary_max;
    public $experience_min;
    public $experience_max;
    public $skills;
    public $type;
    public $workMode;
    public $isActive;
    public $isVerifiedJob;
    public $expired;
    public $deleted;
    public $createdAt;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getActiveJobs($limit = 10, $offset = 0, $search = "", $type = "", $workMode = "", $minSalary = 0) {
        $query = "SELECT j.*, c.companyName, c.companyLogo, c.isVerified
                 FROM " . $this->table_name . " j
                 JOIN Company c ON j.companyId = c.id
                 WHERE j.isActive = TRUE
                 AND j.expired = FALSE
                 AND j.deleted = FALSE";

        if (!empty($search)) {
            $query .= " AND (j.title LIKE :search OR j.description LIKE :search OR j.location LIKE :search)";
        }
        if (!empty($type)) {
            $query .= " AND j.type = :type";
        }
        if (!empty($workMode)) {
            $query .= " AND j.workMode = :workMode";
        }
        if ($minSalary > 0) {
            $query .= " AND j.salary_min >= :minSalary";
        }

        $query .= " ORDER BY j.createdAt DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $searchParam = "%{$search}%";
            $stmt->bindParam(":search", $searchParam);
        }
        if (!empty($type)) {
            $stmt->bindParam(":type", $type);
        }
        if (!empty($workMode)) {
            $stmt->bindParam(":workMode", $workMode);
        }
        if ($minSalary > 0) {
            $stmt->bindParam(":minSalary", $minSalary);
        }

        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    public function getCompanyJobs($companyId) {
        $query = "SELECT j.*,
                 (SELECT COUNT(*) FROM Applications a WHERE a.jobId = j.id) as applicationCount,
                 (SELECT COUNT(*) FROM Applications a WHERE a.jobId = j.id AND a.status = 'PENDING') as pendingCount
                 FROM " . $this->table_name . " j
                 WHERE j.companyId = :companyId AND j.deleted = FALSE
                 ORDER BY j.createdAt DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":companyId", $companyId);
        $stmt->execute();
        return $stmt;
    }

    public function getJob($id) {
        $query = "SELECT j.*, c.companyName, c.companyLogo, c.companyBio, c.companyWebsite, c.isVerified
                 FROM " . $this->table_name . " j
                 JOIN Company c ON j.companyId = c.id
                 WHERE j.id = :id AND j.deleted = FALSE";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt;
    }

    public function countActiveJobs($search = "", $type = "", $workMode = "", $minSalary = 0) {
        $query = "SELECT COUNT(*) as total
                 FROM " . $this->table_name . " j
                 WHERE j.isActive = TRUE AND j.expired = FALSE AND j.deleted = FALSE";

        if (!empty($search)) {
            $query .= " AND (j.title LIKE :search OR j.description LIKE :search OR j.location LIKE :search)";
        }
        if (!empty($type)) {
            $query .= " AND j.type = :type";
        }
        if (!empty($workMode)) {
            $query .= " AND j.workMode = :workMode";
        }
        if ($minSalary > 0) {
            $query .= " AND j.salary_min >= :minSalary";
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $searchParam = "%{$search}%";
            $stmt->bindParam(":search", $searchParam);
        }
        if (!empty($type)) {
            $stmt->bindParam(":type", $type);
        }
        if (!empty($workMode)) {
            $stmt->bindParam(":workMode", $workMode);
        }
        if ($minSalary > 0) {
            $stmt->bindParam(":minSalary", $minSalary);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getAllJobsWithPagination($limit = 10, $offset = 0) {
        $query = "SELECT j.*, c.companyName, c.companyLogo, c.isVerified,
                 (SELECT COUNT(*) FROM Applications a WHERE a.jobId = j.id) as applicationCount
                 FROM " . $this->table_name . " j
                 LEFT JOIN Company c ON j.companyId = c.id
                 ORDER BY j.createdAt DESC
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $countStmt = $this->conn->prepare($countQuery);
        $countStmt->execute();
        $totalJobs = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($totalJobs / $limit);

        return [
            'jobs' => $jobs,
            'currentPage' => ($offset / $limit) + 1,
            'totalPages' => $totalPages,
            'totalJobs' => $totalJobs
        ];
    }

    public function getTotalJobs() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE deleted = FALSE";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function create() {
        if (!$this->conn) {
            throw new Exception("Database connection not established");
        }

        // Validate that companyId is set
        if (!$this->companyId) {
            throw new Exception("Company ID is required. Please create your company profile first.");
        }

        $query = "INSERT INTO " . $this->table_name . " 
                 (id, companyId, title, description, type, workMode, location, salary_min, salary_max, experience_min, experience_max, skills, isActive, isVerifiedJob, expired, deleted, createdAt) 
                 VALUES 
                 (UUID(), :companyId, :title, :description, :type, :workMode, :location, :salary_min, :salary_max, :experience_min, :experience_max, :skills, :isActive, TRUE, :expired, :deleted, NOW())";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":companyId", $this->companyId);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":type", $this->type);
        $stmt->bindParam(":workMode", $this->workMode);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":salary_min", $this->salary_min);
        $stmt->bindParam(":salary_max", $this->salary_max);
        $stmt->bindParam(":experience_min", $this->experience_min);
        $stmt->bindParam(":experience_max", $this->experience_max);
        $stmt->bindParam(":skills", $this->skills);
        $stmt->bindParam(":isActive", $this->isActive, PDO::PARAM_INT);
        $stmt->bindParam(":expired", $this->expired, PDO::PARAM_INT);
        $stmt->bindParam(":deleted", $this->deleted, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function expireJob($jobId) {
        if (!$this->conn) {
            throw new Exception("Database connection not established");
        }

        $query = "UPDATE " . $this->table_name . "
                 SET expired = TRUE,
                     expiryDate = NOW()
                 WHERE id = :id AND deleted = FALSE";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $jobId);
        return $stmt->execute();
    }

    public function activateJob($jobId) {
        if (!$this->conn) {
            throw new Exception("Database connection not established");
        }

        $query = "UPDATE " . $this->table_name . "
                 SET expired = FALSE,
                     expiryDate = NULL
                 WHERE id = :id AND deleted = FALSE";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $jobId);
        return $stmt->execute();
    }

    public function verifyJob($jobId) {
        if (!$this->conn) {
            throw new Exception("Database connection not established");
        }

        $query = "UPDATE " . $this->table_name . "
                 SET isVerifiedJob = TRUE
                 WHERE id = :id AND deleted = FALSE";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $jobId);
        return $stmt->execute();
    }

    public function delete($jobId) {
        if (!$this->conn) {
            throw new Exception("Database connection not established");
        }

        $query = "UPDATE " . $this->table_name . "
                 SET deleted = TRUE,
                     deletedAt = NOW()
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $jobId);
        return $stmt->execute();
    }
}
?>