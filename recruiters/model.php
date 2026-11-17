<?php

declare(strict_types=1);
// profile


function get_recruiter_user_info(PDO $pdo, int $user_id): array|false
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
        AND role = 'recruiter';
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function get_recruiter_company_info(PDO $pdo, int $user_id): array|false
{
    $query = "
        SELECT
            companyName,
            companyWebsite,
            companyNumber,
            PositioninCompany,
            created_at
        FROM recruiters
        WHERE user_id = :user_id;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}




function update_user_database ( object $pdo, int $user_id, string $username, string $fullname, string $email, string $cellno):array|false
{
    $query = "
        UPDATE users
        SET
            username = :username,
            fullname = :fullname,
            email = :email,
            cellno = :cellno
        WHERE user_id = :user_id
        AND role = 'recruiter';
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":fullname", $fullname);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":cellno", $cellno);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}


function upsert_company_database(object $pdo, int $user_id, string $companyName, string $companyWebsite, string $companyNumber, string $PositioninCompany):array|false
{
    $query = "
        INSERT INTO recruiters (
            user_id,
            companyName,
            companyWebsite,
            companyNumber,
            PositioninCompany
        )
        VALUES (
            :user_id,
            :companyName,
            :companyWebsite,
            :companyNumber,
            :PositioninCompany
        )
        ON DUPLICATE KEY UPDATE
            companyName = VALUES(companyName),
            companyWebsite = VALUES(companyWebsite),
            companyNumber = VALUES(companyNumber),
            PositioninCompany = VALUES(PositioninCompany);
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":companyName", $companyName);
    $stmt->bindParam(":companyWebsite", $companyWebsite);
    $stmt->bindParam(":companyNumber", $companyNumber);
    $stmt->bindParam(":PositioninCompany", $PositioninCompany);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}



function get_username_except_current_user(object $pdo,int $user_id ,string $username
): array|false
{
    $query = "
        SELECT username
        FROM users
        WHERE username = :username
        AND user_id != :user_id;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}


function get_email_except_current_user(object $pdo,int $user_id ,string $email
): array|false
{
    $query = "
        SELECT email
        FROM users
        WHERE email = :email
        AND user_id != :user_id;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

// job posting


function get_recruiter_job_post_statistics(PDO $pdo, int $user_id): array
{
    $query = "
        SELECT
            COUNT(jp.job_id) AS total_jobs,
            SUM(CASE WHEN jp.status = 'published' THEN 1 ELSE 0 END) AS published_jobs,
            SUM(CASE WHEN jp.status = 'draft' THEN 1 ELSE 0 END) AS draft_jobs
        FROM recruiters r
        LEFT JOIN job_posts jp
            ON r.recruiter_id = jp.recruiter_id
        WHERE r.user_id = :user_id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':user_id' => $user_id
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
        'total_jobs' => 0,
        'published_jobs' => 0,
        'draft_jobs' => 0
    ];
}

function get_recruiter_job_postings(PDO $pdo, int $user_id): array
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
            jp.salary_period,
            jp.vacancies,
            jp.status,
            jp.application_deadline,
            jp.created_at
        FROM job_posts jp
        INNER JOIN recruiters r
            ON jp.recruiter_id = r.recruiter_id
        WHERE r.user_id = :user_id
        ORDER BY jp.created_at DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':user_id' => $user_id
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_recruiter_id_by_user(PDO $pdo, int $user_id): int|false
{
    $query = "
        SELECT recruiter_id
        FROM recruiters
        WHERE user_id = :user_id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $recruiterId = $stmt->fetchColumn();

    if ($recruiterId === false) {
        return false;
    }

    return (int) $recruiterId;
}

function get_owned_job_post(PDO $pdo, int $user_id, int $job_id): array|false
{
    $query = "
        SELECT
            jp.job_id,
            jp.job_title,
            jp.job_category,
            jp.company_department,
            jp.job_description,
            jp.education_level,
            jp.degree_course,
            jp.employment_type,
            jp.work_setup,
            jp.city,
            jp.province,
            jp.salary_min,
            jp.salary_max,
            jp.experience_level,
            jp.vacancies,
            jp.application_deadline,
            jp.contact_email,
            jp.additional_instructions,
            jp.status,
            jp.created_at
        FROM job_posts jp
        INNER JOIN recruiters r
            ON jp.recruiter_id = r.recruiter_id
        WHERE jp.job_id = :job_id
        AND r.user_id = :user_id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":job_id", $job_id, PDO::PARAM_INT);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
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

