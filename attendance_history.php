<?php

session_start();
include "db.php";

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit();
}

$message = "";
$message_type = "";


/* =========================================================
   UPDATE ATTENDANCE
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] == "POST" &&
    isset($_POST["update_attendance"])
) {

    $record_id = intval($_POST["record_id"]);
    $new_status = $_POST["status"] ?? "";

    if (
        $record_id <= 0 ||
        !in_array(
            $new_status,
            ["Present", "Absent"],
            true
        )
    ) {

        $message = "Invalid attendance update.";
        $message_type = "error";

    } else {

        $update_stmt = mysqli_prepare(
            $conn,
            "UPDATE attendance_records
             SET status = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $update_stmt,
            "si",
            $new_status,
            $record_id
        );

        if (mysqli_stmt_execute($update_stmt)) {

            if (mysqli_stmt_affected_rows($update_stmt) >= 0) {

                $message =
                    "Attendance updated successfully!";

                $message_type = "success";

            } else {

                $message =
                    "Attendance could not be updated.";

                $message_type = "error";
            }

        } else {

            $message =
                "Attendance update failed.";

            $message_type = "error";
        }

        mysqli_stmt_close($update_stmt);
    }
}


/* =========================================================
   FILTER VALUES
========================================================= */

$department =
    isset($_GET["department"])
    ? trim($_GET["department"])
    : "";

$section =
    isset($_GET["section"])
    ? trim($_GET["section"])
    : "";

$date =
    isset($_GET["date"])
    ? trim($_GET["date"])
    : "";


/* =========================================================
   GET FILTER OPTIONS
========================================================= */

$departments = mysqli_query(
    $conn,
    "SELECT DISTINCT department
     FROM timetable
     ORDER BY department"
);

$sections = mysqli_query(
    $conn,
    "SELECT DISTINCT section
     FROM timetable
     ORDER BY section"
);


/* =========================================================
   GET ATTENDANCE HISTORY
========================================================= */

$sql = "
SELECT

    ar.id AS record_id,
    ar.status,

    s.id AS student_id,
    s.roll_no,
    s.name AS student_name,

    ats.id AS session_id,
    ats.attendance_date,

    t.department,
    t.section,
    t.year,
    t.semester,
    t.day_of_week,
    t.subject_code,
    t.subject_name,
    t.start_period,
    t.end_period

FROM attendance_records ar

INNER JOIN students s
    ON ar.student_id = s.id

INNER JOIN attendance_sessions ats
    ON ar.session_id = ats.id

INNER JOIN timetable t
    ON ats.timetable_id = t.id

WHERE 1 = 1
";


$params = [];
$types = "";


/* Department Filter */

if ($department != "") {

    $sql .= " AND t.department = ?";

    $params[] = $department;
    $types .= "s";
}


/* Section Filter */

if ($section != "") {

    $sql .= " AND t.section = ?";

    $params[] = $section;
    $types .= "s";
}


/* Date Filter */

if ($date != "") {

    $sql .= " AND ats.attendance_date = ?";

    $params[] = $date;
    $types .= "s";
}


$sql .= "
ORDER BY
    ats.attendance_date DESC,
    t.department ASC,
    t.section ASC,
    t.start_period ASC,
    s.roll_no ASC
";


$stmt =
    mysqli_prepare(
        $conn,
        $sql
    );


if (!empty($params)) {

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );
}


mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result($stmt);


/* =========================================================
   SUMMARY
========================================================= */

$total_records = 0;
$total_present = 0;
$total_absent = 0;

$rows = [];


