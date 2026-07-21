<?php

session_start();

include "db.php";


/* =========================================================
   STUDENT LOGIN PROTECTION
========================================================= */

if (
    !isset($_SESSION["student_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] != "student"
) {

    header(
        "Location: student_login.php"
    );

    exit();
}


$student_id =
    intval(
        $_SESSION["student_id"]
    );


/* =========================================================
   GET STUDENT INFORMATION
========================================================= */

$student_stmt =
    mysqli_prepare(
        $conn,

        "SELECT
            name,
            registration_no,
            department,
            year,
            semester

         FROM students

         WHERE id = ?"
    );


mysqli_stmt_bind_param(
    $student_stmt,
    "i",
    $student_id
);


mysqli_stmt_execute(
    $student_stmt
);


$student_result =
    mysqli_stmt_get_result(
        $student_stmt
    );


$student =
    mysqli_fetch_assoc(
        $student_result
    );


/* =========================================================
   GET STUDENT MARKS
========================================================= */

$marks_stmt =
    mysqli_prepare(
        $conn,

        "SELECT *

         FROM marks

         WHERE student_id = ?

         ORDER BY id DESC"
    );


mysqli_stmt_bind_param(
    $marks_stmt,
    "i",
    $student_id
);


mysqli_stmt_execute(
    $marks_stmt
);


$result =
    mysqli_stmt_get_result(
        $marks_stmt
    );


/* =========================================================
   MARKS SUMMARY
========================================================= */

$summary_stmt =
    mysqli_prepare(
        $conn,

        "SELECT

            COUNT(*) AS total_subjects,

            ROUND(
                AVG(total_marks),
                2
            ) AS average_marks,

            MAX(total_marks)
                AS highest_marks,

            MIN(total_marks)
                AS lowest_marks

         FROM marks

         WHERE student_id = ?"
    );


mysqli_stmt_bind_param(
    $summary_stmt,
    "i",
    $student_id
);


mysqli_stmt_execute(
    $summary_stmt
);


$summary_result =
    mysqli_stmt_get_result(
        $summary_stmt
    );


$summary =
    mysqli_fetch_assoc(
        $summary_result
    );


$total_subjects =
    intval(
        $summary["total_subjects"]
        ?? 0
    );


$average_marks =
    $summary["average_marks"]
    ?? 0;


$highest_marks =
    $summary["highest_marks"]
    ?? 0;


$lowest_marks =
    $summary["lowest_marks"]
    ?? 0;


/* =========================================================
   OVERALL GRADE
========================================================= */

if ($total_subjects == 0) {

    $overall_grade = "N/A";

}

elseif ($average_marks >= 90) {

    $overall_grade = "A+";

}

elseif ($average_marks >= 80) {

    $overall_grade = "A";

}

elseif ($average_marks >= 70) {

    $overall_grade = "B";

}

elseif ($average_marks >= 60) {

    $overall_grade = "C";

}

elseif ($average_marks >= 50) {

    $overall_grade = "D";

}

else {

    $overall_grade = "F";

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
My Marks - EduTrack
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

🎓 EduTrack

</h2>


<div>


<span>

👤

<?php

echo htmlspecialchars(
    $student["name"]
);

?>

</span>


<a
    href="student_logout.php"
    class="logout-btn"
>

Logout

</a>


</div>


</div>



<div class="main-layout">


<!-- SIDEBAR -->


<div class="sidebar student-sidebar">


<h3>

STUDENT PORTAL

</h3>


<a href="student_dashboard.php">

🏠 Dashboard

</a>


<a href="student_attendance.php">

📅 My Attendance

</a>


<a
    href="student_marks.php"
    class="active"
>

📝 My Marks

</a>


<a href="student_logout.php">

🚪 Logout

</a>


</div>



<!-- CONTENT -->


<div class="main-content">


<div class="student-page-header">


<div>


<h1>

📝 My Marks & Grades

</h1>


<p>

View your academic results
and overall performance.

</p>


</div>


<div class="student-identity-badge">


<?php

echo htmlspecialchars(
    $student["department"]
);

?>


•


<?php

echo htmlspecialchars(
    $student["year"]
);

?>


• Semester


<?php

echo htmlspecialchars(
    $student["semester"]
);

?>


</div>


</div>



<!-- ================================================
     PERFORMANCE CARDS
================================================ -->


<div class="marks-summary-grid">


<div class="marks-summary-card">


<span>

📚

</span>


<p>

Subjects

</p>


<h2>

<?php

echo $total_subjects;

?>

</h2>


</div>



<div class="marks-summary-card">


<span>

📊

</span>


<p>

Average

</p>


<h2>

<?php

echo $average_marks;

?>%

</h2>


</div>



<div class="marks-summary-card">


<span>

🏆

</span>


<p>

Highest Score

</p>


<h2>

<?php

echo $highest_marks;

?>

</h2>


</div>



<div class="marks-summary-card">


<span>

🎓

</span>


<p>

Overall Grade

</p>


<h2>

<?php

echo htmlspecialchars(
    $overall_grade
);

?>

</h2>


</div>


</div>



<!-- ================================================
     RESULT TABLE
================================================ -->


<div class="student-table-section">


<div class="table-section-heading">


<h2>

Subject-wise Results

</h2>


<span>

<?php

echo $total_subjects;

?>

Subjects

</span>


</div>



<div class="student-table-card">


<table class="student-record-table">


<thead>


<tr>


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
    mysqli_num_rows($result) > 0
) {


while (
    $row =
    mysqli_fetch_assoc($result)
) {


?>


<tr>


<td>


<strong>


<?php


echo htmlspecialchars(
    $row["subject"]
);


?>


</strong>


</td>



<td>


<?php


echo htmlspecialchars(
    $row["internal_marks"]
);


?>


/30


</td>



<td>


<?php


echo htmlspecialchars(
    $row["external_marks"]
);


?>


/70


</td>



<td>


<strong>


<?php


echo htmlspecialchars(
    $row["total_marks"]
);


?>


/100


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
    strtolower(
        $row["result"]
    ) == "pass"
) {


?>


<span class="result-pass">

✓ PASS

</span>


<?php


}

else {


?>


<span class="result-fail">

✕ FAIL

</span>


<?php

}


?>


</td>


</tr>


<?php


}


}

else {


?>


<tr>


<td
    colspan="6"
    class="student-no-records"
>


📭 No marks have been
published yet.


</td>


</tr>


<?php

}


?>


</tbody>


</table>


</div>


</div>


</div>


</div>


</body>

</html>