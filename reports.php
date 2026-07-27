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
   TOTAL STUDENTS
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM students"
);

$row = mysqli_fetch_assoc($result);

$total_students = intval($row["total"] ?? 0);


/* =========================================================
   DEPARTMENT STATISTICS
========================================================= */

$department_result = mysqli_query(
    $conn,
    "SELECT
        department,
        COUNT(*) AS total
     FROM students
     GROUP BY department
     ORDER BY total DESC"
);


/* =========================================================
   PASSED SUBJECT RESULTS
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM marks
     WHERE LOWER(result) = 'pass'"
);

$row = mysqli_fetch_assoc($result);

$total_pass = intval($row["total"] ?? 0);


/* =========================================================
   FAILED SUBJECT RESULTS
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM marks
     WHERE LOWER(result) = 'fail'"
);

$row = mysqli_fetch_assoc($result);

$total_fail = intval($row["total"] ?? 0);


/* =========================================================
   AVERAGE MARKS
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT
        COALESCE(
            ROUND(AVG(total_marks), 2),
            0
        ) AS average_marks
     FROM marks"
);

$row = mysqli_fetch_assoc($result);

$average_marks =
    $row["average_marks"] ?? 0;


/* =========================================================
   TOP PERFORMER
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT
        students.id,
        students.name,
        students.roll_no,
        students.department,
        students.section,
        students.year,
        students.semester,

        ROUND(
            AVG(marks.total_marks),
            2
        ) AS average,

        COUNT(marks.id)
            AS subject_count

     FROM marks

     JOIN students
        ON students.id = marks.student_id

     GROUP BY
        students.id,
        students.name,
        students.roll_no,
        students.department,
        students.section,
        students.year,
        students.semester

     ORDER BY average DESC

     LIMIT 1"
);

$top_student =
    mysqli_fetch_assoc($result);


/* =========================================================
   TOP STUDENTS
========================================================= */

$top_students = mysqli_query(
    $conn,
    "SELECT
        students.name,
        students.roll_no,
        students.department,
        students.section,
        students.year,
        students.semester,

        ROUND(
            AVG(marks.total_marks),
            2
        ) AS average,

        COUNT(marks.id)
            AS subjects

     FROM marks

     JOIN students
        ON students.id = marks.student_id

     GROUP BY
        students.id,
        students.name,
        students.roll_no,
        students.department,
        students.section,
        students.year,
        students.semester

     ORDER BY average DESC

     LIMIT 10"
);


/* =========================================================
   SUBJECT PERFORMANCE
========================================================= */

$subject_result = mysqli_query(
    $conn,
    "SELECT
        subject,

        COUNT(*) AS students,

        ROUND(
            AVG(total_marks),
            2
        ) AS average,

        MAX(total_marks)
            AS highest,

        MIN(total_marks)
            AS lowest,

        SUM(
            CASE
                WHEN LOWER(result) = 'pass'
                THEN 1
                ELSE 0
            END
        ) AS passed,

        SUM(
            CASE
                WHEN LOWER(result) = 'fail'
                THEN 1
                ELSE 0
            END
        ) AS failed

     FROM marks

     GROUP BY subject

     ORDER BY average DESC"
);


/* =========================================================
   ATTENDANCE STATISTICS

   Uses the subject-wise attendance system:
   attendance_sessions + attendance_records
========================================================= */

$attendance_result = mysqli_query(
    $conn,
    "SELECT

        COUNT(ar.id)
            AS total_records,

        SUM(
            CASE
                WHEN ar.status = 'Present'
                THEN 1
                ELSE 0
            END
        ) AS present_records,

        SUM(
            CASE
                WHEN ar.status = 'Absent'
                THEN 1
                ELSE 0
            END
        ) AS absent_records

     FROM attendance_records ar"
);


$attendance_stats =
    mysqli_fetch_assoc($attendance_result);


$total_attendance_records =
    intval(
        $attendance_stats["total_records"] ?? 0
    );


$total_present =
    intval(
        $attendance_stats["present_records"] ?? 0
    );


