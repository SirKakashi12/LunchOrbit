<?php

declare(strict_types=1);

function is_field_empty_in_personal(string $username, string $fullname, string $email, string $cellno): bool
{
    return empty($username) || empty($fullname) || empty($email) || empty($cellno);
}

function is_email_invalid(string $email): bool
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    }

    return false;
}

function is_username_taken_for_other_user(PDO $pdo, int $user_id, string $username): bool
{
    return get_username_except_current_user($pdo, $user_id, $username) !== false;
}

function is_email_registered_for_other_user(PDO $pdo, int $user_id, string $email): bool
{
    return get_email_except_current_user($pdo, $user_id, $email) !== false;
}

function collect_skill_names(array $input): array
{
    $skills = [];
    $seenSkills = [];
    $errors = [];
    $postedSkills = $input["skills"] ?? [];

    if (!is_array($postedSkills)) {
        $postedSkills = [];
    }

    foreach ($postedSkills as $postedSkill) {
        $skillName = trim((string) $postedSkill);
        if ($skillName === "") {
            continue;
        }

        if (strlen($skillName) > 100) {
            $errors["skills"] = "Each skill must be 100 characters or fewer.";
            continue;
        }

        $skillKey = strtolower($skillName);
        if (isset($seenSkills[$skillKey])) {
            continue;
        }

        $seenSkills[$skillKey] = true;
        $skills[] = $skillName;
    }

    return [
        "skills" => $skills,
        "errors" => $errors,
    ];
}

function validate_jobseeker_profile(array $input): array
{
    $educationOptions = [
        "junior_high_school",
        "senior_high_school",
        "vocational_certificate",
        "associate_degree",
        "bachelor_degree",
        "masters_degree",
        "doctoral_degree",
        "other",
    ];
    $educationStatuses = [
        "currently_studying",
        "graduated",
        "not_completed",
    ];
    $experienceOptions = [
        "none",
        "internship_or_ojt_only",
        "under_2_years",
        "2_to_5_years",
        "over_5_years",
    ];
    $workOptions = [
        "onsite",
        "hybrid",
        "remote",
        "any",
    ];
    $employmentOptions = [
        "full_time",
        "part_time",
        "internship",
        "contract",
        "freelance",
        "any",
    ];

    $errors = [];
    $educationLevel = trim((string) ($input["education_level"] ?? ""));
    $educationStatus = trim((string) ($input["education_status"] ?? ""));
    $schoolName = trim((string) ($input["school_name"] ?? ""));
    $degreeCourse = trim((string) ($input["degree_course"] ?? ""));
    $graduationYear = trim((string) ($input["graduation_year"] ?? ""));
    $workExperience = trim((string) ($input["work_experience_level"] ?? ""));
    $city = trim((string) ($input["city"] ?? ""));
    $province = trim((string) ($input["province"] ?? ""));
    $profileSummary = trim((string) ($input["profile_summary"] ?? ""));
    $workSetup = trim((string) ($input["preferred_work_setup"] ?? ""));
    $employmentType = trim((string) ($input["preferred_employment_type"] ?? ""));

    $skillResult = collect_skill_names($input);
    $errors = array_merge($errors, $skillResult["errors"]);

    if ($educationLevel !== "" && !in_array($educationLevel, $educationOptions, true)) {
        $errors["education_level"] = "Choose a valid education level.";
    }

    if ($educationStatus !== "" && !in_array($educationStatus, $educationStatuses, true)) {
        $errors["education_status"] = "Choose a valid education status.";
    }

    if (strlen($schoolName) > 150) {
        $errors["school_name"] = "School name must be 150 characters or fewer.";
    }

    if (strlen($degreeCourse) > 150) {
        $errors["degree_course"] = "Degree / course must be 150 characters or fewer.";
    }

    $storedYear = null;
    if ($graduationYear !== "") {
        if (filter_var($graduationYear, FILTER_VALIDATE_INT) === false) {
            $errors["graduation_year"] = "Graduation year must be a whole number.";
        } else {
            $year = (int) $graduationYear;
            if ($year < 1900 || $year > 2155) {
                $errors["graduation_year"] = "Graduation year must be between 1900 and 2155.";
            } else {
                $storedYear = (string) $year;
            }
        }
    }

    if ($workExperience === "") {
        $workExperience = "none";
    } elseif (!in_array($workExperience, $experienceOptions, true)) {
        $errors["work_experience_level"] = "Choose a valid work experience level.";
    }

    if (strlen($city) > 100) {
        $errors["city"] = "City must be 100 characters or fewer.";
    }

    if (strlen($province) > 100) {
        $errors["province"] = "Province must be 100 characters or fewer.";
    }

    if ($workSetup === "") {
        $workSetup = "any";
    } elseif (!in_array($workSetup, $workOptions, true)) {
        $errors["preferred_work_setup"] = "Choose a valid work setup.";
    }

    if ($employmentType === "") {
        $employmentType = "any";
    } elseif (!in_array($employmentType, $employmentOptions, true)) {
        $errors["preferred_employment_type"] = "Choose a valid employment type.";
    }

    return [
        "errors" => $errors,
        "skills" => $skillResult["skills"],
        "profile" => [
            "education_level" => $educationLevel === "" ? null : $educationLevel,
            "education_status" => $educationStatus === "" ? null : $educationStatus,
            "school_name" => $schoolName === "" ? null : $schoolName,
            "degree_course" => $degreeCourse === "" ? null : $degreeCourse,
            "graduation_year" => $storedYear,
            "work_experience_level" => $workExperience,
            "city" => $city === "" ? null : $city,
            "province" => $province === "" ? null : $province,
            "profile_summary" => $profileSummary === "" ? null : $profileSummary,
            "preferred_work_setup" => $workSetup,
            "preferred_employment_type" => $employmentType,
        ],
        "form" => [
            "education_level" => $educationLevel,
            "education_status" => $educationStatus,
            "school_name" => $schoolName,
            "degree_course" => $degreeCourse,
            "graduation_year" => $graduationYear,
            "work_experience_level" => $workExperience,
            "city" => $city,
            "province" => $province,
            "profile_summary" => $profileSummary,
            "preferred_work_setup" => $workSetup,
            "preferred_employment_type" => $employmentType,
            "skills" => $skillResult["skills"],
        ],
    ];
}

