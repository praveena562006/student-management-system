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

    header("Location: view_students.php");
    exit();
}


$id = intval($_GET["id"]);

$message = "";
$message_type = "";


/* ==========================================
   GET STUDENT
========================================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM students WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result($stmt);

$student =
    mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$student) {

    die("Student record not found.");
}


/* ==========================================
   UPDATE STUDENT
========================================== */

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $name =
        trim($_POST["name"] ?? "");

    $roll_no =
        trim($_POST["roll_no"] ?? "");

    $registration_no =
        trim($_POST["registration_no"] ?? "");

    $dob =
        $_POST["dob"] ?? "";

    $gender =
        $_POST["gender"] ?? "";

    $department =
        $_POST["department"] ?? "";

    $section =
        $_POST["section"] ?? "";

    $year =
        $_POST["year"] ?? "";

    $semester =
        $_POST["semester"] ?? "";

    $email =
        trim($_POST["email"] ?? "");

    $phone =
        trim($_POST["phone"] ?? "");

    $address =
        trim($_POST["address"] ?? "");


    /* ==========================================
       VALIDATION
    ========================================== */

    if (
        empty($name) ||
        empty($roll_no) ||
        empty($registration_no) ||
        empty($dob) ||
        empty($gender) ||
        empty($department) ||
        empty($section) ||
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
        !preg_match(
            "/^[A-Za-z ]+$/",
            $name
        )
    ) {

        $message =
            "Student name should contain only letters and spaces.";

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


    elseif (
        strtotime($dob) >
        strtotime(date("Y-m-d"))
    ) {

        $message =
            "Date of Birth cannot be a future date.";

        $message_type =
            "error";
    }


    /* ==========================================
       YEAR / SEMESTER VALIDATION
    ========================================== */

    elseif (

        (
            $year == "1st Year" &&
            !in_array(
                (int)$semester,
                [1, 2],
                true
            )
        )

        ||

        (
            $year == "2nd Year" &&
            !in_array(
                (int)$semester,
                [3, 4],
                true
            )
        )

        ||

        (
            $year == "3rd Year" &&
            !in_array(
                (int)$semester,
                [5, 6],
                true
            )
        )

        ||

        (
            $year == "4th Year" &&
            !in_array(
                (int)$semester,
                [7, 8],
                true
            )
        )

    ) {

        $message =
            "Invalid semester selected for the chosen year.";

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
                    (
                        roll_no = ?
                        OR registration_no = ?
                    )
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
               UPDATE STUDENT
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
                        section = ?,
                        year = ?,
                        semester = ?,
                        email = ?,
                        phone = ?,
                        address = ?

                     WHERE id = ?"
                );


            mysqli_stmt_bind_param(

                $update_stmt,

                "ssssssssssssi",

                $name,
                $roll_no,
                $registration_no,
                $dob,
                $gender,
                $department,
                $section,
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


                /* Update displayed values */

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

                $student["section"] =
                    $section;

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

            } else {

                $message =
                    "Unable to update student: "
                    . mysqli_error($conn);

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
    href="attendance.php"
    class="nav-link"
>
Attendance
</a>


<a
    href="logout.php"
    class="logout-btn"
>
Logout
</a>

</div>

</div>



<!-- FORM CONTAINER -->

<div class="form-container">


<div class="edit-heading">


<div>

<h1>
✏ Edit Student
</h1>

<p>
Update personal and academic information.
</p>

</div>


<a
    href="student_profile.php?id=<?php echo $id; ?>"
    class="profile-small-button"
>
👁 View Profile
</a>


</div>



<!-- MESSAGE -->

<?php

if ($message != "") {


    if (
        $message_type ==
        "success"
    ) {

?>

<div class="success-message">

✓

<?php
echo htmlspecialchars(
    $message
);
?>

</div>

<?php

    } else {

?>

<div class="error-message">

⚠

<?php
echo htmlspecialchars(
    $message
);
?>

</div>

<?php

    }
}

?>



<form method="POST">


<!-- STUDENT NAME -->

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



<!-- ROLL NUMBER -->

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



<!-- REGISTRATION NUMBER -->

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



<!-- DATE OF BIRTH -->

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



<!-- GENDER -->

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
    ) {
        echo "selected";
    }
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
    ) {
        echo "selected";
    }
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
    ) {
        echo "selected";
    }
    ?>
