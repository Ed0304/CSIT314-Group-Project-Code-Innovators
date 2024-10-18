CREATE DATABASE csit314

CREATE TABLE Admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

INSERT INTO Admin(username,password) VALUES("John Doe","abc123");

--Do not execute these two statements first! We are still thinking how the databased design look like!
CREATE TABLE User(
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT FOREIGN KEY
);

CREATE TABLE Role(
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR()
)




