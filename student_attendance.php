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
   GET STUDENT
========================================================= */

$student_stmt =
    mysqli_prepare(
        $conn,

        "SELECT
            name,
            registration_no,
            department

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
   ATTENDANCE SUMMARY
========================================================= */

$summary_stmt =
    mysqli_prepare(
        $conn,

        "SELECT

            COUNT(*) AS total,

            SUM(
                CASE
                    WHEN status = 'Present'
                    THEN 1
                    ELSE 0
                END
            ) AS present,

            SUM(
                CASE
                    WHEN status = 'Absent'
                    THEN 1
                    ELSE 0
                END
            ) AS absent

         FROM attendance

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


$total =
    intval(
        $summary["total"] ?? 0
    );


$present =
    intval(
        $summary["present"] ?? 0
    );


$absent =
    intval(
        $summary["absent"] ?? 0
    );


$percentage =
    $total > 0

    ? round(
        ($present / $total) * 100,
        2
    )

    : 0;


/* =========================================================
   GET ATTENDANCE HISTORY
========================================================= */

$attendance_stmt =
    mysqli_prepare(
        $conn,

        "SELECT
            attendance_date,
            status

         FROM attendance

         WHERE student_id = ?

         ORDER BY attendance_date DESC"
    );


mysqli_stmt_bind_param(
    $attendance_stmt,
    "i",
    $student_id
);


mysqli_stmt_execute(
    $attendance_stmt
);


$result =
    mysqli_stmt_get_result(
        $attendance_stmt
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
My Attendance - EduTrack
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


<a
    href="student_attendance.php"
    class="active"
>

📅 My Attendance

</a>


<a href="student_marks.php">

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

📅 My Attendance

</h1>


<p>

Track your attendance and
view your complete history.

</p>


</div>


<div class="student-identity-badge">


<?php

echo htmlspecialchars(
    $student["registration_no"]
);

?>


•


<?php

echo htmlspecialchars(
    $student["department"]
);

?>


</div>


</div>



<!-- ATTENDANCE CARDS -->


<div class="attendance-summary-grid">


<div class="attendance-summary-card">


<span>

📊

</span>


<p>

Overall Attendance

</p>


<h2>

<?php

echo $percentage;

?>%

</h2>


</div>



<div class="attendance-summary-card">


<span>

📚

</span>


<p>

Working Days

</p>


<h2>

<?php

echo $total;

?>

</h2>


</div>



<div class="attendance-summary-card present-card">


<span>

✅

</span>


<p>

Present

</p>


<h2>

<?php

echo $present;

?>

</h2>


</div>



<div class="attendance-summary-card absent-card">


<span>

❌

</span>


<p>

Absent

</p>


<h2>

<?php

echo $absent;

?>

</h2>


</div>


</div>



<!-- ATTENDANCE STATUS -->


<div class="student-dashboard-card">


<div class="attendance-progress-info">


<strong>

Attendance Progress

</strong>


<strong>

<?php

echo $percentage;

?>%

</strong>


</div>


<div class="attendance-progress-track">


<div

    class="attendance-progress-fill"

    style="width:
    <?php

    echo min(
        $percentage,
        100
    );

    ?>%;"

>

</div>


</div>



<?php


if ($total == 0) {


?>


<div class="student-info-note">

ℹ No attendance records
have been added yet.

</div>


<?php


}

elseif ($percentage >= 75) {


?>


<div class="student-success-note">

✓ You currently meet the
minimum 75% attendance requirement.

</div>


<?php


}

else {


?>


<div class="student-warning">

⚠ Attendance Shortage:
Your attendance is below 75%.

</div>


<?php

}


?>


</div>



<!-- HISTORY -->


<div class="student-table-section">


<div class="table-section-heading">


<h2>

Attendance History

</h2>


<span>

<?php

echo $total;

?>

Records

</span>


</div>



<div class="student-table-card">


<table class="student-record-table">


<thead>


<tr>


<th>

Date

</th>


<th>

Day

</th>


<th>

Status

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


<?php


echo date(

    "d M Y",

    strtotime(
        $row["attendance_date"]
    )

);


?>


</td>



<td>


<?php


echo date(

    "l",

    strtotime(
        $row["attendance_date"]
    )

);


?>


</td>



<td>


<?php


if (
    $row["status"] == "Present"
) {


?>


<span class="attendance-present">

✓ Present

</span>


<?php


}

else {


?>


<span class="attendance-absent">

✕ Absent

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
    colspan="3"
    class="student-no-records"
>


📭 No attendance records available.


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