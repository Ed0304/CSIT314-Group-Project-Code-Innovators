CREATE DATABASE csit314;

CREATE TABLE status (
    status_id INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(256)
);

CREATE TABLE role (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(256),
    role_description VARCHAR(256)
);

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(256) NOT NULL UNIQUE,
    password VARCHAR(256) NOT NULL,
    role_id INT,
    email VARCHAR(256) NOT NULL UNIQUE,
    phone_num VARCHAR(256) NOT NULL UNIQUE,
    status_id INT,
    FOREIGN KEY (role_id) REFERENCES role(role_id),
    FOREIGN KEY (status_id) REFERENCES status(status_id)
);

CREATE TABLE profile (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(256) NOT NULL,
    last_name VARCHAR(256) NOT NULL,
    about VARCHAR(256) NOT NULL,
    gender VARCHAR(16) NOT NULL,
    profile_image LONGBLOB,
    status_id INT,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (status_id) REFERENCES status(status_id)
);


CREATE TABLE listing(
    listing_id INT AUTO_INCREMENT PRIMARY KEY,
    manufacturer_name VARCHAR(256) NOT NULL,
    model_name VARCHAR(256) NOT NULL,
    model_year INT NOT NULL,
    listing_image LONGBLOB,
    listing_color VARCHAR(256) NOT NULL,
    listing_price DOUBLE,
    listing_description VARCHAR(256) NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE review (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    review_details VARCHAR(256),
    review_stars INT,
    reviewer_id INT,
    agent_id INT,
    FOREIGN KEY (reviewer_id) REFERENCES users(user_id),
    FOREIGN KEY (agent_id) REFERENCES users(user_id),
    review_date DATE DEFAULT CURRENT_DATE
);

CREATE TABLE shortlist(
    shortlist_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    listing_id INT NOT NULL,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id),
    FOREIGN KEY (listing_id) REFERENCES listing(listing_id)
);

INSERT INTO status(status_name) VALUES("Active");
INSERT INTO status(status_name) VALUES("Suspended");

INSERT INTO role(role_id, role_name,role_description) VALUES(1,"user admin", "super admin");
INSERT INTO role(role_id, role_name,role_description) VALUES(2,"used car agent", "used car agent can create listing and view all listing");
INSERT INTO role(role_id, role_name,role_description) VALUES(3,"buyer", "buyer can view listing and review listing");
INSERT INTO role(role_id, role_name,role_description) VALUES(4,"seller", "seller can create listing and view all listing");

INSERT INTO users(username, password, role_id, email, phone_num,status_id) VALUES("John Doe", "abc123", 1,"john@exampl3.com","+6581234567",1);
INSERT INTO users(username, password, role_id, email, phone_num,status_id) VALUES("Alice456", "h3ll0!", 2, "Alice@exampl3.com", "+6591234567",1);
INSERT INTO users(username, password, role_id, email, phone_num,status_id) VALUES("TakFujiwara", "initialD", 3, "Tak@TouWenziD.com", "+811234567890",1);
INSERT INTO users(username, password, role_id, email, phone_num,status_id) VALUES("BuntaFujiwara", "initialD", 4, "Bun@TouWenziD.com", "+811234567899",1);
INSERT INTO users(username, password, role_id, email, phone_num,status_id) VALUES("MuzanKibutsuji", "demonSlayer", 4, "kibutsuji@kny.com", "+816666666666",1);

