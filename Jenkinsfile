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

        stage('Drop Database') {
            steps {
                script {
                    // Wait for MariaDB container to initialize
                    sh 'sleep 10'

                    // Drop the existing database if it exists
                    sh 'docker-compose exec mariadb mariadb -u root -e "DROP DATABASE IF EXISTS csit314;"'
                }
            }
        }

        stage('Create Database and Run TestData.sql') {
            steps {
                script {
                     // Set max_allowed_packet to 500M for MariaDB
                    sh 'docker-compose exec mariadb mariadb -u root -e "SET GLOBAL max_allowed_packet=524288000;"'
                    // Create the database and run initial SQL script
                    sh 'docker-compose exec mariadb mariadb -u root -e "CREATE DATABASE IF NOT EXISTS csit314;"'
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
