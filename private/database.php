<?php

$host = 'localhost';
$dbname = 'lunchorbit';
$dbusername = 'root';
$dbpassword = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $dbusername,$dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            fullname VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(254) UNIQUE NOT NULL,
            cellno VARCHAR(25) NULL,
            role ENUM('job_seeker', 'recruiter') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP  
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS recruiters (
            recruiter_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL UNIQUE,
            companyName VARCHAR(150) NOT NULL,
            companyWebsite VARCHAR(255) NOT NULL,
            companyNumber VARCHAR(25) NOT NULL,
            PositioninCompany VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (user_id)
                REFERENCES users(user_id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS jobseekers (
            jobseekers_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL UNIQUE,
            education_level ENUM(
                'junior_high_school',
                'senior_high_school',
                'vocational_certificate',
                'associate_degree',
                'bachelor_degree',
                'masters_degree',
                'doctoral_degree',
                'other'
            ) NULL,

            education_status ENUM(
                'currently_studying',
                'graduated',
                'not_completed'
            ) NULL,

            school_name VARCHAR(150) NULL,
            degree_course VARCHAR(150) NULL,
            graduation_year YEAR NULL,

            -- experience
            work_experience_level ENUM(
                'none',
                'internship_or_ojt_only',
                'under_2_years',
                '2_to_5_years',
                'over_5_years'
            ) NOT NULL DEFAULT 'none',

            -- taga asa ka dol
            city VARCHAR(100) NULL,
            province VARCHAR(100) NULL,

            -- Profile
            profile_summary TEXT NULL,

            -- Job prefer shit
            preferred_work_setup ENUM(
                'onsite',
                'hybrid',
                'remote',
                'any'
            ) NOT NULL DEFAULT 'any',

            preferred_employment_type ENUM(
                'full_time',
                'part_time',
                'internship',
                'contract',
                'freelance',
                'any'
            ) NOT NULL DEFAULT 'any',

            -- Time stampsss
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (user_id)
            REFERENCES users(user_id)
            ON DELETE CASCADE
        )  ENGINE=InnoDB;
    ");



} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

