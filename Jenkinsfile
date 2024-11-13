pipeline {
    agent any

    stages {
        stage('Build Docker Image') {
            steps {
                script {
                    // Build the Docker image
                    sh 'docker build -t my-php-app .'
                }
            }
        }
        stage('Run Docker Container') {
            steps {
                script {
                    // Remove any existing container with the same name
                    sh 'docker rm -f my-php-container || true'

                    // Run the Docker container
                    sh 'docker run -d -p 8081:80 --name my-php-container my-php-app'
                }
            }
        }
        stage('Install PHP and MySQL Extension (Optional)') {
            steps {
                sh 'sudo yum update && sudo yum install -y php php-mysqlnd'
            }
        }
	stage('Install MySQL Server') {
  	    steps {
        	sh 'sudo yum install -y mysql-server'
        	sh 'sudo systemctl start mysqld'
        	sh 'sudo systemctl enable mysqld'
    	    }
	}

        stage('Run TestData.sql') {
            steps {
                script {
                    // Run the SQL data setup
                    sh 'mysql -u root < testdata/TestData.sql'
                }
            }
        }
        stage('Run profileTestData.php') {
            steps {
                script {
                    sh 'php testdata/profileTestData.php'
                }
            }
        }
        stage('Run reviewTestData.php') {
            steps {
                script {
                    sh 'php testdata/reviewTestData.php'
                }
            }
        }
        stage('Run listingTestData.php') {
            steps {
                script {
                    sh 'php testdata/listingTestData.php'
                }
            }
        }
        stage('Run ownershipTestData.php') {
            steps {
                script {
                    sh 'php testdata/ownershipTestData.php'
                }
            }
        }
        stage('Run shortlistTestData.php') {
            steps {
                script {
                    sh 'php testdata/shortlistTestData.php'
                }
            }
        }
    }
}
