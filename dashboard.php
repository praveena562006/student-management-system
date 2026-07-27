<?php

session_start();

include "db.php";


/* =========================================================
   ADMIN LOGIN PROTECTION
========================================================= */

if (!isset($_SESSION["admin"])) {

    header("Location: login.php");
    exit();
}


/* =========================================================
   TODAY
========================================================= */

$today = date("Y-m-d");


/* =========================================================
   TOTAL STUDENTS
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM students"
);

$row = mysqli_fetch_assoc($result);

$total_students =
    intval($row["total"] ?? 0);


/* =========================================================
   TOTAL DEPARTMENTS
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(DISTINCT department) AS total
     FROM students"
);

$row = mysqli_fetch_assoc($result);

$total_departments =
    intval($row["total"] ?? 0);


/* =========================================================
   ATTENDANCE SESSIONS TODAY
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM attendance_sessions
     WHERE attendance_date = '$today'"
);

$row = mysqli_fetch_assoc($result);

$sessions_today =
    intval($row["total"] ?? 0);


/* =========================================================
   PRESENT RECORDS TODAY
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total

     FROM attendance_records ar

     INNER JOIN attendance_sessions ats
        ON ar.session_id = ats.id

     WHERE ats.attendance_date = '$today'
     AND ar.status = 'Present'"
);

$row = mysqli_fetch_assoc($result);

$present_today =
    intval($row["total"] ?? 0);


/* =========================================================
   ABSENT RECORDS TODAY
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total

     FROM attendance_records ar

     INNER JOIN attendance_sessions ats
        ON ar.session_id = ats.id

     WHERE ats.attendance_date = '$today'
     AND ar.status = 'Absent'"
);

$row = mysqli_fetch_assoc($result);

$absent_today =
    intval($row["total"] ?? 0);


/* =========================================================
   TODAY ATTENDANCE PERCENTAGE
========================================================= */

$total_today_records =
    $present_today + $absent_today;


$today_percentage =
    $total_today_records > 0
    ?
    round(
        ($present_today / $total_today_records) * 100,
        1
    )
    :
    0;


/* =========================================================
   OVERALL ATTENDANCE RECORDS
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT

        COUNT(*) AS total_records,

        SUM(
            CASE
                WHEN status = 'Present'
                THEN 1
                ELSE 0
            END
        ) AS present_records,

        SUM(
            CASE
                WHEN status = 'Absent'
                THEN 1
                ELSE 0
            END
        ) AS absent_records

     FROM attendance_records"
);

$row = mysqli_fetch_assoc($result);


$total_attendance_records =
    intval($row["total_records"] ?? 0);

$total_present =
    intval($row["present_records"] ?? 0);

$total_absent =
    intval($row["absent_records"] ?? 0);


$overall_attendance_percentage =
    $total_attendance_records > 0
    ?
    round(
        ($total_present / $total_attendance_records) * 100,
        1
    )
    :
    0;


/* =========================================================
   STUDENTS BELOW 75% ATTENDANCE
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total

     FROM
     (
        SELECT
            s.id,

            COUNT(ar.id) AS total_classes,

            SUM(
                CASE
                    WHEN ar.status = 'Present'
                    THEN 1
                    ELSE 0
                END
            ) AS present_classes

        FROM students s

        INNER JOIN attendance_records ar
            ON s.id = ar.student_id

        GROUP BY s.id

        HAVING
            COUNT(ar.id) > 0

            AND

            (
                SUM(
                    CASE
                        WHEN ar.status = 'Present'
                        THEN 1
                        ELSE 0
                    END
                )
                /
                COUNT(ar.id)
            ) * 100 < 75

     ) AS shortage_students"
);


if ($result) {

    $row =
        mysqli_fetch_assoc($result);

    $below_75 =
        intval($row["total"] ?? 0);

} else {

    $below_75 = 0;
}


/* =========================================================
   TOTAL MARK RECORDS
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM marks"
);

$row = mysqli_fetch_assoc($result);

$total_mark_records =
    intval($row["total"] ?? 0);


/* =========================================================
   STUDENTS WITH ALL RECORDED SUBJECTS PASSED
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total

     FROM
     (
        SELECT student_id

        FROM marks

        GROUP BY student_id

        HAVING
            COUNT(*) > 0

            AND

            SUM(
                CASE
                    WHEN LOWER(result) = 'fail'
                    THEN 1
                    ELSE 0
                END
            ) = 0

     ) AS passed_students"
);


if ($result) {

    $row =
        mysqli_fetch_assoc($result);

    $passed_students =
        intval($row["total"] ?? 0);

} else {

    $passed_students = 0;
}


/* =========================================================
   STUDENTS WITH AT LEAST ONE FAILED SUBJECT
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(DISTINCT student_id) AS total

     FROM marks

     WHERE LOWER(result) = 'fail'"
);

$row = mysqli_fetch_assoc($result);

$failed_students =
    intval($row["total"] ?? 0);


/* =========================================================
   RECENT ATTENDANCE SESSIONS
========================================================= */

