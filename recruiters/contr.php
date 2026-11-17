<?php
declare(strict_types=1);

// job post page


function is_field_empty_in_personal(  string $username,  string $fullname,  string $email,  string $cellno
): bool {
    return empty($username) || empty($fullname) || empty($email) || empty($cellno);
}

function is_email_invalid(string $email):bool
{
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return true;
	}
	return false;
}


function update_user( object $pdo, int $user_id, string $username, string $fullname, string $email, string $cellno): bool{
	if (update_user_database($pdo,$user_id,$username,$fullname,$email,$cellno)){
		return true;
	}
	return false;
}

function is_username_taken_for_other_user( object $pdo, int $user_id, string $username,
): bool
{
    return get_username_except_current_user($pdo,  $user_id, $username,) !== false;
}


function is_email_registered_for_other_user( object $pdo, int $user_id, string $email,
): bool
{
    return get_email_except_current_user($pdo, $user_id, $email) !== false;
}




function is_field_empty_in_company( string $companyName, string $companyWebsite, string $companyNumber, string $positioninCompany
): bool {
    return empty($companyName) || empty($companyWebsite) || empty($companyNumber) || empty($positioninCompany);
}



function is_url_invalid(string $companyWebsite):bool
{
	if (!filter_var($companyWebsite, FILTER_VALIDATE_URL)) {
		return true;
	}
	return false;
}

function validate_company_website(string $companyWebsite): array
{
    $companyWebsite = trim($companyWebsite);

    // Allow an empty website because it is optional
    if (empty($companyWebsite)) {
        return [
            "value" => "",
            "invalid" => false
        ];
    }

    if (!preg_match("~^https?://~i", $companyWebsite)) {
        $companyWebsite = "https://" . $companyWebsite;
    }

    if (!filter_var($companyWebsite, FILTER_VALIDATE_URL)) {
        return [
            "value" => $companyWebsite,
            "invalid" => true
        ];
    }

    return [
        "value" => $companyWebsite,
        "invalid" => false
    ];
}

// job post page

function get_recruiter_job_posting_data(PDO $pdo, int $user_id): array
{
    if ($user_id <= 0) {
        return [
            'statistics' => [
                'total_jobs' => 0,
                'published_jobs' => 0,
                'draft_jobs' => 0
            ],
            'job_postings' => []
        ];
    }

    return [
        'statistics' => get_recruiter_job_post_statistics($pdo, $user_id),
        'job_postings' => get_recruiter_job_postings($pdo, $user_id)
    ];
}

function empty_job_post_form(): array
{
    return [
        "job_id" => "",
        "job_title" => "",
        "job_category" => "",
        "company_department" => "",
        "job_description" => "",
        "education_level" => "",
        "degree_course" => "",
        "skills" => [],
        "experience_level" => "",
        "employment_type" => "",
        "work_setup" => "",
        "job_location" => "",
        "minimum_salary" => "",
        "maximum_salary" => "",
        "vacancies" => "",
        "deadline" => "",
        "contact_email" => "",
        "additional_instructions" => "",
        "status" => "draft",
        "created_at" => "",
        "total_applicants" => 0,
        "total_reviewed" => 0,
        "total_pending" => 0,
    ];
}

