<?php

session_start();
include "db.php";

/* ==========================================
   CHECK ADMIN LOGIN
========================================== */

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}


/* ==========================================
   FILTER VALUES
========================================== */

$department = isset($_GET["department"])
    ? trim($_GET["department"])
    : "";

$section = isset($_GET["section"])
    ? trim($_GET["section"])
    : "";

$year = isset($_GET["year"])
    ? trim($_GET["year"])
    : "";

$semester = isset($_GET["semester"])
    ? trim($_GET["semester"])
    : "";

$day = isset($_GET["day"])
    ? trim($_GET["day"])
    : "";


/* ==========================================
   GET FILTER OPTIONS FROM TIMETABLE
========================================== */

$departments = mysqli_query(
    $conn,
    "SELECT DISTINCT department
     FROM timetable
     ORDER BY department"
);

$sections = mysqli_query(
    $conn,
    "SELECT DISTINCT section
     FROM timetable
     ORDER BY section"
);

$years = mysqli_query(
    $conn,
    "SELECT DISTINCT year
     FROM timetable
     ORDER BY year"
);

$semesters = mysqli_query(
    $conn,
    "SELECT DISTINCT semester
     FROM timetable
     ORDER BY semester"
);


/* ==========================================
   GET TIMETABLE
========================================== */

$timetable_result = null;

if (
    $department != "" &&
    $section != "" &&
    $year != "" &&
    $semester != "" &&
    $day != ""
) {

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

<style>

.attendance-filter-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.06);
}

.filter-grid {
    display: grid;
    grid-template-columns:
        repeat(auto-fit, minmax(170px, 1fr));
    gap: 18px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-weight: 600;
    margin-bottom: 7px;
}

.filter-group select {
    padding: 11px;
    border: 1px solid #d7dce5;
    border-radius: 7px;
    background: white;
}

.load-button {
    background: #2878d0;
    color: white;
    border: none;
    padding: 12px 18px;
    border-radius: 7px;
    cursor: pointer;
    font-weight: 600;
}

.load-button:hover {
    opacity: 0.9;
}

.timetable-card {
    background: white;
    border-radius: 12px;
    padding: 22px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.06);
}

