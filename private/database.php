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

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS job_posts (
            job_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

            recruiter_id INT UNSIGNED NOT NULL,

            -- Job information
            job_title VARCHAR(150) NOT NULL,
            job_category VARCHAR(100) NULL,
            company_department VARCHAR(150) NULL,
            job_description TEXT NOT NULL,
            job_requirements TEXT NOT NULL,
            job_responsibilities TEXT NULL,

            -- Education requirements
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

            degree_course VARCHAR(150) NULL,

            -- Employment details
            employment_type ENUM(
                'full_time',
                'part_time',
                'internship',
                'contract',
                'freelance'
            ) NOT NULL,

            work_setup ENUM(
                'onsite',
                'hybrid',
                'remote'
            ) NOT NULL,

            -- Location
            city VARCHAR(100) NULL,
            province VARCHAR(100) NULL,

            -- Salary
            salary_min DECIMAL(10,2) NULL,
            salary_max DECIMAL(10,2) NULL,

            salary_period ENUM(
                'hourly',
                'daily',
                'monthly',
                'yearly'
            ) NULL,

            -- Experience requirement
            experience_level ENUM(
                'entry_level',
                'internship_or_ojt_only',
                'under_2_years',
                '2_to_5_years',
                'over_5_years',
                'any'
            ) NOT NULL DEFAULT 'any',

            vacancies INT UNSIGNED NOT NULL DEFAULT 1,

            -- Application information
            application_deadline DATE NULL,
            contact_email VARCHAR(254) NULL,
            additional_instructions TEXT NULL,

            -- Job status
            status ENUM(
                'draft',
                'published',
                'paused',
                'closed'
            ) NOT NULL DEFAULT 'draft',

            -- Timestamps
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,

            FOREIGN KEY (recruiter_id)
                REFERENCES recruiters(recruiter_id)
                ON DELETE CASCADE,

            INDEX idx_job_status (status),
            INDEX idx_job_recruiter (recruiter_id),
            INDEX idx_job_deadline (application_deadline)

        ) ENGINE=InnoDB;

    ");


    $pdo->exec("
        CREATE TABLE IF NOT EXISTS applications (
            application_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

            job_id INT UNSIGNED NOT NULL,
            jobseekers_id INT UNSIGNED NOT NULL,

            cover_letter TEXT NULL,
            resume_path VARCHAR(255) NULL,

            status ENUM(
                'pending',
                'reviewing',
                'shortlisted',
                'interview',
                'accepted',
                'rejected',
                'withdrawn'
            ) NOT NULL DEFAULT 'pending',

            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,

            FOREIGN KEY (job_id)
                REFERENCES job_posts(job_id)
                ON DELETE CASCADE,

            FOREIGN KEY (jobseekers_id)
                REFERENCES jobseekers(jobseekers_id)
                ON DELETE CASCADE,

            UNIQUE KEY unique_job_application (
                job_id,
                jobseekers_id
            ),

            INDEX idx_application_status (status),
            INDEX idx_application_jobseeker (jobseekers_id)

        ) ENGINE=InnoDB;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS saved_jobs (
            saved_job_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

            jobseekers_id INT UNSIGNED NOT NULL,
            job_id INT UNSIGNED NOT NULL,

            saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (jobseekers_id)
                REFERENCES jobseekers(jobseekers_id)
                ON DELETE CASCADE,

            FOREIGN KEY (job_id)
                REFERENCES job_posts(job_id)
                ON DELETE CASCADE,

            UNIQUE KEY unique_saved_job (
                jobseekers_id,
                job_id
            )

        ) ENGINE=InnoDB;
    ");

    // job-matching and recommendation system.

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS skills (
            skill_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            skill_name VARCHAR(100) NOT NULL UNIQUE
        ) ENGINE=InnoDB;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS job_skills (
            job_id INT UNSIGNED NOT NULL,
            skill_id INT UNSIGNED NOT NULL,

            PRIMARY KEY (job_id, skill_id),

            FOREIGN KEY (job_id)
                REFERENCES job_posts(job_id)
                ON DELETE CASCADE,

            FOREIGN KEY (skill_id)
                REFERENCES skills(skill_id)
                ON DELETE CASCADE

        ) ENGINE=InnoDB;
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS jobseeker_skills (
            jobseekers_id INT UNSIGNED NOT NULL,
            skill_id INT UNSIGNED NOT NULL,

            proficiency ENUM(
                'beginner',
                'intermediate',
                'advanced',
                'expert'
            ) NOT NULL DEFAULT 'beginner',

            PRIMARY KEY (jobseekers_id, skill_id),

            FOREIGN KEY (jobseekers_id)
                REFERENCES jobseekers(jobseekers_id)
                ON DELETE CASCADE,

            FOREIGN KEY (skill_id)
                REFERENCES skills(skill_id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");


} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