function map_job_post_to_form(array $job, array $skills, array $counts): array
{
    $city = trim((string) ($job["city"] ?? ""));
    $province = trim((string) ($job["province"] ?? ""));
    $location = $city;

    if ($province !== "") {
        $location = $city !== "" ? $city . ", " . $province : $province;
    }

    $deadline = $job["application_deadline"] ?? "";
    if ($deadline === null) {
        $deadline = "";
    }

    return [
        "job_id" => (string) ($job["job_id"] ?? ""),
        "job_title" => (string) ($job["job_title"] ?? ""),
        "job_category" => (string) ($job["job_category"] ?? ""),
        "company_department" => (string) ($job["company_department"] ?? ""),
        "job_description" => (string) ($job["job_description"] ?? ""),
        "education_level" => (string) ($job["education_level"] ?? ""),
        "degree_course" => (string) ($job["degree_course"] ?? ""),
        "skills" => array_values($skills),
        "experience_level" => (string) ($job["experience_level"] ?? ""),
        "employment_type" => (string) ($job["employment_type"] ?? ""),
        "work_setup" => (string) ($job["work_setup"] ?? ""),
        "job_location" => $location,
        "minimum_salary" => $job["salary_min"] === null ? "" : (string) $job["salary_min"],
        "maximum_salary" => $job["salary_max"] === null ? "" : (string) $job["salary_max"],
        "vacancies" => (string) ($job["vacancies"] ?? ""),
        "deadline" => (string) $deadline,
        "contact_email" => (string) ($job["contact_email"] ?? ""),
        "additional_instructions" => (string) ($job["additional_instructions"] ?? ""),
        "status" => (string) ($job["status"] ?? ""),
        "created_at" => (string) ($job["created_at"] ?? ""),
        "total_applicants" => (int) ($counts["total_applicants"] ?? 0),
        "total_reviewed" => (int) ($counts["total_reviewed"] ?? 0),
        "total_pending" => (int) ($counts["total_pending"] ?? 0),
    ];
}

function build_current_job_post(PDO $pdo, int $user_id, int $job_id): array|false
{
    $job = get_owned_job_post($pdo, $user_id, $job_id);

    if ($job === false) {
        return false;
    }

    return map_job_post_to_form(
        $job,
        get_job_skill_names($pdo, $job_id),
        get_job_application_counts($pdo, $job_id)
    );
}

