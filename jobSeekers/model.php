<?php

declare(strict_types=1);

function get_jobseeker_user_info(PDO $pdo, int $user_id): array|false
{
    $query = "
        SELECT
            username,
            fullname,
            email,
            cellno,
            created_at
        FROM users
        WHERE user_id = :user_id
        AND role = 'job_seeker';
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_jobseeker_profile_info(PDO $pdo, int $user_id): array|false
{
    $query = "
        SELECT
            jobseekers_id,
            education_level,
            education_status,
            school_name,
            degree_course,
            graduation_year,
            work_experience_level,
            city,
            province,
            profile_summary,
            preferred_work_setup,
            preferred_employment_type,
            created_at,
            updated_at
        FROM jobseekers
        WHERE user_id = :user_id;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_jobseeker_id_by_user(PDO $pdo, int $user_id): int|false
{
    $query = "
        SELECT jobseekers_id
        FROM jobseekers
        WHERE user_id = :user_id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $jobseekerId = $stmt->fetchColumn();

    if ($jobseekerId === false) {
        return false;
    }

    return (int) $jobseekerId;
}

function update_user_database(PDO $pdo, int $user_id, string $username, string $fullname, string $email, string $cellno): void
{
    $query = "
        UPDATE users
        SET
            username = :username,
            fullname = :fullname,
            email = :email,
            cellno = :cellno
        WHERE user_id = :user_id
        AND role = 'job_seeker';
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":username", $username);
    $stmt->bindValue(":fullname", $fullname);
    $stmt->bindValue(":email", $email);
    $stmt->bindValue(":cellno", $cellno);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
}

function get_username_except_current_user(PDO $pdo, int $user_id, string $username): array|false
{
    $query = "
        SELECT username
        FROM users
        WHERE username = :username
        AND user_id != :user_id;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":username", $username);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_email_except_current_user(PDO $pdo, int $user_id, string $email): array|false
{
    $query = "
        SELECT email
        FROM users
        WHERE email = :email
        AND user_id != :user_id;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":email", $email);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function bind_optional_value(PDOStatement $stmt, string $name, mixed $value): void
{
    if ($value === null) {
        $stmt->bindValue($name, null, PDO::PARAM_NULL);
        return;
    }

    $stmt->bindValue($name, $value);
}

function upsert_jobseeker_profile(PDO $pdo, int $user_id, array $profile): int
{
    $query = "
        INSERT INTO jobseekers (
            user_id,
            education_level,
            education_status,
            school_name,
            degree_course,
            graduation_year,
            work_experience_level,
            city,
            province,
            profile_summary,
            preferred_work_setup,
            preferred_employment_type
        )
        VALUES (
            :user_id,
            :education_level,
            :education_status,
            :school_name,
            :degree_course,
            :graduation_year,
            :work_experience_level,
            :city,
            :province,
            :profile_summary,
            :preferred_work_setup,
            :preferred_employment_type
        )
        ON DUPLICATE KEY UPDATE
            education_level = VALUES(education_level),
            education_status = VALUES(education_status),
            school_name = VALUES(school_name),
            degree_course = VALUES(degree_course),
            graduation_year = VALUES(graduation_year),
            work_experience_level = VALUES(work_experience_level),
            city = VALUES(city),
            province = VALUES(province),
            profile_summary = VALUES(profile_summary),
            preferred_work_setup = VALUES(preferred_work_setup),
            preferred_employment_type = VALUES(preferred_employment_type),
            jobseekers_id = LAST_INSERT_ID(jobseekers_id);
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    bind_optional_value($stmt, ":education_level", $profile["education_level"]);
    bind_optional_value($stmt, ":education_status", $profile["education_status"]);
    bind_optional_value($stmt, ":school_name", $profile["school_name"]);
    bind_optional_value($stmt, ":degree_course", $profile["degree_course"]);
    bind_optional_value($stmt, ":graduation_year", $profile["graduation_year"]);
    $stmt->bindValue(":work_experience_level", $profile["work_experience_level"]);
    bind_optional_value($stmt, ":city", $profile["city"]);
    bind_optional_value($stmt, ":province", $profile["province"]);
    bind_optional_value($stmt, ":profile_summary", $profile["profile_summary"]);
    $stmt->bindValue(":preferred_work_setup", $profile["preferred_work_setup"]);
    $stmt->bindValue(":preferred_employment_type", $profile["preferred_employment_type"]);
    $stmt->execute();

    return (int) $pdo->lastInsertId();
}

function get_jobseeker_skill_names(PDO $pdo, int $jobseekers_id): array
{
    $query = "
        SELECT s.skill_name
        FROM jobseeker_skills js
        INNER JOIN skills s
            ON s.skill_id = js.skill_id
        WHERE js.jobseekers_id = :jobseekers_id
        ORDER BY s.skill_name
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":jobseekers_id", $jobseekers_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function replace_jobseeker_skills(PDO $pdo, int $jobseekers_id, array $skillNames): void
{
    $delete = $pdo->prepare("DELETE FROM jobseeker_skills WHERE jobseekers_id = :jobseekers_id");
    $delete->bindValue(":jobseekers_id", $jobseekers_id, PDO::PARAM_INT);
    $delete->execute();

    $insertSkill = $pdo->prepare("
        INSERT INTO skills (skill_name)
        VALUES (:skill_name)
        ON DUPLICATE KEY UPDATE skill_id = LAST_INSERT_ID(skill_id)
    ");

    $linkSkill = $pdo->prepare("
        INSERT INTO jobseeker_skills (jobseekers_id, skill_id, proficiency)
        VALUES (:jobseekers_id, :skill_id, 'beginner')
    ");

    foreach ($skillNames as $skillName) {
        $insertSkill->bindValue(":skill_name", $skillName);
        $insertSkill->execute();

        $skillId = (int) $pdo->lastInsertId();
        if ($skillId <= 0) {
            continue;
        }

        $linkSkill->bindValue(":jobseekers_id", $jobseekers_id, PDO::PARAM_INT);
        $linkSkill->bindValue(":skill_id", $skillId, PDO::PARAM_INT);
        $linkSkill->execute();
    }
}

function get_jobseeker_dashboard(PDO $pdo, int $user_id): array
{
    $jobseekersId = get_jobseeker_id_by_user($pdo, $user_id);
    $seekerId = $jobseekersId === false ? 0 : $jobseekersId;

    $statsQuery = "
        SELECT
            (
                SELECT COUNT(*)
                FROM job_posts
                WHERE status = 'published'
                AND (
                    application_deadline IS NULL
                    OR application_deadline >= CURDATE()
                )
            ) AS open_jobs,
            (
                SELECT COUNT(*)
                FROM applications
                WHERE jobseekers_id = :jobseekers_id
            ) AS my_applications,
            (
                SELECT COUNT(*)
                FROM saved_jobs
                WHERE jobseekers_id = :saved_jobseekers_id
            ) AS saved_jobs,
            (
                SELECT COUNT(*)
                FROM applications
                WHERE jobseekers_id = :pending_jobseekers_id
                AND status = 'pending'
            ) AS pending,
            (
                SELECT COUNT(*)
                FROM applications
                WHERE jobseekers_id = :reviewing_jobseekers_id
                AND status IN ('reviewing', 'shortlisted', 'interview')
            ) AS in_review,
            (
                SELECT COUNT(*)
                FROM applications
                WHERE jobseekers_id = :accepted_jobseekers_id
                AND status = 'accepted'
            ) AS accepted,
            (
                SELECT COUNT(*)
                FROM applications
                WHERE jobseekers_id = :rejected_jobseekers_id
                AND status = 'rejected'
            ) AS rejected,
            (
                SELECT COUNT(*)
                FROM applications
                WHERE jobseekers_id = :withdrawn_jobseekers_id
                AND status = 'withdrawn'
            ) AS withdrawn
    ";

    $statsStmt = $pdo->prepare($statsQuery);
    $statsStmt->bindValue(":jobseekers_id", $seekerId, PDO::PARAM_INT);
    $statsStmt->bindValue(":saved_jobseekers_id", $seekerId, PDO::PARAM_INT);
    $statsStmt->bindValue(":pending_jobseekers_id", $seekerId, PDO::PARAM_INT);
    $statsStmt->bindValue(":reviewing_jobseekers_id", $seekerId, PDO::PARAM_INT);
    $statsStmt->bindValue(":accepted_jobseekers_id", $seekerId, PDO::PARAM_INT);
    $statsStmt->bindValue(":rejected_jobseekers_id", $seekerId, PDO::PARAM_INT);
    $statsStmt->bindValue(":withdrawn_jobseekers_id", $seekerId, PDO::PARAM_INT);
    $statsStmt->execute();
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $recentQuery = "
        SELECT
            a.application_id,
            a.status,
            a.applied_at,
            jp.job_title,
            r.companyName
        FROM applications a
        INNER JOIN job_posts jp
            ON a.job_id = jp.job_id
        INNER JOIN recruiters r
            ON jp.recruiter_id = r.recruiter_id
        WHERE a.jobseekers_id = :jobseekers_id
        ORDER BY a.applied_at DESC
        LIMIT 5
    ";

    $recentStmt = $pdo->prepare($recentQuery);
    $recentStmt->bindValue(":jobseekers_id", $seekerId, PDO::PARAM_INT);
    $recentStmt->execute();

    return [
        "open_jobs" => (int) ($stats["open_jobs"] ?? 0),
        "my_applications" => (int) ($stats["my_applications"] ?? 0),
        "saved_jobs" => (int) ($stats["saved_jobs"] ?? 0),
        "pending" => (int) ($stats["pending"] ?? 0),
        "in_review" => (int) ($stats["in_review"] ?? 0),
        "accepted" => (int) ($stats["accepted"] ?? 0),
        "rejected" => (int) ($stats["rejected"] ?? 0),
        "withdrawn" => (int) ($stats["withdrawn"] ?? 0),
        "recent_applications" => $recentStmt->fetchAll(PDO::FETCH_ASSOC),
        "profile_ready" => $jobseekersId !== false,
    ];
}

function get_open_jobs(PDO $pdo, int $jobseekers_id, array $filters): array
{
    $query = "
        SELECT
            jp.job_id,
            jp.job_title,
            jp.job_category,
            jp.employment_type,
            jp.work_setup,
            jp.city,
            jp.province,
            jp.salary_min,
            jp.salary_max,
            jp.vacancies,
            jp.application_deadline,
            jp.created_at,
            r.companyName,
            (
                SELECT COUNT(*)
                FROM job_skills jsk
                INNER JOIN jobseeker_skills jss
                    ON jss.skill_id = jsk.skill_id
                WHERE jsk.job_id = jp.job_id
                AND jss.jobseekers_id = :skill_jobseekers_id
            ) AS skill_matches,
            (
                SELECT COUNT(*)
                FROM saved_jobs sj
                WHERE sj.job_id = jp.job_id
                AND sj.jobseekers_id = :saved_jobseekers_id
            ) AS is_saved,
            (
                SELECT COUNT(*)
                FROM applications ap
                WHERE ap.job_id = jp.job_id
                AND ap.jobseekers_id = :applied_jobseekers_id
                AND ap.status <> 'withdrawn'
            ) AS has_applied
        FROM job_posts jp
        INNER JOIN recruiters r
            ON jp.recruiter_id = r.recruiter_id
        WHERE jp.status = 'published'
        AND (
            jp.application_deadline IS NULL
            OR jp.application_deadline >= CURDATE()
        )
    ";

    $params = [
        ":skill_jobseekers_id" => $jobseekers_id,
        ":saved_jobseekers_id" => $jobseekers_id,
        ":applied_jobseekers_id" => $jobseekers_id,
    ];

    $keyword = trim((string) ($filters["keyword"] ?? ""));
    if ($keyword !== "") {
        $query .= "
            AND (
                jp.job_title LIKE :keyword
                OR jp.job_category LIKE :keyword_category
                OR jp.job_description LIKE :keyword_description
                OR r.companyName LIKE :keyword_company
            )
        ";
        $like = "%" . addcslashes($keyword, "\\%_") . "%";
        $params[":keyword"] = $like;
        $params[":keyword_category"] = $like;
        $params[":keyword_description"] = $like;
        $params[":keyword_company"] = $like;
    }

    $employmentType = trim((string) ($filters["employment_type"] ?? ""));
    if ($employmentType !== "") {
        $query .= " AND jp.employment_type = :employment_type";
        $params[":employment_type"] = $employmentType;
    }

    $workSetup = trim((string) ($filters["work_setup"] ?? ""));
    if ($workSetup !== "") {
        $query .= " AND jp.work_setup = :work_setup";
        $params[":work_setup"] = $workSetup;
    }

    $query .= "
        ORDER BY skill_matches DESC, jp.created_at DESC
    ";

    $stmt = $pdo->prepare($query);
    foreach ($params as $name => $value) {
        $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($name, $value, $type);
    }
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_open_job(PDO $pdo, int $job_id, int $jobseekers_id): array|false
{
    $query = "
        SELECT
            jp.job_id,
            jp.job_title,
            jp.job_category,
            jp.company_department,
            jp.job_description,
            jp.job_requirements,
            jp.job_responsibilities,
            jp.education_level,
            jp.degree_course,
            jp.employment_type,
            jp.work_setup,
            jp.city,
            jp.province,
            jp.salary_min,
            jp.salary_max,
            jp.salary_period,
            jp.experience_level,
            jp.vacancies,
            jp.application_deadline,
            jp.contact_email,
            jp.additional_instructions,
            jp.status,
            jp.created_at,
            r.companyName,
            r.companyWebsite,
            (
                SELECT COUNT(*)
                FROM saved_jobs sj
                WHERE sj.job_id = jp.job_id
                AND sj.jobseekers_id = :saved_jobseekers_id
            ) AS is_saved,
            (
                SELECT COUNT(*)
                FROM applications ap
                WHERE ap.job_id = jp.job_id
                AND ap.jobseekers_id = :applied_jobseekers_id
                AND ap.status <> 'withdrawn'
            ) AS has_applied
        FROM job_posts jp
        INNER JOIN recruiters r
            ON jp.recruiter_id = r.recruiter_id
        WHERE jp.job_id = :job_id
        AND jp.status = 'published'
        AND (
            jp.application_deadline IS NULL
            OR jp.application_deadline >= CURDATE()
        )
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":job_id", $job_id, PDO::PARAM_INT);
    $stmt->bindValue(":saved_jobseekers_id", $jobseekers_id, PDO::PARAM_INT);
    $stmt->bindValue(":applied_jobseekers_id", $jobseekers_id, PDO::PARAM_INT);
    $stmt->execute();

    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    return $job === false ? false : $job;
}

function get_job_skill_names(PDO $pdo, int $job_id): array
{
    $query = "
        SELECT s.skill_name
        FROM job_skills js
        INNER JOIN skills s
            ON s.skill_id = js.skill_id
        WHERE js.job_id = :job_id
        ORDER BY s.skill_name
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":job_id", $job_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function jobseeker_has_application(PDO $pdo, int $job_id, int $jobseekers_id): bool
{
    $query = "
        SELECT application_id
        FROM applications
        WHERE job_id = :job_id
        AND jobseekers_id = :jobseekers_id
        AND status <> 'withdrawn'
        LIMIT 1
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":job_id", $job_id, PDO::PARAM_INT);
    $stmt->bindValue(":jobseekers_id", $jobseekers_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchColumn() !== false;
}

function insert_application(PDO $pdo, int $job_id, int $jobseekers_id, ?string $cover_letter): void
{
    $query = "
        INSERT INTO applications (
            job_id,
            jobseekers_id,
            cover_letter,
            status
        )
        VALUES (
            :job_id,
            :jobseekers_id,
            :cover_letter,
            'pending'
        )
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":job_id", $job_id, PDO::PARAM_INT);
    $stmt->bindValue(":jobseekers_id", $jobseekers_id, PDO::PARAM_INT);
    bind_optional_value($stmt, ":cover_letter", $cover_letter);
    $stmt->execute();
}

function get_jobseeker_applications(PDO $pdo, int $jobseekers_id): array
{
    $query = "
        SELECT
            a.application_id,
            a.job_id,
            a.cover_letter,
            a.status,
            a.applied_at,
            a.updated_at,
            jp.job_title,
            jp.employment_type,
            jp.work_setup,
            jp.city,
            jp.province,
            jp.status AS job_status,
            r.companyName
        FROM applications a
        INNER JOIN job_posts jp
            ON a.job_id = jp.job_id
        INNER JOIN recruiters r
            ON jp.recruiter_id = r.recruiter_id
        WHERE a.jobseekers_id = :jobseekers_id
        ORDER BY a.applied_at DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":jobseekers_id", $jobseekers_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function withdraw_jobseeker_application(PDO $pdo, int $jobseekers_id, int $application_id): bool
{
    $query = "
        UPDATE applications
        SET status = 'withdrawn'
        WHERE application_id = :application_id
        AND jobseekers_id = :jobseekers_id
        AND status IN ('pending', 'reviewing', 'shortlisted', 'interview')
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":application_id", $application_id, PDO::PARAM_INT);
    $stmt->bindValue(":jobseekers_id", $jobseekers_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->rowCount() > 0;
}

function jobseeker_has_saved_job(PDO $pdo, int $job_id, int $jobseekers_id): bool
{
    $query = "
        SELECT saved_job_id
        FROM saved_jobs
        WHERE job_id = :job_id
        AND jobseekers_id = :jobseekers_id
        LIMIT 1
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":job_id", $job_id, PDO::PARAM_INT);
    $stmt->bindValue(":jobseekers_id", $jobseekers_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchColumn() !== false;
}

function insert_saved_job(PDO $pdo, int $job_id, int $jobseekers_id): void
{
    $query = "
        INSERT INTO saved_jobs (
            jobseekers_id,
            job_id
        )
        VALUES (
            :jobseekers_id,
            :job_id
        )
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":jobseekers_id", $jobseekers_id, PDO::PARAM_INT);
    $stmt->bindValue(":job_id", $job_id, PDO::PARAM_INT);
    $stmt->execute();
}

function delete_saved_job(PDO $pdo, int $job_id, int $jobseekers_id): bool
{
    $query = "
        DELETE FROM saved_jobs
        WHERE job_id = :job_id
        AND jobseekers_id = :jobseekers_id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":job_id", $job_id, PDO::PARAM_INT);
    $stmt->bindValue(":jobseekers_id", $jobseekers_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->rowCount() > 0;
}

function get_jobseeker_saved_jobs(PDO $pdo, int $jobseekers_id): array
{
    $query = "
        SELECT
            sj.saved_job_id,
            sj.saved_at,
            jp.job_id,
            jp.job_title,
            jp.job_category,
            jp.employment_type,
            jp.work_setup,
            jp.city,
            jp.province,
            jp.salary_min,
            jp.salary_max,
            jp.status,
            jp.application_deadline,
            r.companyName,
            (
                SELECT COUNT(*)
                FROM applications ap
                WHERE ap.job_id = jp.job_id
                AND ap.jobseekers_id = :applied_jobseekers_id
                AND ap.status <> 'withdrawn'
            ) AS has_applied
        FROM saved_jobs sj
        INNER JOIN job_posts jp
            ON sj.job_id = jp.job_id
        INNER JOIN recruiters r
            ON jp.recruiter_id = r.recruiter_id
        WHERE sj.jobseekers_id = :jobseekers_id
        ORDER BY sj.saved_at DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":jobseekers_id", $jobseekers_id, PDO::PARAM_INT);
    $stmt->bindValue(":applied_jobseekers_id", $jobseekers_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
