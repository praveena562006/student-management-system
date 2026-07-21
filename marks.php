<?php

session_start();

include "db.php";


if (!isset($_SESSION["admin"])) {

    header("Location: login.php");

    exit();

}


$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $student_id =
        intval($_POST["student_id"]);


    $subject =
        $_POST["subject"];


    $internal =
        intval($_POST["internal_marks"]);


    $external =
        intval($_POST["external_marks"]);


    /*
     Automatic total calculation
    */

    $total =
        $internal + $external;


    /*
     Automatic Grade Calculation
    */

    if ($total >= 90) {

        $grade = "O";

    } elseif ($total >= 80) {

        $grade = "A+";

    } elseif ($total >= 70) {

        $grade = "A";

    } elseif ($total >= 60) {

        $grade = "B+";

    } elseif ($total >= 50) {

        $grade = "B";

    } elseif ($total >= 40) {

        $grade = "C";

    } else {

        $grade = "F";

    }


    /*
     Automatic result
    */

    if ($total >= 40) {

        $result_status = "Pass";

    } else {

        $result_status = "Fail";

    }


    $sql = "

    INSERT INTO marks

    (
        student_id,
        subject,
        internal_marks,
        external_marks,
        total_marks,
        grade,
        result
    )

    VALUES

    (
        $student_id,
        '$subject',
        $internal,
        $external,
        $total,
        '$grade',
        '$result_status'
    )

    ";


    if (mysqli_query($conn, $sql)) {

        $message =
        "Marks added successfully! Grade: $grade | Result: $result_status";

    }

}


$students =
mysqli_query(
    $conn,
    "SELECT * FROM students ORDER BY name"
);


$marks =
mysqli_query(
    $conn,
    "SELECT
        marks.*,
        students.name,
        students.roll_no

     FROM marks

     JOIN students
     ON marks.student_id = students.id

     ORDER BY marks.id DESC"
);

?>


<!DOCTYPE html>

<html>


<head>

<title>
Marks Management
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

<a href="marks.php" class="active">
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

<h1>
📝 Marks Management
</h1>

<p>
Enter marks and automatically calculate grades.
</p>

</div>

</div>


<?php

if ($message != "") {

?>

<div class="alert-success">

✓ <?php echo $message; ?>

</div>

<?php

}

?>


<div class="marks-layout">


<div class="marks-form-card">


<h2>
Add Student Marks
</h2>


<form method="POST">


<label>
Student
</label>


<select
name="student_id"
required
>


<option value="">
Select Student
</option>


<?php

while ($student =
mysqli_fetch_assoc($students)) {

?>


<option
value="<?php echo $student["id"]; ?>"
>

<?php

echo htmlspecialchars(
$student["roll_no"]
.
" - "
.
$student["name"]
);

?>

</option>


<?php

}

?>


</select>



<label>
Subject
</label>


<input
type="text"
name="subject"
placeholder="Example: Database Management Systems"
required
>



<label>
Internal Marks
</label>


<input
type="number"
id="internal"
name="internal_marks"
min="0"
max="30"
placeholder="Maximum 30"
required
oninput="calculateMarks()"
>



<label>
External Marks
</label>


<input
type="number"
id="external"
name="external_marks"
min="0"
max="70"
placeholder="Maximum 70"
required
oninput="calculateMarks()"
>



<div class="grade-preview">


<p>

Total:

<strong id="totalPreview">
0
</strong>

/100

</p>


<p>

Grade:

<strong id="gradePreview">
-
</strong>

</p>


<p>

Result:

<strong id="resultPreview">
-
</strong>

</p>


</div>


<button type="submit">

💾 Save Marks

</button>


</form>


</div>


</div>


<h2 class="section-title">

Recent Results

</h2>


<div class="table-card">


<table class="modern-table">


<tr>

<th>Student</th>

<th>Subject</th>

<th>Internal</th>

<th>External</th>

<th>Total</th>

<th>Grade</th>

<th>Result</th>

</tr>


<?php

while ($row =
mysqli_fetch_assoc($marks)) {

?>


<tr>


<td>

<?php

echo htmlspecialchars(
$row["roll_no"]
.
" - "
.
$row["name"]
);

?>

</td>


<td>

<?php
echo htmlspecialchars($row["subject"]);
?>

</td>


<td>

<?php echo $row["internal_marks"]; ?>

</td>


<td>

<?php echo $row["external_marks"]; ?>

</td>


<td>

<strong>

<?php echo $row["total_marks"]; ?>

</strong>

</td>


<td>

<span class="grade-badge">

<?php echo $row["grade"]; ?>

</span>

</td>


<td>


<?php

if ($row["result"] == "Pass") {

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

?>


</table>


</div>


</div>

</div>


<script src="js/script.js"></script>


<script>

function calculateMarks() {


let internal =
parseInt(
document.getElementById("internal").value
) || 0;


let external =
parseInt(
document.getElementById("external").value
) || 0;


let total =
internal + external;


let grade;


if (total >= 90) {

grade = "O";

}

else if (total >= 80) {

grade = "A+";

}

else if (total >= 70) {

grade = "A";

}

else if (total >= 60) {

grade = "B+";

}

else if (total >= 50) {

grade = "B";

}

else if (total >= 40) {

grade = "C";

}

else {

grade = "F";

}


let result =
total >= 40
? "PASS"
: "FAIL";


document.getElementById(
"totalPreview"
).innerText = total;


document.getElementById(
"gradePreview"
).innerText = grade;


document.getElementById(
"resultPreview"
).innerText = result;


}

</script>


</body>

</html>