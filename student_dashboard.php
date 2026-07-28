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
   ATTENDANCE SUMMARY
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


$attendance_percentage =
    $total_classes > 0
        ? round(
            ($present_classes / $total_classes) * 100,
            2
        )
        : 0;


/* =========================================================
   MARKS SUMMARY
========================================================= */

$marks_stmt = mysqli_prepare(
    $conn,
    "SELECT
        COUNT(*) AS total_subjects,
        ROUND(AVG(total_marks), 2) AS average_marks,
        MAX(total_marks) AS highest_marks,
        SUM(
            CASE
                WHEN result = 'Fail'
                THEN 1
                ELSE 0
            END
        ) AS failed_subjects
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
        $marks_summary["total_subjects"] ?? 0
    );

$average_marks =
    $marks_summary["average_marks"] ?? 0;

$highest_marks =
    $marks_summary["highest_marks"] ?? 0;

$failed_subjects =
    intval(
        $marks_summary["failed_subjects"] ?? 0
    );


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


/* =========================================================
   RECENT ATTENDANCE
========================================================= */

$recent_attendance_stmt = mysqli_prepare(
    $conn,
    "SELECT
        ats.attendance_date,
        ats.subject_name,
        ats.subject_code,
        ats.start_period,
        ats.end_period,
        ar.status

     FROM attendance_records ar

     INNER JOIN attendance_sessions ats
        ON ar.session_id = ats.id

     WHERE ar.student_id = ?

     ORDER BY
        ats.attendance_date DESC,
        ats.id DESC

     LIMIT 5"
);


mysqli_stmt_bind_param(
    $recent_attendance_stmt,
    "i",
    $student_id
);

mysqli_stmt_execute(
    $recent_attendance_stmt
);

$recent_attendance =
    mysqli_stmt_get_result(
        $recent_attendance_stmt
    );


/* =========================================================
   RECENT MARKS
========================================================= */

$recent_marks_stmt = mysqli_prepare(
    $conn,
    "SELECT
        subject,
        total_marks,
        grade,
        result

     FROM marks

     WHERE student_id = ?

     ORDER BY id DESC

     LIMIT 4"
);


mysqli_stmt_bind_param(
    $recent_marks_stmt,
    "i",
    $student_id
);

mysqli_stmt_execute(
    $recent_marks_stmt
);

$recent_marks =
    mysqli_stmt_get_result(
        $recent_marks_stmt
    );


/* =========================================================
   GREETING
========================================================= */

$hour = intval(date("H"));

