<?php

session_start();
include "db.php";

/* =========================================================
   STUDENT LOGIN PROTECTION
========================================================= */

if (
    !isset($_SESSION["student_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] != "student"
) {
    header("Location: student_login.php");
    exit();
}

$student_id = intval($_SESSION["student_id"]);


/* =========================================================
   GET STUDENT
========================================================= */

$student_stmt = mysqli_prepare(
    $conn,
    "SELECT
        name,
        registration_no,
        department,
        section,
        year,
        semester
     FROM students
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $student_stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($student_stmt);

$student_result =
    mysqli_stmt_get_result($student_stmt);

$student =
    mysqli_fetch_assoc($student_result);

if (!$student) {
    die("Student record not found.");
}


/* =========================================================
   OVERALL ATTENDANCE SUMMARY
========================================================= */

$summary_stmt = mysqli_prepare(
    $conn,
    "SELECT
        COUNT(*) AS total,

        SUM(
            CASE
                WHEN ar.status = 'Present'
                THEN 1
                ELSE 0
            END
        ) AS present,

        SUM(
            CASE
                WHEN ar.status = 'Absent'
                THEN 1
                ELSE 0
            END
        ) AS absent

     FROM attendance_records ar

     INNER JOIN attendance_sessions ats
        ON ar.session_id = ats.id

     WHERE ar.student_id = ?"
);

mysqli_stmt_bind_param(
    $summary_stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($summary_stmt);

$summary_result =
    mysqli_stmt_get_result($summary_stmt);

$summary =
    mysqli_fetch_assoc($summary_result);

$total = intval($summary["total"] ?? 0);
$present = intval($summary["present"] ?? 0);
$absent = intval($summary["absent"] ?? 0);

$percentage =
    $total > 0
    ? round(($present / $total) * 100, 2)
    : 0;


/* =========================================================
   SUBJECT-WISE ATTENDANCE
========================================================= */

$subject_stmt = mysqli_prepare(
    $conn,
    "SELECT
        ats.subject_code,
        ats.subject_name,

        COUNT(*) AS total_classes,

        SUM(
            CASE
                WHEN ar.status = 'Present'
                THEN 1
                ELSE 0
            END
        ) AS present_classes,

        SUM(
            CASE
                WHEN ar.status = 'Absent'
                THEN 1
                ELSE 0
            END
        ) AS absent_classes

     FROM attendance_records ar

     INNER JOIN attendance_sessions ats
        ON ar.session_id = ats.id

     WHERE ar.student_id = ?

     GROUP BY
        ats.subject_code,
        ats.subject_name

     ORDER BY
        ats.subject_name ASC"
);

mysqli_stmt_bind_param(
    $subject_stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($subject_stmt);

$subject_result =
    mysqli_stmt_get_result($subject_stmt);


/* =========================================================
   DETAILED ATTENDANCE HISTORY
========================================================= */

$history_stmt = mysqli_prepare(
    $conn,
    "SELECT
        ats.attendance_date,
        ats.subject_code,
        ats.subject_name,
        ats.start_period,
        ats.end_period,
        ats.department,
        ats.section,
        ats.year,
        ats.semester,
        ar.status

     FROM attendance_records ar

     INNER JOIN attendance_sessions ats
        ON ar.session_id = ats.id

     WHERE ar.student_id = ?

     ORDER BY
        ats.attendance_date DESC,
        ats.start_period ASC"
);

mysqli_stmt_bind_param(
    $history_stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($history_stmt);

$history_result =
    mysqli_stmt_get_result($history_stmt);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
My Attendance - EduTrack
</title>

<link
    rel="stylesheet"
    href="css/style.css"
>

</head>

<body>


<!-- NAVBAR -->

<div class="navbar">

<h2>
🎓 EduTrack
</h2>

<div>

<span>
👤
<?php
echo htmlspecialchars($student["name"]);
?>
</span>

<a
    href="student_logout.php"
    class="logout-btn"
>
Logout
</a>

</div>

</div>



<div class="main-layout">


<!-- SIDEBAR -->

<div class="sidebar student-sidebar">

<h3>
STUDENT PORTAL
</h3>

<a href="student_dashboard.php">
🏠 Dashboard
</a>

<a
    href="student_attendance.php"
    class="active"
>
📅 My Attendance
</a>

<a href="student_marks.php">
📝 My Marks
</a>

<a href="student_logout.php">
🚪 Logout
</a>

</div>



<!-- MAIN CONTENT -->

<div class="main-content">


<div class="student-page-header">

<div>

<h1>
📅 My Attendance
</h1>

<p>
View your overall and subject-wise attendance.
</p>

</div>


<div class="student-identity-badge">

<?php
echo htmlspecialchars(
    $student["registration_no"]
);
?>

•

<?php
echo htmlspecialchars(
    $student["department"]
);
?>

-

<?php
echo htmlspecialchars(
    $student["section"]
);
?>

•

<?php
echo htmlspecialchars(
    $student["year"]
);
?>

•

Semester

<?php
echo htmlspecialchars(
    $student["semester"]
);
?>

</div>

</div>



<!-- OVERALL SUMMARY -->

<div class="attendance-summary-grid">


<div class="attendance-summary-card">

<span>📊</span>

<p>
Overall Attendance
</p>

<h2>
<?php echo $percentage; ?>%
</h2>

</div>



<div class="attendance-summary-card">

<span>📚</span>

<p>
Total Classes
</p>

<h2>
<?php echo $total; ?>
</h2>

</div>



<div class="attendance-summary-card present-card">

<span>✅</span>

<p>
Present
</p>

<h2>
<?php echo $present; ?>
</h2>

</div>



<div class="attendance-summary-card absent-card">

<span>❌</span>

<p>
Absent
</p>

<h2>
<?php echo $absent; ?>
</h2>

</div>


</div>



<!-- ATTENDANCE PROGRESS -->

<div class="student-dashboard-card">

<div class="attendance-progress-info">

<strong>
Overall Attendance Progress
</strong>

<strong>
<?php echo $percentage; ?>%
</strong>

</div>


<div class="attendance-progress-track">

<div
    class="attendance-progress-fill"
    style="width: <?php
        echo min($percentage, 100);
    ?>%;"
>
</div>

</div>


<?php if ($total == 0) { ?>

<div class="student-info-note">

ℹ No attendance has been recorded yet.

</div>

<?php } elseif ($percentage >= 75) { ?>

<div class="student-success-note">

✓ You currently meet the minimum
75% attendance requirement.

</div>

<?php } else { ?>

<div class="student-warning">

⚠ Attendance Shortage:
Your overall attendance is below 75%.

</div>

<?php } ?>

</div>



<!-- =====================================================
     SUBJECT WISE ATTENDANCE
===================================================== -->

<div class="student-table-section">

<div class="table-section-heading">

<h2>
📚 Subject-wise Attendance
</h2>

</div>


<div class="student-table-card">

<table class="student-record-table">

<thead>

<tr>

<th>
Subject
</th>

<th>
Subject Code
</th>

<th>
Total Classes
</th>

<th>
Present
</th>

<th>
Absent
</th>

<th>
Percentage
</th>

<th>
Status
</th>

</tr>

</thead>


<tbody>

<?php

if (
    mysqli_num_rows($subject_result) > 0
) {

    while (
        $subject =
        mysqli_fetch_assoc($subject_result)
    ) {

        $subject_total =
            intval(
                $subject["total_classes"]
            );

        $subject_present =
            intval(
                $subject["present_classes"]
            );

        $subject_absent =
            intval(
                $subject["absent_classes"]
            );

        $subject_percentage =
            $subject_total > 0
            ? round(
                ($subject_present / $subject_total)
                * 100,
                2
            )
            : 0;

?>

<tr>

<td>

<strong>

<?php
echo htmlspecialchars(
    $subject["subject_name"]
    ?: $subject["subject_code"]
);
?>

</strong>

</td>


<td>

<?php
echo htmlspecialchars(
    $subject["subject_code"]
);
?>

</td>


<td>
<?php echo $subject_total; ?>
</td>


<td>

<span class="attendance-present">

✓ <?php echo $subject_present; ?>

</span>

</td>


<td>

<span class="attendance-absent">

✕ <?php echo $subject_absent; ?>

</span>

</td>


<td>

<strong>

<?php
echo $subject_percentage;
?>%

</strong>

</td>


<td>

<?php if ($subject_percentage >= 75) { ?>

<span class="attendance-present">

✓ Eligible

</span>

<?php } else { ?>

<span class="attendance-absent">

⚠ Shortage

</span>

<?php } ?>

</td>

</tr>

<?php

    }

} else {

?>

<tr>

<td
    colspan="7"
    class="student-no-records"
>

📭 No subject-wise attendance
records are available yet.

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>



<!-- =====================================================
     DETAILED ATTENDANCE HISTORY
===================================================== -->

<div class="student-table-section">

<div class="table-section-heading">

<h2>
📋 Attendance History
</h2>

<span>
<?php echo $total; ?> Records
</span>

</div>


<div class="student-table-card">

<table class="student-record-table">

<thead>

<tr>

<th>
Date
</th>

<th>
Day
</th>

<th>
Subject
</th>

<th>
Code
</th>

<th>
Periods
</th>

<th>
Status
</th>

</tr>

</thead>


<tbody>

<?php

if (
    mysqli_num_rows($history_result) > 0
) {

    while (
        $row =
        mysqli_fetch_assoc(
            $history_result
        )
    ) {

?>

<tr>


<td>

<?php

echo date(
    "d M Y",
    strtotime(
        $row["attendance_date"]
    )
);

?>

</td>


<td>

<?php

echo date(
    "l",
    strtotime(
        $row["attendance_date"]
    )
);

?>

</td>


<td>

<strong>

<?php

echo htmlspecialchars(
    $row["subject_name"]
    ?: $row["subject_code"]
);

?>

</strong>

</td>


<td>

<?php

echo htmlspecialchars(
    $row["subject_code"]
);

?>

</td>


<td>

P<?php
echo intval(
    $row["start_period"]
);
?>

-

P<?php
echo intval(
    $row["end_period"]
);
?>

</td>


<td>

<?php

if (
    $row["status"] == "Present"
) {

?>

<span class="attendance-present">

✓ Present

</span>

<?php

} else {

?>

<span class="attendance-absent">

✕ Absent

</span>

<?php

}

?>

</td>

</tr>

<?php

    }

} else {

?>

<tr>

<td
    colspan="6"
    class="student-no-records"
>

📭 No attendance records available.

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>


</div>

</div>

</body>

</html>