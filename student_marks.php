<?php

session_start();
include "db.php";


/* =========================================================
   STUDENT LOGIN PROTECTION
========================================================= */

if (
    !isset($_SESSION["student_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "student"
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
        name,
        registration_no,
        roll_no,
        department,
        section,
        year,
        semester
     FROM students
     WHERE id = ?
     LIMIT 1"
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
   GET STUDENT MARKS
========================================================= */

$marks_stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        subject,
        internal_marks,
        external_marks,
        total_marks,
        grade,
        result
     FROM marks
     WHERE student_id = ?
     ORDER BY subject ASC"
);

mysqli_stmt_bind_param(
    $marks_stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($marks_stmt);

$marks_result =
    mysqli_stmt_get_result($marks_stmt);


/* =========================================================
   MARKS SUMMARY
========================================================= */

$summary_stmt = mysqli_prepare(
    $conn,
    "SELECT

        COUNT(*) AS total_subjects,

        COALESCE(
            ROUND(AVG(total_marks), 2),
            0
        ) AS average_marks,

        COALESCE(
            MAX(total_marks),
            0
        ) AS highest_marks,

        COALESCE(
            MIN(total_marks),
            0
        ) AS lowest_marks,

        COALESCE(
            SUM(
                CASE
                    WHEN LOWER(result) = 'pass'
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS passed_subjects,

        COALESCE(
            SUM(
                CASE
                    WHEN LOWER(result) = 'fail'
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS failed_subjects

     FROM marks
     WHERE student_id = ?"
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


$total_subjects =
    intval($summary["total_subjects"] ?? 0);

$average_marks =
    floatval($summary["average_marks"] ?? 0);

$highest_marks =
    intval($summary["highest_marks"] ?? 0);

$lowest_marks =
    intval($summary["lowest_marks"] ?? 0);

$passed_subjects =
    intval($summary["passed_subjects"] ?? 0);

$failed_subjects =
    intval($summary["failed_subjects"] ?? 0);


/* =========================================================
   OVERALL GRADE
   SAME GRADING SYSTEM AS ADMIN marks.php
========================================================= */

if ($total_subjects === 0) {

    $overall_grade = "N/A";

} elseif ($average_marks >= 90) {

    $overall_grade = "O";

} elseif ($average_marks >= 80) {

    $overall_grade = "A+";

} elseif ($average_marks >= 70) {

    $overall_grade = "A";

} elseif ($average_marks >= 60) {

    $overall_grade = "B+";

} elseif ($average_marks >= 50) {

    $overall_grade = "B";

} elseif ($average_marks >= 40) {

    $overall_grade = "C";

} else {

    $overall_grade = "F";
}


/* =========================================================
   OVERALL RESULT
========================================================= */

if ($total_subjects === 0) {

    $overall_result = "Awaiting Results";

} elseif ($failed_subjects > 0) {

    $overall_result = "Needs Improvement";

} else {

    $overall_result = "All Subjects Passed";
}


/* =========================================================
   PERFORMANCE MESSAGE
========================================================= */

if ($total_subjects === 0) {

    $performance_message =
        "Your academic results will appear here once marks are published.";

} elseif ($average_marks >= 90) {

    $performance_message =
        "Outstanding academic performance. Keep maintaining this consistency.";

} elseif ($average_marks >= 75) {

    $performance_message =
        "Very good academic performance. You are maintaining a strong average.";

} elseif ($average_marks >= 60) {

    $performance_message =
        "Good progress. Continue improving your performance across all subjects.";

} elseif ($average_marks >= 40) {

    $performance_message =
        "You are progressing, but there is room to strengthen your overall score.";

} else {

    $performance_message =
        "Focus on the subjects requiring improvement and work toward a stronger result.";
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
My Marks & Grades - EduTrack
</title>

<link
    rel="stylesheet"
    href="css/style.css"
>

</head>


<body class="student-dashboard-v2-page">


<!-- =========================================================
     NAVBAR
========================================================= -->

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


<!-- =========================================================
     SIDEBAR
========================================================= -->

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

<span>
🏠
</span>

Dashboard

</a>

</div>



<div class="sidebar-section-title">
MY ACADEMICS
</div>


<div class="sidebar-group">


<a href="student_attendance.php">

<span>
📅
</span>

My Attendance

</a>


<a
    href="student_marks.php"
    class="active"
>

<span>
📝
</span>

My Marks & Grades

</a>


</div>



<div class="sidebar-section-title">
ACCOUNT
</div>


<div class="sidebar-group sidebar-account">

<a href="student_logout.php">

<span>
🚪
</span>

Logout

</a>

</div>


</div>



<!-- =========================================================
     MAIN CONTENT
========================================================= -->

<main class="main-content dashboard-v2">


<!-- =========================================================
     MARKS HERO
========================================================= -->

<section class="marks-v2-hero dashboard-reveal">


<div class="marks-v2-hero-copy">


<span class="dashboard-eyebrow">

ACADEMIC PERFORMANCE

</span>


<h1>

My Marks & Grades

</h1>


<p>

Review your subject-wise marks,
grades, results and overall academic
performance in one place.

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



<!-- HERO SCORE -->

<div class="marks-hero-score">


<div class="marks-grade-circle">


<span>
OVERALL GRADE
</span>


<strong>

<?php
echo htmlspecialchars(
    $overall_grade
);
?>

</strong>


<small>

Average

<?php
echo number_format(
    $average_marks,
    2
);
?>%

</small>


</div>



<?php if ($total_subjects === 0) { ?>


<span class="marks-hero-neutral">

Results Pending

</span>


<?php } elseif ($failed_subjects === 0) { ?>


<span class="marks-hero-success">

✓ All Subjects Passed

</span>


<?php } else { ?>


<span class="marks-hero-warning">

⚠ Improvement Required

</span>


<?php } ?>


</div>


</section>



<!-- =========================================================
     STUDENT INFORMATION
========================================================= -->

<section class="marks-student-info dashboard-reveal">


<div class="marks-student-avatar">

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


<div class="marks-student-primary">

<span>
STUDENT
</span>

<strong>

<?php
echo htmlspecialchars(
    $student["name"]
);
?>

</strong>

<small>

<?php
echo htmlspecialchars(
    $student["registration_no"]
);
?>

</small>

</div>



<div class="marks-student-detail">

<span>
Roll Number
</span>

<strong>

<?php
echo htmlspecialchars(
    $student["roll_no"]
);
?>

</strong>

</div>



<div class="marks-student-detail">

<span>
Department
</span>

<strong>

<?php
echo htmlspecialchars(
    $student["department"]
);
?>

</strong>

</div>



<div class="marks-student-detail">

<span>
Semester
</span>

<strong>

<?php
echo htmlspecialchars(
    $student["semester"]
);
?>

</strong>

</div>


</section>



<!-- =========================================================
     PERFORMANCE OVERVIEW
========================================================= -->

<section class="dashboard-v2-section dashboard-reveal">


<div class="dashboard-v2-heading">


<div>

<span class="dashboard-eyebrow dark">

PERFORMANCE OVERVIEW

</span>


<h2>

Academic performance at a glance

</h2>

</div>


<span class="live-data-label">

<i></i>

Published Results

</span>


</div>



<div class="student-v2-metric-grid">


<!-- SUBJECTS -->

<article class="student-v2-metric-card">


<div class="metric-top">

<span class="metric-icon">
📚
</span>

<span class="metric-tag">
Subjects
</span>

</div>


<strong>

<?php
echo $total_subjects;
?>

</strong>


<p>
Published subjects
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
echo number_format(
    $average_marks,
    2
);
?>%

</strong>


<p>
Overall academic average
</p>


</article>



<!-- HIGHEST -->

<article class="student-v2-metric-card">


<div class="metric-top">

<span class="metric-icon">
🏆
</span>

<span class="metric-tag">
Highest
</span>

</div>


<strong>

<?php
echo $highest_marks;
?>

</strong>


<p>
Highest score out of 100
</p>


</article>



<!-- GRADE -->

<article class="student-v2-metric-card">


<div class="metric-top">

<span class="metric-icon">
🎓
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



<!-- =========================================================
     ACADEMIC SUMMARY
========================================================= -->

<section class="student-dashboard-focus-grid dashboard-reveal">


<!-- PERFORMANCE -->

<article class="dashboard-v2-card">


<div class="card-title-row">


<div>

<span class="dashboard-eyebrow dark">

RESULT SUMMARY

</span>


<h2>
Academic Summary
</h2>

</div>


</div>



<div class="marks-result-stat-grid">


<div class="marks-result-stat">

<span>
✓
</span>

<div>

<strong>
<?php echo $passed_subjects; ?>
</strong>

<small>
Subjects Passed
</small>

</div>

</div>



<div class="marks-result-stat">

<span>
!
</span>

<div>

<strong>
<?php echo $failed_subjects; ?>
</strong>

<small>
Subjects Failed
</small>

</div>

</div>



<div class="marks-result-stat">

<span>
↑
</span>

<div>

<strong>
<?php echo $highest_marks; ?>
</strong>

<small>
Highest Mark
</small>

</div>

</div>



<div class="marks-result-stat">

<span>
↓
</span>

<div>

<strong>
<?php echo $lowest_marks; ?>
</strong>

<small>
Lowest Mark
</small>

</div>

</div>


</div>


</article>



<!-- PERFORMANCE STATUS -->

<article class="dashboard-v2-card">


<div class="card-title-row">


<div>

<span class="dashboard-eyebrow dark">

ACADEMIC STATUS

</span>


<h2>
Performance Status
</h2>

</div>


</div>



<?php if ($total_subjects === 0) { ?>


<div class="marks-v2-status neutral">


<div class="marks-status-icon">
ℹ
</div>


<div>

<strong>
Results Awaiting Publication
</strong>

<p>

No marks have been published
for your account yet.

</p>

</div>


</div>


<?php } elseif ($failed_subjects === 0) { ?>


<div class="marks-v2-status success">


<div class="marks-status-icon">
✓
</div>


<div>

<strong>
Excellent — All Subjects Passed
</strong>

<p>

<?php
echo htmlspecialchars(
    $performance_message
);
?>

</p>

</div>


</div>


<?php } else { ?>


<div class="marks-v2-status warning">


<div class="marks-status-icon">
!
</div>


<div>

<strong>
Academic Attention Required
</strong>

<p>

<?php
echo htmlspecialchars(
    $performance_message
);
?>

</p>

</div>


</div>


<?php } ?>



<div class="marks-overall-result">


<span>
Overall Status
</span>

<strong>

<?php
echo htmlspecialchars(
    $overall_result
);
?>

</strong>


</div>


</article>


</section>



<!-- =========================================================
     SCORE RANGE
========================================================= -->

<?php if ($total_subjects > 0) { ?>


<section class="dashboard-v2-section dashboard-reveal">


<div class="dashboard-v2-heading">


<div>

<span class="dashboard-eyebrow dark">

SCORE RANGE

</span>


<h2>
Your performance range
</h2>

</div>


</div>



<div class="marks-score-range-card">


<div class="marks-range-item">


<span>
Lowest Score
</span>


<strong>

<?php
echo $lowest_marks;
?>

<small>/100</small>

</strong>


</div>



<div class="marks-range-line">


<div
    class="marks-range-progress"
    style="width:
    <?php
    echo min(
        100,
        max(
            0,
            $average_marks
        )
    );
    ?>%;"
>
</div>


</div>



<div class="marks-range-item right">


<span>
Highest Score
</span>


<strong>

<?php
echo $highest_marks;
?>

<small>/100</small>

</strong>


</div>


</div>


</section>


<?php } ?>



<!-- =========================================================
     SUBJECT-WISE RESULTS
========================================================= -->

<section class="dashboard-v2-section dashboard-reveal">


<div class="dashboard-v2-heading">


<div>

<span class="dashboard-eyebrow dark">

DETAILED RESULTS

</span>


<h2>
Subject-wise Results
</h2>


<p class="marks-section-description">

Detailed internal, external,
total marks, grade and result
for every published subject.

</p>


</div>


<span class="marks-record-count">

<?php
echo $total_subjects;
?>

Subjects

</span>


</div>



<div class="dashboard-v2-table-card">


<table class="modern-table marks-history-v2">


<thead>


<tr>

<th>
Subject
</th>

<th>
Internal
</th>

<th>
External
</th>

<th>
Total
</th>

<th>
Grade
</th>

<th>
Result
</th>

</tr>


</thead>


<tbody>


<?php


if (
    $marks_result &&
    mysqli_num_rows($marks_result) > 0
) {


while (
    $row =
    mysqli_fetch_assoc($marks_result)
) {


$total_mark =
    intval(
        $row["total_marks"]
    );


$internal_mark =
    intval(
        $row["internal_marks"]
    );


$external_mark =
    intval(
        $row["external_marks"]
    );


$is_pass =
    strtolower(
        $row["result"]
    ) === "pass";


?>


<tr>


<!-- SUBJECT -->

<td>


<div class="marks-subject-cell">


<div class="marks-subject-icon">

<?php

echo strtoupper(
    substr(
        $row["subject"],
        0,
        1
    )
);

?>

</div>


<div>

<strong>

<?php
echo htmlspecialchars(
    $row["subject"]
);
?>

</strong>

<span>
Academic Subject
</span>

</div>


</div>


</td>



<!-- INTERNAL -->

<td>


<div class="marks-score-cell">

<strong>

<?php
echo $internal_mark;
?>

</strong>

<span>
/ 30
</span>

</div>


<div class="marks-mini-track">

<div
    style="width:
    <?php

    echo min(
        100,
        ($internal_mark / 30) * 100
    );

    ?>%;"
>
</div>

</div>


</td>



<!-- EXTERNAL -->

<td>


<div class="marks-score-cell">

<strong>

<?php
echo $external_mark;
?>

</strong>

<span>
/ 70
</span>

</div>


<div class="marks-mini-track">

<div
    style="width:
    <?php

    echo min(
        100,
        ($external_mark / 70) * 100
    );

    ?>%;"
>
</div>

</div>


</td>



<!-- TOTAL -->

<td>


<div class="marks-total-score">

<strong>

<?php
echo $total_mark;
?>

</strong>

<span>
/100
</span>

</div>


</td>



<!-- GRADE -->

<td>


<span
    class="
    marks-grade-badge
    <?php

    echo
        strtolower(
            str_replace(
                "+",
                "plus",
                $row["grade"]
            )
        );

    ?>
    "
>

<?php
echo htmlspecialchars(
    $row["grade"]
);
?>

</span>


</td>



<!-- RESULT -->

<td>


<?php if ($is_pass) { ?>


<span class="student-status-chip present">

✓ PASS

</span>


<?php } else { ?>


<span class="student-status-chip absent">

✕ FAIL

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
    colspan="6"
    class="dashboard-v2-empty"
>

📭 No marks have been published yet.

</td>


</tr>


<?php

}

?>


</tbody>


</table>


</div>


</section>



<!-- =========================================================
     GRADING SCALE
========================================================= -->

<section class="dashboard-v2-section dashboard-reveal">


<div class="dashboard-v2-heading">


<div>

<span class="dashboard-eyebrow dark">

GRADING SYSTEM

</span>


<h2>
EduTrack Grade Scale
</h2>

</div>


</div>



<div class="grade-scale-grid">


<div>

<strong>O</strong>

<span>
90 – 100
</span>

<small>
Outstanding
</small>

</div>


<div>

<strong>A+</strong>

<span>
80 – 89
</span>

<small>
Excellent
</small>

</div>


<div>

<strong>A</strong>

<span>
70 – 79
</span>

<small>
Very Good
</small>

</div>


<div>

<strong>B+</strong>

<span>
60 – 69
</span>

<small>
Good
</small>

</div>


<div>

<strong>B</strong>

<span>
50 – 59
</span>

<small>
Above Average
</small>

</div>


<div>

<strong>C</strong>

<span>
40 – 49
</span>

<small>
Pass
</small>

</div>


<div>

<strong>F</strong>

<span>
Below 40
</span>

<small>
Fail
</small>

</div>


</div>


</section>



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