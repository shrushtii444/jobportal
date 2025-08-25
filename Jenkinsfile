pipeline {
    agent any

    environment {
        DOCKER_IMAGE = 'job-portal-dev'
        DOCKER_TAG = "${env.BUILD_NUMBER}"
        DOCKER_HUB_REPO = 'your-dockerhub-username/job-portal'
        DOCKER_HUB_TAG = 'latest'
    }

    stages {
        stage('Checkout & Dependencies') {
            steps {
                checkout scm
                sh '''
                    apk add --no-cache nodejs npm php-cli py3-pip || true
                    pip3 install docker-compose || true
                    [ -f package.json ] && npm install
                '''
            }
        }

        stage('Build & Lint') {
            steps {
                sh '''
                    [ -f package.json ] && npm run dev
                    find . -name "*.php" -exec php -l {} \\;
                '''
            }
        }

        stage('Docker Build & Test') {
            steps {
                sh '''
                    docker build -t ${DOCKER_IMAGE}:${DOCKER_TAG} .
                    docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_IMAGE}:latest
                    docker-compose up -d db app
                    sleep 15
                    curl -f http://localhost:8080/ || exit 1
                    docker-compose down
                '''
            }
        }

        stage('Push to Docker Hub') {
            when { branch 'main' }
            steps {
                withCredentials([usernamePassword(credentialsId: 'docker-hub-credentials', usernameVariable: 'DOCKER_USERNAME', passwordVariable: 'DOCKER_PASSWORD')]) {
                    sh '''
                        echo $DOCKER_PASSWORD | docker login -u $DOCKER_USERNAME --password-stdin
                        docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_HUB_REPO}:${DOCKER_TAG}
                        docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_HUB_REPO}:${DOCKER_HUB_TAG}
                        docker push ${DOCKER_HUB_REPO}:${DOCKER_TAG}
                        docker push ${DOCKER_HUB_REPO}:${DOCKER_HUB_TAG}
                        docker logout
                    '''
                }
            }
        }
    }

    post {
        always {
            sh 'docker-compose down || true'
            sh 'docker system prune -f || true'
        }
    }
}
