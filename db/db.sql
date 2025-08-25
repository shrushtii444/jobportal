CREATE DATABASE IF NOT EXISTS job_board;
USE job_board;

-- Users table
CREATE TABLE IF NOT EXISTS Users (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('ADMIN', 'HR', 'USER') NOT NULL,
    isVerified BOOLEAN DEFAULT FALSE,
    blockedByAdmin BOOLEAN DEFAULT FALSE,
    resume VARCHAR(255) DEFAULT NULL,
    skills TEXT DEFAULT NULL,
    aboutMe TEXT DEFAULT NULL,
    linkedinLink VARCHAR(255) DEFAULT NULL,
    githubLink VARCHAR(255) DEFAULT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Company table
CREATE TABLE IF NOT EXISTS Company (
    id VARCHAR(36) PRIMARY KEY,
    userId VARCHAR(36),
    companyName VARCHAR(100) NOT NULL,
    companyEmail VARCHAR(100) NOT NULL,
    companyBio TEXT,
    companyWebsite VARCHAR(255),
    companyLogo VARCHAR(255),
    isVerified BOOLEAN DEFAULT FALSE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES Users(id) ON DELETE CASCADE
);

-- Job table
CREATE TABLE IF NOT EXISTS Job (
    id VARCHAR(36) PRIMARY KEY,
    companyId VARCHAR(36) NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('FULL_TIME', 'PART_TIME', 'CONTRACT', 'INTERNSHIP') NOT NULL,
    workMode ENUM('REMOTE', 'HYBRID', 'OFFICE') NOT NULL,
    location VARCHAR(100) NOT NULL,
    salary_min INT,
    salary_max INT,
    experience_min INT,
    experience_max INT,
    skills JSON,
    isActive BOOLEAN DEFAULT TRUE,
    isVerifiedJob BOOLEAN DEFAULT FALSE,
    expired BOOLEAN DEFAULT FALSE,
    deleted BOOLEAN DEFAULT FALSE,
    deletedAt TIMESTAMP NULL,
    expiryDate TIMESTAMP NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (companyId) REFERENCES Company(id) ON DELETE CASCADE
);

-- Applications table
CREATE TABLE IF NOT EXISTS Applications (
    id VARCHAR(36) PRIMARY KEY,
    jobId VARCHAR(36) NOT NULL,
    userId VARCHAR(36) NOT NULL,
    status ENUM('PENDING', 'SHORTLISTED', 'REJECTED', 'HIRED') DEFAULT 'PENDING',
    coverLetter TEXT,
    resume VARCHAR(255),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jobId) REFERENCES Job(id) ON DELETE CASCADE,
    FOREIGN KEY (userId) REFERENCES Users(id) ON DELETE CASCADE
);

-- Experience table
CREATE TABLE IF NOT EXISTS Experience (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId VARCHAR(36) NOT NULL,
    companyName TEXT NOT NULL,
    designation TEXT NOT NULL,
    employmentType ENUM('FULL_TIME', 'PART_TIME', 'INTERNSHIP', 'CONTRACT') NOT NULL,
    address TEXT NOT NULL,
    workMode ENUM('REMOTE', 'HYBRID', 'OFFICE') NOT NULL,
    currentWorkStatus BOOLEAN NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE,
    description TEXT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES Users(id) ON DELETE CASCADE
);

-- Education table
CREATE TABLE IF NOT EXISTS Education (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId VARCHAR(36) NOT NULL,
    instituteName TEXT NOT NULL,
    degree ENUM('BTech', 'MTech', 'BCA', 'MCA', 'BSc', 'MSc', 'BBA', 'MBA', 'Other') NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES Users(id) ON DELETE CASCADE
);

-- Create admin user
INSERT INTO Users (id, name, email, password, role, isVerified)
VALUES (
    UUID(), 
    'Admin',
    'admin@100xjobs.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'ADMIN',
    TRUE
);

-- Insert dummy HR users
INSERT INTO Users (id, name, email, password, role)
VALUES 
(UUID(), 'John HR', 'hr1@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR'),
(UUID(), 'Sarah Manager', 'hr2@innovatech.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR'),
(UUID(), 'Mike Recruiter', 'hr3@globalsoft.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR'),
(UUID(), 'Lisa Hiring', 'hr4@futuretech.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR'),
(UUID(), 'David HR', 'hr5@webcraft.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR');