$recent_sessions = mysqli_query(
    $conn,
    "SELECT

        ats.attendance_date,

        t.department,
        t.section,
        t.year,
        t.semester,
        t.subject_code,
        t.subject_name,
        t.start_period,
        t.end_period

     FROM attendance_sessions ats

     INNER JOIN timetable t
        ON ats.timetable_id = t.id

     ORDER BY
        ats.attendance_date DESC,
        ats.id DESC

     LIMIT 5"
);

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
Admin Dashboard - EduTrack
</title>

<link
    rel="stylesheet"
    href="css/style.css"
>

<style>

.dashboard-warning {
    background: #fff3cd;
    border: 1px solid #ffe69c;
    color: #664d03;
    padding: 16px;
    border-radius: 10px;
    margin: 20px 0;
}

.dashboard-success {
    background: #d1e7dd;
    border: 1px solid #badbcc;
    color: #0f5132;
    padding: 16px;
    border-radius: 10px;
    margin: 20px 0;
}

.recent-session-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-top: 25px;
    overflow-x: auto;
    box-shadow: 0 3px 12px rgba(0,0,0,0.06);
}

.period-badge {
    display: inline-block;
    background: #edf5ff;
    padding: 5px 10px;
    border-radius: 15px;
    white-space: nowrap;
}

</style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<div class="navbar">

<h2>
🎓 Student Management Portal
</h2>


<div>

<span>

Welcome,

<?php
echo htmlspecialchars(
    $_SESSION["admin"]
);
?>

</span>


<a
    href="logout.php"
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

<div class="sidebar">

<h3>
ADMIN PANEL
</h3>


<a
    href="dashboard.php"
    class="active"
>
🏠 Dashboard
</a>


<a href="add_student.php">
➕ Add Student
</a>


<a href="view_students.php">
👨‍🎓 Students
</a>


<a href="attendance.php">
📅 Attendance
</a>


<a href="attendance_history.php">
📋 Attendance History
</a>


<a href="marks.php">
📝 Marks
</a>


<a href="subjects.php">
📚 Subjects
</a>


<a href="reports.php">
📊 Reports
</a>


<a href="logout.php">
🚪 Logout
</a>

</div>



<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="main-content">


<h1>
Dashboard
</h1>


<p class="subtitle">

Overview of your Student Management System

</p>



<!-- =====================================================
     MAIN STATISTICS
===================================================== -->

<div class="stats-grid">


<!-- TOTAL STUDENTS -->

<div class="stat-card">

<h3>
Total Students
</h3>

<h1>
<?php echo $total_students; ?>
</h1>

<p>
Registered Students
</p>

</div>



<!-- DEPARTMENTS -->

<div class="stat-card">

<h3>
Departments
</h3>

<h1>
<?php echo $total_departments; ?>
</h1>

<p>
Active Departments
</p>

</div>



<!-- SESSIONS TODAY -->

<div class="stat-card">

<h3>
Sessions Today
</h3>

<h1>
<?php echo $sessions_today; ?>
</h1>

<p>
Subjects Taken Today
</p>

</div>



<!-- PRESENT TODAY -->

<div class="stat-card">

<h3>
Present Today
</h3>

<h1>
<?php echo $present_today; ?>
</h1>

<p>
Attendance Records
</p>

</div>



<!-- ABSENT TODAY -->

<div class="stat-card">

<h3>
Absent Today
</h3>

<h1>
<?php echo $absent_today; ?>
</h1>

<p>
Attendance Records
</p>

</div>



<!-- TODAY ATTENDANCE -->

<div class="stat-card">

<h3>
Today's Attendance
</h3>

<h1>
<?php echo $today_percentage; ?>%
</h1>

<p>
Overall Today
</p>

</div>



<!-- OVERALL ATTENDANCE -->

