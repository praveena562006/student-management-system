<?php

session_start();
include "db.php";

/* =========================================================
   ADMIN PROTECTION
========================================================= */

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}


/* =========================================================
   FILTER VALUES
========================================================= */

$department = trim($_GET["department"] ?? "");
$section    = trim($_GET["section"] ?? "");
$year       = trim($_GET["year"] ?? "");
$semester   = trim($_GET["semester"] ?? "");
$day        = trim($_GET["day"] ?? "");


/* =========================================================
   CURRENT DAY
========================================================= */

$current_day = date("l");

$allowed_days = [
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday"
];


/* =========================================================
   GET FILTER OPTIONS
========================================================= */

$departments = mysqli_query(
    $conn,
    "SELECT DISTINCT department
     FROM timetable
     WHERE department IS NOT NULL
     AND department != ''
     ORDER BY department"
);

$sections = mysqli_query(
    $conn,
    "SELECT DISTINCT section
     FROM timetable
     WHERE section IS NOT NULL
     AND section != ''
     ORDER BY section"
);

$years = mysqli_query(
    $conn,
    "SELECT DISTINCT year
     FROM timetable
     WHERE year IS NOT NULL
     AND year != ''
     ORDER BY year"
);

$semesters = mysqli_query(
    $conn,
    "SELECT DISTINCT semester
     FROM timetable
     WHERE semester IS NOT NULL
     ORDER BY semester"
);


/* =========================================================
   DASHBOARD STATISTICS
========================================================= */

/* TOTAL STUDENTS */

$total_students = 0;

$student_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM students"
);

if ($student_result) {

    $student_row =
        mysqli_fetch_assoc($student_result);

    $total_students =
        intval($student_row["total"] ?? 0);
}


/* TOTAL TIMETABLE CLASSES */

$total_classes = 0;

$class_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM timetable"
);

if ($class_result) {

    $class_row =
        mysqli_fetch_assoc($class_result);

    $total_classes =
        intval($class_row["total"] ?? 0);
}


/* TODAY'S ATTENDANCE SESSIONS */

$today = date("Y-m-d");

$today_sessions = 0;

$today_session_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM attendance_sessions
     WHERE attendance_date = '$today'"
);

if ($today_session_result) {

    $today_session_row =
        mysqli_fetch_assoc(
            $today_session_result
        );

    $today_sessions =
        intval(
            $today_session_row["total"]
            ?? 0
        );
}


/* TODAY PRESENT RECORDS */

$present_today = 0;

$present_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total

     FROM attendance_records ar

     INNER JOIN attendance_sessions ats
        ON ar.session_id = ats.id

     WHERE ats.attendance_date = '$today'
     AND ar.status = 'Present'"
);

if ($present_result) {

    $present_row =
        mysqli_fetch_assoc($present_result);

    $present_today =
        intval($present_row["total"] ?? 0);
}


/* TODAY ABSENT RECORDS */

$absent_today = 0;

$absent_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total

     FROM attendance_records ar

     INNER JOIN attendance_sessions ats
        ON ar.session_id = ats.id

     WHERE ats.attendance_date = '$today'
     AND ar.status = 'Absent'"
);

if ($absent_result) {

    $absent_row =
        mysqli_fetch_assoc($absent_result);

    $absent_today =
        intval($absent_row["total"] ?? 0);
}


/* TODAY ATTENDANCE RATE */

$attendance_entries =
    $present_today + $absent_today;

$today_percentage =
    $attendance_entries > 0
        ? round(
            ($present_today / $attendance_entries)
            * 100,
            1
        )
        : 0;


/* =========================================================
   GET SELECTED TIMETABLE
========================================================= */

$timetable_result = null;

$filters_complete =
    $department !== "" &&
    $section !== "" &&
    $year !== "" &&
    $semester !== "" &&
    $day !== "";


if ($filters_complete) {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT *

         FROM timetable

         WHERE department = ?
         AND section = ?
         AND year = ?
         AND semester = ?
         AND day_of_week = ?

         ORDER BY start_period ASC"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sssis",
        $department,
        $section,
        $year,
        $semester,
        $day
    );

    mysqli_stmt_execute($stmt);

    $timetable_result =
        mysqli_stmt_get_result($stmt);
}


/* =========================================================
   NUMBER OF SELECTED CLASSES
========================================================= */

$selected_class_count = 0;

