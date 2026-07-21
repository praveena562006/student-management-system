<?php

session_start();

include "db.php";


if (!isset($_SESSION["admin"])) {

    header("Location: login.php");

    exit();

}


if (!isset($_GET["id"])) {

    header("Location: view_students.php");

    exit();

}


$id = intval($_GET["id"]);


$result =
mysqli_query(
    $conn,
    "SELECT * FROM students WHERE id=$id"
);


$student =
mysqli_fetch_assoc($result);


if (!$student) {

    die("Student not found.");

}


/*
====================================
ATTENDANCE STATISTICS
====================================
*/

$attendance_result =
mysqli_query(
$conn,
"
SELECT

COUNT(*) AS total,

SUM(
CASE
WHEN status='Present'
THEN 1
ELSE 0
END
) AS present,

SUM(
CASE
WHEN status='Absent'
THEN 1
ELSE 0
END
) AS absent

FROM attendance

WHERE student_id=$id
"
);


$attendance =
mysqli_fetch_assoc($attendance_result);


$total_days =
$attendance["total"];


$present_days =
$attendance["present"] ?? 0;


$absent_days =
$attendance["absent"] ?? 0;


if ($total_days > 0) {

$attendance_percentage =
round(
($present_days / $total_days) * 100,
2
);

} else {

$attendance_percentage = 0;

}


/*
====================================
MARKS
====================================
*/


$marks =
mysqli_query(
$conn,
"
SELECT *
FROM marks
WHERE student_id=$id
ORDER BY id DESC
"
);


?>


<!DOCTYPE html>

<html>


<head>

<title>

<?php
echo htmlspecialchars($student["name"]);
?>
- Student Profile

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

<a href="view_students.php">
👨‍🎓 Students
</a>

<a href="attendance.php">
📅 Attendance
</a>

<a href="marks.php">
📝 Marks
</a>

<a href="reports.php">
📊 Reports
</a>

</div>


<div class="main-content">


<div class="profile-header">


<div class="profile-avatar">

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


<div>


<h1>

<?php
echo htmlspecialchars($student["name"]);
?>

</h1>


<p>

<?php
echo htmlspecialchars($student["roll_no"]);
?>

•

<?php
echo htmlspecialchars($student["department"]);
?>

</p>


</div>


</div>



<div class="profile-grid">


<div class="profile-card">


<h2>
👤 Personal Information
</h2>


<div class="info-row">

<span>Registration Number</span>

<strong>
<?php echo htmlspecialchars($student["registration_no"]); ?>
</strong>

</div>


<div class="info-row">

<span>Date of Birth</span>

<strong>
<?php echo htmlspecialchars($student["dob"]); ?>
</strong>

</div>


<div class="info-row">

<span>Gender</span>

<strong>
<?php echo htmlspecialchars($student["gender"]); ?>
</strong>

</div>


<div class="info-row">

<span>Email</span>

<strong>
<?php echo htmlspecialchars($student["email"]); ?>
</strong>

</div>


<div class="info-row">

<span>Phone</span>

<strong>
<?php echo htmlspecialchars($student["phone"]); ?>
</strong>

</div>


<div class="info-row">

<span>Address</span>

<strong>
<?php echo htmlspecialchars($student["address"]); ?>
</strong>

</div>


</div>



<div class="profile-card">


<h2>
🎓 Academic Information
</h2>


<div class="info-row">

<span>Department</span>

<strong>
<?php echo htmlspecialchars($student["department"]); ?>
</strong>

</div>


<div class="info-row">

<span>Year</span>

<strong>
<?php echo htmlspecialchars($student["year"]); ?>
</strong>

</div>


<div class="info-row">

<span>Semester</span>

<strong>
<?php echo htmlspecialchars($student["semester"]); ?>
</strong>

</div>


</div>


</div>



<h2 class="section-title">

📅 Attendance Summary

</h2>


<div class="stats-grid">


<div class="stat-card">

<h3>
Working Days
</h3>

<h1>

<?php echo $total_days; ?>

</h1>

</div>


<div class="stat-card">

<h3>
Present
</h3>

<h1>

<?php echo $present_days; ?>

</h1>

</div>


<div class="stat-card">

<h3>
Absent
</h3>

<h1>

<?php echo $absent_days; ?>

</h1>

</div>


<div class="stat-card">

<h3>
Attendance
</h3>

<h1>

<?php
echo $attendance_percentage;
?>%

</h1>


<?php

if ($attendance_percentage >= 75) {

?>

<span class="good-status">
Eligible
</span>

<?php

} else {

?>

<span class="bad-status">
Shortage
</span>

<?php

}

?>


</div>


</div>



<h2 class="section-title">

📝 Academic Results

</h2>


<div class="table-card">


<table class="modern-table">


<tr>

<th>Subject</th>

<th>Internal</th>

<th>External</th>

<th>Total</th>

<th>Grade</th>

<th>Result</th>

</tr>


<?php

if (mysqli_num_rows($marks) > 0) {


while ($mark =
mysqli_fetch_assoc($marks)) {

?>


<tr>


<td>

<?php
echo htmlspecialchars($mark["subject"]);
?>

</td>


<td>

<?php echo $mark["internal_marks"]; ?>

</td>


<td>

<?php echo $mark["external_marks"]; ?>

</td>


<td>

<strong>

<?php echo $mark["total_marks"]; ?>

</strong>

</td>


<td>

<span class="grade-badge">

<?php echo $mark["grade"]; ?>

</span>

</td>


<td>


<?php

if ($mark["result"] == "Pass") {

?>


<span class="good-status">

PASS

</span>


<?php

} else {

?>


<span class="bad-status">

FAIL

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

<td colspan="6" class="no-data">

No marks have been added yet.

</td>

</tr>


<?php

}

?>


</table>


</div>


</div>

</div>


</body>

</html>