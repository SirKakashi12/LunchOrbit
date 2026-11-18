<?php

$applications = $_SESSION["applicationData"] ?? [];

if (($_GET["page"] ?? "") === "applications"):

?>

<h2>My Applications</h2>
<p>Track the roles you have applied for.</p>

<table class="applicant-table">

    <thead>
        <tr>
            <th>Job</th>
            <th>Company</th>
            <th>Type</th>
            <th>Location</th>
            <th>Date Applied</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>

        <?php if (empty($applications)): ?>

            <tr>
                <td colspan="7">
                    You have not applied to any jobs yet.
                </td>
            </tr>

        <?php else: ?>

            <?php foreach ($applications as $application): ?>

                <?php
                    $status = (string) ($application["status"] ?? "");
                    $canWithdraw = in_array($status, ["pending", "reviewing", "shortlisted", "interview"], true);
                ?>

                <tr>
                    <td>
                        <strong><?= escape_value($application["job_title"] ?? "") ?></strong>
                    </td>

                    <td>
                        <?= escape_value($application["companyName"] ?? "") ?>
                    </td>

                    <td>
                        <?= escape_value(label_from_value($application["employment_type"] ?? "")) ?>
                        <br>
                        <?= escape_value(label_from_value($application["work_setup"] ?? "")) ?>
                    </td>

                    <td>
                        <?= escape_value(format_location($application["city"] ?? "", $application["province"] ?? "")) ?>
                    </td>

                    <td>
                        <?= escape_value($application["applied_at"] ?? "") ?>
                    </td>

                    <td>
                        <?= escape_value(label_from_value($status)) ?>
                    </td>

                    <td>
                        <?php if ($canWithdraw): ?>
                            <form action="jobSeekers.php" method="POST">
                                <input type="hidden" name="application_id" value="<?= escape_value($application["application_id"] ?? "") ?>">
                                <button type="submit" name="withdraw_application" value="1" class="job-btn job-btn-danger">
                                    Withdraw
                                </button>
                            </form>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

    </tbody>

</table>

<?php endif; ?>
