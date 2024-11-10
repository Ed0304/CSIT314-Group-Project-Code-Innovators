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


CREATE TABLE listing (
    listing_id INT AUTO_INCREMENT PRIMARY KEY,
    manufacturer_name VARCHAR(256) NOT NULL,
    model_name VARCHAR(256) NOT NULL,
    model_year INT NOT NULL,
    listing_image LONGBLOB,
    listing_color VARCHAR(256) NOT NULL,
    listing_price DOUBLE,
    listing_description VARCHAR(256) NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    views INT DEFAULT 0,
    CONSTRAINT views CHECK (views >= 0),
    shortlisted INT DEFAULT 0,
    CONSTRAINT shortlisted CHECK (shortlisted >= 0)
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
    shortlist_date DATE DEFAULT CURRENT_DATE,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id),
    FOREIGN KEY (listing_id) REFERENCES listing(listing_id)
);

CREATE TABLE ownership(
    ownership_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    listing_id INT NOT NULL,
    ownership_date DATE DEFAULT CURRENT_DATE,
    FOREIGN KEY (seller_id) REFERENCES users(user_id),
    FOREIGN KEY (listing_id) REFERENCES listing(listing_id)
);

INSERT INTO status(status_name) VALUES("Active");
INSERT INTO status(status_name) VALUES("Suspended");

INSERT INTO role(role_id, role_name,role_description) VALUES(1,"user admin", "super admin");
INSERT INTO role(role_id, role_name,role_description) VALUES(2,"used car agent", "used car agent can create listing and view all listing");
INSERT INTO role(role_id, role_name,role_description) VALUES(3,"buyer", "buyer can view listing and review listing");
INSERT INTO role(role_id, role_name,role_description) VALUES(4,"seller", "seller can create listing and view all listing");

