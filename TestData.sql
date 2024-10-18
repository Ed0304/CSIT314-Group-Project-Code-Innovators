CREATE DATABASE csit314;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(256) NOT NULL UNIQUE,
    password VARCHAR(256) NOT NULL,
    role_id INT,
    FOREIGN KEY (role_id) REFERENCES role(role_id)
);

CREATE TABLE role (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(256)
);

CREATE TABLE profile (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(256) NOT NULL,
    last_name VARCHAR(256) NOT NULL,
    email VARCHAR(256) NOT NULL UNIQUE,
    phone_num VARCHAR(256) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

INSERT INTO role(role_id, role_name) VALUES(1,"admin");
INSERT INTO role(role_id, role_name) VALUES(2,"agent");
INSERT INTO role(role_id, role_name) VALUES(3,"buyer");
INSERT INTO role(role_id, role_name) VALUES(4,"seller");

INSERT INTO users(username, password, role_id) VALUES("John Doe", "abc123", 1);
INSERT INTO users(username, password, role_id) VALUES("Alice456", "h3ll0!", 2);
INSERT INTO users(username, password, role_id) VALUES("Kazama3", "m1shim@", 3);
INSERT INTO users(username, password, role_id) VALUES("Akazaza", "upper3", 4);

INSERT INTO profile(user_id, first_name, last_name, email, phone_num) VALUES (1,"John","Doe","john@exampl3.com","+6581234567")
