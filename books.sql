CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    description TEXT,
    publication_year INT,
    genre VARCHAR(100),
    isbn VARCHAR(20),
    cover_image VARCHAR(255),
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

select * from rest_api.books;

UPDATE rest_api.books
SET cover_image = '/../../public/uploads/covers/6840f52766cd6_4f309d1751e7c389044fe037adcbc049.jpg'
WHERE id = 1;

CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    UNIQUE INDEX idx_category_slug (slug)
);

-- First, check existing indexes
SHOW INDEXES FROM categories;

-- If needed, drop the duplicate index
DROP INDEX idx_category_slug ON categories;

-- Ensure we have the correct unique constraint
ALTER TABLE categories ADD UNIQUE KEY `slug` (slug);

CREATE TABLE book_categories (
    book_id INT,
    category_id INT,
    PRIMARY KEY (book_id, category_id),
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

ALTER TABLE categories 
MODIFY COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;

select * from rest_api.book_categories;
select * from rest_api.categories;
select * from rest_api.users;