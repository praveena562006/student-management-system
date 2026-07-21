<?php

session_start();

include "db.php";


if (!isset($_SESSION["admin"])) {

    header("Location: login.php");

    exit();

}


/* TOTAL STUDENTS */

$result =
mysqli_query(
$conn,
"SELECT COUNT(*) AS total FROM students"
);

$row =
mysqli_fetch_assoc($result);

$total_students =
$row["total"];


/* DEPARTMENT STATISTICS */

$department_result =
mysqli_query(
$conn,
"
SELECT
department,
COUNT(*) AS total
FROM students
GROUP BY department
ORDER BY total DESC
"
);


/* PASS COUNT */

$result =
mysqli_query(
$conn,
"
SELECT COUNT(*) AS total
FROM marks
WHERE result='Pass'
"
);

$row =
mysqli_fetch_assoc($result);

$total_pass =
$row["total"];


/* FAIL COUNT */

$result =
mysqli_query(
$conn,
"
SELECT COUNT(*) AS total
FROM marks
WHERE result='Fail'
"
);

$row =
mysqli_fetch_assoc($result);

$total_fail =
$row["total"];


/* AVERAGE MARKS */

$result =
mysqli_query(
$conn,
"
SELECT
ROUND(AVG(total_marks),2)
AS average_marks
FROM marks
"
);

$row =
mysqli_fetch_assoc($result);

$average_marks =
$row["average_marks"] ?? 0;


/* TOP PERFORMER */

$result =
mysqli_query(
$conn,
"
SELECT

students.name,

students.roll_no,

ROUND(AVG(marks.total_marks),2)
AS average

FROM marks

JOIN students
ON students.id = marks.student_id

GROUP BY students.id

ORDER BY average DESC

LIMIT 1
"
);

$top_student =
mysqli_fetch_assoc($result);

?>


<!DOCTYPE html>

<html>


<head>

<title>
Reports & Analytics
</title>

<link
rel="stylesheet"
href="css/style.css"
>

</head>


<body>


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

<a href="reports.php" class="active">
📊 Reports
</a>

<a href="logout.php">
🚪 Logout
</a>

</div>



<div class="main-content">


<div class="page-header">

<div>

<h1>
📊 Reports & Analytics
</h1>

<p>
Academic performance and student statistics.
</p>

</div>

</div>



<div class="stats-grid">


<div class="stat-card">

<h3>
Total Students
</h3>

<h1>

<?php echo $total_students; ?>

</h1>

</div>



<div class="stat-card">

<h3>
Passed Results
</h3>

<h1>

<?php echo $total_pass; ?>

</h1>

</div>



<div class="stat-card">

<h3>
Failed Results
</h3>

<h1>

<?php echo $total_fail; ?>

</h1>

</div>



<div class="stat-card">

<h3>
Average Marks
</h3>

<h1>

<?php echo $average_marks; ?>%

</h1>

</div>


</div>



<div class="report-grid">


<div class="report-card">


<h2>
🏆 Top Performer
</h2>


<?php

if ($top_student) {

?>


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

Roll Number:

<?php
echo htmlspecialchars(
$top_student["roll_no"]
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


<?php

} else {

?>

<p>
No marks available yet.
</p>

<?php

}

?>


</div>



<div class="report-card">


<h2>
🏫 Department Distribution
</h2>


<table class="mini-table">


<tr>

<th>
Department
</th>

<th>
Students
</th>

</tr>


<?php

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
echo $department["total"];
?>

</td>

</tr>


<?php

}

?>


</table>


</div>


</div>


</div>

</div>


</body>

</html>