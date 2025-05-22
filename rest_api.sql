CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL
);

select * from rest_api.users;

alter table users 
ADD token TEXT DEFAULT NULL;

alter table users 
ADD created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

alter table users 
add password varchar(255) NOT NULL;

ALTER TABLE users
MODIFY COLUMN name VARCHAR(100) NOT NULL
AFTER id;

ALTER TABLE users
MODIFY COLUMN password varchar(255) NOT NULL
AFTER email;

lucabahogutot 