$total_absent =
    intval(
        $attendance_stats["absent_records"] ?? 0
    );


$attendance_percentage =
    $total_attendance_records > 0
    ?
    round(
        ($total_present / $total_attendance_records) * 100,
        2
    )
    :
    0;


/* =========================================================
   TOTAL ATTENDANCE SESSIONS
========================================================= */

$session_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM attendance_sessions"
);

$session_row =
    mysqli_fetch_assoc($session_result);

$total_sessions =
    intval(
        $session_row["total"] ?? 0
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
Reports & Analytics - EduTrack
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
🎓 Student Management Portal
</h2>

<a
    href="logout.php"
    class="logout-btn"
>
Logout
</a>

</div>



<div class="main-layout">


<!-- SIDEBAR -->

<div class="sidebar">

<h3>
ADMIN PANEL
</h3>

<a href="dashboard.php">
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

<a
    href="reports.php"
    class="active"
>
📊 Reports
</a>

<a href="logout.php">
🚪 Logout
</a>

</div>



<!-- MAIN CONTENT -->

<div class="main-content">


<div class="page-header">

<div>

<h1>
📊 Reports & Analytics
</h1>

<p>
Academic performance, attendance and student statistics.
</p>

</div>

</div>



<!-- =====================================================
     MAIN STATISTICS
===================================================== -->

<div class="stats-grid">


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



<div class="stat-card">

<h3>
Passed Subjects
</h3>

<h1>
<?php echo $total_pass; ?>
</h1>

<p>
Published Results
</p>

</div>



<div class="stat-card">

<h3>
Failed Subjects
</h3>

<h1>
<?php echo $total_fail; ?>
</h1>

<p>
Published Results
</p>

</div>



<div class="stat-card">

<h3>
Average Marks
</h3>

<h1>
<?php echo $average_marks; ?>%
</h1>

<p>
Overall Academic Average
</p>

</div>


</div>



<!-- =====================================================
     ATTENDANCE STATISTICS
===================================================== -->

<h2 class="section-title">
📅 Attendance Analytics
</h2>


<div class="stats-grid">


<div class="stat-card">

<h3>
Attendance Sessions
</h3>

<h1>
<?php echo $total_sessions; ?>
</h1>

</div>


<div class="stat-card">

<h3>
Present Records
</h3>

<h1>
<?php echo $total_present; ?>
</h1>

</div>


<div class="stat-card">

<h3>
Absent Records
</h3>

<h1>
<?php echo $total_absent; ?>
</h1>

</div>


<div class="stat-card">

<h3>
Overall Attendance
</h3>

<h1>
<?php echo $attendance_percentage; ?>%
</h1>

</div>


</div>



<!-- =====================================================
     TOP PERFORMER + DEPARTMENT DISTRIBUTION
===================================================== -->

<div class="report-grid">


<div class="report-card">


<h2>
🏆 Top Performer
</h2>


<?php if ($top_student) { ?>


<div class="top-student">


<h1>
🥇
</h1>


<h2>

<?php
echo htmlspecialchars(
    $top_student["name"]
);
?>

</h2>


<p>

<strong>
Roll Number:
</strong>

<?php
echo htmlspecialchars(
    $top_student["roll_no"]
);
?>

</p>


<p>

<strong>
Department:
</strong>

<?php
echo htmlspecialchars(
    $top_student["department"]
);
?>

-

<?php
echo htmlspecialchars(
    $top_student["section"]
);
?>

</p>


<p>

<strong>
Class:
</strong>

<?php
echo htmlspecialchars(
    $top_student["year"]
);
?>

•

Semester

<?php
echo htmlspecialchars(
    $top_student["semester"]
);
?>

</p>


<p>

<strong>
Subjects:
</strong>

<?php
echo intval(
    $top_student["subject_count"]
);
?>

</p>


<strong>

Average:

<?php
echo $top_student["average"];
?>%

</strong>


</div>


<?php } else { ?>


<p>
No marks available yet.
</p>


<?php } ?>


</div>



<!-- DEPARTMENT DISTRIBUTION -->

<div class="report-card">


<h2>
🏫 Department Distribution
</h2>


<table class="mini-table">


<thead>

<tr>

<th>
Department
</th>

<th>
Students
</th>

</tr>

</thead>


<tbody>


<?php

if (
    $department_result &&
    mysqli_num_rows(
        $department_result
    ) > 0
) {

    while (
        $department =
        mysqli_fetch_assoc(
            $department_result
        )
    ) {

?>


<tr>


<td>

<?php
echo htmlspecialchars(
    $department["department"]
);
?>

</td>


<td>

<?php
echo intval(
    $department["total"]
);
?>

</td>


</tr>


<?php

    }

} else {

?>


<tr>

<td colspan="2">
No students available.
</td>

</tr>


<?php } ?>


</tbody>


</table>


</div>


</div>



<!-- =====================================================
     TOP STUDENTS TABLE
===================================================== -->

<h2 class="section-title">
🏆 Student Performance Ranking
</h2>


<div class="table-card">


<table class="modern-table">


<thead>

<tr>

<th>
Rank
</th>

<th>
Roll No
</th>

<th>
Student
</th>

<th>
Department
</th>

<th>
Year
</th>

<th>
Semester
</th>

<th>
Subjects
</th>

<th>
Average
</th>

</tr>

</thead>


<tbody>


<?php

$rank = 1;


if (
    $top_students &&
    mysqli_num_rows(
        $top_students
    ) > 0
) {

    while (
        $student =
        mysqli_fetch_assoc(
            $top_students
        )
    ) {

?>


<tr>


<td>

<strong>

#<?php echo $rank; ?>

</strong>

</td>


<td>

<?php
echo htmlspecialchars(
    $student["roll_no"]
);
?>

</td>


<td>

<strong>

<?php
echo htmlspecialchars(
    $student["name"]
);
?>

</strong>

</td>


<td>

<?php

echo htmlspecialchars(
    $student["department"]
);

echo " - ";

echo htmlspecialchars(
    $student["section"]
);

?>

</td>


<td>

<?php
echo htmlspecialchars(
    $student["year"]
);
?>

</td>


<td>

Semester

<?php
echo htmlspecialchars(
    $student["semester"]
);
?>

</td>


<td>

<?php
echo intval(
    $student["subjects"]
);
?>

</td>


<td>

<strong>

<?php
echo $student["average"];
?>%

</strong>

</td>


</tr>


<?php

        $rank++;

    }

} else {

?>


<tr>

<td
    colspan="8"
    class="no-data"
>

No marks have been entered yet.

</td>

</tr>


<?php } ?>


</tbody>


</table>


</div>



<!-- =====================================================
     SUBJECT PERFORMANCE
===================================================== -->

<h2 class="section-title">
📚 Subject Performance
</h2>


<div class="table-card">


<table class="modern-table">


<thead>

<tr>

<th>
Subject
</th>

<th>
Students
</th>

<th>
Average
</th>

<th>
Highest
</th>

<th>
Lowest
</th>

<th>
Passed
</th>

<th>
Failed
</th>

</tr>

</thead>


<tbody>


<?php

if (
    $subject_result &&
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

?>


<tr>


<td>

<strong>

<?php
echo htmlspecialchars(
    $subject["subject"]
);
?>

</strong>

</td>


<td>

<?php
echo intval(
    $subject["students"]
);
?>

</td>


<td>

<?php
echo $subject["average"];
?>%

</td>


<td>

<?php
echo intval(
    $subject["highest"]
);
?>

/100

</td>


<td>

<?php
echo intval(
    $subject["lowest"]
);
?>

/100

</td>


<td>

<span class="good-status">

<?php
echo intval(
    $subject["passed"]
);
?>

</span>

</td>


<td>

<span class="bad-status">

<?php
echo intval(
    $subject["failed"]
);
?>

</span>

</td>


</tr>


<?php

    }

} else {

?>


<tr>

<td
    colspan="7"
    class="no-data"
>

No subject results available yet.

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