function get_job_application_counts(PDO $pdo, int $job_id): array
{
    $query = "
        SELECT
            COUNT(application_id) AS total_applicants,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) AS total_pending,
            COALESCE(SUM(CASE WHEN status <> 'pending' THEN 1 ELSE 0 END), 0) AS total_reviewed
        FROM applications
        WHERE job_id = :job_id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":job_id", $job_id, PDO::PARAM_INT);
    $stmt->execute();

    $counts = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    return [
        "total_applicants" => (int) ($counts["total_applicants"] ?? 0),
        "total_pending" => (int) ($counts["total_pending"] ?? 0),
        "total_reviewed" => (int) ($counts["total_reviewed"] ?? 0),
    ];
}

function bind_job_value(PDOStatement $stmt, string $name, mixed $value): void
{
    if ($value === null) {
        $stmt->bindValue($name, null, PDO::PARAM_NULL);
        return;
    }

    $stmt->bindValue($name, $value);
}

function insert_job_post(PDO $pdo, int $recruiter_id, array $job): int
{
    $query = "
        INSERT INTO job_posts (
            recruiter_id,
            job_title,
            job_category,
            company_department,
            job_description,
            job_requirements,
            education_level,
            degree_course,
            employment_type,
            work_setup,
            city,
            province,
            salary_min,
            salary_max,
            experience_level,
            vacancies,
            application_deadline,
            contact_email,
            additional_instructions,
            status
        )
        VALUES (
            :recruiter_id,
            :job_title,
            :job_category,
            :company_department,
            :job_description,
            :job_requirements,
            :education_level,
            :degree_course,
            :employment_type,
            :work_setup,
            :city,
            :province,
            :salary_min,
            :salary_max,
            :experience_level,
            :vacancies,
            :application_deadline,
            :contact_email,
            :additional_instructions,
            :status
        )
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":recruiter_id", $recruiter_id, PDO::PARAM_INT);
    $stmt->bindValue(":job_title", $job["job_title"]);
    bind_job_value($stmt, ":job_category", $job["job_category"]);
    bind_job_value($stmt, ":company_department", $job["company_department"]);
    $stmt->bindValue(":job_description", $job["job_description"]);
    $stmt->bindValue(":job_requirements", $job["job_requirements"]);
    bind_job_value($stmt, ":education_level", $job["education_level"]);
    bind_job_value($stmt, ":degree_course", $job["degree_course"]);
    $stmt->bindValue(":employment_type", $job["employment_type"]);
    $stmt->bindValue(":work_setup", $job["work_setup"]);
    bind_job_value($stmt, ":city", $job["city"]);
    bind_job_value($stmt, ":province", $job["province"]);
    bind_job_value($stmt, ":salary_min", $job["salary_min"]);
    bind_job_value($stmt, ":salary_max", $job["salary_max"]);
    $stmt->bindValue(":experience_level", $job["experience_level"]);
    $stmt->bindValue(":vacancies", $job["vacancies"], PDO::PARAM_INT);
    bind_job_value($stmt, ":application_deadline", $job["application_deadline"]);
    bind_job_value($stmt, ":contact_email", $job["contact_email"]);
    bind_job_value($stmt, ":additional_instructions", $job["additional_instructions"]);
    $stmt->bindValue(":status", $job["status"]);
    $stmt->execute();

    return (int) $pdo->lastInsertId();
}

