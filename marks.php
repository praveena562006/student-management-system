<?php

session_start();
include "db.php";

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}

$message = "";
$message_type = "";

/* =====================================================
   SELECTED CLASS
===================================================== */

$department = $_GET["department"] ?? "";
$section    = $_GET["section"] ?? "";
$year       = $_GET["year"] ?? "";
$semester   = $_GET["semester"] ?? "";


/* =====================================================
   SAVE MARKS
===================================================== */

if (
    $_SERVER["REQUEST_METHOD"] == "POST" &&
    isset($_POST["save_marks"])
) {

    $department = $_POST["department"];
    $section    = $_POST["section"];
    $year       = $_POST["year"];
    $semester   = $_POST["semester"];
    $subject    = trim($_POST["subject"]);

    if (
        empty($department) ||
        empty($section) ||
        empty($year) ||
        empty($semester) ||
        empty($subject)
    ) {

        $message = "Please select class and subject.";
        $message_type = "error";

    } elseif (!isset($_POST["marks"])) {

        $message = "No student marks were entered.";
        $message_type = "error";

    } else {

        $saved_count = 0;

        foreach ($_POST["marks"] as $student_id => $student_marks) {

            $student_id = intval($student_id);

            /*
             Skip students where marks were
             completely left blank.
            */

            if (
                $student_marks["internal"] === "" &&
                $student_marks["external"] === ""
            ) {
                continue;
            }

            $internal =
                intval($student_marks["internal"]);

            $external =
                intval($student_marks["external"]);


            /* VALIDATE MARKS */

            if (
                $internal < 0 ||
                $internal > 30 ||
                $external < 0 ||
                $external > 70
            ) {
                continue;
            }


            /* TOTAL */

            $total =
                $internal + $external;


            /* GRADE */

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


            /* RESULT */

            $result_status =
                ($total >= 40)
                ? "Pass"
                : "Fail";


            /* =========================================
               CHECK WHETHER MARKS ALREADY EXIST
            ========================================= */

            $check_stmt =
                mysqli_prepare(
                    $conn,
                    "SELECT id
                     FROM marks
                     WHERE student_id = ?
                     AND subject = ?
                     LIMIT 1"
                );

            mysqli_stmt_bind_param(
                $check_stmt,
                "is",
                $student_id,
                $subject
            );

            mysqli_stmt_execute(
                $check_stmt
            );

            $check_result =
                mysqli_stmt_get_result(
                    $check_stmt
                );


            if (
                mysqli_num_rows(
                    $check_result
                ) > 0
            ) {

                /* UPDATE EXISTING MARKS */

                $update_stmt =
                    mysqli_prepare(
                        $conn,
                        "UPDATE marks
                         SET
                            internal_marks = ?,
                            external_marks = ?,
                            total_marks = ?,
                            grade = ?,
                            result = ?
                         WHERE student_id = ?
                         AND subject = ?"
                    );

                mysqli_stmt_bind_param(
                    $update_stmt,
                    "iiissis",
                    $internal,
                    $external,
                    $total,
                    $grade,
                    $result_status,
                    $student_id,
                    $subject
                );

                if (
                    mysqli_stmt_execute(
                        $update_stmt
                    )
                ) {
                    $saved_count++;
                }

                mysqli_stmt_close(
                    $update_stmt
                );

            } else {

                /* INSERT NEW MARKS */

                $insert_stmt =
                    mysqli_prepare(
                        $conn,
                        "INSERT INTO marks
                        (
                            student_id,
                            subject,
                            internal_marks,
                            external_marks,
                            total_marks,
                            grade,
                            result
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );

                mysqli_stmt_bind_param(
                    $insert_stmt,
                    "isiiiss",
                    $student_id,
                    $subject,
                    $internal,
                    $external,
                    $total,
                    $grade,
                    $result_status
                );

                if (
                    mysqli_stmt_execute(
                        $insert_stmt
                    )
                ) {
                    $saved_count++;
                }

                mysqli_stmt_close(
                    $insert_stmt
                );
            }

            mysqli_stmt_close(
                $check_stmt
            );
        }


        $message =
            "Marks saved successfully for "
            . $saved_count
            . " student(s)!";

        $message_type = "success";
    }
}


/* =====================================================
   LOAD STUDENTS FOR SELECTED CLASS
===================================================== */

$students = null;

if (
    $department != "" &&
    $section != "" &&
    $year != "" &&
    $semester != ""
) {

    $student_stmt =
        mysqli_prepare(
            $conn,
            "SELECT
                id,
                roll_no,
                name
             FROM students
             WHERE department = ?
             AND section = ?
             AND year = ?
             AND semester = ?
             ORDER BY roll_no ASC"
        );

    mysqli_stmt_bind_param(
        $student_stmt,
        "sssi",
        $department,
        $section,
        $year,
        $semester
    );

    mysqli_stmt_execute(
        $student_stmt
    );

    $students =
        mysqli_stmt_get_result(
            $student_stmt
        );
}


/* =====================================================
   LOAD SUBJECTS FROM TIMETABLE
===================================================== */

$subjects = null;

if (
    $department != "" &&
    $section != "" &&
    $year != "" &&
    $semester != ""
) {

    $subject_stmt =
        mysqli_prepare(
            $conn,
            "SELECT DISTINCT
                subject_code,
                subject_name
             FROM timetable
             WHERE department = ?
             AND section = ?
             AND year = ?
             AND semester = ?
             ORDER BY subject_name ASC"
        );

    mysqli_stmt_bind_param(
        $subject_stmt,
        "sssi",
        $department,
        $section,
        $year,
        $semester
    );

    mysqli_stmt_execute(
        $subject_stmt
    );

    $subjects =
        mysqli_stmt_get_result(
            $subject_stmt
        );
}


/* =====================================================
   RECENT MARKS
===================================================== */

$recent_marks =
    mysqli_query(
        $conn,
        "SELECT
            marks.*,
            students.name,
            students.roll_no,
            students.department,
            students.section,
            students.year,
            students.semester
         FROM marks
         JOIN students
         ON marks.student_id = students.id
         ORDER BY marks.id DESC
         LIMIT 50"
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
Marks Management - EduTrack
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

<a
    href="marks.php"
    class="active"
>
📝 Marks
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


<div class="page-header">

<div>

<h1>
📝 Marks Management
</h1>

<p>
Select a class and enter subject-wise marks.
</p>

</div>

</div>


<?php if ($message != "") { ?>

<div
class="<?php
echo ($message_type == "success")
    ? "alert-success"
    : "error-message";
?>"
>

<?php
echo htmlspecialchars($message);
?>

</div>

<?php } ?>


<!-- ==================================================
     CLASS SELECTION
================================================== -->

<div class="marks-form-card">

<h2>
Select Class
</h2>


<form
    method="GET"
    action="marks.php"
>


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

$departments = [
    "AI&DS",
    "CSBS",
    "CSE",
    "IT",
    "ECE",
    "EEE",
    "Mechanical",
    "Civil"
];

foreach ($departments as $dept) {

    $selected =
        ($department == $dept)
        ? "selected"
        : "";

?>

<option
    value="<?php echo htmlspecialchars($dept); ?>"
    <?php echo $selected; ?>
>

<?php
echo htmlspecialchars($dept);
?>

</option>

<?php } ?>

</select>



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

$sections = [
    "A",
    "B",
    "C",
    "D"
];

foreach ($sections as $sec) {

    $selected =
        ($section == $sec)
        ? "selected"
        : "";

?>

<option
    value="<?php echo $sec; ?>"
    <?php echo $selected; ?>
>

<?php echo $sec; ?>

</option>

<?php } ?>

</select>



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

$years = [
    "1st Year",
    "2nd Year",
    "3rd Year",
    "4th Year"
];

foreach ($years as $year_option) {

    $selected =
        ($year == $year_option)
        ? "selected"
        : "";

?>

<option
    value="<?php echo $year_option; ?>"
    <?php echo $selected; ?>
>

<?php echo $year_option; ?>

</option>

<?php } ?>

</select>



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

for ($i = 1; $i <= 8; $i++) {

    $selected =
        ($semester == $i)
        ? "selected"
        : "";

?>

<option
    value="<?php echo $i; ?>"
    <?php echo $selected; ?>
>

Semester <?php echo $i; ?>

</option>

<?php } ?>

</select>


<br><br>


<button type="submit">

🔍 Load Class

</button>


</form>

</div>



<?php

if (
    $department != "" &&
    $section != "" &&
    $year != "" &&
    $semester != ""
) {

?>


<!-- ==================================================
     SELECTED CLASS INFO
================================================== -->

<div class="student-dashboard-card">

<h3>
Selected Class
</h3>

<p>

<strong>Department:</strong>

<?php
echo htmlspecialchars($department);
?>

&nbsp;&nbsp;

<strong>Section:</strong>

<?php
echo htmlspecialchars($section);
?>

</p>


<p>

<strong>Year:</strong>

<?php
echo htmlspecialchars($year);
?>

&nbsp;&nbsp;

<strong>Semester:</strong>

<?php
echo htmlspecialchars($semester);
?>

</p>

</div>



<?php

$student_count =
    ($students)
    ? mysqli_num_rows($students)
    : 0;

?>


<?php if ($student_count > 0) { ?>


<!-- ==================================================
     MARKS ENTRY
================================================== -->

<div class="marks-form-card">

<h2>
Enter Marks
</h2>


<form method="POST">


<input
    type="hidden"
    name="department"
    value="<?php echo htmlspecialchars($department); ?>"
>

<input
    type="hidden"
    name="section"
    value="<?php echo htmlspecialchars($section); ?>"
>

<input
    type="hidden"
    name="year"
    value="<?php echo htmlspecialchars($year); ?>"
>

<input
    type="hidden"
    name="semester"
    value="<?php echo htmlspecialchars($semester); ?>"
>


<label>
Subject
</label>


<select
    name="subject"
    required
>

<option value="">
Select Subject
</option>


<?php

if (
    $subjects &&
    mysqli_num_rows($subjects) > 0
) {

    while (
        $subject_row =
        mysqli_fetch_assoc($subjects)
    ) {

        $subject_value =
            $subject_row["subject_code"];

?>

<option
value="<?php
echo htmlspecialchars(
    $subject_value
);
?>"
>

<?php

echo htmlspecialchars(
    $subject_row["subject_name"]
);

?>

(

<?php

echo htmlspecialchars(
    $subject_row["subject_code"]
);

?>

)

</option>

<?php

    }

}

?>

</select>


<br><br>


<div class="table-card">


<table class="modern-table">


<thead>

<tr>

<th>
Roll No
</th>

<th>
Student Name
</th>

<th>
Internal / 30
</th>

<th>
External / 70
</th>

<th>
Total / 100
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

while (
    $student =
    mysqli_fetch_assoc($students)
) {

    $student_id =
        intval($student["id"]);

?>


<tr>


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

<input
    type="number"

    name="marks[<?php
        echo $student_id;
    ?>][internal]"

    id="internal_<?php
        echo $student_id;
    ?>"

    min="0"
    max="30"

    oninput="calculateRow(
        <?php echo $student_id; ?>
    )"
>

</td>


<td>

<input
    type="number"

    name="marks[<?php
        echo $student_id;
    ?>][external]"

    id="external_<?php
        echo $student_id;
    ?>"

    min="0"
    max="70"

    oninput="calculateRow(
        <?php echo $student_id; ?>
    )"
>

</td>


<td>

<strong
id="total_<?php
echo $student_id;
?>"
>

0

</strong>

</td>


<td>

<span
id="grade_<?php
echo $student_id;
?>"
>

-

</span>

</td>


<td>

<span
id="result_<?php
echo $student_id;
?>"
>

-

</span>

</td>


</tr>


<?php } ?>


