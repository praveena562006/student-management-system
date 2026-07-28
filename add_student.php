<?php

session_start();

include "db.php";
require_once __DIR__ . '/send_verification.php';


/* =========================================================
   CHECK WHETHER ADMIN IS LOGGED IN
========================================================= */

if (!isset($_SESSION["admin"])) {

    header("Location: login.php");
    exit();
}


/* =========================================================
   MESSAGE VARIABLES
========================================================= */

$message = "";
$message_type = "";
$created_username = "";
$created_password = "";


/* =========================================================
   DEFAULT FORM VALUES
========================================================= */

$name = "";
$roll_no = "";
$registration_no = "";
$dob = "";
$gender = "";
$department = "";
$section = "";
$year = "";
$semester = "";
$email = "";
$phone = "";
$address = "";


/* =========================================================
   WHEN ADD STUDENT FORM IS SUBMITTED
========================================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    /* =====================================================
       GET VALUES FROM FORM
    ===================================================== */

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


    /* =====================================================
       BASIC VALIDATION
    ===================================================== */

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
            "Please fill in all the required fields.";

        $message_type =
            "error";
    }


    /* =====================================================
       VALIDATE STUDENT NAME
    ===================================================== */

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


    /* =====================================================
       VALIDATE EMAIL
    ===================================================== */

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


    /* =====================================================
       VALIDATE PHONE NUMBER
    ===================================================== */

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


    /* =====================================================
       VALIDATE DOB
    ===================================================== */

    elseif (
        strtotime($dob) >
        strtotime(date("Y-m-d"))
    ) {

        $message =
            "Date of Birth cannot be a future date.";

        $message_type =
            "error";
    }


    /* =====================================================
       VALIDATE YEAR AND SEMESTER
    ===================================================== */

    elseif (
        ($year == "1st Year" &&
            !in_array(
                (int)$semester,
                [1, 2],
                true
            )
        )
        ||
        ($year == "2nd Year" &&
            !in_array(
                (int)$semester,
                [3, 4],
                true
            )
        )
        ||
        ($year == "3rd Year" &&
            !in_array(
                (int)$semester,
                [5, 6],
                true
            )
        )
        ||
        ($year == "4th Year" &&
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


        /* =================================================
           CHECK DUPLICATE ROLL NUMBER OR REGISTRATION NO
        ================================================= */

        $check_sql = "
            SELECT id
            FROM students
            WHERE roll_no = ?
            OR registration_no = ?
        ";


        $check_stmt =
            mysqli_prepare(
                $conn,
                $check_sql
            );


        mysqli_stmt_bind_param(
            $check_stmt,
            "ss",
            $roll_no,
            $registration_no
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

            $message =
                "A student with this Roll Number or Registration Number already exists.";

            $message_type =
                "error";

        } else {


            /* =================================================
               AUTOMATIC STUDENT LOGIN
            ================================================= */

            $username =
                $registration_no;


            /*
               DOB password:
               DDMMYYYY
            */

            $dob_object =
                new DateTime($dob);


            $default_password =
                $dob_object->format(
                    "dmY"
                );


            /* =================================================
               HASH PASSWORD
            ================================================= */

            $hashed_password =
                password_hash(
                    $default_password,
                    PASSWORD_DEFAULT
                );


            /* =================================================
               EMAIL VERIFICATION TOKEN
            ================================================= */

            $verification_token =
                bin2hex(
                    random_bytes(32)
                );


            $email_verified = 0;


            /* =================================================
               INSERT STUDENT
            ================================================= */

            $sql = "

                INSERT INTO students
                (
                    name,
                    roll_no,
                    registration_no,
                    dob,
                    gender,
                    department,
                    section,
                    year,
                    semester,
                    email,
                    phone,
                    address,
                    username,
                    password,
                    verification_token,
                    email_verified
                )

                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )

            ";


            $stmt =
                mysqli_prepare(
                    $conn,
                    $sql
                );


            mysqli_stmt_bind_param(

                $stmt,

                "sssssssssssssssi",

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
                $username,
                $hashed_password,
                $verification_token,
                $email_verified

            );


            /* =================================================
               EXECUTE
            ================================================= */

            if (
                mysqli_stmt_execute(
                    $stmt
                )
            ) {


                $created_username =
                    $username;


                $created_password =
                    $default_password;


                /* =============================================
                   SEND VERIFICATION EMAIL
                ============================================= */

                $email_sent =
                    sendVerificationEmail(
                        $email,
                        $name,
                        $verification_token
                    );


                if ($email_sent) {

                    $message =
                        "Student added successfully! A verification email has been sent to "
                        . $email
                        . ".";

                } else {

                    $message =
                        "Student was added successfully, but the verification email could not be sent.";
                }


                $message_type =
                    "success";


                /* =============================================
                   CLEAR FORM
                ============================================= */

                $name = "";
                $roll_no = "";
                $registration_no = "";
                $dob = "";
                $gender = "";
                $department = "";
                $section = "";
                $year = "";
                $semester = "";
                $email = "";
                $phone = "";
                $address = "";

            } else {

                $message =
                    "Error adding student: "
                    . mysqli_error($conn);

                $message_type =
                    "error";
            }


            mysqli_stmt_close(
                $stmt
            );
        }


        mysqli_stmt_close(
            $check_stmt
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
Add Student - EduTrack
</title>

<link
    rel="stylesheet"
    href="css/style.css"
>

</head>


<body class="edutrack-admin">


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
    href="marks.php"
    class="nav-link"
>
Marks
</a>

<a
    href="reports.php"
    class="nav-link"
>
Reports
</a>

<a
    href="logout.php"
    class="logout-btn"
>
Logout
</a>

</div>

</div>



<!-- ADD STUDENT FORM -->

<div class="form-container">


<h1>
➕ Add New Student
</h1>


<p>
Enter the student's personal and academic information below.
</p>



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



<!-- STUDENT LOGIN CREDENTIALS -->

<?php

if (
    $created_username != "" &&
    $created_password != ""
) {

?>


<div class="student-credentials-card">


<div class="credentials-icon">

🔐

</div>


<div>


<h3>
Student Login Created
</h3>


<p>
Share these initial login credentials with the student.
</p>


<div class="credential-row">

<span>
Username
</span>

<strong>

<?php
echo htmlspecialchars(
    $created_username
);
?>

</strong>

</div>


<div class="credential-row">

<span>
Initial Password
</span>

<strong>

<?php
echo htmlspecialchars(
    $created_password
);
?>

</strong>

</div>


<small>

Username is the student's Registration Number and the
initial password is their DOB in DDMMYYYY format.

</small>


</div>

</div>


<?php } ?>



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
            $name
        );
    ?>"

    placeholder="Enter student full name"

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
            $roll_no
        );
    ?>"

    placeholder="Example: 101"

    maxlength="50"

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
            $registration_no
        );
    ?>"

    placeholder="Example: 23CSBS001"

    maxlength="50"

    required
