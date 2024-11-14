pipeline {
    agent any

    stages {
	stage('Clean Up Existing Containers') {
            steps {
                script {
                    // Stop and remove any containers using ports 3307 and 8081
                    sh '''
                    docker ps -q --filter "publish=3307" | xargs -r docker stop
                    docker ps -a -q --filter "publish=3307" | xargs -r docker rm
                    docker ps -q --filter "publish=8081" | xargs -r docker stop
                    docker ps -a -q --filter "publish=8081" | xargs -r docker rm
                    '''
                }
            }
        }
        stage('Build and Start Containers') {
            steps {
                script {
                    // Stop any existing containers and rebuild the services
                    sh 'docker-compose down || true'
                    sh 'docker-compose up -d --build'
                }
            }
        }

        stage('Run TestData.sql') {
            steps {
                script {
                    // Wait for MariaDB container to initialize
                    sh 'sleep 20'

                    // Run SQL script in the MariaDB container
                    sh 'docker-compose exec mariadb mariadb -u root csit314 < testdata/TestData.sql'
                }
            }
        }

        stage('Run profileTestData.php') {
            steps {
                script {
                    // Run profileTestData.php inside the PHP container
                    sh 'docker-compose exec phpapp php testdata/profileTestData.php'
                }
            }
        }

        stage('Run reviewTestData.php') {
            steps {
                script {
                    // Run reviewTestData.php inside the PHP container
                    sh 'docker-compose exec phpapp php testdata/reviewTestData.php'
                }
            }
        }

        stage('Run listingTestData.php') {
            steps {
                script {
                    // Run listingTestData.php inside the PHP container
                    sh 'docker-compose exec phpapp php testdata/listingTestData.php'
                }
            }
        }

        stage('Run ownershipTestData.php') {
            steps {
                script {
                    // Run ownershipTestData.php inside the PHP container
                    sh 'docker-compose exec phpapp php testdata/ownershipTestData.php'
                }
            }
        }

        stage('Run shortlistTestData.php') {
            steps {
                script {
                    // Run shortlistTestData.php inside the PHP container
                    sh 'docker-compose exec phpapp php testdata/shortlistTestData.php'
                }
            }
        }
    }
}
