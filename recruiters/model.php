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



// applicant 


// applicant page

function get_recruiter_applicants(PDO $pdo, int $user_id, int $job_id = 0): array
{
    $query = "
        SELECT
            a.application_id,
            a.job_id,
            a.status,
            a.applied_at,
            jp.job_title,
            jp.vacancies,
            jp.status AS job_status,
            u.fullname,
            u.email,
            u.cellno,
            js.education_level,
            js.degree_course,
            js.work_experience_level,
            js.city,
            js.province
        FROM applications a
        INNER JOIN job_posts jp
            ON a.job_id = jp.job_id
        INNER JOIN recruiters r
            ON jp.recruiter_id = r.recruiter_id
        INNER JOIN jobseekers js
            ON a.jobseekers_id = js.jobseekers_id
        INNER JOIN users u
            ON js.user_id = u.user_id
        WHERE r.user_id = :user_id
    ";

    if ($job_id > 0) {
        $query .= " AND jp.job_id = :job_id";
    }

    $query .= " ORDER BY a.applied_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);

    if ($job_id > 0) {
        $stmt->bindValue(":job_id", $job_id, PDO::PARAM_INT);
    }

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_owned_application(PDO $pdo, int $user_id, int $application_id): array|false
{
    $query = "
        SELECT
            a.application_id,
            a.job_id,
            a.jobseekers_id,
            a.status,
            a.applied_at,
            a.cover_letter,
            jp.job_title,
            jp.vacancies,
            jp.status AS job_status,
            js.user_id AS jobseeker_user_id
        FROM applications a
        INNER JOIN job_posts jp
            ON a.job_id = jp.job_id
        INNER JOIN recruiters r
            ON jp.recruiter_id = r.recruiter_id
        INNER JOIN jobseekers js
            ON a.jobseekers_id = js.jobseekers_id
        WHERE a.application_id = :application_id
        AND r.user_id = :user_id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":application_id", $application_id, PDO::PARAM_INT);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    return $application === false ? false : $application;
}

function get_applicant_profile_for_recruiter(PDO $pdo, int $user_id, int $application_id): array|false
{
    $application = get_owned_application($pdo, $user_id, $application_id);

    if ($application === false) {
        return false;
    }

    $query = "
        SELECT
            u.username,
            u.fullname,
            u.email,
            u.cellno,
            u.created_at,
            js.jobseekers_id,
            js.education_level,
            js.education_status,
            js.school_name,
            js.degree_course,
            js.graduation_year,
            js.work_experience_level,
            js.city,
            js.province,
            js.profile_summary,
            js.preferred_work_setup,
            js.preferred_employment_type,
            js.updated_at
        FROM jobseekers js
        INNER JOIN users u
            ON js.user_id = u.user_id
        WHERE js.jobseekers_id = :jobseekers_id
        AND u.role = 'job_seeker'
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":jobseekers_id", (int) $application["jobseekers_id"], PDO::PARAM_INT);
    $stmt->execute();
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile === false) {
        return false;
    }

    $skillsStmt = $pdo->prepare("
        SELECT s.skill_name
        FROM jobseeker_skills js
        INNER JOIN skills s
            ON s.skill_id = js.skill_id
        WHERE js.jobseekers_id = :jobseekers_id
        ORDER BY s.skill_name
    ");
    $skillsStmt->bindValue(":jobseekers_id", (int) $application["jobseekers_id"], PDO::PARAM_INT);
    $skillsStmt->execute();

    return [
        "application_id" => (int) $application["application_id"],
        "job_id" => (int) $application["job_id"],
        "job_title" => (string) $application["job_title"],
        "vacancies" => (int) $application["vacancies"],
        "job_status" => (string) $application["job_status"],
        "application_status" => (string) $application["status"],
        "applied_at" => (string) $application["applied_at"],
        "cover_letter" => (string) ($application["cover_letter"] ?? ""),
        "username" => (string) ($profile["username"] ?? ""),
        "lastupdate" => (string) ($profile["updated_at"] ?? $profile["created_at"] ?? ""),
        "fullname" => (string) ($profile["fullname"] ?? ""),
        "email" => (string) ($profile["email"] ?? ""),
        "cellno" => (string) ($profile["cellno"] ?? ""),
        "education_level" => (string) ($profile["education_level"] ?? ""),
        "education_status" => (string) ($profile["education_status"] ?? ""),
        "school_name" => (string) ($profile["school_name"] ?? ""),
        "degree_course" => (string) ($profile["degree_course"] ?? ""),
        "graduation_year" => (string) ($profile["graduation_year"] ?? ""),
        "work_experience_level" => (string) ($profile["work_experience_level"] ?? "none"),
        "city" => (string) ($profile["city"] ?? ""),
        "province" => (string) ($profile["province"] ?? ""),
        "profile_summary" => (string) ($profile["profile_summary"] ?? ""),
        "preferred_work_setup" => (string) ($profile["preferred_work_setup"] ?? "any"),
        "preferred_employment_type" => (string) ($profile["preferred_employment_type"] ?? "any"),
        "skills" => $skillsStmt->fetchAll(PDO::FETCH_COLUMN),
    ];
}

