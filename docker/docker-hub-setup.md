# Docker Hub Integration Setup Guide

This guide explains how to set up Docker Hub integration with Jenkins for automatically pushing Docker images.

## 📋 Prerequisites

1. **Docker Hub Account**: Create an account at [hub.docker.com](https://hub.docker.com)
2. **Jenkins with Docker Plugin**: Ensure Jenkins has Docker capabilities
3. **Docker Hub Repository**: Create a repository for your project

## 🔧 Docker Hub Setup

### 1. Create Docker Hub Account

1. Go to [hub.docker.com](https://hub.docker.com)
2. Click "Sign Up" and create an account
3. Verify your email address

### 2. Create Docker Hub Repository

1. Login to Docker Hub
2. Click "Create Repository"
3. Repository name: `job-portal` (or your preferred name)
4. Description: "Job Portal PHP Application"
5. Set visibility (Public or Private)
6. Click "Create"

### 3. Generate Access Token

1. Go to Docker Hub → Account Settings → Security
2. Click "New Access Token"
3. Token name: `jenkins-job-portal`
4. Set expiration (recommend 1 year)
5. Copy the generated token (you'll need it for Jenkins)

## 🚀 Jenkins Configuration

### 1. Install Required Jenkins Plugins

Go to Jenkins → Manage Jenkins → Manage Plugins and install:

- **Docker Pipeline**
- **Credentials Binding**
- **Pipeline Utility Steps**

### 2. Add Docker Hub Credentials

1. Go to Jenkins → Manage Jenkins → Manage Credentials
2. Click "System" → "Global credentials" → "Add Credentials"
3. Select "Username with password"
4. Configure:
   - **Kind**: Username with password
   - **Scope**: Global
   - **Username**: Your Docker Hub username
   - **Password**: Your Docker Hub access token (not your account password)
   - **ID**: `docker-hub-credentials`
   - **Description**: "Docker Hub credentials for job portal"
5. Click "Create"

### 3. Update Jenkinsfile Configuration

Edit the `Jenkinsfile` and update the Docker Hub repository name:

```groovy
environment {
    DOCKER_HUB_REPO = 'your-dockerhub-username/job-portal'
}
```

Replace `your-dockerhub-username` with your actual Docker Hub username.

## 🔄 Pipeline Stages

The updated Jenkins pipeline includes these new stages:

### Tag for Docker Hub
- Only runs on `main` branch
- Tags the built image for Docker Hub
- Creates both versioned and latest tags

### Push to Docker Hub
- Only runs on `main` branch
- Logs into Docker Hub using stored credentials
- Pushes both versioned and latest images

### Deploy from Docker Hub
- Placeholder for deployment logic
- Can be customized for your deployment needs

## 🧪 Testing Docker Hub Integration

### Manual Testing

```bash
# Test Docker Hub login
docker login -u your-username

# Test image push (after building)
docker tag job-portal-dev:latest your-username/job-portal:test
docker push your-username/job-portal:test

# Test image pull
docker pull your-username/job-portal:test
```

### Jenkins Pipeline Testing

1. **Create a test branch**:
   ```bash
   git checkout -b test-docker-hub
   ```

2. **Update Jenkinsfile** for testing:
   ```groovy
   DOCKER_HUB_REPO = 'your-username/job-portal-test'
   ```

3. **Push and trigger pipeline**:
   ```bash
   git add .
   git commit -m "Test Docker Hub integration"
   git push origin test-docker-hub
   ```

## 📊 Monitoring and Verification

### Check Pushed Images

1. **Docker Hub Web Interface**:
   - Go to your repository on hub.docker.com
   - Check "Tags" tab for pushed images

2. **Docker CLI**:
   ```bash
   # List local images
   docker images | grep your-username/job-portal
   
   # Pull and test image
   docker pull your-username/job-portal:latest
   docker run -p 8080:80 your-username/job-portal:latest
   ```

### Jenkins Build Logs

Check Jenkins build logs for:
- Docker login success/failure
- Image push success/failure
- Tag creation confirmation

## 🔒 Security Best Practices

### 1. Use Access Tokens
- Never use your Docker Hub account password
- Generate and use access tokens with limited scope
- Rotate tokens regularly

### 2. Repository Visibility
- Use private repositories for sensitive projects
- Public repositories are visible to everyone

### 3. Image Security
- Scan images for vulnerabilities
- Use minimal base images
- Keep images updated

### 4. Credential Management
- Store credentials securely in Jenkins
- Use credential IDs in pipelines
- Never hardcode credentials

## 🚨 Troubleshooting

### Common Issues

1. **Authentication Failed**
   ```
   Error: unauthorized: authentication required
   ```
   - Check Docker Hub credentials in Jenkins
   - Verify access token is valid
   - Ensure username is correct

2. **Repository Not Found**
   ```
   Error: repository not found
   ```
   - Verify repository name in Jenkinsfile
   - Check repository exists on Docker Hub
   - Ensure repository visibility settings

3. **Permission Denied**
   ```
   Error: denied: requested access to the resource is denied
   ```
   - Check repository permissions
   - Verify access token has push permissions
   - Ensure repository name matches exactly

### Debug Commands

```bash
# Test Docker Hub connection
docker login -u your-username

# Check local images
docker images

# Test image push manually
docker push your-username/job-portal:test

# Check Jenkins credentials
# Go to Jenkins → Manage Jenkins → Manage Credentials
```

## 📈 Advanced Configuration

### Multi-Architecture Builds

For supporting multiple architectures:

```groovy
stage('Build Multi-Arch Image') {
    steps {
        script {
            sh '''
                docker buildx create --use
                docker buildx build --platform linux/amd64,linux/arm64 \
                    -t ${DOCKER_HUB_REPO}:${DOCKER_TAG} \
                    -t ${DOCKER_HUB_REPO}:latest \
                    --push .
            '''
        }
    }
}
```

### Automated Tagging

For semantic versioning:

```groovy
environment {
    VERSION = sh(script: 'git describe --tags --abbrev=0', returnStdout: true).trim()
    DOCKER_HUB_REPO = 'your-username/job-portal'
}
```

### Conditional Pushing

Push only on specific conditions:

```groovy
stage('Push to Docker Hub') {
    when {
        allOf {
            branch 'main'
            not { changeRequest() }
        }
    }
    steps {
        // Push logic here
    }
}
```

## 🎓 College Project Integration

### For Presentation

1. **Show Docker Hub Repository**: Display your repository on hub.docker.com
2. **Demonstrate Pipeline**: Run Jenkins pipeline and show image push
3. **Verify Deployment**: Pull and run image from Docker Hub
4. **Explain Benefits**: Discuss containerization and CI/CD advantages

### Project Features Demonstrated

- **Container Registry**: Docker Hub for image storage
- **Automated Deployment**: Jenkins pipeline with Docker Hub integration
- **Version Management**: Tagged images for different versions
- **CI/CD Pipeline**: Complete automation from code to deployment

---

**Note**: Remember to replace `your-dockerhub-username` with your actual Docker Hub username throughout the configuration.