<div class="stat-card">

<h3>
Overall Attendance
</h3>

<h1>
<?php
echo $overall_attendance_percentage;
?>%
</h1>

<p>
All Attendance Records
</p>

</div>



<!-- BELOW 75 -->

<div class="stat-card">

<h3>
Below 75%
</h3>

<h1>
<?php echo $below_75; ?>
</h1>

<p>
Students With Shortage
</p>

</div>



<!-- PASSED -->

<div class="stat-card">

<h3>
Passed
</h3>

<h1>
<?php echo $passed_students; ?>
</h1>

<p>
Students Passing All Recorded Subjects
</p>

</div>



<!-- FAILED -->

<div class="stat-card">

<h3>
Failed
</h3>

<h1>
<?php echo $failed_students; ?>
</h1>

<p>
Students With Failed Subjects
</p>

</div>



<!-- MARK RECORDS -->

<div class="stat-card">

<h3>
Marks Records
</h3>

<h1>
<?php echo $total_mark_records; ?>
</h1>

<p>
Published Subject Results
</p>

</div>


</div>



<!-- =====================================================
     ATTENDANCE WARNING
===================================================== -->

<?php

if ($below_75 > 0) {

?>


<div class="dashboard-warning">

<strong>
⚠ Attendance Warning
</strong>

<br><br>

<?php
echo $below_75;
?>

student(s) currently have attendance below the required
<strong>75%</strong>.

</div>


<?php

} elseif ($total_attendance_records > 0) {

?>


<div class="dashboard-success">

<strong>
✓ Attendance Status
</strong>

<br><br>

All students with recorded attendance currently meet
the 75% requirement.

</div>


<?php } ?>



<!-- =====================================================
     QUICK ACTIONS
===================================================== -->

<h2 class="section-title">
Quick Actions
</h2>


<div class="quick-actions">


<a href="add_student.php">

➕
<br>

Add Student

</a>



<a href="attendance.php">

📅
<br>

Take Attendance

</a>



<a href="attendance_history.php">

📋
<br>

Attendance History

</a>



<a href="marks.php">

📝
<br>

Manage Marks

</a>



<a href="view_students.php">

🔍
<br>

Search Students

</a>



<a href="subjects.php">

📚
<br>

Subjects

</a>



<a href="reports.php">

📊
<br>

View Reports

</a>


</div>



<!-- =====================================================
     RECENT ATTENDANCE SESSIONS
===================================================== -->

<div class="recent-session-card">


<h2>
📅 Recent Attendance Sessions
</h2>


<table class="modern-table">


<thead>

<tr>

<th>
Date
</th>

<th>
Department
</th>

<th>
Section
</th>

<th>
Year / Semester
</th>

<th>
Subject
</th>

<th>
Period
</th>

</tr>

</thead>


<tbody>


<?php

if (
    $recent_sessions &&
    mysqli_num_rows(
        $recent_sessions
    ) > 0
) {


    while (
        $session =
        mysqli_fetch_assoc(
            $recent_sessions
        )
    ) {

?>


<tr>


<td>

<?php

echo date(
    "d M Y",
    strtotime(
        $session["attendance_date"]
    )
);

?>

</td>



<td>

<?php

echo htmlspecialchars(
    $session["department"]
);

?>

</td>



<td>

<?php

echo htmlspecialchars(
    $session["section"]
);

?>

</td>



<td>

<?php

echo htmlspecialchars(
    $session["year"]
);

?>

<br>

<small>

Semester

<?php

echo htmlspecialchars(
    $session["semester"]
);

?>

</small>

</td>



<td>

<strong>

<?php

echo htmlspecialchars(
    $session["subject_name"]
);

?>

</strong>

<br>

<small>

<?php

echo htmlspecialchars(
    $session["subject_code"]
);

?>

</small>

</td>



<td>

<span class="period-badge">

P<?php

echo intval(
    $session["start_period"]
);

?>


<?php

if (
    $session["start_period"]
    !=
    $session["end_period"]
) {

?>

-

P<?php

echo intval(
    $session["end_period"]
);

?>

<?php } ?>


</span>

</td>


</tr>


<?php

    }

} else {

?>


<tr>

<td
    colspan="6"
    style="
        text-align:center;
        padding:30px;
    "
>

No attendance sessions have been recorded yet.

</td>

</tr>


<?php } ?>


</tbody>


</table>


</div>


</div>

</div>


</body>

</html>