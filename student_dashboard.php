<?php

session_start();
include "db.php";


/* =========================================================
   PROTECT STUDENT PAGE
========================================================= */

if (
    !isset($_SESSION["student_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] != "student"
) {
    header("Location: student_login.php");
    exit();
}


/* =========================================================
   CURRENT STUDENT
========================================================= */

$student_id = intval($_SESSION["student_id"]);


/* =========================================================
   GET STUDENT INFORMATION
========================================================= */

$student_stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        name,
        roll_no,
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

mysqli_stmt_close($student_stmt);


if (!$student) {

    session_destroy();

    header("Location: student_login.php");

    exit();
}


/* =========================================================
   NEW ATTENDANCE SUMMARY

   Uses:
   attendance_records
   attendance_sessions
========================================================= */

$attendance_stmt = mysqli_prepare(
    $conn,
    "SELECT

        COUNT(ar.id) AS total_classes,

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

     WHERE ar.student_id = ?"
);


mysqli_stmt_bind_param(
    $attendance_stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($attendance_stmt);

$attendance_result =
    mysqli_stmt_get_result($attendance_stmt);

$attendance =
    mysqli_fetch_assoc($attendance_result);

mysqli_stmt_close($attendance_stmt);


$total_classes =
    intval($attendance["total_classes"] ?? 0);

$present_classes =
    intval($attendance["present_classes"] ?? 0);

$absent_classes =
    intval($attendance["absent_classes"] ?? 0);


if ($total_classes > 0) {

    $attendance_percentage =
        round(
            ($present_classes / $total_classes) * 100,
            2
        );

} else {

    $attendance_percentage = 0;
}


/* =========================================================
   MARKS SUMMARY
========================================================= */

$marks_stmt = mysqli_prepare(
    $conn,
    "SELECT
        COUNT(*) AS total_subjects,
        ROUND(AVG(total_marks), 2) AS average_marks
     FROM marks
     WHERE student_id = ?"
);


mysqli_stmt_bind_param(
    $marks_stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($marks_stmt);

$marks_result =
    mysqli_stmt_get_result($marks_stmt);

$marks_summary =
    mysqli_fetch_assoc($marks_result);

mysqli_stmt_close($marks_stmt);


$total_subjects =
    intval(
        $marks_summary["total_subjects"]
        ?? 0
    );

$average_marks =
    $marks_summary["average_marks"]
    ?? 0;


/* =========================================================
   OVERALL GRADE
========================================================= */

if ($total_subjects == 0) {

    $overall_grade = "N/A";

} elseif ($average_marks >= 90) {

    $overall_grade = "A+";

} elseif ($average_marks >= 80) {

    $overall_grade = "A";

} elseif ($average_marks >= 70) {

    $overall_grade = "B";

} elseif ($average_marks >= 60) {

    $overall_grade = "C";

} elseif ($average_marks >= 50) {

    $overall_grade = "D";

} else {

    $overall_grade = "F";
}

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
Student Dashboard - EduTrack
</title>

<link
    rel="stylesheet"
    href="css/style.css"
>

<style>

.attendance-safe {
    color: #198754;
    font-weight: bold;
}

.attendance-danger {
    color: #dc3545;
    font-weight: bold;
}

.attendance-neutral {
    color: #6c757d;
    font-weight: bold;
}

</style>

</head>


<body>


<!-- NAVBAR -->

<div class="navbar">

<div>

<h2>
🎓 EduTrack
</h2>

<small>
Student Portal
</small>

</div>


<div class="student-nav-user">

<span>

👤

<?php
echo htmlspecialchars(
    $student["name"]
);
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


<!-- STUDENT SIDEBAR -->

<div class="sidebar student-sidebar">

<h3>
STUDENT PORTAL
</h3>


<a
    href="student_dashboard.php"
    class="active"
>
🏠 Dashboard
</a>


<a href="student_attendance.php">
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


<!-- WELCOME -->

<div class="student-welcome-card">

<div>

<p class="welcome-label">
WELCOME BACK
</p>


<h1>

Hello,

<?php
echo htmlspecialchars(
    $student["name"]
);
?>

👋

</h1>


<span>

<?php
echo htmlspecialchars(
    $student["registration_no"]
);
?>

&nbsp; • &nbsp;

<?php
echo htmlspecialchars(
    $student["department"]
);
?>

&nbsp; • &nbsp;

Section

<?php
echo htmlspecialchars(
    $student["section"]
);
?>

&nbsp; • &nbsp;

<?php
echo htmlspecialchars(
    $student["year"]
);
?>

&nbsp; • &nbsp;

Semester

<?php
echo htmlspecialchars(
    $student["semester"]
);
?>

</span>

</div>



<div class="welcome-avatar">

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

</div>



<!-- SUMMARY CARDS -->

<div class="student-stat-grid">


<!-- ATTENDANCE -->

<div class="student-stat-card">

<div class="student-stat-icon">
📅
</div>

<p>
Attendance
</p>


<h2>

<?php
echo $attendance_percentage;
?>%

</h2>


<?php if ($total_classes == 0): ?>

<span class="attendance-neutral">
No Records
</span>


<?php elseif ($attendance_percentage >= 75): ?>

<span class="attendance-safe">
✓ Safe
</span>


<?php else: ?>

<span class="attendance-danger">
⚠ Below 75%
</span>

<?php endif; ?>

</div>



<!-- PRESENT -->

<div class="student-stat-card">

<div class="student-stat-icon">
✅
</div>

<p>
Classes Present
</p>

<h2>
<?php echo $present_classes; ?>
</h2>

<small>

Out of

<?php echo $total_classes; ?>

classes

</small>

</div>



<!-- ABSENT -->

<div class="student-stat-card">

<div class="student-stat-icon">
❌
</div>

<p>
Classes Absent
</p>

<h2>
<?php echo $absent_classes; ?>
</h2>

<small>
Subject-wise attendance
</small>

</div>



<!-- GRADE -->

<div class="student-stat-card">

<div class="student-stat-icon">
🏆
</div>

<p>
Overall Grade
</p>

<h2>

<?php
echo htmlspecialchars(
    $overall_grade
);
?>

</h2>

<small>

Average Marks:

<?php echo $average_marks; ?>%

</small>

</div>


</div>



<!-- ATTENDANCE OVERVIEW -->

<div class="student-dashboard-card">


<div class="card-title-row">

<div>

<h2>
📅 Attendance Overview
</h2>

<p>
Your period and subject-wise attendance
</p>

</div>


<a href="student_attendance.php">

View Full Attendance →

</a>

</div>



<div class="attendance-progress-info">


<strong>

<?php
echo $attendance_percentage;
?>%

</strong>


<span>

<?php echo $present_classes; ?>

Present

&nbsp; • &nbsp;

<?php echo $absent_classes; ?>

Absent

&nbsp; • &nbsp;

<?php echo $total_classes; ?>

Total

</span>

</div>



<div class="attendance-progress-track">

<div
    class="attendance-progress-fill"

    style="width:
    <?php
        echo min(
            $attendance_percentage,
            100
        );
    ?>%;"
>

</div>

</div>



<?php

if (
    $total_classes > 0 &&
    $attendance_percentage < 75
) {

?>

<div class="student-warning">

⚠ Your attendance is below the required
75%.

Please improve your attendance.

</div>

<?php

} elseif (
    $total_classes > 0 &&
    $attendance_percentage >= 75
) {

?>

<div class="student-success-note">

✓ Your attendance is currently
75% or above.

</div>

<?php

}

?>


</div>



<!-- QUICK ACCESS -->

<h2 class="student-section-heading">

Quick Access

</h2>


<div class="student-quick-grid">


<a
    href="student_attendance.php"
    class="student-quick-card"
>

<div class="quick-card-icon">
📅
</div>

<div>

<h3>
My Attendance
</h3>

<p>
View subject-wise attendance,
percentage and daily history.
</p>

<span>
View Attendance →
</span>

</div>

</a>



<a
    href="student_marks.php"
    class="student-quick-card"
>

<div class="quick-card-icon">
📝
</div>

<div>

<h3>
My Marks & Grades
</h3>

<p>
View your marks,
grades and results.
</p>

<span>
View Results →
</span>

</div>

</a>


</div>


</div>

</div>


</body>

</html>