while (
    $row =
    mysqli_fetch_assoc($result)
) {

    $rows[] = $row;

    $total_records++;

    if (
        $row["status"] == "Present"
    ) {

        $total_present++;
    }

    if (
        $row["status"] == "Absent"
    ) {

        $total_absent++;
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
Attendance History - EduTrack
</title>

<link
    rel="stylesheet"
    href="css/style.css"
>


<style>

.filter-card {
    background: white;
    padding: 22px;
    border-radius: 12px;
    margin-bottom: 22px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.06);
}

.filter-grid {
    display: grid;
    grid-template-columns:
        repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-weight: 600;
    margin-bottom: 7px;
}

.filter-group select,
.filter-group input {
    padding: 11px;
    border: 1px solid #d8dde5;
    border-radius: 7px;
}

.filter-button {
    background: #2878d0;
    color: white;
    border: none;
    padding: 12px 17px;
    border-radius: 7px;
    cursor: pointer;
}

.clear-button {
    display: inline-block;
    background: #6c757d;
    color: white;
    padding: 11px 17px;
    border-radius: 7px;
    text-decoration: none;
    text-align: center;
}

.summary-grid {
    display: grid;
    grid-template-columns:
        repeat(auto-fit, minmax(180px, 1fr));
    gap: 18px;
    margin-bottom: 25px;
}

.summary-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.06);
}

.summary-card h3 {
    margin: 0 0 8px 0;
    font-size: 15px;
    color: #666;
}

.summary-card strong {
    font-size: 28px;
}

.status-present {
    color: #198754;
    font-weight: 600;
}

.status-absent {
    color: #dc3545;
    font-weight: 600;
}

.history-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    overflow-x: auto;
    box-shadow: 0 3px 12px rgba(0,0,0,0.06);
}

.period-badge {
    background: #edf5ff;
    padding: 5px 9px;
    border-radius: 15px;
    white-space: nowrap;
}


/* EDIT ATTENDANCE */

.edit-attendance-form {
    display: flex;
    gap: 7px;
    align-items: center;
}

.edit-attendance-form select {
    padding: 7px;
    border: 1px solid #d8dde5;
    border-radius: 6px;
}

.edit-save-button {
    background: #2878d0;
    color: white;
    border: none;
    padding: 7px 11px;
    border-radius: 6px;
    cursor: pointer;
    white-space: nowrap;
}

.edit-save-button:hover {
    opacity: 0.9;
}

