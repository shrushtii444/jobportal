# DevOps Setup Guide - Job Portal Project

This guide will help you set up the Job Portal project with Docker and Jenkins for development purposes.

## 📋 Prerequisites

Before you begin, make sure you have the following installed on your system:

- **Docker** (version 20.10 or higher)
- **Docker Compose** (version 2.0 or higher)
- **Git**
- **Jenkins** (optional, for CI/CD)


```

## 🚀 Quick Start

### 1. Clone the Repository

```bash
git clone <your-repository-url>
cd Job-Portal
```

### 2. Build and Run with Docker Compose

```bash
# Build and start all services
docker-compose up --build

# Or run in detached mode
docker-compose up -d --build
```

### 3. Access the Application

Once the containers are running, you can access:

- **Job Portal Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
  - Username: `jobportal`
  - Password: `jobportal123`

## 🐳 Docker Configuration

### Project Structure

```
Job-Portal/
├── Dockerfile                 # PHP application container
├── docker-compose.yml         # Multi-container setup
├── docker/
│   ├── apache.conf           # Apache configuration
│   ├── database.env          # Database environment variables
│   └── scripts/
│       └── start.sh          # Container startup script
└── .dockerignore             # Files to exclude from build
```

### Services Overview

1. **app** (PHP Application)
   - Port: 8080
   - Based on PHP 8.1 with Apache
   - Includes Tailwind CSS build process

2. **db** (MySQL Database)
   - Port: 3306
   - Database: `job_board`
   - Auto-initializes with `db/db.sql`

3. **phpmyadmin** (Database Management)
   - Port: 8081
   - Web interface for MySQL management

## 🔧 Development Workflow

### Starting Development

```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f app

# Access container shell
docker-compose exec app bash
```

### Making Changes

1. **PHP Files**: Changes are reflected immediately due to volume mounting
2. **CSS Changes**: Run `npm run dev` inside the container to rebuild Tailwind CSS
3. **Database Changes**: Use phpMyAdmin or connect directly to MySQL

### Rebuilding After Changes

```bash
# Rebuild and restart
docker-compose down
docker-compose up --build

# Or rebuild specific service
docker-compose build app
docker-compose up -d app
```

The Jenkins pipeline includes automatic Docker Hub image pushing:

1. **Setup Docker Hub Account**:
   - Create account at [hub.docker.com](https://hub.docker.com)
   - Create repository for your project
   - Generate access token for Jenkins

2. **Configure Jenkins Credentials**:
   - Go to Jenkins → Manage Jenkins → Manage Credentials
   - Add Docker Hub credentials with ID: `docker-hub-credentials`
   - Use your Docker Hub username and access token

3. **Update Jenkinsfile**:
   - Replace `your-dockerhub-username` with your actual Docker Hub username
   - Images will be pushed automatically on main branch

4. **Pipeline Stages**:
   - **Tag for Docker Hub**: Tags images for Docker Hub repository
   - **Push to Docker Hub**: Pushes images using stored credentials
   - **Deploy from Docker Hub**: Placeholder for deployment logic

For detailed Docker Hub setup instructions, see `docker/docker-hub-setup.md`.

## 🗄️ Database Management

### Using phpMyAdmin

1. Open http://localhost:8081
2. Login with:
   - Username: `jobportal`
   - Password: `jobportal123`
3. Select `job_board` database