>

Other

</option>


</select>



<!-- DEPARTMENT -->

<label>
Department
</label>


<select
    name="department"
    id="department"
    required
>


<option value="">
Select Department
</option>


<?php

$departments = [

    "AI&DS",
    "CSBS",
    "IT",
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

?>


<option
    value="<?php
        echo htmlspecialchars(
            $dept
        );
    ?>"
    <?php echo $selected; ?>
>

<?php
echo htmlspecialchars(
    $dept
);
?>

</option>


<?php } ?>


</select>



<!-- SECTION -->

<label>
Section
</label>


<select
    name="section"
    id="section"
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


foreach (
    $sections
    as $section_option
) {

    $selected =
        (
            isset(
                $student["section"]
            )
            &&
            $student["section"]
            ==
            $section_option
        )
        ? "selected"
        : "";

?>


<option
    value="<?php
        echo htmlspecialchars(
            $section_option
        );
    ?>"
    <?php echo $selected; ?>
>

<?php
echo htmlspecialchars(
    $section_option
);
?>

</option>


<?php } ?>


</select>



<!-- YEAR -->

<label>
Year
</label>


<select
    name="year"
    id="year"
    onchange="updateSemesters()"
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


foreach (
    $years
    as $year_option
) {

    $selected =
        $student["year"]
        == $year_option
        ? "selected"
        : "";

?>


<option
    value="<?php
        echo htmlspecialchars(
            $year_option
        );
    ?>"
    <?php echo $selected; ?>
>

<?php
echo htmlspecialchars(
    $year_option
);
?>

</option>


<?php } ?>


</select>



<!-- SEMESTER -->

<label>
Semester
</label>


<select
    name="semester"
    id="semester"
    required
>

<option value="">
Select Semester
</option>

</select>


<small class="form-help">

1st Year → Semester 1 & 2,
2nd Year → Semester 3 & 4,
3rd Year → Semester 5 & 6,
4th Year → Semester 7 & 8.

</small>



<!-- EMAIL -->

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



<!-- PHONE -->

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



<!-- ADDRESS -->

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



<!-- ==========================================
     YEAR -> SEMESTER JAVASCRIPT
========================================== -->

<script>


function updateSemesters() {


    const year =
        document.getElementById(
            "year"
        ).value;


    const semesterSelect =
        document.getElementById(
            "semester"
        );


    /*
       Current semester stored in database
       or submitted by the form.
    */

    const currentSemester =
        "<?php
            echo htmlspecialchars(
                (string)$student["semester"]
            );
        ?>";


    semesterSelect.innerHTML =
        '<option value="">Select Semester</option>';


    let semesters = [];


    if (
        year === "1st Year"
    ) {

        semesters = [1, 2];

    }


    else if (
        year === "2nd Year"
    ) {

        semesters = [3, 4];

    }


    else if (
        year === "3rd Year"
    ) {

        semesters = [5, 6];

    }


    else if (
        year === "4th Year"
    ) {

        semesters = [7, 8];

    }


    semesters.forEach(
        function(number) {


            const option =
                document.createElement(
                    "option"
                );


            option.value =
                number;


            option.textContent =
                "Semester " + number;


            if (
                String(number)
                ===
                String(currentSemester)
            ) {

                option.selected =
                    true;
            }


            semesterSelect.appendChild(
                option
            );

        }
    );


    if (year === "") {

        semesterSelect.innerHTML =
            '<option value="">Select Year First</option>';
    }

}


/* LOAD CORRECT SEMESTER AUTOMATICALLY */

document.addEventListener(
    "DOMContentLoaded",
    function() {

        updateSemesters();

    }
);


</script>


</body>

</html>