if ($hour < 12) {

    $greeting = "Good Morning";

} elseif ($hour < 17) {

    $greeting = "Good Afternoon";

} else {

    $greeting = "Good Evening";
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

</head>


<body class="student-dashboard-v2-page">


<!-- =====================================================
     NAVBAR
===================================================== -->

<div class="navbar">


<div class="dashboard-nav-brand">

<span>
🎓
</span>

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
     STUDENT SIDEBAR
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

<a
    href="student_dashboard.php"
    class="active"
>

<span>🏠</span>

Dashboard

</a>

</div>



<div class="sidebar-section-title">
MY ACADEMICS
</div>


<div class="sidebar-group">


<a href="student_attendance.php">

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
     HERO
===================================================== -->

<section
    class="student-campus-hero dashboard-reveal"
>


<div class="student-campus-copy">


<span class="dashboard-eyebrow">

MY ACADEMIC SPACE

</span>


<h1>

<?php
echo $greeting;
?>,

<br>

<?php

echo htmlspecialchars(
    $student["name"]
);

?>.

</h1>


<p>

Stay updated with your attendance,
academic performance and latest results
through your personal EduTrack dashboard.

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



<div class="university-hero-actions">


<a
    href="student_attendance.php"
    class="hero-action-primary"
>

View Attendance

</a>


<a
    href="student_marks.php"
    class="hero-action-secondary"
>

View Results

</a>


</div>


</div>



<div class="student-hero-profile">


<div class="student-large-avatar">

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


<strong>

<?php

echo htmlspecialchars(
    $student["name"]
);

?>

</strong>


<span>

<?php

echo htmlspecialchars(
    $student["roll_no"]
);

?>

</span>


<small>

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

</small>


</div>


</section>



<!-- =====================================================
     OVERVIEW
===================================================== -->

<section
    class="dashboard-v2-section dashboard-reveal"
>


<div class="dashboard-v2-heading">


<div>

<span class="dashboard-eyebrow dark">

ACADEMIC OVERVIEW

</span>


<h2>

Your performance at a glance

</h2>

</div>


<span class="live-data-label">

<i></i>

Updated records

</span>


</div>



<div class="student-v2-metric-grid">


<!-- ATTENDANCE -->

<article class="student-v2-metric-card">


<div class="metric-top">

<span class="metric-icon">
📅
</span>

<span class="metric-tag">
Attendance
</span>

</div>


<strong>

<?php
echo $attendance_percentage;
?>%

</strong>


<p>

<?php

if ($total_classes == 0) {

    echo "No attendance recorded";

} elseif ($attendance_percentage >= 75) {

    echo "Attendance requirement met";

} else {

    echo "Attendance requires attention";
}

?>

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
echo $present_classes;
?>

</strong>


<p>

Out of

<?php
echo $total_classes;
?>

recorded classes

</p>


</article>



<!-- AVERAGE -->

<article class="student-v2-metric-card">


<div class="metric-top">

<span class="metric-icon">
📊
</span>

<span class="metric-tag">
Average
</span>

</div>


<strong>

<?php
echo $average_marks;
?>%

</strong>


<p>

Across

<?php
echo $total_subjects;
?>

published subject(s)

</p>


</article>



<!-- GRADE -->

<article class="student-v2-metric-card">


<div class="metric-top">

<span class="metric-icon">
🏆
</span>

<span class="metric-tag">
Grade
</span>

</div>


<strong>

<?php

echo htmlspecialchars(
    $overall_grade
);

?>

</strong>


<p>

Overall academic grade

</p>


</article>


</div>


</section>



<!-- =====================================================
     ATTENDANCE + STATUS
===================================================== -->

<section
    class="student-dashboard-focus-grid dashboard-reveal"
>


<!-- ATTENDANCE -->

<article
    class="dashboard-v2-card"
>


<div class="card-title-row">


<div>

<span class="dashboard-eyebrow dark">

ATTENDANCE

</span>


<h2>

Attendance progress

</h2>

</div>


<a href="student_attendance.php">

Full Attendance →

</a>


</div>



<div class="student-attendance-v2-content">


<div
    class="v2-attendance-ring"

    style="--attendance:
    <?php

    echo min(
        100,
        max(
            0,
            $attendance_percentage
        )
    );

    ?>;"
>


<div>

<strong>

<?php
echo $attendance_percentage;
?>%

</strong>

<span>
Overall
</span>

</div>


</div>



<div class="student-attendance-details">


<div>

<span>
Total Classes
</span>

<strong>
<?php echo $total_classes; ?>
</strong>

</div>


<div>

<span>
✓ Classes Present
</span>

<strong>
<?php echo $present_classes; ?>
</strong>

</div>


<div>

<span>
✕ Classes Absent
</span>

<strong>
<?php echo $absent_classes; ?>
</strong>

</div>


</div>


</div>


</article>



<!-- ACADEMIC STATUS -->

<article
    class="dashboard-v2-card student-status-panel"
>


<div class="card-title-row">


<div>

<span class="dashboard-eyebrow dark">

MY STATUS

</span>


<h2>

Academic health

</h2>

</div>


</div>



<?php

if ($total_classes == 0) {

?>


<div class="priority-item priority-neutral">

<div class="priority-icon">
ℹ
</div>

<div>

<strong>
Attendance pending
</strong>

<p>

Your attendance records have
not been published yet.

</p>

</div>

</div>


<?php

} elseif ($attendance_percentage < 75) {

?>


<div class="priority-item priority-warning">

<div class="priority-icon">
⚠
</div>

<div>

<strong>
Attendance shortage
</strong>

<p>

Your attendance is

<?php
echo $attendance_percentage;
?>%.

The minimum requirement is 75%.

</p>

</div>

</div>


<?php

} else {

?>


<div class="priority-item priority-success">

<div class="priority-icon">
✓
</div>

<div>

<strong>
Attendance on track
</strong>

<p>

You currently meet the
75% attendance requirement.

</p>

</div>

</div>


<?php

}


if ($failed_subjects > 0) {

?>


<div class="priority-item priority-warning">

<div class="priority-icon">
!
</div>

<div>

<strong>
Academic attention
</strong>

<p>

You currently have

<?php
echo $failed_subjects;
?>

failed subject(s).

</p>

</div>

</div>


<?php

} elseif ($total_subjects > 0) {

?>


<div class="priority-item priority-success">

<div class="priority-icon">
🏆
</div>

<div>

<strong>
Results on track
</strong>

<p>

No failed subjects are currently
recorded in your published results.

</p>

</div>

</div>


<?php

} else {

?>


<div class="priority-item priority-neutral">

<div class="priority-icon">
📝
</div>

<div>

<strong>
Results pending
</strong>

<p>

Your marks have not been
published yet.

</p>

</div>

</div>


<?php

}

?>


</article>


</section>



<!-- =====================================================
     RECENT ACTIVITY
===================================================== -->

<section
    class="student-dashboard-focus-grid dashboard-reveal"
>


<!-- RECENT ATTENDANCE -->

<article class="dashboard-v2-card">


<div class="card-title-row">


<div>

<span class="dashboard-eyebrow dark">

RECENT ACTIVITY

</span>


<h2>

Latest attendance

</h2>

</div>


<a href="student_attendance.php">

See All →

</a>


</div>



<div class="student-activity-list">


<?php


if (
    mysqli_num_rows(
        $recent_attendance
    ) > 0
) {


while (
    $record =
    mysqli_fetch_assoc(
        $recent_attendance
    )
) {


?>


<div class="student-activity-row">


<div class="activity-date-box">

<strong>

<?php

echo date(
    "d",
    strtotime(
        $record["attendance_date"]
    )
);

?>

</strong>


<span>

<?php

echo date(
    "M",
    strtotime(
        $record["attendance_date"]
    )
);

?>

</span>

</div>



<div class="activity-main">


<strong>

<?php

echo htmlspecialchars(
    $record["subject_name"]
);

?>

</strong>


<span>

<?php

echo htmlspecialchars(
    $record["subject_code"]
);

?>

• Period

<?php

echo intval(
    $record["start_period"]
);

?>


<?php

if (
    $record["start_period"]
    !=
    $record["end_period"]
) {

    echo "–" .
        intval(
            $record["end_period"]
        );
}

?>

</span>


</div>



<?php

if (
    $record["status"]
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


</div>


<?php

}


} else {

?>


<div class="student-empty-state">

📭 No attendance activity yet.

</div>


<?php

}

?>


</div>


</article>



<!-- RECENT RESULTS -->

<article class="dashboard-v2-card">


<div class="card-title-row">


<div>

<span class="dashboard-eyebrow dark">

RESULTS

</span>


<h2>

Recent performance

</h2>

</div>


<a href="student_marks.php">

All Results →

</a>


</div>



<div class="student-result-list">


<?php


if (
    mysqli_num_rows(
        $recent_marks
    ) > 0
) {


while (
    $mark =
    mysqli_fetch_assoc(
        $recent_marks
    )
) {


?>


<div class="student-result-row">


<div class="result-subject">


<strong>

<?php

echo htmlspecialchars(
    $mark["subject"]
);

?>

</strong>


<span>

<?php

echo htmlspecialchars(
    $mark["result"]
);

?>

</span>


</div>



<div class="result-score">

<strong>

<?php

echo htmlspecialchars(
    $mark["total_marks"]
);

?>

</strong>

<small>
/100
</small>

</div>



<span class="result-grade-chip">

<?php

echo htmlspecialchars(
    $mark["grade"]
);

?>

</span>


</div>


<?php

}


} else {

?>


<div class="student-empty-state">

📝 No academic results
have been published yet.

</div>


<?php

}

?>


</div>


</article>


</section>



<!-- =====================================================
     QUICK ACCESS
===================================================== -->

<section
    class="dashboard-v2-section dashboard-reveal"
>


<div class="dashboard-v2-heading">


<div>

<span class="dashboard-eyebrow dark">

QUICK ACCESS

</span>


<h2>

Continue to your academics

</h2>

</div>


</div>



<div class="student-v2-action-grid">


<a href="student_attendance.php">


<div class="student-action-icon">

📅

</div>


<div>

<strong>

My Attendance

</strong>

<small>

View subject and period-wise
attendance records.

</small>

</div>


<b>
→
</b>


</a>



<a href="student_marks.php">


<div class="student-action-icon">

📝

</div>


<div>

<strong>

My Marks & Grades

</strong>

<small>

View marks, grades and
academic performance.

</small>

</div>


<b>
→
</b>


</a>


</div>


</section>


</main>


</div>


<script src="js/script.js"></script>


</body>

</html>