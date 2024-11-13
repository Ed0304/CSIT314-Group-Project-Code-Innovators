pipeline {
    agent any

    environment {
        DB_HOST = 'localhost'
        DB_NAME = 'csit314'
        DB_USER = 'root'
        DB_PASS = ''
    }

    stages {
        stage('Checkout') {
            steps {
                // Clone the GitHub repository
                git 'https://github.com/Ed0304/CSIT314-Group-Project-Code-Innovators'
            }
        }

        stage('Build Docker Image') {
            steps {
                // Build the Docker image with a tag name, e.g., 'my-php-app'
                sh 'docker build -t my-php-app .'
            }
        }

        stage('Run Docker Container') {
            steps {
                // Run the Docker container with the correct port mapping and environment variables
                sh 'docker run -d -p 8081:80 --name my-php-container my-php-app'
            }
        }

        stage('Install PHP and MySQL Extension (Optional)') {
            steps {
                // If needed, you can install PHP directly on Jenkins agent for other tasks (e.g., running scripts)
                sh 'sudo apt-get update'
                sh 'sudo apt-get install -y php php-mysql'
            }
        }

        stage('Run TestData.sql') {
            steps {
                // Execute the SQL file located in the testdata folder
                sh "docker exec my-php-container mysql -h $DB_HOST -u $DB_USER $DB_NAME < /var/www/html/testdata/TestData.sql"
            }
        }

        stage('Run profileTestData.php') {
            steps {
                // Run the PHP script for profile data insertion within the Docker container
                sh 'docker exec my-php-container php /var/www/html/testdata/profileTestData.php'
            }
        }

        stage('Run reviewTestData.php') {
            steps {
                // Run the PHP script for review data insertion within the Docker container
                sh 'docker exec my-php-container php /var/www/html/testdata/reviewTestData.php'
            }
        }

        stage('Run listingTestData.php') {
            steps {
                // Run the PHP script for listing data insertion within the Docker container
                sh 'docker exec my-php-container php /var/www/html/testdata/listingTestData.php'
            }
        }

        stage('Run ownershipTestData.php') {
            steps {
                // Run the PHP script for ownership data insertion within the Docker container
                sh 'docker exec my-php-container php /var/www/html/testdata/ownershipTestData.php'
            }
        }

        stage('Run shortlistTestData.php') {
            steps {
                // Run the PHP script for shortlist data insertion within the Docker container
                sh 'docker exec my-php-container php /var/www/html/testdata/shortlistTestData.php'
            }
        }
    }

    post {
        success {
            echo 'Data insertion and deployment completed successfully!'
        }
        failure {
            echo 'Data insertion or deployment failed.'
        }
    }
}