function build_jobseeker_profile_session(PDO $pdo, int $user_id): array
{
    $userInfo = get_jobseeker_user_info($pdo, $user_id);
    $profileInfo = get_jobseeker_profile_info($pdo, $user_id);
    $skills = [];

    if ($profileInfo !== false) {
        $skills = get_jobseeker_skill_names($pdo, (int) $profileInfo["jobseekers_id"]);
    }

    $summary = trim((string) ($profileInfo["profile_summary"] ?? ""));
    $education = trim((string) ($profileInfo["education_level"] ?? ""));
    $profileIncomplete = $profileInfo === false || ($summary === "" && $education === "");

    return [
        "username" => $userInfo["username"] ?? "",
        "lastupdate" => $profileInfo["updated_at"] ?? ($userInfo["created_at"] ?? ""),
        "fullname" => $userInfo["fullname"] ?? "",
        "email" => $userInfo["email"] ?? "",
        "cellno" => $userInfo["cellno"] ?? "",
        "education_level" => $profileInfo["education_level"] ?? "",
        "education_status" => $profileInfo["education_status"] ?? "",
        "school_name" => $profileInfo["school_name"] ?? "",
        "degree_course" => $profileInfo["degree_course"] ?? "",
        "graduation_year" => $profileInfo["graduation_year"] ?? "",
        "work_experience_level" => $profileInfo["work_experience_level"] ?? "none",
        "city" => $profileInfo["city"] ?? "",
        "province" => $profileInfo["province"] ?? "",
        "profile_summary" => $profileInfo["profile_summary"] ?? "",
        "preferred_work_setup" => $profileInfo["preferred_work_setup"] ?? "any",
        "preferred_employment_type" => $profileInfo["preferred_employment_type"] ?? "any",
        "skills" => $skills,
        "profileIncomplete" => $profileIncomplete,
    ];
}

function job_search_filters_from_request(array $source): array
{
    $employmentOptions = [
        "full_time",
        "part_time",
        "internship",
        "contract",
        "freelance",
    ];
    $workOptions = [
        "onsite",
        "hybrid",
        "remote",
    ];

    $employmentType = trim((string) ($source["employment_type"] ?? ""));
    $workSetup = trim((string) ($source["work_setup"] ?? ""));

    if (!in_array($employmentType, $employmentOptions, true)) {
        $employmentType = "";
    }

    if (!in_array($workSetup, $workOptions, true)) {
        $workSetup = "";
    }

    return [
        "keyword" => trim((string) ($source["keyword"] ?? "")),
        "employment_type" => $employmentType,
        "work_setup" => $workSetup,
    ];
}

function build_find_job_data(PDO $pdo, int $user_id, array $filters): array
{
    $jobseekersId = get_jobseeker_id_by_user($pdo, $user_id);
    $seekerId = $jobseekersId === false ? 0 : $jobseekersId;

    return [
        "filters" => $filters,
        "profile_ready" => $jobseekersId !== false,
        "jobs" => get_open_jobs($pdo, $seekerId, $filters),
    ];
}

function build_current_open_job(PDO $pdo, int $user_id, int $job_id): array|false
{
    $jobseekersId = get_jobseeker_id_by_user($pdo, $user_id);
    $seekerId = $jobseekersId === false ? 0 : $jobseekersId;
    $job = get_open_job($pdo, $job_id, $seekerId);

    if ($job === false) {
        return false;
    }

    $job["skills"] = get_job_skill_names($pdo, $job_id);
    $job["profile_ready"] = $jobseekersId !== false;

    return $job;
}

function require_jobseeker_profile(PDO $pdo, int $user_id): int|false
{
    return get_jobseeker_id_by_user($pdo, $user_id);
}
