CREATE DATABASE csit314;

CREATE TABLE role (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(256)
);

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(256) NOT NULL UNIQUE,
    password VARCHAR(256) NOT NULL,
    role_id INT,
    email VARCHAR(256) NOT NULL UNIQUE,
    phone_num VARCHAR(256) NOT NULL UNIQUE,
    FOREIGN KEY (role_id) REFERENCES role(role_id)
);

CREATE TABLE profile (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(256) NOT NULL,
    last_name VARCHAR(256) NOT NULL,
    about VARCHAR(256) NOT NULL,
    gender VARCHAR(16) NOT NULL,
    profile_image BLOB,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

INSERT INTO role(role_id, role_name) VALUES(1,"user admin");
INSERT INTO role(role_id, role_name) VALUES(2,"used car agent");
INSERT INTO role(role_id, role_name) VALUES(3,"buyer");
INSERT INTO role(role_id, role_name) VALUES(4,"seller");

INSERT INTO users(username, password, role_id, email, phone_num) VALUES("John Doe", "abc123", 1,"john@exampl3.com","+6581234567");
INSERT INTO users(username, password, role_id, email, phone_num) VALUES("Alice456", "h3ll0!", 2, "Alice@exampl3.com", "+6591234567");

INSERT INTO profile(user_id, first_name, last_name, about, gender) VALUES (1,"John","Doe", "I am the only user admin here", "M");
INSERT INTO profile(user_id, first_name, last_name, about, gender) VALUES (2,"Alice", "Tan", "Specializes in selling used Japanese Cars (Toyota, Honda, Nissan)" ,"F");