function update_owned_job_post(PDO $pdo, int $user_id, int $job_id, array $job): void
{
    $query = "
        UPDATE job_posts jp
        INNER JOIN recruiters r
            ON jp.recruiter_id = r.recruiter_id
        SET
            jp.job_title = :job_title,
            jp.job_category = :job_category,
            jp.company_department = :company_department,
            jp.job_description = :job_description,
            jp.job_requirements = :job_requirements,
            jp.education_level = :education_level,
            jp.degree_course = :degree_course,
            jp.employment_type = :employment_type,
            jp.work_setup = :work_setup,
            jp.city = :city,
            jp.province = :province,
            jp.salary_min = :salary_min,
            jp.salary_max = :salary_max,
            jp.experience_level = :experience_level,
            jp.vacancies = :vacancies,
            jp.application_deadline = :application_deadline,
            jp.contact_email = :contact_email,
            jp.additional_instructions = :additional_instructions,
            jp.status = :status
        WHERE jp.job_id = :job_id
        AND r.user_id = :user_id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":job_title", $job["job_title"]);
    bind_job_value($stmt, ":job_category", $job["job_category"]);
    bind_job_value($stmt, ":company_department", $job["company_department"]);
    $stmt->bindValue(":job_description", $job["job_description"]);
    $stmt->bindValue(":job_requirements", $job["job_requirements"]);
    bind_job_value($stmt, ":education_level", $job["education_level"]);
    bind_job_value($stmt, ":degree_course", $job["degree_course"]);
    $stmt->bindValue(":employment_type", $job["employment_type"]);
    $stmt->bindValue(":work_setup", $job["work_setup"]);
    bind_job_value($stmt, ":city", $job["city"]);
    bind_job_value($stmt, ":province", $job["province"]);
    bind_job_value($stmt, ":salary_min", $job["salary_min"]);
    bind_job_value($stmt, ":salary_max", $job["salary_max"]);
    $stmt->bindValue(":experience_level", $job["experience_level"]);
    $stmt->bindValue(":vacancies", $job["vacancies"], PDO::PARAM_INT);
    bind_job_value($stmt, ":application_deadline", $job["application_deadline"]);
    bind_job_value($stmt, ":contact_email", $job["contact_email"]);
    bind_job_value($stmt, ":additional_instructions", $job["additional_instructions"]);
    $stmt->bindValue(":status", $job["status"]);
    $stmt->bindValue(":job_id", $job_id, PDO::PARAM_INT);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
}

function replace_job_skills(PDO $pdo, int $job_id, array $skillNames): void
{
    $delete = $pdo->prepare("DELETE FROM job_skills WHERE job_id = :job_id");
    $delete->bindValue(":job_id", $job_id, PDO::PARAM_INT);
    $delete->execute();

    $insertSkill = $pdo->prepare("
        INSERT INTO skills (skill_name)
        VALUES (:skill_name)
        ON DUPLICATE KEY UPDATE skill_id = LAST_INSERT_ID(skill_id)
    ");

    $linkSkill = $pdo->prepare("
        INSERT INTO job_skills (job_id, skill_id)
        VALUES (:job_id, :skill_id)
    ");

    foreach ($skillNames as $skillName) {
        $insertSkill->bindValue(":skill_name", $skillName);
        $insertSkill->execute();

        $skillId = (int) $pdo->lastInsertId();
        if ($skillId <= 0) {
            continue;
        }

        $linkSkill->bindValue(":job_id", $job_id, PDO::PARAM_INT);
        $linkSkill->bindValue(":skill_id", $skillId, PDO::PARAM_INT);
        $linkSkill->execute();
    }
}

function update_owned_job_status(PDO $pdo, int $user_id, int $job_id, string $status): void
{
    $query = "
        UPDATE job_posts jp
        INNER JOIN recruiters r
            ON jp.recruiter_id = r.recruiter_id
        SET jp.status = :status
        WHERE jp.job_id = :job_id
        AND r.user_id = :user_id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":status", $status);
    $stmt->bindValue(":job_id", $job_id, PDO::PARAM_INT);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
}

function delete_owned_job_post(PDO $pdo, int $user_id, int $job_id): bool
{
    $query = "
        DELETE jp
        FROM job_posts jp
        INNER JOIN recruiters r
            ON jp.recruiter_id = r.recruiter_id
        WHERE jp.job_id = :job_id
        AND r.user_id = :user_id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":job_id", $job_id, PDO::PARAM_INT);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->rowCount() > 0;
}