.alert-success {
    background: #d1e7dd;
    color: #0f5132;
    padding: 14px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-error {
    background: #f8d7da;
    color: #842029;
    padding: 14px;
    border-radius: 8px;
    margin-bottom: 20px;
}

</style>

</head>


<body class="edutrack-admin">


<!-- NAVBAR -->

<div class="navbar">

<h2>
🎓 Student Management Portal
</h2>

<div>

<span>

Welcome,

<?php
echo htmlspecialchars(
    $_SESSION["admin"]
);
?>

</span>

<a
    href="logout.php"
    class="logout-btn"
>
Logout
</a>

</div>

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

<a
    href="attendance_history.php"
    class="active"
>
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

<h1>
📋 Attendance History
</h1>

<p>
View and correct subject-wise student attendance.
</p>

</div>

</div>


<!-- MESSAGE -->

<?php if ($message != "") { ?>

<div
class="<?php
echo
    ($message_type == "success")
    ? "alert-success"
    : "alert-error";
?>"
>

<?php
echo htmlspecialchars($message);
?>

</div>

<?php } ?>



<!-- FILTERS -->

<div class="filter-card">

<form method="GET">

<div class="filter-grid">


<div class="filter-group">

<label>
Department
</label>

<select name="department">

<option value="">
All Departments
</option>


<?php

while (
    $filter_row =
    mysqli_fetch_assoc($departments)
) {

    $value =
        $filter_row["department"];

    $selected =
        ($department == $value)
        ? "selected"
        : "";

?>

<option
    value="<?php
        echo htmlspecialchars($value);
    ?>"
    <?php echo $selected; ?>
>

<?php
echo htmlspecialchars($value);
?>

</option>

<?php } ?>

</select>

</div>



<div class="filter-group">

<label>
Section
</label>

<select name="section">

<option value="">
All Sections
</option>


<?php

while (
    $filter_row =
    mysqli_fetch_assoc($sections)
) {

    $value =
        $filter_row["section"];

    $selected =
        ($section == $value)
        ? "selected"
        : "";

?>

<option
    value="<?php
        echo htmlspecialchars($value);
    ?>"
    <?php echo $selected; ?>
>

<?php
echo htmlspecialchars($value);
?>

</option>

<?php } ?>

</select>

</div>



<div class="filter-group">

<label>
Attendance Date
</label>

<input
    type="date"
    name="date"
    value="<?php
        echo htmlspecialchars($date);
    ?>"
>

</div>



<div class="filter-group">

<button
    type="submit"
    class="filter-button"
>
Apply Filters
</button>

</div>



<div class="filter-group">

<a
    href="attendance_history.php"
    class="clear-button"
>
Clear Filters
</a>

</div>


</div>

</form>

</div>



<!-- SUMMARY -->

<div class="summary-grid">


<div class="summary-card">

<h3>
Total Records
</h3>

<strong>
<?php echo $total_records; ?>
</strong>

</div>



<div class="summary-card">

<h3>
Present
</h3>

<strong class="status-present">
<?php echo $total_present; ?>
</strong>

</div>



<div class="summary-card">

<h3>
Absent
</h3>

<strong class="status-absent">
<?php echo $total_absent; ?>
</strong>

</div>



<div class="summary-card">

<h3>
Attendance %
</h3>

<strong>

<?php

if ($total_records > 0) {

    echo round(
        (
            $total_present /
            $total_records
        ) * 100,
        1
    );

} else {

    echo "0";
}

?>%

</strong>

</div>


</div>



<!-- HISTORY TABLE -->

<div class="history-card">


<table class="modern-table">


<thead>

<tr>

<th>Date</th>

<th>Roll No</th>

<th>Student</th>

<th>Department</th>

<th>Section</th>

<th>Year / Sem</th>

<th>Subject</th>

<th>Period</th>

<th>Status</th>

<th>Edit</th>

</tr>

</thead>


<tbody>


<?php

if (count($rows) > 0) {

foreach ($rows as $row) {

?>


<tr>


<td>

<?php

echo date(
    "d-m-Y",
    strtotime(
        $row["attendance_date"]
    )
);

?>

</td>



<td>

<?php
echo htmlspecialchars(
    $row["roll_no"]
);
?>

</td>



<td>

<strong>

<?php
echo htmlspecialchars(
    $row["student_name"]
);
?>

</strong>

</td>



<td>

<?php
echo htmlspecialchars(
    $row["department"]
);
?>

</td>



<td>

<?php
echo htmlspecialchars(
    $row["section"]
);
?>

</td>



<td>

<?php
echo htmlspecialchars(
    $row["year"]
);
?>

<br>

<small>

Semester

<?php
echo htmlspecialchars(
    $row["semester"]
);
?>

</small>

</td>



<td>

<strong>

<?php
echo htmlspecialchars(
    $row["subject_name"]
);
?>

</strong>

<br>

<small>

<?php
echo htmlspecialchars(
    $row["subject_code"]
);
?>

</small>

</td>



<td>

<span class="period-badge">

P<?php
echo intval(
    $row["start_period"]
);
?>

<?php

if (
    $row["start_period"]
    !=
    $row["end_period"]
) {

?>

-

P<?php
echo intval(
    $row["end_period"]
);
?>

<?php } ?>

</span>

</td>



<!-- CURRENT STATUS -->

<td>

<?php

if (
    $row["status"] == "Present"
) {

?>

<span class="status-present">

✓ Present

</span>

<?php

} else {

?>

<span class="status-absent">

✕ Absent

</span>

<?php } ?>

</td>



<!-- EDIT ATTENDANCE -->

<td>


<form
    method="POST"
    class="edit-attendance-form"
>


<input
    type="hidden"
    name="record_id"
    value="<?php
        echo intval(
            $row["record_id"]
        );
    ?>"
>


<select
    name="status"
    required
>


<option
    value="Present"

    <?php

    if (
        $row["status"]
        == "Present"
    ) {

        echo "selected";
    }

    ?>
>

Present

</option>


<option
    value="Absent"

    <?php

    if (
        $row["status"]
        == "Absent"
    ) {

        echo "selected";
    }

    ?>
>

Absent

</option>


</select>


<button
    type="submit"
    name="update_attendance"
    class="edit-save-button"
>

Save

</button>


</form>


</td>


</tr>


<?php

}

} else {

?>


<tr>

<td
    colspan="10"
    style="
        text-align:center;
        padding:30px;
    "
>

No attendance records found.

</td>

</tr>


<?php } ?>


</tbody>

</table>


</div>


</div>

</div>



<script src="js/script.js"></script>
</body>

</html>