function update_owned_application_status(PDO $pdo, int $user_id, int $application_id, string $status): bool
{
    $query = "
        UPDATE applications a
        INNER JOIN job_posts jp
            ON a.job_id = jp.job_id
        INNER JOIN recruiters r
            ON jp.recruiter_id = r.recruiter_id
        SET a.status = :status
        WHERE a.application_id = :application_id
        AND r.user_id = :user_id
        AND a.status <> 'withdrawn'
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":status", $status);
    $stmt->bindValue(":application_id", $application_id, PDO::PARAM_INT);
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->rowCount() > 0;
}

function count_accepted_applications(PDO $pdo, int $job_id): int
{
    $query = "
        SELECT COUNT(application_id)
        FROM applications
        WHERE job_id = :job_id
        AND status = 'accepted'
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(":job_id", $job_id, PDO::PARAM_INT);
    $stmt->execute();

    return (int) $stmt->fetchColumn();
}

function close_job_when_vacancies_filled(PDO $pdo, int $user_id, int $job_id): bool
{
    $job = get_owned_job_post($pdo, $user_id, $job_id);

    if ($job === false || ($job["status"] ?? "") === "closed") {
        return false;
    }

    $vacancies = (int) ($job["vacancies"] ?? 0);
    $acceptedCount = count_accepted_applications($pdo, $job_id);
    if ($vacancies < 1) {
        return false;
    }

    if ($acceptedCount < $vacancies) {
        return false;
    }

    update_owned_job_status($pdo, $user_id, $job_id, "closed");

    return true;
}

function get_recruiter_dashboard(PDO $pdo, int $user_id): array
{
    $jobQuery = "
        SELECT
            COUNT(jp.job_id) AS total_jobs,
            COALESCE(SUM(CASE WHEN jp.status = 'published' THEN 1 ELSE 0 END), 0) AS active_jobs,
            COALESCE(SUM(CASE WHEN jp.status = 'draft' THEN 1 ELSE 0 END), 0) AS draft_jobs,
            COALESCE(SUM(CASE WHEN jp.status = 'paused' THEN 1 ELSE 0 END), 0) AS paused_jobs,
            COALESCE(SUM(CASE WHEN jp.status = 'closed' THEN 1 ELSE 0 END), 0) AS closed_jobs
        FROM recruiters r
        LEFT JOIN job_posts jp
            ON r.recruiter_id = jp.recruiter_id
        WHERE r.user_id = :user_id
    ";

    $jobStmt = $pdo->prepare($jobQuery);
    $jobStmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $jobStmt->execute();
    $jobs = $jobStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $applicationQuery = "
        SELECT
            COUNT(a.application_id) AS total_applicants,
            COALESCE(SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END), 0) AS pending,
            COALESCE(SUM(CASE WHEN a.status = 'reviewing' THEN 1 ELSE 0 END), 0) AS reviewing,
            COALESCE(SUM(CASE WHEN a.status = 'shortlisted' THEN 1 ELSE 0 END), 0) AS shortlisted,
            COALESCE(SUM(CASE WHEN a.status = 'interview' THEN 1 ELSE 0 END), 0) AS interview,
            COALESCE(SUM(CASE WHEN a.status = 'accepted' THEN 1 ELSE 0 END), 0) AS accepted,
            COALESCE(SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END), 0) AS rejected,
            COALESCE(SUM(CASE WHEN a.status = 'withdrawn' THEN 1 ELSE 0 END), 0) AS withdrawn
        FROM applications a
        INNER JOIN job_posts jp
            ON a.job_id = jp.job_id
        INNER JOIN recruiters r
            ON jp.recruiter_id = r.recruiter_id
        WHERE r.user_id = :user_id
    ";

    $applicationStmt = $pdo->prepare($applicationQuery);
    $applicationStmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $applicationStmt->execute();
    $applications = $applicationStmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $recentQuery = "
        SELECT
            a.application_id,
            a.status,
            a.applied_at,
            jp.job_title,
            u.fullname
        FROM applications a
        INNER JOIN job_posts jp
            ON a.job_id = jp.job_id
        INNER JOIN recruiters r
            ON jp.recruiter_id = r.recruiter_id
        INNER JOIN jobseekers js
            ON a.jobseekers_id = js.jobseekers_id
        INNER JOIN users u
            ON js.user_id = u.user_id
        WHERE r.user_id = :user_id
        ORDER BY a.applied_at DESC
        LIMIT 5
    ";

    $recentStmt = $pdo->prepare($recentQuery);
    $recentStmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $recentStmt->execute();

    return [
        "total_jobs" => (int) ($jobs["total_jobs"] ?? 0),
        "active_jobs" => (int) ($jobs["active_jobs"] ?? 0),
        "draft_jobs" => (int) ($jobs["draft_jobs"] ?? 0),
        "paused_jobs" => (int) ($jobs["paused_jobs"] ?? 0),
        "closed_jobs" => (int) ($jobs["closed_jobs"] ?? 0),
        "total_applicants" => (int) ($applications["total_applicants"] ?? 0),
        "pending" => (int) ($applications["pending"] ?? 0),
        "reviewing" => (int) ($applications["reviewing"] ?? 0),
        "shortlisted" => (int) ($applications["shortlisted"] ?? 0),
        "interview" => (int) ($applications["interview"] ?? 0),
        "accepted" => (int) ($applications["accepted"] ?? 0),
        "rejected" => (int) ($applications["rejected"] ?? 0),
        "withdrawn" => (int) ($applications["withdrawn"] ?? 0),
        "recent_applicants" => $recentStmt->fetchAll(PDO::FETCH_ASSOC),
    ];
}
