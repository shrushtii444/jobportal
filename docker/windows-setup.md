# Windows Setup Guide - Job Portal Project

This guide provides Windows-specific instructions for running the Job Portal project with Docker.

## 🪟 Windows Requirements

### System Requirements
- **Windows 10/11 Pro, Enterprise, or Education** (64-bit)
- **Windows 10/11 Home**: Requires WSL2 setup
- **4GB RAM minimum** (8GB recommended)
- **Virtualization enabled** in BIOS (Intel VT-x or AMD-V)

### Docker Desktop Installation
1. Download Docker Desktop from: https://www.docker.com/products/docker-desktop
2. Run the installer as Administrator
3. Restart your computer
4. Start Docker Desktop

## 🔧 Windows-Specific Configuration

### Volume Mounting Configuration

The project uses Windows-optimized volume mounting for better performance and compatibility:

```yaml
volumes:
  - type: bind
    source: .
    target: /var/www/html
    consistency: cached
  - type: bind
    source: ./docker/apache.conf
    target: /etc/apache2/sites-available/000-default.conf
    read_only: true
```

## 🚀 Running on Windows

### Method 1: Command Prompt
```cmd
# Navigate to project directory
cd C:\path\to\Job-Portal

# Start the application
docker-compose up --build
```

### Method 2: PowerShell
```powershell
# Same commands work in PowerShell
docker-compose up --build
```

## 🔍 Windows Troubleshooting

### Volume Mounting Issues

#### **Problem**: Files not syncing between host and container
**Solution**: Ensure Docker Desktop is running and project directory is shared
```cmd
docker-compose up --build
```

#### **Problem**: Permission denied errors
**Solution**: Run as Administrator
```cmd
# Right-click Command Prompt → Run as Administrator
docker-compose up --build
```

#### **Problem**: File path issues
**Solution**: Use forward slashes in docker-compose files (already configured)

### Performance Issues

#### **Problem**: Slow file operations
**Solution**: Enable WSL2 backend in Docker Desktop
1. Open Docker Desktop
2. Go to Settings → General
3. Check "Use the WSL 2 based engine"
4. Apply & Restart

#### **Problem**: High CPU usage
**Solution**: Exclude project directory from antivirus scanning

### Network Issues

#### **Problem**: Port already in use
```cmd
# Check what's using the port
netstat -ano | findstr :8080
netstat -ano | findstr :8081

# Kill the process
taskkill /PID <process_id> /F
```

#### **Problem**: Cannot access localhost
**Solution**: Check Windows Firewall
1. Open Windows Defender Firewall
2. Allow Docker Desktop through firewall

## 📁 Windows File Structure

Ensure your project structure is correct:
```
Job-Portal/
├── docker-compose.yml          # Windows-optimized
├── Dockerfile
├── docker/
│   ├── apache.conf
│   └── windows-setup.md
├── uploads/                    # File uploads directory
├── db/
│   └── db.sql
└── ... (other project files)
```

## 🛠️ Windows Development Commands

### Basic Commands
```cmd
# Start services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down

# Rebuild after changes
docker-compose down
docker-compose up --build
```

### Windows-Specific Commands
```cmd
# Check Docker Desktop status
docker version

# View running containers
docker ps

# Start with rebuild
docker-compose up --build
```

### Container Access
```cmd
# Access PHP container
docker-compose exec app bash

# Access database
docker-compose exec db mysql -u jobportal -p job_board

# View container logs
docker-compose logs app
```

## 🔧 Windows Configuration Tips

### Docker Desktop Settings
1. **Resources**: Allocate more memory (4GB+) for better performance
2. **File Sharing**: Ensure your project drive is shared
3. **WSL2**: Enable for better performance on Windows 10/11 Home

### Antivirus Configuration
- **Exclude project directory** from real-time scanning
- **Allow Docker Desktop** through firewall
- **Disable real-time scanning** for uploads directory

### File Permissions
```cmd
# Fix upload directory permissions
icacls uploads /grant Everyone:F /T

# Or use PowerShell
Set-Acl -Path "uploads" -AclObject (New-Object System.Security.AccessControl.DirectorySecurity)
```

## 🌐 Accessing the Application

Once running, access via:
- **Job Portal**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
  - Username: `jobportal`
  - Password: `jobportal123`

## ✅ Windows Compatibility Checklist

- [ ] Docker Desktop installed and running
- [ ] Virtualization enabled in BIOS
- [ ] WSL2 installed (Windows Home users)
- [ ] Project directory shared in Docker Desktop
- [ ] Antivirus configured to allow Docker
- [ ] Windows Firewall allows Docker
- [ ] Ports 8080 and 8081 available

## 🚨 Common Windows Issues & Solutions

### Issue 1: "Docker Desktop is starting..."
**Solution**: Wait for Docker Desktop to fully start (check system tray)

### Issue 2: "Hyper-V not available"
**Solution**: Enable virtualization in BIOS (Intel VT-x or AMD-V)

### Issue 3: "WSL2 not found"
**Solution**: Install WSL2 for Windows Home users
```cmd
wsl --set-default-version 2
```

### Issue 4: "Permission denied"
**Solution**: Run Command Prompt as Administrator

### Issue 5: "Port already in use"
**Solution**: Check for other services using the ports and kill them

## 🎯 Windows Development Workflow

1. **Start Docker Desktop**
2. **Open Command Prompt as Administrator**
3. **Navigate to project directory**
4. **Run**: `docker-compose up --build`
5. **Access application** at http://localhost:8080
6. **Make changes** - they'll reflect immediately
7. **View logs**: `docker-compose logs -f`
8. **Stop**: `docker-compose down`

## 📚 Additional Resources

- **Docker Desktop Documentation**: https://docs.docker.com/desktop/windows/
- **WSL2 Setup**: https://docs.microsoft.com/en-us/windows/wsl/install
- **Windows Terminal**: https://docs.microsoft.com/en-us/windows/terminal/

---

**Note**: This configuration is optimized for Windows development with Docker Desktop.
