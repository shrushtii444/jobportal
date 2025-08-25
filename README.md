# Job Portal - Complete Job Board System

A comprehensive job portal built with PHP, MySQL, and modern web technologies. This system provides a complete solution for job seekers and companies to connect, with features for job posting, application management, and user administration.

## 🚀 Features

### For Job Seekers
- **User Registration & Profile Management**: Create accounts and manage personal profiles
- **Job Search & Discovery**: Browse jobs with advanced filtering (location, type, salary, work mode)
- **Job Applications**: Apply to jobs with cover letters and resume uploads
- **Application Tracking**: Monitor application status (Pending, Shortlisted, Rejected, Hired)
- **Resume Management**: Upload and manage resumes

### For Companies (HR)
- **Company Profile Management**: Create and manage company profiles with logos
- **Job Posting**: Create detailed job listings with requirements and salary information
- **Application Management**: Review and manage job applications
- **Candidate Evaluation**: Shortlist, reject, or hire candidates
- **Resume Viewing**: Access candidate resumes for evaluation

### For Administrators
- **User Management**: Monitor and manage all users in the system
- **System Overview**: Dashboard with statistics and system health
- **Content Moderation**: Manage jobs and user accounts

## 🏗️ System Architecture

### Technology Stack
- **Backend**: PHP 8.1 with Apache
- **Database**: MySQL 8.0
- **Frontend**: HTML5, CSS3, JavaScript, Tailwind CSS
- **Containerization**: Docker & Docker Compose
- **Database Management**: phpMyAdmin

### Database Schema
- **Users**: User accounts with roles (ADMIN, HR, USER)
- **Company**: Company profiles and information
- **Job**: Job listings with detailed requirements
- **Applications**: Job applications with status tracking
- **Experience**: User work experience
- **Education**: User educational background

## 🔄 User Flows

### Job Seeker Flow
1. **Registration**: Create account as a job seeker
2. **Profile Setup**: Complete profile with skills, experience, and education
3. **Job Search**: Browse available jobs with filters
4. **Application**: Apply to jobs with cover letter and resume
5. **Tracking**: Monitor application status and updates

### Company (HR) Flow
1. **Registration**: Create account as HR/Company representative
2. **Company Profile**: Set up company profile with logo and information
3. **Job Posting**: Create detailed job listings
4. **Application Review**: Review incoming applications
5. **Candidate Management**: Shortlist, interview, and hire candidates

### Admin Flow
1. **System Monitoring**: Overview of all users and activities
2. **User Management**: Monitor and manage user accounts
3. **Content Oversight**: Ensure system integrity and quality

## 🛠️ Installation & Setup

### Prerequisites
- Docker and Docker Compose
- Git

### Quick Start
```bash
# Clone the repository
git clone https://github.com/sid-methiya99/Job-Portal.git
cd Job-Portal

# Start the application
docker compose up --build -d

# Access the application
# Main App: http://localhost:8080
# phpMyAdmin: http://localhost:8081
```

### Default Credentials
- **Admin**: admin@100xjobs.com / password
- **HR Users**: hr1@techcorp.com / password
- **Job Seekers**: alice@example.com / password

## 📁 Project Structure

```
Job-Portal/
├── admin/                 # Admin panel files
│   ├── dashboard.php     # Admin dashboard
│   ├── users.php         # User management
│   └── jobs.php          # Job management
├── api/                  # API endpoints
│   └── jobs.php          # Jobs API
├── auth/                 # Authentication
│   ├── login.php         # Login page
│   ├── register.php      # Registration
│   └── logout.php        # Logout
├── classes/              # PHP classes
│   ├── User.php          # User management
│   ├── Job.php           # Job operations
│   └── Company.php       # Company operations
├── company/              # Company/HR panel
│   ├── dashboard.php     # HR dashboard
│   ├── profile.php       # Company profile
│   ├── post-job.php      # Job posting
│   ├── jobs.php          # Job management
│   └── view-applications.php # Application review
├── config/               # Configuration
│   └── database.php      # Database connection
├── db/                   # Database
│   └── db.sql           # Database schema
├── uploads/              # File uploads
│   ├── company_logos/    # Company logos
│   └── resumes/          # User resumes
├── docker/               # Docker configuration
├── Dockerfile            # Docker image
├── docker-compose.yml    # Docker services
└── README.md            # This file
```

## 🔧 Configuration

### Environment Variables
The system uses environment variables for database configuration:
- `DB_HOST`: Database host (default: db)
- `DB_NAME`: Database name (default: job_board)
- `DB_USER`: Database user (default: jobportal)
- `DB_PASSWORD`: Database password (default: jobportal123)

### File Permissions
The system automatically sets up proper permissions for:
- Upload directories (company logos, resumes)
- Application files
- Database access

## 🎯 Key Features Explained

### Job Search & Filtering
- **Semantic Search**: Search by job title, description, or location
- **Advanced Filters**: Filter by job type, work mode, salary range
- **Pagination**: Efficient browsing with paginated results
- **Real-time Updates**: Jobs appear immediately after posting

### Application Management
- **Status Tracking**: Applications progress through states (Pending → Shortlisted → Hired/Rejected)
- **Communication**: Cover letters and resume attachments
- **Timeline**: Track application history and updates

### Company Verification
- **Automatic Verification**: Companies are verified upon profile creation
- **Trust Indicators**: Verified badges for companies and jobs
- **Quality Assurance**: Admin oversight for system integrity

### File Management
- **Secure Uploads**: File validation and secure storage
- **Image Optimization**: Company logos with fallback placeholders
- **Resume Support**: Multiple document formats supported
