CREATE DATABASE IF NOT EXISTS skillbridge
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE skillbridge;

CREATE TABLE IF NOT EXISTS students (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    course VARCHAR(100) DEFAULT NULL,
    year_of_study VARCHAR(12) DEFAULT NULL,
    bio VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_students_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS skills (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    skill_type ENUM('teach', 'learn') NOT NULL,
    name VARCHAR(80) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_student_skill (student_id, skill_type, name),
    KEY idx_skills_type_name (skill_type, name),
    CONSTRAINT fk_skills_student
        FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exchange_requests (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    sender_id INT UNSIGNED NOT NULL,
    receiver_id INT UNSIGNED NOT NULL,
    offered_skill_id INT UNSIGNED NOT NULL,
    wanted_skill_id INT UNSIGNED NOT NULL,
    message VARCHAR(300) DEFAULT NULL,
    status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_requests_sender (sender_id, status),
    KEY idx_requests_receiver (receiver_id, status),
    CONSTRAINT fk_requests_sender
        FOREIGN KEY (sender_id) REFERENCES students (id) ON DELETE CASCADE,
    CONSTRAINT fk_requests_receiver
        FOREIGN KEY (receiver_id) REFERENCES students (id) ON DELETE CASCADE,
    CONSTRAINT fk_requests_offered_skill
        FOREIGN KEY (offered_skill_id) REFERENCES skills (id) ON DELETE CASCADE,
    CONSTRAINT fk_requests_wanted_skill
        FOREIGN KEY (wanted_skill_id) REFERENCES skills (id) ON DELETE CASCADE
) ENGINE=InnoDB;