if ($timetable_result) {

    $selected_class_count =
        mysqli_num_rows($timetable_result);
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
Attendance Management - EduTrack
</title>

<link
    rel="stylesheet"
    href="css/style.css"
>

</head>


<body class="attendance-v2-page">


<!-- =====================================================
     TOP NAVIGATION
===================================================== -->

<div class="navbar admin-navbar">

<div class="admin-brand">

<div class="admin-brand-icon">
🎓
</div>

<div>

<h2>
EduTrack
</h2>

<small>
ACADEMIC MANAGEMENT
</small>

</div>

</div>


<div class="admin-navbar-title">

University Academic Management System

</div>


<div class="admin-navbar-user">

<div class="admin-user-info">

<span class="admin-user-label">
Administrator
</span>

<strong>

<?php
echo htmlspecialchars(
    $_SESSION["admin"]
);
?>

</strong>

</div>


<div class="admin-avatar">

<?php

echo strtoupper(
    substr(
        $_SESSION["admin"],
        0,
        1
    )
);

?>

</div>


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
ADMINISTRATION
</h3>


<a href="dashboard.php">
🏠 Dashboard
</a>


<a href="view_students.php">
🎓 Students
</a>


<a href="add_student.php">
＋ Add Student
</a>


<a
    href="attendance.php"
    class="active"
>
📅 Attendance
</a>


<a href="attendance_history.php">
📋 Attendance History
</a>


<a href="marks.php">
📝 Marks & Grades
</a>


<a href="subjects.php">
📚 Subjects
</a>


<a href="reports.php">
📊 Reports & Analytics
</a>


<a href="logout.php">
🚪 Logout
</a>

</div>



<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="main-content admin-v2-content">


<!-- =====================================================
     HERO
===================================================== -->

<section class="admin-page-hero attendance-hero">

<div>

<p class="admin-eyebrow">
ACADEMIC OPERATIONS
</p>

<h1>
Attendance Management
</h1>

<p>
Manage daily class attendance using the
official academic timetable and maintain
accurate period-wise attendance records.
</p>


<div class="hero-meta-row">

<span>
📅 <?php echo date("d M Y"); ?>
</span>

<span>
• <?php echo htmlspecialchars($current_day); ?>
</span>

<span>
• <?php echo $total_students; ?> Students
</span>

</div>

</div>


<div class="hero-decoration">

<div class="hero-icon-circle">
✓
</div>

</div>

</section>



<!-- =====================================================
     STATISTICS
===================================================== -->

<div class="admin-metric-grid">


<div class="admin-metric-card">

<div class="metric-icon metric-blue">
🎓
</div>

<div>

<span class="metric-label">
TOTAL STUDENTS
</span>

<h2>
<?php echo $total_students; ?>
</h2>

<p>
Registered students
</p>

</div>

</div>



<div class="admin-metric-card">

<div class="metric-icon metric-purple">
📚
</div>

<div>

<span class="metric-label">
TIMETABLE CLASSES
</span>

<h2>
<?php echo $total_classes; ?>
</h2>

<p>
Configured classes
</p>

</div>

</div>



<div class="admin-metric-card">

<div class="metric-icon metric-green">
✓
</div>

<div>

<span class="metric-label">
TODAY'S SESSIONS
</span>

<h2>
<?php echo $today_sessions; ?>
</h2>

<p>
Attendance sessions recorded
</p>

</div>

</div>



<div class="admin-metric-card">

<div class="metric-icon metric-orange">
%
</div>

<div>

<span class="metric-label">
TODAY'S ATTENDANCE
</span>

<h2>
<?php echo $today_percentage; ?>%
</h2>

<p>

<?php

if ($attendance_entries == 0) {

    echo "No attendance recorded yet";

} else {

    echo $present_today .
         " present • " .
         $absent_today .
         " absent";
}

?>

</p>

</div>

</div>


</div>



<!-- =====================================================
     ATTENDANCE WORKSPACE
===================================================== -->

<section class="admin-section-card">


<div class="admin-section-header">

<div>

<span class="section-kicker">
FACULTY WORKSPACE
</span>

<h2>
Select Academic Class
</h2>

<p>
Choose the academic group and day to load
the corresponding timetable.
</p>

</div>


<div class="section-header-icon">
📅
</div>

</div>



<form
    method="GET"
    class="professional-filter-form"
>


<div class="professional-filter-grid">


<!-- DEPARTMENT -->

<div class="professional-field">

<label>
<span>Department</span>
<small>Academic branch</small>
</label>

<select
    name="department"
    required
>

<option value="">
Choose Department
</option>

<?php

while (
    $row =
    mysqli_fetch_assoc($departments)
) {

    $value =
        $row["department"];

?>

<option
    value="<?php
        echo htmlspecialchars($value);
    ?>"

    <?php
    echo
        $department === $value
            ? "selected"
            : "";
    ?>
>

<?php
echo htmlspecialchars($value);
?>

</option>

<?php
}
?>

</select>

</div>



<!-- SECTION -->

<div class="professional-field">

<label>
<span>Section</span>
<small>Student group</small>
</label>

<select
    name="section"
    required
>

<option value="">
Choose Section
</option>

<?php

while (
    $row =
    mysqli_fetch_assoc($sections)
) {

    $value =
        $row["section"];

?>

<option
    value="<?php
        echo htmlspecialchars($value);
    ?>"

    <?php
    echo
        $section === $value
            ? "selected"
            : "";
    ?>
>

Section
<?php
echo htmlspecialchars($value);
?>

</option>

<?php
}
?>

</select>

</div>



<!-- YEAR -->

<div class="professional-field">

<label>
<span>Academic Year</span>
<small>Current year</small>
</label>

<select
    name="year"
    required
>

<option value="">
Choose Year
</option>

<?php

while (
    $row =
    mysqli_fetch_assoc($years)
) {

    $value =
        $row["year"];

?>

<option
    value="<?php
        echo htmlspecialchars($value);
    ?>"

    <?php
    echo
        $year === $value
            ? "selected"
            : "";
    ?>
>

<?php
echo htmlspecialchars($value);
?>

</option>

<?php
}
?>

</select>

</div>



<!-- SEMESTER -->

<div class="professional-field">

<label>
<span>Semester</span>
<small>Academic semester</small>
</label>

<select
    name="semester"
    required
>

<option value="">
Choose Semester
</option>

<?php

while (
    $row =
    mysqli_fetch_assoc($semesters)
) {

    $value =
        $row["semester"];

?>

<option
    value="<?php
        echo htmlspecialchars($value);
    ?>"

    <?php
    echo
        (string)$semester ===
        (string)$value
            ? "selected"
            : "";
    ?>
>

Semester
<?php
echo htmlspecialchars($value);
?>

</option>

<?php
}
?>

</select>

</div>



<!-- DAY -->

<div class="professional-field">

<label>
<span>Day</span>
<small>Teaching day</small>
</label>

<select
    name="day"
    required
>

<option value="">
Choose Day
</option>

<?php

foreach (
    $allowed_days
    as $day_option
) {

?>

<option
    value="<?php
        echo htmlspecialchars(
            $day_option
        );
    ?>"

    <?php
    echo
        $day === $day_option
            ? "selected"
            : "";
    ?>
>

<?php
echo htmlspecialchars(
    $day_option
);
?>

<?php

if (
    $day_option === $current_day
) {
    echo " (Today)";
}

?>

</option>

<?php
}
?>

</select>

</div>


</div>



<div class="filter-action-row">

<div class="filter-help">

<span>
ℹ
</span>

Select all academic details before
loading the timetable.

</div>


<div class="filter-buttons">

<a
    href="attendance.php"
    class="secondary-admin-btn"
>
Reset
</a>


<button
    type="submit"
    class="primary-admin-btn"
>

Load Timetable →

</button>

</div>

</div>


</form>

</section>



<?php if ($filters_complete) { ?>


<!-- =====================================================
     SELECTED CLASS BANNER
===================================================== -->

<div class="selected-class-banner">


<div class="selected-class-icon">
🎓
</div>


<div>

<span>
SELECTED ACADEMIC GROUP
</span>

<h3>

<?php
echo htmlspecialchars($department);
?>

-

<?php
echo htmlspecialchars($section);
?>

&nbsp; • &nbsp;

<?php
echo htmlspecialchars($year);
?>

&nbsp; • &nbsp;

Semester

<?php
echo htmlspecialchars($semester);
?>

</h3>

<p>

<?php
echo htmlspecialchars($day);
?>

•

<?php
echo $selected_class_count;
?>

scheduled class<?php
echo
    $selected_class_count == 1
        ? ""
        : "es";
?>

</p>

</div>


<div class="selected-class-status">

<span class="status-dot"></span>

Timetable Loaded

</div>

</div>



<!-- =====================================================
     TIMETABLE
===================================================== -->

<section class="admin-section-card timetable-section">


<div class="admin-section-header">

<div>

<span class="section-kicker">
DAILY SCHEDULE
</span>

<h2>

<?php
echo htmlspecialchars($day);
?>

Timetable

</h2>

<p>
Select a subject to begin taking attendance.
</p>

</div>


<div class="record-counter">

<?php
echo $selected_class_count;
?>

Classes

</div>

</div>



<?php

if (
    $timetable_result &&
    mysqli_num_rows(
        $timetable_result
    ) > 0
) {

?>


<div class="professional-table-wrapper">

<table class="professional-admin-table">

<thead>

<tr>

<th>
Period
</th>

<th>
Course
</th>

<th>
Subject
</th>

<th>
Time
</th>

<th>
Session
</th>

<th>
Attendance
</th>

</tr>

</thead>


<tbody>


<?php

while (
    $class =
    mysqli_fetch_assoc(
        $timetable_result
    )
) {

?>


<tr>


<!-- PERIOD -->

<td>

<div class="period-pill">

P<?php
echo intval(
    $class["start_period"]
);
?>

<?php

if (
    $class["end_period"] !=
    $class["start_period"]
) {

    echo "–P" .
        intval(
            $class["end_period"]
        );
}

?>

</div>

</td>



<!-- SUBJECT CODE -->

<td>

<div class="course-code">

<?php
echo htmlspecialchars(
    $class["subject_code"]
);
?>

</div>

</td>



<!-- SUBJECT -->

<td>

<div class="subject-cell">

<strong>

<?php
echo htmlspecialchars(
    $class["subject_name"]
);
?>

</strong>

<small>

<?php
echo htmlspecialchars(
    $department
);
?>

• Semester

<?php
echo htmlspecialchars(
    $semester
);
?>

</small>

</div>

</td>



<!-- TIME -->

<td>

<div class="class-time">

<strong>

<?php
echo date(
    "g:i A",
    strtotime(
        $class["start_time"]
    )
);
?>

</strong>

<span>
to
</span>

<?php
echo date(
    "g:i A",
    strtotime(
        $class["end_time"]
    )
);
?>

</div>

</td>



<!-- TYPE -->

<td>

<span class="session-type-badge">

<?php
echo htmlspecialchars(
    $class["session_type"]
);
?>

</span>

</td>



<!-- ACTION -->

<td>

<a
    href="take_attendance.php?timetable_id=<?php
        echo intval(
            $class["id"]
        );
    ?>"
    class="attendance-action-btn"
>

<span>
✓
</span>

Take Attendance

</a>

</td>


</tr>


<?php
}
?>


</tbody>

</table>

</div>


<?php

} else {

?>


<div class="professional-empty-state">

<div class="empty-state-icon">
📭
</div>

<h3>
No Classes Scheduled
</h3>

<p>
There are no timetable entries for the
selected academic group on

<strong>
<?php
echo htmlspecialchars($day);
?>
</strong>.
</p>

<a
    href="attendance.php"
    class="primary-admin-btn"
>
Choose Another Class
</a>

</div>


<?php
}
?>


</section>


<?php } else { ?>


<!-- =====================================================
     INITIAL EMPTY STATE
===================================================== -->

<div class="attendance-start-state">

<div class="attendance-start-visual">

<div class="start-calendar">
<span>✓</span>
</div>

</div>


<div>

<span class="section-kicker">
READY FOR ATTENDANCE
</span>

<h2>
Start Today's Attendance
</h2>

<p>
Choose the department, section, academic
year, semester and day above. EduTrack will
load the corresponding classes directly
from your timetable.
</p>


<div class="attendance-workflow">

<span>
1
<strong>Select Class</strong>
</span>

<div></div>

<span>
2
<strong>Choose Subject</strong>
</span>

<div></div>

<span>
3
<strong>Mark Attendance</strong>
</span>

</div>

</div>

</div>


<?php } ?>



<!-- =====================================================
     FACULTY TIP
===================================================== -->

<div class="faculty-tip-card">

<div class="faculty-tip-icon">
💡
</div>

<div>

<strong>
Attendance Management Tip
</strong>

<p>
Always select the correct academic group and
subject before recording attendance. Period-wise
records improve the accuracy of student attendance
analytics and shortage reports.
</p>

</div>


<a href="attendance_history.php">
View Attendance History →
</a>

</div>


</div>

</div>


<script src="js/script.js"></script>

</body>

</html>