INSERT INTO users(username, password, role_id, email, phone_num,status_id) VALUES
("John Doe", "abc123", 1,"john@exampl3.com","+6581234567",1),
("Alice456", "h3ll0!", 2, "Alice@exampl3.com", "+6591234567",1),
("TakFujiwara", "initialD", 3, "Tak@TouWenziD.com", "+811234567890",1),
("BuntaFujiwara", "initialD", 4, "Bun@TouWenziD.com", "+811234567899",1),
('user1', 'pass1', 1, 'user1@example.com', '1234567890', 1),
('user2', 'pass2', 2, 'user2@example.com', '2345678901', 1),
('user3', 'pass3', 3, 'user3@example.com', '3456789012', 1),
('user4', 'pass4', 4, 'user4@example.com', '4567890123', 1),
('user5', 'pass5', 1, 'user5@example.com', '5678901234', 1),
('user6', 'pass6', 2, 'user6@example.com', '6789012345', 1),
('user7', 'pass7', 3, 'user7@example.com', '7890123456', 1),
('user8', 'pass8', 4, 'user8@example.com', '8901234567', 1),
('user9', 'pass9', 1, 'user9@example.com', '9012345678', 1),
('user10', 'pass10', 2, 'user10@example.com', '1234567891', 1),
('user11', 'pass11', 3, 'user11@example.com', '2345678902', 1),
('user12', 'pass12', 4, 'user12@example.com', '3456789013', 1),
('user13', 'pass13', 1, 'user13@example.com', '4567890124', 1),
('user14', 'pass14', 2, 'user14@example.com', '5678901235', 1),
('user15', 'pass15', 3, 'user15@example.com', '6789012346', 1),
('user16', 'pass16', 4, 'user16@example.com', '7890123457', 1),
('user17', 'pass17', 1, 'user17@example.com', '8901234568', 1),
('user18', 'pass18', 2, 'user18@example.com', '9012345679', 1),
('user19', 'pass19', 3, 'user19@example.com', '1234567892', 1),
('user20', 'pass20', 4, 'user20@example.com', '2345678903', 1),
('user21', 'pass21', 1, 'user21@example.com', '3456789014', 1),
('user22', 'pass22', 2, 'user22@example.com', '4567890125', 1),
('user23', 'pass23', 3, 'user23@example.com', '5678901236', 1),
('user24', 'pass24', 4, 'user24@example.com', '6789012347', 1),
('user25', 'pass25', 1, 'user25@example.com', '7890123458', 1),
('user26', 'pass26', 2, 'user26@example.com', '8901234569', 1),
('user27', 'pass27', 3, 'user27@example.com', '9012345670', 1),
('user28', 'pass28', 4, 'user28@example.com', '1234567893', 1),
('user29', 'pass29', 1, 'user29@example.com', '2345678904', 1),
('user30', 'pass30', 2, 'user30@example.com', '3456789015', 1),
('user31', 'pass31', 3, 'user31@example.com', '4567890126', 1),
('user32', 'pass32', 4, 'user32@example.com', '5678901237', 1),
('user33', 'pass33', 1, 'user33@example.com', '6789012348', 1),
('user34', 'pass34', 2, 'user34@example.com', '7890123459', 1),
('user35', 'pass35', 3, 'user35@example.com', '8901234560', 1),
('user36', 'pass36', 4, 'user36@example.com', '9012345671', 1),
('user37', 'pass37', 1, 'user37@example.com', '1234567894', 1),
('user38', 'pass38', 2, 'user38@example.com', '2345678905', 1),
('user39', 'pass39', 3, 'user39@example.com', '3456789016', 1),
('user40', 'pass40', 4, 'user40@example.com', '4567890127', 1),
('user41', 'pass41', 1, 'user41@example.com', '5678901238', 1),
('user42', 'pass42', 2, 'user42@example.com', '6789012349', 1),
('user43', 'pass43', 3, 'user43@example.com', '7890123450', 1),
('user44', 'pass44', 4, 'user44@example.com', '8901234561', 1),
('user45', 'pass45', 1, 'user45@example.com', '9012345672', 1),
('user46', 'pass46', 2, 'user46@example.com', '1234567895', 1),
('user47', 'pass47', 3, 'user47@example.com', '2345678906', 1),
('user48', 'pass48', 4, 'user48@example.com', '3456789017', 1),
('user49', 'pass49', 1, 'user49@example.com', '4567890128', 1),
('user50', 'pass50', 2, 'user50@example.com', '5678901239', 1),
('user51', 'pass51', 3, 'user51@example.com', '6789012340', 1),
('user52', 'pass52', 4, 'user52@example.com', '7890123451', 1),
('user53', 'pass53', 1, 'user53@example.com', '8901234562', 1),
('user54', 'pass54', 2, 'user54@example.com', '9012345673', 1),
('user55', 'pass55', 3, 'user55@example.com', '1234567896', 1),
('user56', 'pass56', 4, 'user56@example.com', '2345678907', 1),
('user57', 'pass57', 1, 'user57@example.com', '3456789018', 1),
('user58', 'pass58', 2, 'user58@example.com', '4567890129', 1),
('user59', 'pass59', 3, 'user59@example.com', '5678901230', 1),
('user60', 'pass60', 4, 'user60@example.com', '6789012341', 1),
('user61', 'pass61', 1, 'user61@example.com', '7890123452', 1),
('user62', 'pass62', 2, 'user62@example.com', '8901234563', 1),
('user63', 'pass63', 3, 'user63@example.com', '9012345674', 1),
('user64', 'pass64', 4, 'user64@example.com', '1234567897', 1),
('user65', 'pass65', 1, 'user65@example.com', '2345678908', 1),
('user66', 'pass66', 2, 'user66@example.com', '3456789019', 1),
('user67', 'pass67', 3, 'user67@example.com', '4567890130', 1),
('user68', 'pass68', 4, 'user68@example.com', '5678901240', 1),
('user69', 'pass69', 1, 'user69@example.com', '6789012342', 1),
('user70', 'pass70', 2, 'user70@example.com', '7890123453', 1),
('user71', 'pass71', 3, 'user71@example.com', '8901234564', 1),
('user72', 'pass72', 4, 'user72@example.com', '9012345675', 1),
('user73', 'pass73', 1, 'user73@example.com', '1234567898', 1),
('user74', 'pass74', 2, 'user74@example.com', '2345678909', 1),
('user75', 'pass75', 3, 'user75@example.com', '3456789020', 1),
('user76', 'pass76', 4, 'user76@example.com', '4567890131', 1),
('user77', 'pass77', 1, 'user77@example.com', '5678901241', 1),
('user78', 'pass78', 2, 'user78@example.com', '6789012343', 1),
('user79', 'pass79', 3, 'user79@example.com', '7890123454', 1),
('user80', 'pass80', 4, 'user80@example.com', '8901234565', 1),
('user81', 'pass81', 1, 'user81@example.com', '9012345676', 1),
('user82', 'pass82', 2, 'user82@example.com', '1234567899', 1),
('user83', 'pass83', 3, 'user83@example.com', '2345678910', 1),
('user84', 'pass84', 4, 'user84@example.com', '3456789021', 1),
('user85', 'pass85', 1, 'user85@example.com', '4567890132', 1),
('user86', 'pass86', 2, 'user86@example.com', '5678901242', 1),
('user87', 'pass87', 3, 'user87@example.com', '6789012344', 1),
('user88', 'pass88', 4, 'user88@example.com', '7890123455', 1),
('user89', 'pass89', 1, 'user89@example.com', '8901234566', 1),
('user90', 'pass90', 2, 'user90@example.com', '9012345677', 1),
('user91', 'pass91', 3, 'user91@example.com', '1234567800', 1),
('user92', 'pass92', 4, 'user92@example.com', '2345678911', 1),
('user93', 'pass93', 1, 'user93@example.com', '3456789022', 1),
('user94', 'pass94', 2, 'user94@example.com', '4567890133', 1),
('user95', 'pass95', 3, 'user95@example.com', '5678901243', 1),
("MuzanKibutsuji", "demonSlayer", 4, "kibutsuji@kny.com", "+816666666666",1);

