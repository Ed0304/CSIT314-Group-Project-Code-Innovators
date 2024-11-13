pipeline {
    agent any

    stages {
        stage('Build Docker Image') {
            steps {
                script {
                    sh 'docker build -t my-php-app .'
                }
            }
        }
        stage('Run Docker Container') {
            steps {
                script {
                    sh 'docker rm -f my-php-container || true'
                    sh 'docker run -d -p 8081:80 --name my-php-container my-php-app'
                }
            }
        }
        stage('Install PHP and MariaDB') {
            steps {
                sh 'sudo yum update -y && sudo yum install -y php php-mysqlnd mariadb105-server mariadb105'
                sh 'sudo systemctl start mariadb'
                sh 'sudo systemctl enable mariadb'
            }
        }
        stage('Setup Database User') {
            steps {
                script {
                    sh 'sudo mysql -u root -e "GRANT ALL PRIVILEGES ON csit314.* TO \'root\'@\'localhost\' IDENTIFIED BY \'\'"'
                    sh 'sudo mysql -u root -e "FLUSH PRIVILEGES"'
                }
            }
        }
        stage('Run TestData.sql') {
            steps {
                script {
                    sh 'sudo mysql -u root < testdata/TestData.sql'
                }
            }
        }
        stage('Run profileTestData.php') {
            steps {
                script {
                    sh 'chmod +x testdata/profileTestData.php'
                    sh 'sudo php testdata/profileTestData.php'
                }
            }
        }
        stage('Run reviewTestData.php') {
            steps {
                script {
                    sh 'chmod +x testdata/reviewTestData.php'
                    sh 'sudo php testdata/reviewTestData.php'
                }
            }
        }
        stage('Run listingTestData.php') {
            steps {
                script {
                    sh 'chmod +x testdata/listingTestData.php'
                    sh 'sudo php testdata/listingTestData.php'
                }
            }
        }
        stage('Run ownershipTestData.php') {
            steps {
                script {
                    sh 'chmod +x testdata/ownershipTestData.php'
                    sh 'sudo php testdata/ownershipTestData.php'
                }
            }
        }
        stage('Run shortlistTestData.php') {
            steps {
                script {
                    sh 'chmod +x testdata/shortlistTestData.php'
                    sh 'sudo php testdata/shortlistTestData.php'
                }
            }
        }
    }
}
