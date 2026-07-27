<?php

session_start();
require_once 'db.php';

/* =========================================================
   ADMIN LOGIN CHECK
========================================================= */

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}

$message = "";
$message_type = "";


/* =========================================================
   SAVE ATTENDANCE
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['save_attendance'])
) {

    $timetable_id = intval($_POST['timetable_id']);
    $attendance_date = $_POST['attendance_date'] ?? '';

    /* Get timetable information */

    $stmt = $conn->prepare("
        SELECT *
        FROM timetable
        WHERE id = ?
    ");

    $stmt->bind_param("i", $timetable_id);
    $stmt->execute();

    $timetable =
        $stmt->get_result()->fetch_assoc();

    $stmt->close();


    if (!$timetable) {

        $message = "Timetable session not found.";
        $message_type = "error";

    } elseif (empty($attendance_date)) {

        $message = "Please select the attendance date.";
        $message_type = "error";

    } elseif ($attendance_date > date("Y-m-d")) {

        $message = "Attendance cannot be entered for a future date.";
        $message_type = "error";

    } else {

        /* =================================================
           CREATE / FIND ATTENDANCE SESSION
        ================================================= */

        $stmt = $conn->prepare("
            SELECT id
            FROM attendance_sessions
            WHERE timetable_id = ?
            AND attendance_date = ?
            LIMIT 1
        ");

        $stmt->bind_param(
            "is",
            $timetable_id,
            $attendance_date
        );

        $stmt->execute();

        $existing =
            $stmt->get_result()->fetch_assoc();

        $stmt->close();


        if ($existing) {

            $session_id =
                intval($existing['id']);

        } else {

            $semester =
                intval($timetable['semester']);

            $start_period =
                intval($timetable['start_period']);

            $end_period =
                intval($timetable['end_period']);


            $stmt = $conn->prepare("
                INSERT INTO attendance_sessions
                (
                    timetable_id,
                    attendance_date,
                    department,
                    section,
                    year,
                    semester,
                    subject_code,
                    subject_name,
                    start_period,
                    end_period
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");


            $stmt->bind_param(
                "issssissii",
                $timetable_id,
                $attendance_date,
                $timetable['department'],
                $timetable['section'],
                $timetable['year'],
                $semester,
                $timetable['subject_code'],
                $timetable['subject_name'],
                $start_period,
                $end_period
            );


            if ($stmt->execute()) {

                $session_id =
                    $conn->insert_id;

            } else {

                $session_id = 0;

                $message =
                    "Could not create attendance session: "
                    . $stmt->error;

                $message_type = "error";
            }

            $stmt->close();
        }


        /* =================================================
           SAVE STUDENT ATTENDANCE
        ================================================= */

        if (
            !empty($session_id)
            && isset($_POST['attendance'])
            && is_array($_POST['attendance'])
        ) {

            $saved_count = 0;


            $record_stmt = $conn->prepare("
                INSERT INTO attendance_records
                (
                    session_id,
                    student_id,
                    status
                )

                VALUES (?, ?, ?)

                ON DUPLICATE KEY UPDATE
                status = VALUES(status)
            ");


            foreach (
                $_POST['attendance']
                as $student_id => $status
            ) {

                $student_id =
                    intval($student_id);


                if (
                    $status !== 'Present'
                    && $status !== 'Absent'
                ) {
                    continue;
                }


                $record_stmt->bind_param(
                    "iis",
                    $session_id,
                    $student_id,
                    $status
                );


                if ($record_stmt->execute()) {
                    $saved_count++;
                }
            }


            $record_stmt->close();


            $message =
                "Attendance saved successfully for "
                . $saved_count
                . " student(s)!";

            $message_type = "success";

        } elseif (empty($message)) {

            $message =
                "No student attendance was submitted.";

            $message_type = "error";
        }
    }
}


/* =========================================================
   LOAD TIMETABLE
========================================================= */

$timetable_result = $conn->query("
    SELECT *
    FROM timetable

    ORDER BY
        department,
        section,
        year,
        semester,

        FIELD(
            day_of_week,
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday'
        ),

        start_period
");


/* =========================================================
   LOAD STUDENTS WHEN CLASS SELECTED
========================================================= */

$students = [];

$selected_timetable = null;


if (isset($_GET['timetable_id'])) {

    $timetable_id =
        intval($_GET['timetable_id']);


    /* Get selected timetable */

    $stmt = $conn->prepare("
        SELECT *
        FROM timetable
        WHERE id = ?
    ");

    $stmt->bind_param(
        "i",
        $timetable_id
    );

    $stmt->execute();


    $selected_timetable =
        $stmt
        ->get_result()
        ->fetch_assoc();


    $stmt->close();


    /* =====================================================
       GET STUDENTS OF EXACT CLASS
    ===================================================== */

    if ($selected_timetable) {

        $department =
            trim($selected_timetable['department']);

        $section =
            trim($selected_timetable['section']);

        $year =
            trim($selected_timetable['year']);

        $semester =
            intval($selected_timetable['semester']);


        $stmt = $conn->prepare("
            SELECT *
            FROM students

            WHERE TRIM(department) = ?
            AND TRIM(section) = ?
            AND TRIM(year) = ?
            AND semester = ?

            ORDER BY roll_no
        ");


        $stmt->bind_param(
            "sssi",
            $department,
            $section,
            $year,
            $semester
        );


        $stmt->execute();


        $students =
            $stmt
            ->get_result()
            ->fetch_all(MYSQLI_ASSOC);


        $stmt->close();
    }
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
Take Attendance - EduTrack
</title>


<style>

body {
    font-family: Arial, sans-serif;
    background: #f4f7fb;
    margin: 0;
    padding: 30px;
}


.container {
    max-width: 1100px;
    margin: auto;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
}


h1 {
    color: #334e7d;
    margin-bottom: 30px;
}


select,
input[type="date"] {
    padding: 10px;
    margin: 5px;
    border: 1px solid #ccc;
    border-radius: 6px;
}


select {
    min-width: 600px;
}


button {
    padding: 10px 20px;
    background: #4169e1;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}


button:hover {
    background: #3154c7;
}


table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}


th {
    background: #4169e1;
    color: white;
    padding: 12px;
}


td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}


.present {
    color: green;
    font-weight: bold;
}


.absent {
    color: red;
    font-weight: bold;
}


.message {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 6px;
}


.success-message {
    background: #d4edda;
    color: #155724;
}


.error-message {
    background: #f8d7da;
    color: #721c24;
}


.session-info {
    background: #eef3ff;
    padding: 20px;
    margin-top: 20px;
    border-left: 5px solid #4169e1;
    border-radius: 5px;
}


.info-row {
    margin-bottom: 12px;
}


.info-row:last-child {
    margin-bottom: 0;
}


.no-students {
    background: #fff3cd;
    color: #856404;
    padding: 18px;
    margin-top: 20px;
    border-radius: 6px;
    line-height: 1.7;
}


@media (max-width: 750px) {

    body {
        padding: 10px;
    }

    .container {
        padding: 15px;
    }

    select {
        min-width: 100%;
        width: 100%;
    }
}

</style>

</head>


<body>


<div class="container">


<h1>
EduTrack - Take Attendance
</h1>



<?php if ($message): ?>


<div class="<?php

echo
    $message_type === "success"
    ? "message success-message"
    : "message error-message";

?>">


<?php
echo htmlspecialchars($message);
?>


</div>


<?php endif; ?>



<!-- =====================================================
     SELECT CLASS
===================================================== -->


<form method="GET">


<label>

<strong>
Select Class:
</strong>

</label>


<select
    name="timetable_id"
    required
>


<option value="">

Select timetable session

</option>



<?php while (
    $row =
    $timetable_result->fetch_assoc()
): ?>


<option

value="<?php
echo intval($row['id']);
?>"

<?php

if (
    isset($_GET['timetable_id'])
    &&
    intval($_GET['timetable_id'])
    === intval($row['id'])
) {

    echo "selected";
}

?>

>


<?php

echo htmlspecialchars(

    $row['department']

    . " - "

    . $row['section']

    . " | "

    . $row['year']

    . " | Sem "

    . $row['semester']

    . " | "

    . $row['day_of_week']

    . " | "

    . $row['subject_code']

    . " | P"

    . $row['start_period']

    . "-P"

    . $row['end_period']

);

?>


</option>


<?php endwhile; ?>


</select>



<button type="submit">

Load Students

</button>


</form>



<?php if ($selected_timetable): ?>



<!-- =====================================================
     SELECTED CLASS INFORMATION
===================================================== -->


<div class="session-info">


<div class="info-row">


<strong>
Department:
</strong>


<?php
echo htmlspecialchars(
    $selected_timetable['department']
);
?>


&nbsp;&nbsp;&nbsp;&nbsp;


<strong>
Section:
</strong>


<?php
echo htmlspecialchars(
    $selected_timetable['section']
);
?>


</div>



<div class="info-row">


<strong>
Year:
</strong>


<?php
echo htmlspecialchars(
    $selected_timetable['year']
);
?>


&nbsp;&nbsp;&nbsp;&nbsp;


<strong>
Semester:
</strong>


<?php
echo htmlspecialchars(
    $selected_timetable['semester']
);
?>


</div>



<div class="info-row">


<strong>
Subject:
</strong>


<?php
echo htmlspecialchars(
    $selected_timetable['subject_name']
);
?>


(


<?php
echo htmlspecialchars(
    $selected_timetable['subject_code']
);
?>


)


</div>



<div class="info-row">


<strong>
Periods:
</strong>


P<?php
echo intval(
    $selected_timetable['start_period']
);
?>


-


P<?php
echo intval(
    $selected_timetable['end_period']
);
?>


&nbsp;&nbsp;&nbsp;&nbsp;


<strong>
Day:
</strong>


<?php
echo htmlspecialchars(
    $selected_timetable['day_of_week']
);
?>


</div>


</div>



<!-- =====================================================
     CHECK WHETHER STUDENTS WERE FOUND
===================================================== -->


<?php if (empty($students)): ?>


<div class="no-students">


<strong>
No students found for this class.
</strong>


<br><br>


The selected timetable is looking for:


<br>


Department:

<strong>
<?php
echo htmlspecialchars(
    $selected_timetable['department']
);
?>
</strong>


<br>


Section:

<strong>
<?php
echo htmlspecialchars(
    $selected_timetable['section']
);
?>
</strong>


<br>


Year:

<strong>
<?php
echo htmlspecialchars(
    $selected_timetable['year']
);
?>
</strong>


<br>


Semester:

<strong>
<?php
echo htmlspecialchars(
    $selected_timetable['semester']
);
?>
</strong>


<br><br>


The student's Department, Section,
Year and Semester must match these
values.


</div>



<?php else: ?>



<!-- =====================================================
     ATTENDANCE FORM
===================================================== -->


<form method="POST">


<input
    type="hidden"
    name="timetable_id"

    value="<?php
        echo intval(
            $selected_timetable['id']
        );
    ?>"
>



<br>



<label>

<strong>
Attendance Date:
</strong>

</label>



<input

    type="date"

    name="attendance_date"

    value="<?php
        echo date('Y-m-d');
    ?>"

    max="<?php
        echo date('Y-m-d');
    ?>"

    required

>



<table>


<tr>

<th>
Roll No
</th>

<th>
Student Name
</th>

<th>
Present
</th>

<th>
Absent
</th>

</tr>



<?php foreach (
    $students
    as $student
): ?>


<tr>


<td>

<?php
echo htmlspecialchars(
    $student['roll_no']
);
?>

</td>



<td>

<?php
echo htmlspecialchars(
    $student['name']
);
?>

</td>



<td>


<label class="present">


<input

    type="radio"

    name="attendance[<?php
        echo intval(
            $student['id']
        );
    ?>]"

    value="Present"

    checked

>


Present


</label>


</td>



<td>


<label class="absent">


<input

    type="radio"

    name="attendance[<?php
        echo intval(
            $student['id']
        );
    ?>]"

    value="Absent"

>


Absent


</label>


</td>


</tr>


<?php endforeach; ?>


</table>



<br>



<button
    type="submit"
    name="save_attendance"
>

Save Attendance

</button>


</form>



<?php endif; ?>


<?php endif; ?>


</div>


</body>

</html>