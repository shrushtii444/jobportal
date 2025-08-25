pipeline {
    agent any  // Use a standard Jenkins agent

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
                    // Install Node.js & npm if not already installed
                    sh '''
                        apk add --no-cache nodejs npm || true
                        if [ -f package.json ]; then
                            npm install
                        fi
                    '''
                }
            }
        }

        stage('Build CSS') {
            steps {
                script {
                    sh '''
                        if [ -f package.json ]; then
                            npm run dev
                        fi
                    '''
                }
            }
        }

        stage('Code Quality Check') {
            steps {
                script {
                    // Install PHP CLI if not already present
                    sh '''
                        apk add --no-cache php-cli || true
                        # PHP lint check
                        find . -name "*.php" -exec php -l {} \\;
                        
                        # Security checks
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
                sh "docker build -t ${DOCKER_IMAGE}:${DOCKER_TAG} ."
                sh "docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_IMAGE}:latest"
            }
        }

        stage('Test Application') {
            steps {
                script {
                    sh '''
                        # Install docker-compose if needed
                        apk add --no-cache py3-pip || true
                        pip3 install docker-compose || true

                        docker-compose up -d db
                        sleep 30
                        docker-compose up -d app
                        sleep 10

                        if curl -f http://localhost:8080/; then
                            echo "Application is running successfully"
                        else
                            echo "Application failed to start"
                            exit 1
                        fi

                        docker-compose down
                    '''
                }
            }
        }

        stage('Tag for Docker Hub') {
            when {
                branch 'main'
            }
            steps {
                sh "docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_HUB_REPO}:${DOCKER_TAG}"
                sh "docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_HUB_REPO}:${DOCKER_HUB_TAG}"
            }
        }

        stage('Push to Docker Hub') {
            when {
                branch 'main'
            }
            steps {
                withCredentials([usernamePassword(credentialsId: 'docker-hub-credentials', usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD')]) {
                    sh 'echo $DOCKER_PASSWORD | docker login -u $DOCKER_USERNAME --password-stdin'
                    sh "docker push ${DOCKER_HUB_REPO}:${DOCKER_TAG}"
                    sh "docker push ${DOCKER_HUB_REPO}:${DOCKER_HUB_TAG}"
                }
            }
        }

        stage('Deploy from Docker Hub') {
            when {
                branch 'main'
            }
            steps {
                echo "Deployment stage - add deployment logic here"
            }
        }
    }

    post {
        always {
            sh 'docker-compose down || true'
            sh 'docker system prune -f || true'
            sh 'docker logout || true'
        }
        success {
            echo 'Pipeline completed successfully!'
        }
        failure {
            echo 'Pipeline failed!'
        }
    }
}
