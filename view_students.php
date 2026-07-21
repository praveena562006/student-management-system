<?php

session_start();

include "db.php";


/* ==========================================
   CHECK LOGIN
========================================== */

if (!isset($_SESSION["admin"])) {

    header("Location: login.php");

    exit();

}


/* ==========================================
   GET FILTER VALUES
========================================== */

$search =
    isset($_GET["search"])
    ? trim($_GET["search"])
    : "";


$department =
    isset($_GET["department"])
    ? $_GET["department"]
    : "";


$year =
    isset($_GET["year"])
    ? $_GET["year"]
    : "";


/* ==========================================
   BUILD QUERY
========================================== */

$sql = "SELECT * FROM students WHERE 1=1";


if ($search != "") {

    $safe_search =
        mysqli_real_escape_string(
            $conn,
            $search
        );

    $sql .= " AND (
                name LIKE '%$safe_search%'
                OR roll_no LIKE '%$safe_search%'
                OR registration_no LIKE '%$safe_search%'
                OR email LIKE '%$safe_search%'
              )";
}


if ($department != "") {

    $safe_department =
        mysqli_real_escape_string(
            $conn,
            $department
        );

    $sql .=
        " AND department='$safe_department'";
}


if ($year != "") {

    $safe_year =
        mysqli_real_escape_string(
            $conn,
            $year
        );

    $sql .=
        " AND year='$safe_year'";
}


$sql .= " ORDER BY id DESC";


$result =
    mysqli_query(
        $conn,
        $sql
    );


/* ==========================================
   TOTAL STUDENT COUNT
========================================== */

$count_result =
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM students"
    );


$count_row =
    mysqli_fetch_assoc(
        $count_result
    );


$total_students =
    $count_row["total"];

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
    Students - EduTrack
</title>

<link
    rel="stylesheet"
    href="css/style.css"
>

</head>


<body>


<!-- ==========================================
     NAVBAR
========================================== -->

<div class="navbar">


<div>

<h2>
🎓 EduTrack
</h2>

</div>


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


<!-- ==========================================
     SIDEBAR
========================================== -->

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


<a
    href="view_students.php"
    class="active"
>

👨‍🎓 Students

</a>


<a href="attendance.php">

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



<!-- ==========================================
     MAIN CONTENT
========================================== -->

<div class="main-content">


<div class="student-page-heading">


<div>


<h1>
👨‍🎓 Student Directory
</h1>


<p>

Manage, search and view
registered students.

</p>


</div>



<a
    href="add_student.php"
    class="primary-action-btn"
>

+ Add New Student

</a>


</div>



<!-- ==========================================
     SUMMARY
========================================== -->

<div class="student-summary">


<div class="summary-icon">

👨‍🎓

</div>


<div>

<span>
Total Registered Students
</span>

<h2>

<?php
echo $total_students;
?>

</h2>

</div>


</div>



<!-- ==========================================
     SEARCH AND FILTERS
========================================== -->

<div class="filter-card">


<form
    method="GET"
    class="advanced-filter-form"
>


<div class="filter-search">


<label>
Search Student
</label>


<input
    type="text"
    id="studentSearch"
    name="search"

    value="<?php
        echo htmlspecialchars(
            $search
        );
    ?>"

    placeholder="Search name, roll no, registration no..."

    onkeyup="liveSearch()"
>


</div>



<div>


<label>
Department
</label>


<select name="department">


<option value="">

All Departments

</option>


<option
    value="CSBS"
    <?php
    if ($department == "CSBS")
        echo "selected";
    ?>
>
CSBS
</option>


<option
    value="CSE"
    <?php
    if ($department == "CSE")
        echo "selected";
    ?>
>
CSE
</option>


<option
    value="ECE"
    <?php
    if ($department == "ECE")
        echo "selected";
    ?>
>
ECE
</option>


<option
    value="EEE"
    <?php
    if ($department == "EEE")
        echo "selected";
    ?>
>
EEE
</option>


<option
    value="Mechanical"
    <?php
    if ($department == "Mechanical")
        echo "selected";
    ?>
>
Mechanical
</option>


<option
    value="Civil"
    <?php
    if ($department == "Civil")
        echo "selected";
    ?>
>
Civil
</option>


</select>


</div>



<div>


<label>
Year
</label>


<select name="year">


<option value="">

All Years

</option>


<option
    value="1st Year"
    <?php
    if ($year == "1st Year")
        echo "selected";
    ?>
>

1st Year

</option>


<option
    value="2nd Year"
    <?php
    if ($year == "2nd Year")
        echo "selected";
    ?>
>

2nd Year

</option>


<option
    value="3rd Year"
    <?php
    if ($year == "3rd Year")
        echo "selected";
    ?>
>

3rd Year

</option>


<option
    value="4th Year"
    <?php
    if ($year == "4th Year")
        echo "selected";
    ?>
>

4th Year

</option>


</select>


</div>



<div class="filter-buttons">


<button
    type="submit"
    class="filter-button"
>

🔍 Filter

</button>


<a
    href="view_students.php"
    class="reset-button"
>

Reset

</a>


</div>


</form>


</div>



<!-- ==========================================
     STUDENT TABLE
========================================== -->

<div class="student-table-card">


<table
    class="student-directory-table"
    id="studentTable"
>


<thead>


<tr>


<th>
Student
</th>


<th>
Roll Number
</th>


<th>
Registration
</th>


<th>
Department
</th>


<th>
Year
</th>


<th>
Semester
</th>


<th>
Actions
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


<!-- STUDENT -->

<td>


<div class="student-cell">


<div class="mini-avatar">


<?php

echo strtoupper(
    substr(
        $row["name"],
        0,
        1
    )
);

?>


</div>



<div>


<a
    href="student_profile.php?id=<?php echo $row["id"]; ?>"
    class="student-name-link"
>


<?php

echo htmlspecialchars(
    $row["name"]
);

?>


</a>


<small>


<?php

echo htmlspecialchars(
    $row["email"]
);

?>


</small>


</div>


</div>


</td>



<!-- ROLL NUMBER -->

<td>

<?php

echo htmlspecialchars(
    $row["roll_no"]
);

?>

</td>



<!-- REGISTRATION -->

<td>

<?php

echo htmlspecialchars(
    $row["registration_no"]
);

?>

</td>



<!-- DEPARTMENT -->

<td>


<span class="department-pill">


<?php

echo htmlspecialchars(
    $row["department"]
);

?>


</span>


</td>



<!-- YEAR -->

<td>

<?php

echo htmlspecialchars(
    $row["year"]
);

?>

</td>



<!-- SEMESTER -->

<td>

Semester

<?php

echo htmlspecialchars(
    $row["semester"]
);

?>

</td>



<!-- ACTIONS -->

<td>


<div class="table-actions">


<a
    href="student_profile.php?id=<?php echo $row["id"]; ?>"
    class="view-action"
    title="View Student"
>

👁 View

</a>



<a
    href="edit_student.php?id=<?php echo $row["id"]; ?>"
    class="edit-action"
    title="Edit Student"
>

✏ Edit

</a>



<a
    href="delete_student.php?id=<?php echo $row["id"]; ?>"
    class="delete-action"
    onclick="return confirm('Are you sure you want to permanently delete this student?');"
    title="Delete Student"
>

🗑 Delete

</a>


</div>


</td>


</tr>


<?php


}


}

else {


?>


<tr>


<td
    colspan="7"
    class="empty-students"
>


<div>


<h2>
🔍
</h2>


<h3>
No Students Found
</h3>


<p>

Try changing your
search or filters.

</p>


</div>


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



<script src="js/script.js"></script>


</body>

</html>