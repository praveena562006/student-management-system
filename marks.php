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

    $department = $_POST["department"] ?? "";
    $section    = $_POST["section"] ?? "";
    $year       = $_POST["year"] ?? "";
    $semester   = $_POST["semester"] ?? "";
    $subject    = trim($_POST["subject"] ?? "");

    if (
        empty($department) ||
        empty($section) ||
        empty($year) ||
        empty($semester) ||
        empty($subject)
    ) {

        $message = "Please select the class and subject before saving marks.";
        $message_type = "error";

    } elseif (!isset($_POST["marks"])) {

        $message = "No student marks were entered.";
        $message_type = "error";

    } else {

        $saved_count = 0;

        foreach ($_POST["marks"] as $student_id => $student_marks) {

            $student_id = intval($student_id);

            $internal_raw = $student_marks["internal"] ?? "";
            $external_raw = $student_marks["external"] ?? "";

            /* Skip completely empty rows */
            if ($internal_raw === "" && $external_raw === "") {
                continue;
            }

            $internal = intval($internal_raw);
            $external = intval($external_raw);

            /* Validate marks */
            if (
                $internal < 0 ||
                $internal > 30 ||
                $external < 0 ||
                $external > 70
            ) {
                continue;
            }

            $total = $internal + $external;

            /* Grade */
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

            $result_status =
                ($total >= 40)
                ? "Pass"
                : "Fail";

            /* Check existing marks */
            $check_stmt = mysqli_prepare(
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

            mysqli_stmt_execute($check_stmt);

            $check_result =
                mysqli_stmt_get_result($check_stmt);

            if (mysqli_num_rows($check_result) > 0) {

                /* Update */
                $update_stmt = mysqli_prepare(
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

                if (mysqli_stmt_execute($update_stmt)) {
                    $saved_count++;
                }

                mysqli_stmt_close($update_stmt);

            } else {

                /* Insert */
                $insert_stmt = mysqli_prepare(
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

                if (mysqli_stmt_execute($insert_stmt)) {
                    $saved_count++;
                }

                mysqli_stmt_close($insert_stmt);
            }

            mysqli_stmt_close($check_stmt);
        }

        $message =
            "Marks saved successfully for "
            . $saved_count
            . " student(s).";

        $message_type = "success";
    }
}


/* =====================================================
   LOAD STUDENTS
===================================================== */

$students = null;

if (
    $department != "" &&
    $section != "" &&
    $year != "" &&
    $semester != ""
) {

    $student_stmt = mysqli_prepare(
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

    mysqli_stmt_execute($student_stmt);

    $students =
        mysqli_stmt_get_result($student_stmt);
}


/* =====================================================
   LOAD SUBJECTS
===================================================== */

$subjects = null;

if (
    $department != "" &&
    $section != "" &&
    $year != "" &&
    $semester != ""
) {

    $subject_stmt = mysqli_prepare(
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

    mysqli_stmt_execute($subject_stmt);

    $subjects =
        mysqli_stmt_get_result($subject_stmt);
}


/* =====================================================
   RECENT MARKS
===================================================== */

$recent_marks = mysqli_query(
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


/* =====================================================
   SUMMARY
===================================================== */

$total_marks_records = 0;
$passed_records = 0;
$failed_records = 0;
$overall_average = 0;

$summary_query = mysqli_query(
    $conn,
    "SELECT
        COUNT(*) AS total_records,
        SUM(CASE WHEN result = 'Pass' THEN 1 ELSE 0 END) AS passed,
        SUM(CASE WHEN result = 'Fail' THEN 1 ELSE 0 END) AS failed,
        ROUND(AVG(total_marks), 2) AS average_marks
     FROM marks"
);

if ($summary_query) {

    $summary = mysqli_fetch_assoc($summary_query);

    $total_marks_records =
        intval($summary["total_records"] ?? 0);

    $passed_records =
        intval($summary["passed"] ?? 0);

    $failed_records =
        intval($summary["failed"] ?? 0);

    $overall_average =
        floatval($summary["average_marks"] ?? 0);
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
Academic Assessment | EduTrack
</title>

<link
    rel="stylesheet"
    href="css/style.css"
>

<style>

/* =========================================================
   MARKS PAGE PROFESSIONAL EXTENSIONS
   Safe page-specific styling
========================================================= */

.marks-page-intro {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.marks-header-badge {
    padding: 8px 14px;
    background: rgba(255,255,255,0.13);
    border: 1px solid rgba(255,255,255,0.20);
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
}

.marks-overview-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.marks-overview-card {
    background: #ffffff;
    border: 1px solid #e8edf5;
    border-radius: 18px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 8px 25px rgba(15, 23, 42, 0.05);
    transition: 0.25s ease;
}

.marks-overview-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 35px rgba(15, 23, 42, 0.09);
}

.marks-overview-icon {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    background: #edf4ff;
    font-size: 22px;
}

.marks-overview-card p {
    margin: 0 0 4px;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.marks-overview-card h3 {
    margin: 0;
    color: #172033;
    font-size: 24px;
}

.academic-workspace {
    background: #ffffff;
    border: 1px solid #e6ebf2;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(15,23,42,0.06);
    overflow: hidden;
    margin-bottom: 25px;
}

.workspace-heading {
    padding: 20px 22px;
    border-bottom: 1px solid #edf0f5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.workspace-heading h2 {
    margin: 0 0 4px;
    font-size: 19px;
}

.workspace-heading p {
    margin: 0;
    color: #64748b;
    font-size: 13px;
}

.workspace-number {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: #eaf2ff;
    color: #1d5fd1;
    display: grid;
    place-items: center;
    font-weight: 800;
}

.class-filter-form {
    padding: 22px;
}

.class-filter-grid {
    display: grid;
    grid-template-columns:
        repeat(4, minmax(140px, 1fr))
        auto;
    gap: 14px;
    align-items: end;
}

.filter-field label {
    display: block;
    margin: 0 0 7px;
    font-size: 12px;
    color: #475569;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.class-filter-grid select {
    min-height: 44px;
}

.load-class-button {
    min-height: 44px;
    white-space: nowrap;
}

.selected-class-banner {
    margin-bottom: 22px;
    background: linear-gradient(
        100deg,
        #eef5ff,
        #f8fbff
    );
    border: 1px solid #d9e7fb;
    border-radius: 17px;
    padding: 18px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.selected-class-banner h3 {
    margin: 0 0 6px;
    font-size: 17px;
    color: #173b6c;
}

.selected-class-banner p {
    margin: 0;
    color: #52657e;
}

.class-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.class-meta span {
    background: #ffffff;
    border: 1px solid #dce7f5;
    padding: 7px 11px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 750;
    color: #345270;
}

.student-count-box {
    text-align: center;
    min-width: 100px;
}

.student-count-box strong {
    display: block;
    font-size: 27px;
    color: #174f9d;
}

.student-count-box small {
    color: #64748b;
}

.subject-selection-area {
    padding: 20px 22px;
    background: #f8faff;
    border-bottom: 1px solid #e9eef5;
}

.subject-selection-area label {
    margin-top: 0;
}

.subject-selection-area select {
    max-width: 600px;
}

.marks-table-wrapper {
    overflow-x: auto;
}

.marks-entry-table {
    width: 100%;
    min-width: 900px;
    border-collapse: collapse;
}

.marks-entry-table th {
    background: #f5f8fc;
    padding: 13px 14px;
    font-size: 11px;
    color: #5c6b80;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: left;
    border-bottom: 1px solid #e8edf4;
}

.marks-entry-table td {
    padding: 13px 14px;
    border-bottom: 1px solid #eef1f5;
}

.marks-entry-table tbody tr:hover {
    background: #fafcff;
}

.marks-entry-table input[type="number"] {
    width: 100px;
    min-width: 85px;
    text-align: center;
    padding: 9px;
}

.student-number {
    color: #607086;
    font-weight: 700;
}

.student-name-cell {
    min-width: 180px;
}

.student-name-cell strong {
    display: block;
}

.student-name-cell small {
    color: #94a3b8;
}

.live-total {
    font-size: 16px;
    color: #1e3a5f;
}

.live-grade {
    display: inline-flex;
    min-width: 38px;
    justify-content: center;
    padding: 5px 9px;
    border-radius: 8px;
    background: #eef4ff;
    color: #1d5fd1;
    font-weight: 800;
}

.live-result {
    font-size: 12px;
    font-weight: 850;
}

.marks-action-bar {
    padding: 18px 22px;
    background: #fafbfd;
    border-top: 1px solid #edf0f4;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.marks-action-note {
    color: #64748b;
    font-size: 12px;
}

.save-marks-button {
    min-width: 180px;
}

.recent-results-header {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 15px;
    margin: 30px 0 14px;
}

.recent-results-header h2 {
    margin: 0 0 4px;
}

.recent-results-header p {
    margin: 0;
    color: #64748b;
}

.result-search {
    width: min(310px, 100%);
}

.result-search input {
    background: #ffffff;
}

.professional-result-table td {
    font-size: 13px;
}

.result-student strong {
    display: block;
}

.result-student small {
    color: #64748b;
}

.result-pass-pill,
.result-fail-pill {
    display: inline-flex;
    padding: 5px 9px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 850;
}

.result-pass-pill {
    background: #ecfdf3;
    color: #15803d;
}

.result-fail-pill {
    background: #fff1f2;
    color: #be123c;
}

.grade-pill {
    display: inline-grid;
    place-items: center;
    min-width: 38px;
    padding: 5px 8px;
    border-radius: 9px;
    background: #edf4ff;
    color: #1d5fd1;
    font-weight: 850;
}

.no-class-state {
    padding: 30px;
    text-align: center;
    color: #64748b;
}

.no-class-state span {
    display: block;
    font-size: 35px;
    margin-bottom: 8px;
}

@media (max-width: 1100px) {

    .marks-overview-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .class-filter-grid {
        grid-template-columns:
            repeat(2, minmax(150px, 1fr));
    }

    .load-class-button {
        grid-column: 1 / -1;
    }
}

@media (max-width: 700px) {

    .marks-overview-grid {
        grid-template-columns: 1fr;
    }

    .class-filter-grid {
        grid-template-columns: 1fr;
    }

    .selected-class-banner,
    .marks-action-bar,
    .recent-results-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .marks-header-badge {
        display: none;
    }

    .result-search {
        width: 100%;
    }
}

</style>

</head>


<body class="edutrack-admin">


<!-- =====================================================
     NAVBAR
===================================================== -->

<div class="navbar">

<div>

<h2>
🎓 EduTrack
</h2>

<small>
University Academic Management System
</small>

</div>

<div>

<span>
Administrator
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


<!-- =====================================================
     SIDEBAR
===================================================== -->

<div class="sidebar">

<h3>
ADMINISTRATION
</h3>

<a href="dashboard.php">
🏠 Dashboard
</a>

<a href="view_students.php">
👨‍🎓 Students
</a>

<a href="add_student.php">
➕ Add Student
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
📝 Marks & Grades
</a>

<a href="subjects.php">
📚 Subjects
</a>

<a href="reports.php">
📊 Reports & Analytics
</a>

<a href="logout.php">
🚪 Logout
</a>

</div>



<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="main-content">


<!-- PAGE HEADER -->

<div class="page-header marks-page-intro">

<div>

<h1>
Academic Assessment
</h1>

<p>
Manage class-wise marks, grades and student academic results.
</p>

</div>

<div class="marks-header-badge">
🎓 Assessment Management
</div>

</div>



<!-- =====================================================
     SYSTEM OVERVIEW
===================================================== -->

<div class="marks-overview-grid">


<div class="marks-overview-card">

<div class="marks-overview-icon">
📝
</div>

<div>

<p>
Assessment Records
</p>

<h3>
<?php echo $total_marks_records; ?>
</h3>

</div>

</div>



<div class="marks-overview-card">

<div class="marks-overview-icon">
✅
</div>

<div>

<p>
Pass Records
</p>

<h3>
<?php echo $passed_records; ?>
</h3>

</div>

</div>



<div class="marks-overview-card">

<div class="marks-overview-icon">
⚠️
</div>

<div>

<p>
Fail Records
</p>

<h3>
<?php echo $failed_records; ?>
</h3>

</div>

</div>



<div class="marks-overview-card">

<div class="marks-overview-icon">
📊
</div>

<div>

<p>
Overall Average
</p>

<h3>
<?php
echo number_format(
    $overall_average,
    1
);
?>%
</h3>

</div>

</div>


</div>



<!-- MESSAGE -->

<?php if ($message != "") { ?>

<div
class="<?php
echo ($message_type == "success")
    ? "alert-success"
    : "error-message";
?>"
>

<?php
echo ($message_type == "success")
    ? "✓ "
    : "⚠ ";
?>

<?php
echo htmlspecialchars($message);
?>

</div>

<?php } ?>



<!-- =====================================================
     CLASS SELECTION
===================================================== -->

<div class="academic-workspace">

<div class="workspace-heading">

<div>

<h2>
Select Academic Class
</h2>

<p>
Choose the class for which you want to enter or update marks.
</p>

</div>

<div class="workspace-number">
1
</div>

</div>


<form
    method="GET"
    action="marks.php"
    class="class-filter-form"
>

<div class="class-filter-grid">


<div class="filter-field">

<label>
Department
</label>

<select
    name="department"
    required
>

<option value="">
Choose Department
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

?>

<option
value="<?php
echo htmlspecialchars($dept);
?>"

<?php
echo ($department == $dept)
    ? "selected"
    : "";
?>
>

<?php
echo htmlspecialchars($dept);
?>

</option>

<?php } ?>

</select>

</div>



<div class="filter-field">

<label>
Section
</label>

<select
    name="section"
    required
>

<option value="">
Choose Section
</option>

<?php

foreach (
    ["A", "B", "C", "D"]
    as $sec
) {

?>

<option
value="<?php echo $sec; ?>"

<?php
echo ($section == $sec)
    ? "selected"
    : "";
?>
>

Section <?php echo $sec; ?>

</option>

<?php } ?>

</select>

</div>



<div class="filter-field">

<label>
Academic Year
</label>

<select
    name="year"
    required
>

<option value="">
Choose Year
</option>

<?php

$years = [
    "1st Year",
    "2nd Year",
    "3rd Year",
    "4th Year"
];

foreach ($years as $year_option) {

?>

<option
value="<?php
echo htmlspecialchars($year_option);
?>"

<?php
echo ($year == $year_option)
    ? "selected"
    : "";
?>
>

<?php
echo htmlspecialchars($year_option);
?>

</option>

<?php } ?>

</select>

</div>



<div class="filter-field">

<label>
Semester
</label>

<select
    name="semester"
    required
>

<option value="">
Choose Semester
</option>

<?php

for ($i = 1; $i <= 8; $i++) {

?>

<option
value="<?php echo $i; ?>"

<?php
echo ($semester == $i)
    ? "selected"
    : "";
?>
>

Semester <?php echo $i; ?>

</option>

<?php } ?>

</select>

</div>


<button
    type="submit"
    class="load-class-button"
>
🔍 Load Class
</button>


</div>

</form>

</div>



<?php

if (
    $department != "" &&
    $section != "" &&
    $year != "" &&
    $semester != ""
) {

    $student_count =
        ($students)
        ? mysqli_num_rows($students)
        : 0;

?>


<!-- =====================================================
     SELECTED CLASS
===================================================== -->

<div class="selected-class-banner">

<div>

<h3>
Selected Academic Class
</h3>

<div class="class-meta">

<span>
🏫 <?php
echo htmlspecialchars($department);
?>
</span>

<span>
Section <?php
echo htmlspecialchars($section);
?>
</span>

<span>
<?php
echo htmlspecialchars($year);
?>
</span>

<span>
Semester <?php
echo intval($semester);
?>
</span>

</div>

</div>


<div class="student-count-box">

<strong>
<?php echo $student_count; ?>
</strong>

<small>
Students
</small>

</div>

</div>



<?php if ($student_count > 0) { ?>


<!-- =====================================================
     MARKS ENTRY WORKSPACE
===================================================== -->

<div class="academic-workspace">


<div class="workspace-heading">

<div>

<h2>
Enter Subject Marks
</h2>

<p>
Enter internal and external marks for the selected class.
</p>

</div>

<div class="workspace-number">
2
</div>

</div>



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



<div class="subject-selection-area">

<label>
📚 Select Subject
</label>

<select
    name="subject"
    required
>

<option value="">
Choose Subject
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

?>

<option
value="<?php
echo htmlspecialchars(
    $subject_row["subject_code"]
);
?>"
>

<?php

echo htmlspecialchars(
    $subject_row["subject_code"]
);

?>

 —

<?php

echo htmlspecialchars(
    $subject_row["subject_name"]
);

?>

</option>

<?php

    }

}

?>

</select>

<small class="form-help">
Subjects are loaded automatically from the timetable for this class.
</small>

</div>



<div class="marks-table-wrapper">

<table class="marks-entry-table">

<thead>

<tr>

<th>
Roll Number
</th>

<th>
Student
</th>

<th>
Internal
<br>
<small>Maximum 30</small>
</th>

<th>
External
<br>
<small>Maximum 70</small>
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

while (
    $student =
    mysqli_fetch_assoc($students)
) {

    $student_id =
        intval($student["id"]);

?>

<tr>


<td>

<span class="student-number">

<?php
echo htmlspecialchars(
    $student["roll_no"]
);
?>

</span>

</td>



<td class="student-name-cell">

<strong>

<?php
echo htmlspecialchars(
    $student["name"]
);
?>

</strong>

<small>
Student
</small>

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

    placeholder="0"

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

    placeholder="0"

    oninput="calculateRow(
        <?php echo $student_id; ?>
    )"
>

</td>



<td>

<strong
    class="live-total"

    id="total_<?php
    echo $student_id;
    ?>"
>
0
</strong>

/100

</td>



<td>

<span
    class="live-grade"

    id="grade_<?php
    echo $student_id;
    ?>"
>
-
</span>

</td>



<td>

<span
    class="live-result"

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



<div class="marks-action-bar">

<div class="marks-action-note">

Marks are automatically converted into
total, grade and result.

</div>

<button
    type="submit"
    name="save_marks"
    class="save-marks-button"
>
💾 Save Class Marks
</button>

</div>


</form>

</div>



<?php } else { ?>


<div class="academic-workspace">

<div class="no-class-state">

<span>
👨‍🎓
</span>

<h3>
No Students Found
</h3>

<p>
There are no students registered for
<strong>
<?php
echo htmlspecialchars($department);
?>
-
<?php
echo htmlspecialchars($section);
?>
</strong>,
<?php
echo htmlspecialchars($year);
?>,
Semester
<?php
echo intval($semester);
?>.
</p>

</div>

</div>


<?php } ?>


<?php } ?>



<!-- =====================================================
     RECENT RESULTS
===================================================== -->

<div class="recent-results-header">

<div>

<h2>
Recent Academic Results
</h2>

<p>
Review the latest marks published across classes.
</p>

</div>


<div class="result-search">

<input
    type="text"
    id="marksSearch"
    placeholder="🔍 Search student, subject or class..."
    oninput="searchMarks()"
>

</div>

</div>



<div class="table-card">

<div class="marks-table-wrapper">

<table
    class="modern-table professional-result-table"
    id="recentMarksTable"
>

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


<td class="result-student">

<strong>

<?php
echo htmlspecialchars(
    $row["name"]
);
?>

</strong>

<small>

<?php
echo htmlspecialchars(
    $row["roll_no"]
);
?>

</small>

</td>



<td>

<strong>

<?php
echo htmlspecialchars(
    $row["department"]
    . "-"
    . $row["section"]
);
?>

</strong>

<br>

<small>

<?php
echo htmlspecialchars(
    $row["year"]
);
?>

• Semester

<?php
echo intval(
    $row["semester"]
);
?>

</small>

</td>



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
echo intval(
    $row["internal_marks"]
);
?>

<span style="color:#94a3b8;">
/30
</span>

</td>



<td>

<?php
echo intval(
    $row["external_marks"]
);
?>

<span style="color:#94a3b8;">
/70
</span>

</td>



<td>

<strong>

<?php
echo intval(
    $row["total_marks"]
);
?>

/100

</strong>

</td>



<td>

<span class="grade-pill">

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

<span class="result-pass-pill">
✓ PASS
</span>

<?php

} else {

?>

<span class="result-fail-pill">
✕ FAIL
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

No academic results have been published yet.

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>


</div>

</div>



<script>

/* =========================================================
   LIVE MARK CALCULATION
========================================================= */

function calculateRow(studentId) {

    const internalInput =
        document.getElementById(
            "internal_" + studentId
        );

    const externalInput =
        document.getElementById(
            "external_" + studentId
        );

    let internal =
        parseInt(internalInput.value) || 0;

    let external =
        parseInt(externalInput.value) || 0;


    if (internal > 30) {

        internal = 30;
        internalInput.value = 30;
    }

    if (internal < 0) {

        internal = 0;
        internalInput.value = 0;
    }


    if (external > 70) {

        external = 70;
        externalInput.value = 70;
    }

    if (external < 0) {

        external = 0;
        externalInput.value = 0;
    }


    const total =
        internal + external;


    let grade;

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


    const result =
        total >= 40
        ? "PASS"
        : "FAIL";


    const totalElement =
        document.getElementById(
            "total_" + studentId
        );

    const gradeElement =
        document.getElementById(
            "grade_" + studentId
        );

    const resultElement =
        document.getElementById(
            "result_" + studentId
        );


    totalElement.innerText =
        total;

    gradeElement.innerText =
        grade;

    resultElement.innerText =
        result;


    if (result === "PASS") {

        resultElement.style.color =
            "#15803d";

    } else {

        resultElement.style.color =
            "#be123c";
    }
}


/* =========================================================
   SEARCH RECENT RESULTS
========================================================= */

function searchMarks() {

    const searchInput =
        document.getElementById(
            "marksSearch"
        );

    const table =
        document.getElementById(
            "recentMarksTable"
        );

    if (!searchInput || !table) {
        return;
    }

    const filter =
        searchInput.value
        .toLowerCase()
        .trim();

    const rows =
        table.querySelectorAll(
            "tbody tr"
        );

    rows.forEach(function(row) {

        const text =
            row.textContent
            .toLowerCase();

        row.style.display =
            text.includes(filter)
            ? ""
            : "none";
    });
}

</script>


<script src="js/script.js"></script>

</body>

</html>