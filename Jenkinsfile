pipeline {
    agent {
        docker {
            image 'node:20-bullseye' // Node.js + npm
            args '-v /var/run/docker.sock:/var/run/docker.sock'
        }
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build & Test') {
            steps {
                sh '''
                    npm install
                    npm run dev
                    php -l $(find . -name "*.php")
                    docker-compose up -d db app
                    sleep 15
                    curl -f http://localhost:8080/ || exit 1
                    docker-compose down
                '''
            }
        }
    }

    post {
        always {
            sh 'docker-compose down || true'
        }
    }
}