</tbody>


</table>


</div>


<br>


<button
    type="submit"
    name="save_marks"
    class="save-large-button"
>

💾 Save Marks

</button>


</form>

</div>


<?php } else { ?>


<div class="student-warning">

⚠ No students found for:

<br><br>

Department:
<strong>
<?php echo htmlspecialchars($department); ?>
</strong>

<br>

Section:
<strong>
<?php echo htmlspecialchars($section); ?>
</strong>

<br>

Year:
<strong>
<?php echo htmlspecialchars($year); ?>
</strong>

<br>

Semester:
<strong>
<?php echo htmlspecialchars($semester); ?>
</strong>

</div>


<?php } ?>


<?php } ?>



<!-- ==================================================
     RECENT RESULTS
================================================== -->


<h2 class="section-title">
Recent Results
</h2>


<div class="table-card">


<table class="modern-table">


<thead>

<tr>

<th>
Student
</th>

<th>
Class
</th>

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
    $recent_marks &&
    mysqli_num_rows($recent_marks) > 0
) {

while (
    $row =
    mysqli_fetch_assoc($recent_marks)
) {

?>


<tr>


<td>

<?php

echo htmlspecialchars(
    $row["roll_no"]
    . " - "
    . $row["name"]
);

?>

</td>


<td>

<?php

echo htmlspecialchars(
    $row["department"]
    . "-"
    . $row["section"]
);

?>

<br>

<small>

<?php

echo htmlspecialchars(
    $row["year"]
);

?>

|

Sem

<?php
echo intval(
    $row["semester"]
);
?>

</small>

</td>


<td>

<?php
echo htmlspecialchars(
    $row["subject"]
);
?>

</td>


<td>

<?php
echo intval(
    $row["internal_marks"]
);
?>

</td>


<td>

<?php
echo intval(
    $row["external_marks"]
);
?>

</td>


<td>

<strong>

<?php
echo intval(
    $row["total_marks"]
);
?>

</strong>

</td>


<td>

<span class="grade-badge">

<?php
echo htmlspecialchars(
    $row["grade"]
);
?>

</span>

</td>


<td>


<?php

if (
    $row["result"] == "Pass"
) {

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

<?php } ?>


</td>


</tr>


<?php

}

} else {

?>


<tr>

<td
    colspan="8"
    class="no-data"
>

No marks records found.

</td>

</tr>


<?php } ?>


</tbody>


</table>


</div>


</div>

</div>


<script>


function calculateRow(studentId) {


    let internal =
        parseInt(
            document.getElementById(
                "internal_" + studentId
            ).value
        ) || 0;


    let external =
        parseInt(
            document.getElementById(
                "external_" + studentId
            ).value
        ) || 0;


    if (internal > 30) {
        internal = 30;
    }


    if (external > 70) {
        external = 70;
    }


    let total =
        internal + external;


    let grade = "";


    if (total >= 90) {

        grade = "O";

    } else if (total >= 80) {

        grade = "A+";

    } else if (total >= 70) {

        grade = "A";

    } else if (total >= 60) {

        grade = "B+";

    } else if (total >= 50) {

        grade = "B";

    } else if (total >= 40) {

        grade = "C";

    } else {

        grade = "F";
    }


    let result =
        total >= 40
        ? "PASS"
        : "FAIL";


    document.getElementById(
        "total_" + studentId
    ).innerText =
        total;


    document.getElementById(
        "grade_" + studentId
    ).innerText =
        grade;


    document.getElementById(
        "result_" + studentId
    ).innerText =
        result;
}


</script>


</body>

</html>