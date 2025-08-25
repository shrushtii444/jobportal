pipeline {
    agent any
    
    environment {
        DOCKER_IMAGE = 'job-portal-dev'
        DOCKER_TAG = "${env.BUILD_NUMBER}"
        DOCKER_HUB_REPO = 'your-dockerhub-username/job-portal'
        DOCKER_HUB_TAG = 'latest'
    }
    
    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }
        
        stage('Install Dependencies') {
            steps {
                script {
                    if (fileExists('package.json')) {
                        sh 'npm install'
                    }
                }
            }
        }
        
        stage('Build CSS') {
            steps {
                script {
                    if (fileExists('package.json')) {
                        sh 'npm run dev'
                    }
                }
            }
        }
        
        stage('Code Quality Check') {
            steps {
                script {
                    // Check for PHP syntax errors
                    sh 'find . -name "*.php" -exec php -l {} \;'
                    
                    // Check for common security issues
                    sh '''
                        echo "Checking for common security issues..."
                        if grep -r "mysql_query" . --include="*.php"; then
                            echo "WARNING: Found mysql_query usage (deprecated)"
                        fi
                        if grep -r "password.*=.*['\"]" . --include="*.php"; then
                            echo "WARNING: Found hardcoded passwords"
                        fi
                    '''
                }
            }
        }
        
        stage('Build Docker Image') {
            steps {
                script {
                    sh "docker build -t ${DOCKER_IMAGE}:${DOCKER_TAG} ."
                    sh "docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_IMAGE}:latest"
                }
            }
        }
        
        stage('Test Application') {
            steps {
                script {
                    // Start the application with docker-compose for testing
                    sh 'docker-compose up -d db'
                    sh 'sleep 30' // Wait for database to be ready
                    sh 'docker-compose up -d app'
                    sh 'sleep 10' // Wait for application to start
                    
                    // Basic health check
                    sh '''
                        if curl -f http://localhost:8080/; then
                            echo "Application is running successfully"
                        else
                            echo "Application failed to start"
                            exit 1
                        fi
                    '''
                    
                    // Cleanup test containers
                    sh 'docker-compose down'
                }
            }
        }
        
        stage('Tag for Docker Hub') {
            when {
                branch 'main'
            }
            steps {
                script {
                    // Tag image for Docker Hub
                    sh "docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_HUB_REPO}:${DOCKER_TAG}"
                    sh "docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_HUB_REPO}:${DOCKER_HUB_TAG}"
                    
                    echo "Images tagged for Docker Hub:"
                    sh "docker images | grep ${DOCKER_HUB_REPO}"
                }
            }
        }
        
        stage('Push to Docker Hub') {
            when {
                branch 'main'
            }
            steps {
                script {
                    // Login to Docker Hub using credentials from Jenkins
                    withCredentials([usernamePassword(credentialsId: 'docker-hub-credentials', usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD')]) {
                        sh 'echo $DOCKER_PASSWORD | docker login -u $DOCKER_USERNAME --password-stdin'
                        
                        // Push images to Docker Hub
                        sh "docker push ${DOCKER_HUB_REPO}:${DOCKER_TAG}"
                        sh "docker push ${DOCKER_HUB_REPO}:${DOCKER_HUB_TAG}"
                        
                        echo "Successfully pushed images to Docker Hub"
                    }
                }
            }
        }
        
        stage('Deploy from Docker Hub') {
            when {
                branch 'main'
            }
            steps {
                script {
                    echo "Deployment stage - you can add deployment logic here"
                    echo "Example: Deploy to staging/production servers using the pushed image"
                    
                    // Example deployment commands (uncomment and modify as needed)
                    // sh "docker pull ${DOCKER_HUB_REPO}:${DOCKER_HUB_TAG}"
                    // sh "docker-compose -f docker-compose.prod.yml up -d"
                }
            }
        }
    }
    
    post {
        always {
            // Cleanup
            sh 'docker-compose down'
            sh 'docker system prune -f'
            
            // Logout from Docker Hub
            sh 'docker logout'
        }
        success {
            echo 'Pipeline completed successfully!'
            script {
                if (env.BRANCH_NAME == 'main') {
                    echo "Docker images pushed to Docker Hub:"
                    echo "${DOCKER_HUB_REPO}:${DOCKER_TAG}"
                    echo "${DOCKER_HUB_REPO}:${DOCKER_HUB_TAG}"
                }
            }
        }
        failure {
            echo 'Pipeline failed!'
        }
    }
}