>


<small class="form-help">

Registration Number will automatically
become the student's login username.

</small>



<!-- DATE OF BIRTH -->

<label>
Date of Birth
</label>

<input
    type="date"
    name="dob"

    value="<?php
        echo htmlspecialchars(
            $dob
        );
    ?>"

    max="<?php
        echo date("Y-m-d");
    ?>"

    required
>


<small class="form-help">

The student's DOB will be used to generate the initial
login password in DDMMYYYY format.

</small>



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
    if ($gender == "Female") {
        echo "selected";
    }
    ?>
>
Female
</option>


<option
    value="Male"

    <?php
    if ($gender == "Male") {
        echo "selected";
    }
    ?>
>
Male
</option>


<option
    value="Other"

    <?php
    if ($gender == "Other") {
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
        ($department == $dept)
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
        ($section == $section_option)
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
        ($year == $year_option)
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
Select Year First
</option>

</select>


<small class="form-help">

1st Year → Semesters 1 & 2,
2nd Year → 3 & 4,
3rd Year → 5 & 6,
4th Year → 7 & 8.

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
            $email
        );
    ?>"

    placeholder="Example: student@gmail.com"

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
            $phone
        );
    ?>"

    placeholder="Enter 10-digit phone number"

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
    placeholder="Enter student's complete address"
    required
><?php
echo htmlspecialchars(
    $address
);
?></textarea>



<!-- SUBMIT -->

<button
    type="submit"
    class="add-student-button"
>

➕ Add Student & Create Login

</button>


</form>


</div>



<!-- =====================================================
     YEAR -> SEMESTER JAVASCRIPT
===================================================== -->

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


    const previousSemester =
        "<?php
            echo htmlspecialchars(
                (string)$semester
            );
        ?>";


    semesterSelect.innerHTML =
        '<option value="">Select Semester</option>';


    let semesters = [];


    if (year === "1st Year") {

        semesters = [1, 2];

    }

    else if (year === "2nd Year") {

        semesters = [3, 4];

    }

    else if (year === "3rd Year") {

        semesters = [5, 6];

    }

    else if (year === "4th Year") {

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
                String(number) ===
                String(previousSemester)
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


/* Run automatically when page loads */

document.addEventListener(
    "DOMContentLoaded",
    function() {

        updateSemesters();

    }
);

</script>



<script src="js/script.js"></script>
</body>

</html>