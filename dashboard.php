<?php

session_start();

include "db.php";

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}


/* TOTAL STUDENTS */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM students"
);

$row = mysqli_fetch_assoc($result);

$total_students = $row["total"];


/* TOTAL DEPARTMENTS */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(DISTINCT department) AS total FROM students"
);

$row = mysqli_fetch_assoc($result);

$total_departments = $row["total"];


/* PRESENT TODAY */

$today = date("Y-m-d");

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM attendance
     WHERE attendance_date='$today'
     AND status='Present'"
);

$row = mysqli_fetch_assoc($result);

$present_today = $row["total"];


/* ABSENT TODAY */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM attendance
     WHERE attendance_date='$today'
     AND status='Absent'"
);

$row = mysqli_fetch_assoc($result);

$absent_today = $row["total"];


/* PASSED STUDENTS */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(DISTINCT student_id) AS total
     FROM marks
     WHERE result='Pass'"
);

$row = mysqli_fetch_assoc($result);

$passed_students = $row["total"];


/* FAILED STUDENTS */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(DISTINCT student_id) AS total
     FROM marks
     WHERE result='Fail'"
);

$row = mysqli_fetch_assoc($result);

$failed_students = $row["total"];

?>

<!DOCTYPE html>

<html>

<head>

<title>Admin Dashboard</title>

<link rel="stylesheet" href="css/style.css">

</head>

<body>


<div class="navbar">

<h2>🎓 Student Management Portal</h2>

<div>

<span>
Welcome,
<?php echo htmlspecialchars($_SESSION["admin"]); ?>
</span>

<a href="logout.php" class="logout-btn">
Logout
</a>

</div>

</div>


<div class="main-layout">


<!-- SIDEBAR -->

<div class="sidebar">

<h3>ADMIN PANEL</h3>

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


<!-- MAIN CONTENT -->

<div class="main-content">


<h1>Dashboard</h1>

<p class="subtitle">
Overview of your Student Management System
</p>


<!-- STATISTICS CARDS -->

<div class="stats-grid">


<div class="stat-card">

<h3>Total Students</h3>

<h1>
<?php echo $total_students; ?>
</h1>

<p>Registered Students</p>

</div>


<div class="stat-card">

<h3>Departments</h3>

<h1>
<?php echo $total_departments; ?>
</h1>

<p>Active Departments</p>

</div>


<div class="stat-card">

<h3>Present Today</h3>

<h1>
<?php echo $present_today; ?>
</h1>

<p>Students Present</p>

</div>


<div class="stat-card">

<h3>Absent Today</h3>

<h1>
<?php echo $absent_today; ?>
</h1>

<p>Students Absent</p>

</div>


<div class="stat-card">

<h3>Passed</h3>

<h1>
<?php echo $passed_students; ?>
</h1>

<p>Students Passed</p>

</div>


<div class="stat-card">

<h3>Failed</h3>

<h1>
<?php echo $failed_students; ?>
</h1>

<p>Students Failed</p>

</div>


</div>


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

Manage Attendance

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


<a href="reports.php">

📊
<br>

View Reports

</a>


</div>


</div>

</div>


</body>

</html>