.class-summary {
    background: #edf5ff;
    border-left: 4px solid #2878d0;
    padding: 15px 18px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.take-attendance-btn {
    display: inline-block;
    background: #198754;
    color: white;
    text-decoration: none;
    padding: 9px 15px;
    border-radius: 6px;
    font-weight: 600;
}

.take-attendance-btn:hover {
    opacity: 0.9;
}

.no-classes {
    padding: 30px;
    text-align: center;
    color: #777;
}

.session-badge {
    padding: 5px 10px;
    border-radius: 20px;
    background: #eef2f7;
    font-size: 13px;
}

</style>

</head>


<body>


<!-- ==========================================
     NAVBAR
========================================== -->

<div class="navbar">

<h2>
🎓 Student Management Portal
</h2>

<div>

<span>
Welcome,
<?php
echo htmlspecialchars($_SESSION["admin"]);
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


<!-- ==========================================
     SIDEBAR
========================================== -->

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
📝 Marks
</a>

<a href="reports.php">
📊 Reports
</a>

<a href="logout.php">
🚪 Logout
</a>

</div>



<!-- ==========================================
     MAIN CONTENT
========================================== -->

<div class="main-content">


<div class="page-header">

<div>

<h1>
📅 Attendance Management
</h1>

<p>
Select a class and subject from the timetable
to take period-wise attendance.
</p>

</div>

</div>



<!-- ==========================================
     FILTERS
========================================== -->

<div class="attendance-filter-card">

<form method="GET">

<div class="filter-grid">


<!-- DEPARTMENT -->

<div class="filter-group">

<label>
Department
</label>

<select
    name="department"
    required
>

<option value="">
Select Department
</option>

<?php

while (
    $row = mysqli_fetch_assoc($departments)
) {

    $value = $row["department"];

    $selected =
        ($department == $value)
        ? "selected"
        : "";

?>

<option
    value="<?php
        echo htmlspecialchars($value);
    ?>"
    <?php echo $selected; ?>
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

<div class="filter-group">

<label>
Section
</label>

<select
    name="section"
    required
>

<option value="">
Select Section
</option>

<?php

while (
    $row = mysqli_fetch_assoc($sections)
) {

    $value = $row["section"];

    $selected =
        ($section == $value)
        ? "selected"
        : "";

?>

<option
    value="<?php
        echo htmlspecialchars($value);
    ?>"
    <?php echo $selected; ?>
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



<!-- YEAR -->

<div class="filter-group">

<label>
Year
</label>

<select
    name="year"
    required
>

<option value="">
Select Year
</option>

<?php

while (
    $row = mysqli_fetch_assoc($years)
) {

    $value = $row["year"];

    $selected =
        ($year == $value)
        ? "selected"
        : "";

?>

<option
    value="<?php
        echo htmlspecialchars($value);
    ?>"
    <?php echo $selected; ?>
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

<div class="filter-group">

<label>
Semester
</label>

<select
    name="semester"
    required
>

<option value="">
Select Semester
</option>

<?php

while (
    $row = mysqli_fetch_assoc($semesters)
) {

    $value = $row["semester"];

    $selected =
        ((string)$semester === (string)$value)
        ? "selected"
        : "";

?>

<option
    value="<?php
        echo htmlspecialchars($value);
    ?>"
    <?php echo $selected; ?>
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

<div class="filter-group">

<label>
Day
</label>

<select
    name="day"
    required
>

<option value="">
Select Day
</option>

<?php

$days = [
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday"
];

foreach ($days as $day_option) {

    $selected =
        ($day == $day_option)
        ? "selected"
        : "";

?>

<option
    value="<?php echo $day_option; ?>"
    <?php echo $selected; ?>
>

<?php echo $day_option; ?>

</option>

<?php
}
?>

</select>

</div>



<!-- LOAD BUTTON -->

<div class="filter-group">

<button
    type="submit"
    class="load-button"
>

Load Timetable

</button>

</div>


</div>

</form>

</div>



<?php

if (
    $department != "" &&
    $section != "" &&
    $year != "" &&
    $semester != "" &&
    $day != ""
) {

?>


<!-- ==========================================
     SELECTED CLASS
========================================== -->

<div class="class-summary">

<strong>
Selected Class:
</strong>

<?php
echo htmlspecialchars($department);
?>

-

<?php
echo htmlspecialchars($section);
?>

&nbsp; | &nbsp;

<?php
echo htmlspecialchars($year);
?>

&nbsp; | &nbsp;

Semester

<?php
echo htmlspecialchars($semester);
?>

&nbsp; | &nbsp;

<?php
echo htmlspecialchars($day);
?>

</div>



<!-- ==========================================
     TIMETABLE TABLE
========================================== -->

<div class="timetable-card">


<table class="modern-table">

<thead>

<tr>

<th>
Subject Code
</th>

<th>
Subject Name
</th>

<th>
Periods
</th>

<th>
Time
</th>

<th>
Type
</th>

<th>
Action
</th>

</tr>

</thead>


<tbody>


<?php

if (
    $timetable_result &&
    mysqli_num_rows($timetable_result) > 0
) {

    while (
        $class =
        mysqli_fetch_assoc($timetable_result)
    ) {

?>


<tr>


<td>

<strong>

<?php
echo htmlspecialchars(
    $class["subject_code"]
);
?>

</strong>

</td>



<td>

<?php
echo htmlspecialchars(
    $class["subject_name"]
);
?>

</td>



<td>

<span class="session-badge">

P<?php
echo intval(
    $class["start_period"]
);
?>

<?php

if (
    $class["end_period"]
    !=
    $class["start_period"]
) {

?>

-

P<?php
echo intval(
    $class["end_period"]
);
?>

<?php
}
?>

</span>

</td>



<td>

<?php

echo date(
    "g:i A",
    strtotime($class["start_time"])
);

?>

-

<?php

echo date(
    "g:i A",
    strtotime($class["end_time"])
);

?>

</td>



<td>

<?php
echo htmlspecialchars(
    $class["session_type"]
);
?>

</td>



<td>

<a
    class="take-attendance-btn"
    href="take_attendance.php?timetable_id=<?php
        echo intval($class["id"]);
    ?>"
>

Take Attendance

</a>

</td>


</tr>


<?php

    }

} else {

?>


<tr>

<td
    colspan="6"
    class="no-classes"
>

No timetable classes found for this selection.

</td>

</tr>


<?php

}

?>


</tbody>

</table>


</div>


<?php

}

?>


</div>

</div>


</body>

</html>