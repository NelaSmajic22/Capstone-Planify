-- Remove tables in reverse dependency order
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS flashcard_progress;
DROP TABLE IF EXISTS flashcards;
DROP TABLE IF EXISTS decks;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS users;

-- Database initialization settings
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Users table
CREATE TABLE users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Subjects table (belongs to users)
CREATE TABLE subjects (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    subject_name VARCHAR(100) NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (subject_name)
) ENGINE=InnoDB;

-- Decks table (belongs to subjects and users)
CREATE TABLE decks (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    deck_name VARCHAR(100) NOT NULL,
    subject_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Flashcards table (belongs to decks and users)
CREATE TABLE flashcards (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    deck_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_reviewed TIMESTAMP NULL,
    next_review TIMESTAMP NULL,
    FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Flashcard progress tracking
CREATE TABLE flashcard_progress (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    flashcard_id INT NOT NULL,
    deck_id INT NOT NULL,
    user_id INT NOT NULL,
    mastered TINYINT(1) DEFAULT 0,
    last_reviewed TIMESTAMP NULL,
    next_review TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flashcard_id) REFERENCES flashcards(id) ON DELETE CASCADE,
    FOREIGN KEY (deck_id) REFERENCES decks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tasks table (belongs to users)
CREATE TABLE tasks (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    task_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    due_date DATETIME NOT NULL,
    status ENUM('pending','completed') DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

COMMIT;
