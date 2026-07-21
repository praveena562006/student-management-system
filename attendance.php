<?php

session_start();

include "db.php";

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}

$message = "";

$selected_date = isset($_GET["date"])
    ? $_GET["date"]
    : date("Y-m-d");


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $selected_date = $_POST["attendance_date"];

    if (isset($_POST["attendance"])) {

        foreach ($_POST["attendance"] as $student_id => $status) {

            $student_id = intval($student_id);

            /*
             Check whether attendance for this
             student and date already exists.
            */

            $check_sql = "SELECT id FROM attendance
                          WHERE student_id = $student_id
                          AND attendance_date = '$selected_date'";

            $check_result = mysqli_query($conn, $check_sql);


            if (mysqli_num_rows($check_result) > 0) {

                /*
                 Attendance already exists,
                 so update it.
                */

                $sql = "UPDATE attendance
                        SET status = '$status'
                        WHERE student_id = $student_id
                        AND attendance_date = '$selected_date'";

            } else {

                /*
                 Attendance does not exist,
                 so create a new record.
                */

                $sql = "INSERT INTO attendance
                        (student_id, attendance_date, status)
                        VALUES
                        ($student_id, '$selected_date', '$status')";
            }

            mysqli_query($conn, $sql);
        }

        $message = "Attendance saved successfully!";
    }
}


/*
 Get all students.
*/

$students = mysqli_query(
    $conn,
    "SELECT * FROM students ORDER BY roll_no ASC"
);

?>

<!DOCTYPE html>

<html>

<head>

    <title>Attendance Management</title>

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

    <a href="attendance.php" class="active">
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


<!-- MAIN CONTENT -->

<div class="main-content">


<div class="page-header">

    <div>

        <h1>📅 Attendance Management</h1>

        <p>
            Mark daily attendance for students.
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


<form method="POST">


<div class="attendance-toolbar">


<div>

    <label>
        Attendance Date
    </label>

    <input
        type="date"
        name="attendance_date"
        value="<?php echo $selected_date; ?>"
        required
    >

</div>


<div class="attendance-actions">

    <button
        type="button"
        class="secondary-button"
        onclick="markAll('Present')"
    >

        ✓ Mark All Present

    </button>


    <button
        type="button"
        class="danger-action"
        onclick="markAll('Absent')"
    >

        ✕ Mark All Absent

    </button>

</div>


</div>


<div class="table-card">


<table class="modern-table">


<thead>

<tr>

    <th>Roll Number</th>

    <th>Student Name</th>

    <th>Department</th>

    <th>Year</th>

    <th>Attendance Status</th>

</tr>

</thead>


<tbody>


<?php

if (mysqli_num_rows($students) > 0) {

    while ($student = mysqli_fetch_assoc($students)) {

        $student_id = $student["id"];


        /*
         Find existing attendance for selected date.
        */

        $existing = mysqli_query(
            $conn,
            "SELECT status FROM attendance
             WHERE student_id = $student_id
             AND attendance_date = '$selected_date'"
        );


        $existing_row = mysqli_fetch_assoc($existing);

        $current_status =
            $existing_row
            ? $existing_row["status"]
            : "";

?>


<tr>

<td>

    <?php
    echo htmlspecialchars($student["roll_no"]);
    ?>

</td>


<td>

    <strong>

        <?php
        echo htmlspecialchars($student["name"]);
        ?>

    </strong>

</td>


<td>

    <span class="department-badge">

        <?php
        echo htmlspecialchars($student["department"]);
        ?>

    </span>

</td>


<td>

    <?php
    echo htmlspecialchars($student["year"]);
    ?>

</td>


<td>


<div class="attendance-options">


<label class="present-option">

<input
    type="radio"
    name="attendance[<?php echo $student_id; ?>]"
    value="Present"

    <?php

    if ($current_status == "Present")
        echo "checked";

    ?>

    required
>

<span>
✓ Present
</span>

</label>



<label class="absent-option">

<input
    type="radio"
    name="attendance[<?php echo $student_id; ?>]"
    value="Absent"

    <?php

    if ($current_status == "Absent")
        echo "checked";

    ?>

>

<span>
✕ Absent
</span>

</label>


</div>


</td>


</tr>


<?php

    }

} else {

?>


<tr>

<td colspan="5" class="no-data">

    No students found.

</td>

</tr>


<?php

}

?>


</tbody>


</table>


</div>


<button
    type="submit"
    class="save-large-button"
>

    💾 Save Attendance

</button>


</form>


</div>

</div>


<script src="js/script.js"></script>


</body>

</html>