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

mysqli_stmt_bind_param($student_stmt, "i", $student_id);
mysqli_stmt_execute($student_stmt);

$student_result = mysqli_stmt_get_result($student_stmt);
$student = mysqli_fetch_assoc($student_result);

mysqli_stmt_close($student_stmt);

if (!$student) {
    session_destroy();
    header("Location: student_login.php");
    exit();
}


/* =========================================================
   OVERALL ATTENDANCE
========================================================= */

$summary_stmt = mysqli_prepare(
    $conn,
    "SELECT
        COUNT(*) AS total,

        SUM(
            CASE
                WHEN ar.status = 'Present'
                THEN 1 ELSE 0
            END
        ) AS present,

        SUM(
            CASE
                WHEN ar.status = 'Absent'
                THEN 1 ELSE 0
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

mysqli_stmt_close($summary_stmt);


$total =
    intval($summary["total"] ?? 0);

$present =
    intval($summary["present"] ?? 0);

$absent =
    intval($summary["absent"] ?? 0);

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
                THEN 1 ELSE 0
            END
        ) AS present_classes,

        SUM(
            CASE
                WHEN ar.status = 'Absent'
                THEN 1 ELSE 0
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
   ATTENDANCE HISTORY
========================================================= */

$history_stmt = mysqli_prepare(
    $conn,
    "SELECT
        ats.attendance_date,
        ats.subject_code,
        ats.subject_name,
        ats.start_period,
        ats.end_period,
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


<body class="student-dashboard-v2-page">


<!-- =====================================================
     NAVBAR
===================================================== -->

<div class="navbar">


<div class="dashboard-nav-brand">

<span>🎓</span>

<div>

<strong>
EduTrack
</strong>

<small>
Student Academic Portal
</small>

</div>

</div>



<div class="dashboard-nav-user">


<div class="dashboard-admin-avatar">

<?php

echo strtoupper(
    substr(
        $student["name"],
        0,
        1
    )
);

?>

</div>


<div class="dashboard-admin-copy">

<strong>

<?php
echo htmlspecialchars(
    $student["name"]
);
?>

</strong>

<small>
Student
</small>

</div>


<a
    href="student_logout.php"
    class="logout-btn"
>
Logout
</a>


</div>

</div>



<div class="main-layout">


<!-- =====================================================
     SIDEBAR
===================================================== -->

<div class="sidebar edutrack-sidebar student-v2-sidebar">


<div class="sidebar-brand-block">

<div class="sidebar-brand-icon">
🎓
</div>

<div>

<strong>
EduTrack
</strong>

<small>
Student Portal
</small>

</div>

</div>



<div class="sidebar-section-title">
OVERVIEW
</div>


<div class="sidebar-group">

<a href="student_dashboard.php">

<span>🏠</span>

Dashboard

</a>

</div>



<div class="sidebar-section-title">
MY ACADEMICS
</div>


<div class="sidebar-group">


<a
    href="student_attendance.php"
    class="active"
>

<span>📅</span>

My Attendance

</a>


<a href="student_marks.php">

<span>📝</span>

My Marks & Grades

</a>


</div>



<div class="sidebar-section-title">
ACCOUNT
</div>


<div class="sidebar-group sidebar-account">

<a href="student_logout.php">

<span>🚪</span>

Logout

</a>

</div>


</div>



<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<main class="main-content dashboard-v2">


<!-- =====================================================
     ATTENDANCE HERO
===================================================== -->

<section
    class="attendance-v2-hero dashboard-reveal"
>


<div class="attendance-v2-hero-copy">


<span class="dashboard-eyebrow">

ATTENDANCE CENTER

</span>


<h1>

My Attendance

</h1>


<p>

Track your class participation,
subject-wise attendance and complete
academic attendance history.

</p>



<div class="student-hero-meta">


<span>

🎓

<?php

echo htmlspecialchars(
    $student["registration_no"]
);

?>

</span>


<span>

🏛

<?php

echo htmlspecialchars(
    $student["department"]
);

?>

</span>


<span>

Section

<?php

echo htmlspecialchars(
    $student["section"]
);

?>

</span>


<span>

<?php

echo htmlspecialchars(
    $student["year"]
);

?>

</span>


<span>

Semester

<?php

echo htmlspecialchars(
    $student["semester"]
);

?>

</span>


</div>


</div>



<!-- HERO ATTENDANCE RING -->

<div class="attendance-hero-score">


<div
    class="attendance-large-ring"

    style="--attendance:
    <?php

    echo min(
        100,
        max(
            0,
            $percentage
        )
    );

    ?>;"
>


<div>

<strong>

<?php
echo $percentage;
?>%

</strong>

<span>

Overall Attendance

</span>

</div>


</div>



<?php

if ($total == 0) {

?>


<span class="attendance-hero-neutral">

No records yet

</span>


<?php

} elseif ($percentage >= 75) {

?>


<span class="attendance-hero-safe">

✓ Requirement Met

</span>


<?php

} else {

?>


<span class="attendance-hero-danger">

⚠ Attendance Shortage

</span>


<?php

}

?>


</div>


</section>



<!-- =====================================================
     SUMMARY
===================================================== -->

<section
    class="dashboard-v2-section dashboard-reveal"
>


<div class="dashboard-v2-heading">


<div>

<span class="dashboard-eyebrow dark">

OVERVIEW

</span>


<h2>

Attendance at a glance

</h2>

</div>


<span class="live-data-label">

<i></i>

Updated records

</span>


</div>



<div class="student-v2-metric-grid">


<!-- TOTAL -->

<article class="student-v2-metric-card">


<div class="metric-top">

<span class="metric-icon">
📚
</span>

<span class="metric-tag">
Classes
</span>

</div>


<strong>

<?php
echo $total;
?>

</strong>


<p>
Total recorded classes
</p>


</article>



<!-- PRESENT -->

<article class="student-v2-metric-card">


<div class="metric-top">

<span class="metric-icon">
✓
</span>

<span class="metric-tag">
Present
</span>

</div>


<strong>

<?php
echo $present;
?>

</strong>


<p>
Classes attended
</p>


</article>



<!-- ABSENT -->

<article class="student-v2-metric-card">


<div class="metric-top">

<span class="metric-icon">
✕
</span>

<span class="metric-tag">
Absent
</span>

</div>


<strong>

<?php
echo $absent;
?>

</strong>


<p>
Classes missed
</p>


</article>



<!-- PERCENTAGE -->

<article class="student-v2-metric-card">


<div class="metric-top">

<span class="metric-icon">
📈
</span>

<span class="metric-tag">
Attendance
</span>

</div>


<strong>

<?php
echo $percentage;
?>%

</strong>


<p>

<?php

if ($total == 0) {

    echo "Awaiting attendance records";

} elseif ($percentage >= 75) {

    echo "You are currently eligible";

} else {

    echo "Below required 75%";
}

?>

</p>


</article>


</div>


</section>



<!-- =====================================================
     PROGRESS + STATUS
===================================================== -->

<section
    class="student-dashboard-focus-grid dashboard-reveal"
>


<article class="dashboard-v2-card">


<div class="card-title-row">


<div>

<span class="dashboard-eyebrow dark">

PROGRESS

</span>


<h2>

Overall attendance progress

</h2>

</div>


<strong class="attendance-big-number">

<?php
echo $percentage;
?>%

</strong>


</div>



<div class="attendance-v2-progress-track">


<div
    class="attendance-v2-progress-fill"

    style="width:
    <?php

    echo min(
        $percentage,
        100
    );

    ?>%;"
>

</div>


</div>



<div class="attendance-progress-labels">

<span>
0%
</span>

<span>
Required: 75%
</span>

<span>
100%
</span>

</div>



<div class="attendance-v2-breakdown">


<div>

<span>
Total
</span>

<strong>
<?php echo $total; ?>
</strong>

</div>


<div>

<span>
Present
</span>

<strong>
<?php echo $present; ?>
</strong>

</div>


<div>

<span>
Absent
</span>

<strong>
<?php echo $absent; ?>
</strong>

</div>


</div>


</article>



<!-- STATUS -->

<article class="dashboard-v2-card">


<div class="card-title-row">


<div>

<span class="dashboard-eyebrow dark">

ATTENDANCE STATUS

</span>


<h2>

Eligibility status

</h2>

</div>


</div>



<?php

if ($total == 0) {

?>


<div class="attendance-v2-status neutral">


<div class="attendance-status-icon">
ℹ
</div>


<div>

<strong>
Attendance pending
</strong>

<p>

No attendance records have been
published for your account yet.

</p>

</div>


</div>


<?php

} elseif ($percentage >= 75) {

?>


<div class="attendance-v2-status safe">


<div class="attendance-status-icon">
✓
</div>


<div>

<strong>
Attendance requirement met
</strong>

<p>

Your current attendance is

<b>
<?php echo $percentage; ?>%
</b>.

You are above the required
75% attendance level.

</p>

</div>


</div>


<?php

} else {

?>


<div class="attendance-v2-status danger">


<div class="attendance-status-icon">
!
</div>


<div>

<strong>
Attendance shortage
</strong>

<p>

Your current attendance is

<b>
<?php echo $percentage; ?>%
</b>.

You need to improve your attendance
to reach the required 75%.

</p>

</div>


</div>


<?php

}

?>


<a
    href="#subjectAttendance"
    class="panel-link-button"
>

Check Subject-wise Attendance ↓

</a>


</article>


</section>



<!-- =====================================================
     SUBJECT-WISE ATTENDANCE
===================================================== -->

<section
    id="subjectAttendance"
    class="dashboard-v2-section dashboard-reveal"
>


<div class="dashboard-v2-heading">


<div>

<span class="dashboard-eyebrow dark">

SUBJECT ANALYSIS

</span>


<h2>

Subject-wise attendance

</h2>


<p class="attendance-section-description">

See exactly where your attendance
is strong and where improvement is needed.

</p>


</div>


</div>



<div class="subject-attendance-grid">


<?php


if (
    mysqli_num_rows(
        $subject_result
    ) > 0
) {


while (
    $subject =
    mysqli_fetch_assoc(
        $subject_result
    )
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
            (
                $subject_present /
                $subject_total
            ) * 100,
            2
        )
        : 0;


?>


<article class="subject-attendance-card">


<div class="subject-card-top">


<div>


<span class="subject-code">

<?php

echo htmlspecialchars(
    $subject["subject_code"]
);

?>

</span>


<h3>

<?php

echo htmlspecialchars(
    $subject["subject_name"]
    ?: $subject["subject_code"]
);

?>

</h3>


</div>



<?php

if ($subject_percentage >= 75) {

?>


<span class="subject-eligible">

✓ Eligible

</span>


<?php

} else {

?>


<span class="subject-shortage">

⚠ Shortage

</span>


<?php

}

?>


</div>



<div class="subject-percentage-row">


<strong>

<?php
echo $subject_percentage;
?>%

</strong>


<span>

<?php
echo $subject_present;
?>

/

<?php
echo $subject_total;
?>

classes attended

</span>


</div>



<div class="subject-progress-track">


<div
    class="
    subject-progress-fill
    <?php

    echo
        $subject_percentage >= 75
        ? "safe"
        : "danger";

    ?>
    "

    style="width:
    <?php

    echo min(
        $subject_percentage,
        100
    );

    ?>%;"
>

</div>


</div>



<div class="subject-card-stats">


<div>

<span>
Present
</span>

<strong>
<?php echo $subject_present; ?>
</strong>

</div>


<div>

<span>
Absent
</span>

<strong>
<?php echo $subject_absent; ?>
</strong>

</div>


<div>

<span>
Total
</span>

<strong>
<?php echo $subject_total; ?>
</strong>

</div>


</div>


</article>


<?php

}


} else {

?>


<div class="attendance-empty-card">

<div>
📭
</div>

<h3>
No attendance records yet
</h3>

<p>

Subject-wise attendance will appear
here once your faculty records attendance.

</p>

</div>


<?php

}

?>


</div>


</section>



<!-- =====================================================
     ATTENDANCE HISTORY
===================================================== -->

<section
    class="dashboard-v2-section dashboard-reveal"
>


<div class="dashboard-v2-heading">


<div>

<span class="dashboard-eyebrow dark">

HISTORY

</span>


<h2>

Attendance timeline

</h2>

</div>


<span class="attendance-record-count">

<?php
echo $total;
?>

Records

</span>


</div>



<div class="dashboard-v2-table-card">


<table class="modern-table attendance-history-v2">


<thead>


<tr>

<th>
Date
</th>

<th>
Subject
</th>

<th>
Subject Code
</th>

<th>
Period
</th>

<th>
Status
</th>

</tr>


</thead>



<tbody>


<?php


if (
    mysqli_num_rows(
        $history_result
    ) > 0
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


<div class="attendance-date-cell">


<div class="attendance-date-icon">

<?php

echo date(
    "d",
    strtotime(
        $row["attendance_date"]
    )
);

?>

</div>


<div>

<strong>

<?php

echo date(
    "d M Y",
    strtotime(
        $row["attendance_date"]
    )
);

?>

</strong>


<span>

<?php

echo date(
    "l",
    strtotime(
        $row["attendance_date"]
    )
);

?>

</span>


</div>


</div>


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


<span class="class-pill">

<?php

echo htmlspecialchars(
    $row["subject_code"]
);

?>

</span>


</td>



<td>


<span class="period-pill">

P<?php
echo intval(
    $row["start_period"]
);
?>


<?php

if (
    $row["start_period"]
    !=
    $row["end_period"]
) {

?>


–

P<?php
echo intval(
    $row["end_period"]
);
?>


<?php

}

?>


</span>


</td>



<td>


<?php

if (
    $row["status"]
    == "Present"
) {

?>


<span class="student-status-chip present">

✓ Present

</span>


<?php

} else {

?>


<span class="student-status-chip absent">

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
    colspan="5"
    class="dashboard-v2-empty"
>

📭 No attendance history available.

</td>


</tr>


<?php

}

?>


</tbody>


</table>


</div>


</section>



<!-- BACK -->

<div class="attendance-back-area dashboard-reveal">


<a
    href="student_dashboard.php"
    class="attendance-back-button"
>

← Back to Dashboard

</a>


</div>


</main>


</div>


<script src="js/script.js"></script>


</body>

</html>