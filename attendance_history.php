<?php

session_start();

include "db.php";

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}


$sql = "

SELECT

students.id,

students.name,

students.roll_no,

students.department,

COUNT(attendance.id) AS total_days,

SUM(
    CASE
    WHEN attendance.status = 'Present'
    THEN 1
    ELSE 0
    END
) AS present_days,

SUM(
    CASE
    WHEN attendance.status = 'Absent'
    THEN 1
    ELSE 0
    END
) AS absent_days

FROM students

LEFT JOIN attendance
ON students.id = attendance.student_id

GROUP BY students.id

ORDER BY students.roll_no ASC

";


$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html>

<head>

<title>Attendance History</title>

<link rel="stylesheet" href="css/style.css">

</head>


<body>


<div class="navbar">

<h2>🎓 Student Management Portal</h2>

<a href="logout.php" class="logout-btn">
Logout
</a>

</div>


<div class="main-layout">


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

<a href="attendance_history.php" class="active">
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


<div class="main-content">


<div class="page-header">

<div>

<h1>📋 Attendance Report</h1>

<p>
Student attendance percentage and statistics.
</p>

</div>

</div>


<div class="table-card">


<table class="modern-table">


<tr>

<th>Roll No.</th>

<th>Student</th>

<th>Department</th>

<th>Total Days</th>

<th>Present</th>

<th>Absent</th>

<th>Percentage</th>

<th>Status</th>

</tr>


<?php

while ($row = mysqli_fetch_assoc($result)) {


$total = $row["total_days"];

$present = $row["present_days"];


if ($total > 0) {

    $percentage =
        round(($present / $total) * 100, 2);

} else {

    $percentage = 0;

}


?>


<tr>


<td>

<?php echo htmlspecialchars($row["roll_no"]); ?>

</td>


<td>

<a
href="student_profile.php?id=<?php echo $row["id"]; ?>"
class="student-link"
>

<?php echo htmlspecialchars($row["name"]); ?>

</a>

</td>


<td>

<?php echo htmlspecialchars($row["department"]); ?>

</td>


<td>

<?php echo $total; ?>

</td>


<td>

<span class="status-present">

<?php echo $present; ?>

</span>

</td>


<td>

<span class="status-absent">

<?php echo $row["absent_days"]; ?>

</span>

</td>


<td>

<strong>

<?php echo $percentage; ?>%

</strong>

</td>


<td>


<?php

if ($percentage >= 75) {

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