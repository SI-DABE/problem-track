SET foreign_key_checks = 0;

DROP TABLE IF EXISTS problem_user_reinforce;
DROP TABLE IF EXISTS problems;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(50) NOT NULL,
    encrypted_password VARCHAR(255) NOT NULL,
    avatar_name VARCHAR(65),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE problems (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    INDEX idx_problems_user_id (user_id),
    CONSTRAINT fk_problems_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE problem_user_reinforce (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    problem_id INT NOT NULL,
    INDEX idx_reinforce_user_id (user_id),
    INDEX idx_reinforce_problem_id (problem_id),
    UNIQUE KEY uq_user_problem (user_id, problem_id),
    CONSTRAINT fk_reinforce_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_reinforce_problem
        FOREIGN KEY (problem_id)
        REFERENCES problems(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

SET foreign_key_checks = 1;