function validate_job_post_form(array $input): array
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
    $experienceOptions = [
        "entry_level",
        "internship_or_ojt_only",
        "under_2_years",
        "2_to_5_years",
        "over_5_years",
    ];
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

    $errors = [];
    $jobId = (int) ($input["job_id"] ?? 0);
    $statusInput = (string) ($input["job_status"] ?? "");
    $isPublish = $statusInput === "active";

    $jobTitle = trim((string) ($input["job_title"] ?? ""));
    $jobCategory = trim((string) ($input["job_category"] ?? ""));
    $companyDepartment = trim((string) ($input["company_department"] ?? ""));
    $jobDescription = trim((string) ($input["job_description"] ?? ""));
    $educationLevel = trim((string) ($input["education_level"] ?? ""));
    $degreeCourse = trim((string) ($input["degree_course"] ?? ""));
    $experienceLevel = trim((string) ($input["experience_level"] ?? ""));
    $employmentType = trim((string) ($input["employment_type"] ?? ""));
    $workSetup = trim((string) ($input["work_setup"] ?? ""));
    $jobLocation = trim((string) ($input["job_location"] ?? ""));
    $minimumSalary = trim((string) ($input["minimum_salary"] ?? ""));
    $maximumSalary = trim((string) ($input["maximum_salary"] ?? ""));
    $vacanciesInput = trim((string) ($input["vacancies"] ?? ""));
    $deadline = trim((string) ($input["deadline"] ?? ""));
    $contactEmail = trim((string) ($input["contact_email"] ?? ""));
    $additionalInstructions = trim((string) ($input["additional_instructions"] ?? ""));

    $skills = [];
    $seenSkills = [];
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

    if ($statusInput !== "draft" && $statusInput !== "active") {
        $errors["job_status"] = "Choose draft or publish.";
    }

    if ($jobTitle === "") {
        $errors["job_title"] = "Job title is required.";
    } elseif (strlen($jobTitle) > 150) {
        $errors["job_title"] = "Job title must be 150 characters or fewer.";
    }

    if ($jobCategory !== "" && strlen($jobCategory) > 100) {
        $errors["job_category"] = "Job category must be 100 characters or fewer.";
    }

    if ($companyDepartment !== "" && strlen($companyDepartment) > 150) {
        $errors["company_department"] = "Company / department must be 150 characters or fewer.";
    }

    if ($degreeCourse !== "" && strlen($degreeCourse) > 150) {
        $errors["degree_course"] = "Degree / course must be 150 characters or fewer.";
    }

    if ($jobLocation !== "" && strlen($jobLocation) > 201) {
        $errors["job_location"] = "Job location is too long.";
    }

    if ($contactEmail !== "" && (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL) || strlen($contactEmail) > 254)) {
        $errors["contact_email"] = "Contact email is invalid.";
    }

    if ($educationLevel !== "" && !in_array($educationLevel, $educationOptions, true)) {
        $errors["education_level"] = "Choose a valid education level.";
    }

    if ($experienceLevel !== "" && !in_array($experienceLevel, $experienceOptions, true)) {
        $errors["experience_level"] = "Choose a valid experience level.";
    }

    if ($employmentType === "any") {
        $errors["employment_type"] = "Choose a specific employment type.";
    } elseif ($employmentType !== "" && !in_array($employmentType, $employmentOptions, true)) {
        $errors["employment_type"] = "Choose a valid employment type.";
    }

    if ($workSetup === "any") {
        $errors["work_setup"] = "Choose a specific work setup.";
    } elseif ($workSetup !== "" && !in_array($workSetup, $workOptions, true)) {
        $errors["work_setup"] = "Choose a valid work setup.";
    }

    $salaryMin = null;
    $salaryMax = null;

    if ($minimumSalary !== "") {
        if (!is_numeric($minimumSalary) || (float) $minimumSalary < 0) {
            $errors["minimum_salary"] = "Minimum salary must be a number that is 0 or greater.";
        } else {
            $salaryMin = number_format((float) $minimumSalary, 2, ".", "");
        }
    }

    if ($maximumSalary !== "") {
        if (!is_numeric($maximumSalary) || (float) $maximumSalary < 0) {
            $errors["maximum_salary"] = "Maximum salary must be a number that is 0 or greater.";
        } else {
            $salaryMax = number_format((float) $maximumSalary, 2, ".", "");
        }
    }

    if ($salaryMin !== null && $salaryMax !== null && (float) $salaryMin > (float) $salaryMax) {
        $errors["salary_range"] = "Minimum salary cannot be greater than maximum salary.";
    }

    $vacancies = null;
    if ($vacanciesInput !== "") {
        if (filter_var($vacanciesInput, FILTER_VALIDATE_INT) === false || (int) $vacanciesInput < 1) {
            $errors["vacancies"] = "Vacancies must be a whole number of at least 1.";
        } else {
            $vacancies = (int) $vacanciesInput;
        }
    }

    $applicationDeadline = null;
    if ($deadline !== "") {
        $parsedDeadline = DateTime::createFromFormat("Y-m-d", $deadline);
        $deadlineErrors = DateTime::getLastErrors();
        $deadlineInvalid = $parsedDeadline === false
            || ($deadlineErrors !== false && ($deadlineErrors["warning_count"] > 0 || $deadlineErrors["error_count"] > 0))
            || $parsedDeadline->format("Y-m-d") !== $deadline;

        if ($deadlineInvalid) {
            $errors["deadline"] = "Application deadline must be a valid date.";
        } else {
            $applicationDeadline = $deadline;
        }
    }

    if ($isPublish) {
        if ($jobCategory === "") {
            $errors["job_category"] = "Job category is required.";
        }
        if ($companyDepartment === "") {
            $errors["company_department"] = "Company / department is required.";
        }
        if ($jobDescription === "") {
            $errors["job_description"] = "Job description is required.";
        }
        if ($educationLevel === "" || !in_array($educationLevel, $educationOptions, true)) {
            $errors["education_level"] = "Education level is required.";
        }
        if ($experienceLevel === "" || !in_array($experienceLevel, $experienceOptions, true)) {
            $errors["experience_level"] = "Experience level is required.";
        }
        if (!in_array($employmentType, $employmentOptions, true)) {
            $errors["employment_type"] = "Choose a specific employment type.";
        }
        if (!in_array($workSetup, $workOptions, true)) {
            $errors["work_setup"] = "Choose a specific work setup.";
        }
        if ($jobLocation === "") {
            $errors["job_location"] = "Job location is required.";
        }
        if ($vacancies === null) {
            $errors["vacancies"] = "Vacancies must be a whole number of at least 1.";
        }
        if ($contactEmail === "" || !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $errors["contact_email"] = "Contact email is required.";
        }
    }

    $storedEmployment = $employmentType;
    $storedWorkSetup = $workSetup;
    $storedExperience = $experienceLevel;
    $storedVacancies = $vacancies ?? 1;

    if (!$isPublish) {
        if ($storedEmployment === "") {
            $storedEmployment = "full_time";
        }
        if ($storedWorkSetup === "") {
            $storedWorkSetup = "onsite";
        }
        if ($storedExperience === "") {
            $storedExperience = "entry_level";
        }
    }

    $city = null;
    $province = null;
    if ($jobLocation !== "") {
        $locationParts = explode(",", $jobLocation, 2);
        $city = trim($locationParts[0]);
        $province = isset($locationParts[1]) ? trim($locationParts[1]) : "";

        if ($city === "") {
            $city = null;
        } elseif (strlen($city) > 100) {
            $errors["job_location"] = "City must be 100 characters or fewer.";
        }

        if ($province === "") {
            $province = null;
        } elseif (strlen($province) > 100) {
            $errors["job_location"] = "Province must be 100 characters or fewer.";
        }
    }

    $form = [
        "job_id" => $jobId > 0 ? (string) $jobId : "",
        "job_title" => $jobTitle,
        "job_category" => $jobCategory,
        "company_department" => $companyDepartment,
        "job_description" => $jobDescription,
        "education_level" => $educationLevel,
        "degree_course" => $degreeCourse,
        "skills" => $skills,
        "experience_level" => $experienceLevel,
        "employment_type" => $employmentType,
        "work_setup" => $workSetup,
        "job_location" => $jobLocation,
        "minimum_salary" => $minimumSalary,
        "maximum_salary" => $maximumSalary,
        "vacancies" => $vacanciesInput,
        "deadline" => $deadline,
        "contact_email" => $contactEmail,
        "additional_instructions" => $additionalInstructions,
        "status" => $isPublish ? "published" : "draft",
        "created_at" => "",
        "total_applicants" => 0,
        "total_reviewed" => 0,
        "total_pending" => 0,
    ];

    return [
        "errors" => $errors,
        "skills" => $skills,
        "form" => $form,
        "job" => [
            "job_title" => $jobTitle,
            "job_category" => $jobCategory === "" ? null : $jobCategory,
            "company_department" => $companyDepartment === "" ? null : $companyDepartment,
            "job_description" => $jobDescription,
            "job_requirements" => "",
            "education_level" => $educationLevel === "" ? null : $educationLevel,
            "degree_course" => $degreeCourse === "" ? null : $degreeCourse,
            "employment_type" => $storedEmployment,
            "work_setup" => $storedWorkSetup,
            "city" => $city,
            "province" => $province,
            "salary_min" => $salaryMin,
            "salary_max" => $salaryMax,
            "experience_level" => $storedExperience,
            "vacancies" => $storedVacancies,
            "application_deadline" => $applicationDeadline,
            "contact_email" => $contactEmail === "" ? null : $contactEmail,
            "additional_instructions" => $additionalInstructions === "" ? null : $additionalInstructions,
            "status" => $isPublish ? "published" : "draft",
        ],
    ];
}