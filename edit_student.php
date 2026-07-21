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
   CHECK STUDENT ID
========================================== */

if (!isset($_GET["id"])) {

    header(
        "Location: view_students.php"
    );

    exit();

}


$id =
    intval($_GET["id"]);


$message = "";

$message_type = "";


/* ==========================================
   GET STUDENT
========================================== */

$stmt =
    mysqli_prepare(
        $conn,
        "SELECT *
         FROM students
         WHERE id = ?"
    );


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);


mysqli_stmt_execute(
    $stmt
);


$result =
    mysqli_stmt_get_result(
        $stmt
    );


$student =
    mysqli_fetch_assoc(
        $result
    );


if (!$student) {

    die(
        "Student record not found."
    );

}


/* ==========================================
   UPDATE STUDENT
========================================== */

if (
    $_SERVER["REQUEST_METHOD"]
    == "POST"
) {


$name =
    trim($_POST["name"]);


$roll_no =
    trim($_POST["roll_no"]);


$registration_no =
    trim(
        $_POST["registration_no"]
    );


$dob =
    $_POST["dob"];


$gender =
    $_POST["gender"];


$department =
    $_POST["department"];


$year =
    $_POST["year"];


$semester =
    $_POST["semester"];


$email =
    trim($_POST["email"]);


$phone =
    trim($_POST["phone"]);


$address =
    trim($_POST["address"]);



/* VALIDATION */

if (
    empty($name) ||
    empty($roll_no) ||
    empty($registration_no) ||
    empty($dob) ||
    empty($gender) ||
    empty($department) ||
    empty($year) ||
    empty($semester) ||
    empty($email) ||
    empty($phone) ||
    empty($address)
) {


$message =
    "Please complete all fields.";


$message_type =
    "error";


}


elseif (
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
) {


$message =
    "Please enter a valid email address.";


$message_type =
    "error";


}


elseif (
    !preg_match(
        "/^[0-9]{10}$/",
        $phone
    )
) {


$message =
    "Phone number must contain exactly 10 digits.";


$message_type =
    "error";


}


else {


/* ==========================================
   CHECK DUPLICATES
========================================== */

$duplicate_stmt =
    mysqli_prepare(
        $conn,
        "SELECT id
         FROM students
         WHERE
         (roll_no = ?
         OR registration_no = ?)
         AND id != ?"
    );


mysqli_stmt_bind_param(
    $duplicate_stmt,
    "ssi",
    $roll_no,
    $registration_no,
    $id
);


mysqli_stmt_execute(
    $duplicate_stmt
);


$duplicate_result =
    mysqli_stmt_get_result(
        $duplicate_stmt
    );


if (
    mysqli_num_rows(
        $duplicate_result
    ) > 0
) {


$message =
    "Another student already uses this Roll Number or Registration Number.";


$message_type =
    "error";


}


else {


/* ==========================================
   UPDATE QUERY
========================================== */

$update_stmt =
    mysqli_prepare(
        $conn,
        "UPDATE students

         SET
         name = ?,
         roll_no = ?,
         registration_no = ?,
         dob = ?,
         gender = ?,
         department = ?,
         year = ?,
         semester = ?,
         email = ?,
         phone = ?,
         address = ?

         WHERE id = ?"
    );


mysqli_stmt_bind_param(
    $update_stmt,
    "sssssssssssi",

    $name,
    $roll_no,
    $registration_no,
    $dob,
    $gender,
    $department,
    $year,
    $semester,
    $email,
    $phone,
    $address,
    $id
);


if (
    mysqli_stmt_execute(
        $update_stmt
    )
) {


$message =
    "Student information updated successfully!";


$message_type =
    "success";


/*
Update displayed student values
*/

$student["name"] =
    $name;

$student["roll_no"] =
    $roll_no;

$student["registration_no"] =
    $registration_no;

$student["dob"] =
    $dob;

$student["gender"] =
    $gender;

$student["department"] =
    $department;

$student["year"] =
    $year;

$student["semester"] =
    $semester;

$student["email"] =
    $email;

$student["phone"] =
    $phone;

$student["address"] =
    $address;


}


else {


$message =
    "Unable to update student.";


$message_type =
    "error";


}


mysqli_stmt_close(
    $update_stmt
);


}


mysqli_stmt_close(
    $duplicate_stmt
);


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

Edit Student - EduTrack

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


<a
    href="dashboard.php"
    class="nav-link"
>

Dashboard

</a>


<a
    href="view_students.php"
    class="nav-link"
>

Students

</a>


<a
    href="logout.php"
    class="logout-btn"
>

Logout

</a>


</div>


</div>



<div class="form-container">


<div class="edit-heading">


<div>


<h1>

✏ Edit Student

</h1>


<p>

Update personal and academic
information.

</p>


</div>


<a
    href="student_profile.php?id=<?php echo $id; ?>"
    class="profile-small-button"
>

👁 View Profile

</a>


</div>



<?php


if ($message != "") {


if (
    $message_type == "success"
) {


echo
"<div class='success-message'>
✓ $message
</div>";


}


else {


echo
"<div class='error-message'>
⚠ $message
</div>";


}


}


?>



<form method="POST">


<label>
Student Name
</label>


<input
    type="text"
    name="name"

    value="<?php
        echo htmlspecialchars(
            $student["name"]
        );
    ?>"

    pattern="[A-Za-z ]+"

    title="Name should contain only letters and spaces"

    required
>



<label>
Roll Number
</label>


<input
    type="text"
    name="roll_no"

    value="<?php
        echo htmlspecialchars(
            $student["roll_no"]
        );
    ?>"

    required
>



<label>
Registration Number
</label>


<input
    type="text"
    name="registration_no"

    value="<?php
        echo htmlspecialchars(
            $student["registration_no"]
        );
    ?>"

    required
>



<label>
Date of Birth
</label>


<input
    type="date"
    name="dob"

    value="<?php
        echo htmlspecialchars(
            $student["dob"]
        );
    ?>"

    max="<?php
        echo date("Y-m-d");
    ?>"

    required
>



<label>
Gender
</label>


<select
    name="gender"
    required
>


<option value="">
Select Gender
</option>


<option
    value="Female"

    <?php
    if (
        $student["gender"]
        == "Female"
    )
        echo "selected";
    ?>
>

Female

</option>


<option
    value="Male"

    <?php
    if (
        $student["gender"]
        == "Male"
    )
        echo "selected";
    ?>
>

Male

</option>


<option
    value="Other"

    <?php
    if (
        $student["gender"]
        == "Other"
    )
        echo "selected";
    ?>
>

Other

</option>


</select>



<label>
Department
</label>


<select
    name="department"
    required
>


<?php


$departments = [

    "CSBS",
    "CSE",
    "ECE",
    "EEE",
    "Mechanical",
    "Civil"

];


foreach (
    $departments
    as $dept
) {


$selected =
    $student["department"]
    == $dept
    ? "selected"
    : "";


echo
"<option
value='$dept'
$selected
>
$dept
</option>";


}


?>


</select>



<label>
Year
</label>


<select
    name="year"
    required
>


<?php


$years = [

    "1st Year",
    "2nd Year",
    "3rd Year",
    "4th Year"

];


foreach (
    $years
    as $year_option
) {


$selected =
    $student["year"]
    == $year_option
    ? "selected"
    : "";


echo
"<option
value='$year_option'
$selected
>
$year_option
</option>";


}


?>


</select>



<label>
Semester
</label>


<select
    name="semester"
    required
>


<?php


for (
    $i = 1;
    $i <= 8;
    $i++
) {


$selected =
    $student["semester"]
    == $i
    ? "selected"
    : "";


echo
"<option
value='$i'
$selected
>
Semester $i
</option>";


}


?>


</select>



<label>
Email Address
</label>


<input
    type="email"
    name="email"

    value="<?php
        echo htmlspecialchars(
            $student["email"]
        );
    ?>"

    required
>



<label>
Phone Number
</label>


<input
    type="tel"
    name="phone"

    value="<?php
        echo htmlspecialchars(
            $student["phone"]
        );
    ?>"

    pattern="[0-9]{10}"

    maxlength="10"

    title="Please enter exactly 10 digits"

    required
>



<label>
Address
</label>


<textarea
    name="address"
    required
><?php

echo htmlspecialchars(
    $student["address"]
);

?></textarea>



<button type="submit">

💾 Save Changes

</button>


</form>


</div>


</body>

</html>