-- Insert dummy companies
INSERT INTO Company (id, userId, companyName, companyEmail, companyBio, companyWebsite)
SELECT 
    UUID(),
    u.id,
    CASE 
        WHEN u.email = 'hr1@techcorp.com' THEN 'TechCorp Solutions'
        WHEN u.email = 'hr2@innovatech.com' THEN 'InnovaTech Systems'
        WHEN u.email = 'hr3@globalsoft.com' THEN 'GlobalSoft Technologies'
        WHEN u.email = 'hr4@futuretech.com' THEN 'FutureTech Innovations'
        WHEN u.email = 'hr5@webcraft.com' THEN 'WebCraft Studios'
    END,
    u.email,
    CASE 
        WHEN u.email = 'hr1@techcorp.com' THEN 'Leading provider of enterprise software solutions'
        WHEN u.email = 'hr2@innovatech.com' THEN 'Innovative technology solutions for modern businesses'
        WHEN u.email = 'hr3@globalsoft.com' THEN 'Global leader in software development and consulting'
        WHEN u.email = 'hr4@futuretech.com' THEN 'Building the technology of tomorrow'
        WHEN u.email = 'hr5@webcraft.com' THEN 'Creative web solutions and digital experiences'
    END,
    CASE 
        WHEN u.email = 'hr1@techcorp.com' THEN 'https://techcorp.com'
        WHEN u.email = 'hr2@innovatech.com' THEN 'https://innovatech.com'
        WHEN u.email = 'hr3@globalsoft.com' THEN 'https://globalsoft.com'
        WHEN u.email = 'hr4@futuretech.com' THEN 'https://futuretech.com'
        WHEN u.email = 'hr5@webcraft.com' THEN 'https://webcraft.com'
    END
FROM Users u
WHERE u.role = 'HR';

-- Insert dummy jobs
INSERT INTO Job (id, companyId, title, description, type, workMode, location, salary_min, salary_max, experience_min, experience_max, isActive, isVerifiedJob)
SELECT 
    UUID(),
    c.id,
    CASE c.companyName
        WHEN 'TechCorp Solutions' THEN 'Senior Full Stack Developer'
        WHEN 'InnovaTech Systems' THEN 'DevOps Engineer'
        WHEN 'GlobalSoft Technologies' THEN 'Mobile App Developer'
        WHEN 'FutureTech Innovations' THEN 'AI/ML Engineer'
        WHEN 'WebCraft Studios' THEN 'UI/UX Designer'
    END as title,
    CASE c.companyName
        WHEN 'TechCorp Solutions' THEN 'Looking for an experienced Full Stack Developer proficient in React and Node.js'
        WHEN 'InnovaTech Systems' THEN 'Seeking a DevOps Engineer with expertise in AWS and Kubernetes'
        WHEN 'GlobalSoft Technologies' THEN 'Experienced Mobile Developer needed for iOS and Android development'
        WHEN 'FutureTech Innovations' THEN 'AI/ML Engineer with expertise in Python and TensorFlow'
        WHEN 'WebCraft Studios' THEN 'Creative UI/UX Designer with experience in modern design tools'
    END as description,
    'FULL_TIME' as type,
    'HYBRID' as workMode,
    CASE c.companyName
        WHEN 'TechCorp Solutions' THEN 'New York, NY'
        WHEN 'InnovaTech Systems' THEN 'San Francisco, CA'
        WHEN 'GlobalSoft Technologies' THEN 'Austin, TX'
        WHEN 'FutureTech Innovations' THEN 'Boston, MA'
        WHEN 'WebCraft Studios' THEN 'Seattle, WA'
    END as location,
    80000 as salary_min,
    150000 as salary_max,
    3 as experience_min,
    8 as experience_max,
    TRUE as isActive,
    TRUE as isVerifiedJob
FROM Company c;

-- Insert additional jobs for variety
INSERT INTO Job (id, companyId, title, description, type, workMode, location, salary_min, salary_max, experience_min, experience_max, isActive, isVerifiedJob)
SELECT 
    UUID(),
    c.id,
    CASE c.companyName
        WHEN 'TechCorp Solutions' THEN 'Product Manager'
        WHEN 'InnovaTech Systems' THEN 'Backend Developer'
        WHEN 'GlobalSoft Technologies' THEN 'Frontend Developer'
        WHEN 'FutureTech Innovations' THEN 'Data Scientist'
        WHEN 'WebCraft Studios' THEN 'WordPress Developer'
    END as title,
    CASE c.companyName
        WHEN 'TechCorp Solutions' THEN 'Experienced Product Manager to lead our enterprise products'
        WHEN 'InnovaTech Systems' THEN 'Backend Developer with Java Spring Boot expertise'
        WHEN 'GlobalSoft Technologies' THEN 'Frontend Developer with React and TypeScript skills'
        WHEN 'FutureTech Innovations' THEN 'Data Scientist with experience in big data technologies'
        WHEN 'WebCraft Studios' THEN 'WordPress Developer for custom theme development'
    END as description,
    'FULL_TIME' as type,
    'REMOTE' as workMode,
    CASE c.companyName
        WHEN 'TechCorp Solutions' THEN 'Chicago, IL'
        WHEN 'InnovaTech Systems' THEN 'Los Angeles, CA'
        WHEN 'GlobalSoft Technologies' THEN 'Miami, FL'
        WHEN 'FutureTech Innovations' THEN 'Denver, CO'
        WHEN 'WebCraft Studios' THEN 'Portland, OR'
    END as location,
    70000 as salary_min,
    130000 as salary_max,
    2 as experience_min,
    6 as experience_max,
    TRUE as isActive,
    TRUE as isVerifiedJob
FROM Company c;

-- Insert dummy job seekers
INSERT INTO Users (id, name, email, password, role) VALUES
(UUID(), 'Alice Smith', 'alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'USER'),
(UUID(), 'Bob Johnson', 'bob@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'USER'),
(UUID(), 'Carol Wilson', 'carol@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'USER'),
(UUID(), 'David Brown', 'david@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'USER');