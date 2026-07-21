<?php

session_start();

include "db.php";


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
   WHEN ADD STUDENT FORM IS SUBMITTED
========================================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    /* =====================================================
       GET VALUES FROM FORM
    ===================================================== */

    $name = trim($_POST["name"]);

    $roll_no = trim($_POST["roll_no"]);

    $registration_no =
        trim($_POST["registration_no"]);

    $dob = $_POST["dob"];

    $gender = $_POST["gender"];

    $department = $_POST["department"];

    $year = $_POST["year"];

    $semester = $_POST["semester"];

    $email = trim($_POST["email"]);

    $phone = trim($_POST["phone"]);

    $address = trim($_POST["address"]);


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


    else {


        /* =================================================
           CHECK DUPLICATE ROLL NUMBER OR REGISTRATION NO
        ================================================= */

        $check_sql =

            "SELECT id

             FROM students

             WHERE roll_no = ?

             OR registration_no = ?";


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

        }


        else {


            /* =================================================
               AUTOMATICALLY CREATE STUDENT LOGIN
            ================================================= */


            /*
               Registration Number becomes
               Student Username

               Example:

               Registration Number:
               23CSBS001

               Username:
               23CSBS001
            */

            $username =
                $registration_no;



            /*
               INITIAL PASSWORD

               DOB stored by HTML:

               2005-05-10

               Converted into:

               10052005

               Format:
               DDMMYYYY
            */

            $dob_object =
                new DateTime($dob);


            $default_password =
                $dob_object->format(
                    "dmY"
                );



            /* =================================================
               HASH PASSWORD BEFORE STORING
            ================================================= */


            /*
               IMPORTANT:

               We DO NOT store:

               10052005

               directly inside MySQL.

               password_hash() converts it
               into a secure password hash.
            */


            $hashed_password =
                password_hash(
                    $default_password,
                    PASSWORD_DEFAULT
                );



            /* =================================================
               INSERT STUDENT INTO DATABASE
            ================================================= */


            $sql =

                "INSERT INTO students

                (
                    name,
                    roll_no,
                    registration_no,
                    dob,
                    gender,
                    department,
                    year,
                    semester,
                    email,
                    phone,
                    address,
                    username,
                    password
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
                    ?
                )";


            $stmt =
                mysqli_prepare(
                    $conn,
                    $sql
                );


            /* =================================================
               BIND VALUES
            ================================================= */


            mysqli_stmt_bind_param(

                $stmt,

                "sssssssssssss",

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

                $username,

                $hashed_password

            );



            /* =================================================
               EXECUTE INSERT
            ================================================= */


            if (
                mysqli_stmt_execute(
                    $stmt
                )
            ) {


                /* =============================================
                   SAVE CREDENTIALS FOR DISPLAY
                ============================================= */


                $created_username =
                    $username;


                $created_password =
                    $default_password;



                $message =

                    "Student added successfully!";


                $message_type =
                    "success";



                /* =============================================
                   CLEAR FORM AFTER SUCCESS
                ============================================= */


                $name = "";

                $roll_no = "";

                $registration_no = "";

                $dob = "";

                $gender = "";

                $department = "";

                $year = "";

                $semester = "";

                $email = "";

                $phone = "";

                $address = "";

            }


            else {


                $message =

                    "Error adding student: "
                    .
                    mysqli_error($conn);


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


<body>


<!-- =====================================================
     NAVIGATION BAR
===================================================== -->


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



<!-- =====================================================
     ADD STUDENT FORM
===================================================== -->


<div class="form-container">


    <h1>

        ➕ Add New Student

    </h1>


    <p>

        Enter the student's personal and
        academic information below.

    </p>



    <!-- =================================================
         SUCCESS / ERROR MESSAGE
    ================================================= -->


    <?php


    if ($message != "") {


        if (
            $message_type
            == "success"
        ) {


            echo

            "<div class='success-message'>

                ✓ "
                .
                htmlspecialchars($message)
                .

            "</div>";


        }


        else {


            echo

            "<div class='error-message'>

                ⚠ "
                .
                htmlspecialchars($message)
                .

            "</div>";


        }

    }


    ?>



    <!-- =================================================
         NEW STUDENT LOGIN CREDENTIALS
    ================================================= -->


    <?php


    if (
        $created_username != ""
        &&
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

                    Share these initial login
                    credentials with the student.

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

                    Username is the student's
                    Registration Number and the
                    initial password is their DOB
                    in DDMMYYYY format.

                </small>


            </div>


        </div>


    <?php


    }


    ?>



    <!-- =================================================
         ADD STUDENT FORM
    ================================================= -->


    <form method="POST">



        <!-- =============================================
             STUDENT NAME
        ============================================= -->


        <label>

            Student Name

        </label>


        <input

            type="text"

            name="name"


            value="<?php

                echo isset($name)

                    ? htmlspecialchars($name)

                    : '';

            ?>"


            placeholder="Enter student full name"


            pattern="[A-Za-z ]+"


            title="Name should contain only letters and spaces"


            required

        >



        <!-- =============================================
             ROLL NUMBER
        ============================================= -->


        <label>

            Roll Number

        </label>


        <input

            type="text"

            name="roll_no"


            value="<?php

                echo isset($roll_no)

                    ? htmlspecialchars($roll_no)

                    : '';

            ?>"


            placeholder="Example: 101"


            maxlength="50"


            required

        >



        <!-- =============================================
             REGISTRATION NUMBER
        ============================================= -->


        <label>

            Registration Number

        </label>


        <input

            type="text"

            name="registration_no"


            value="<?php

                echo isset($registration_no)

                    ? htmlspecialchars(
                        $registration_no
                    )

                    : '';

            ?>"


            placeholder="Example: 23CSBS001"


            maxlength="50"


            required

        >



        <small class="form-help">

            Registration Number will automatically
            become the student's login username.

        </small>



        <!-- =============================================
             DATE OF BIRTH
        ============================================= -->


        <label>

            Date of Birth

        </label>


        <input

            type="date"

            name="dob"


            value="<?php

                echo isset($dob)

                    ? htmlspecialchars($dob)

                    : '';

            ?>"


            max="<?php

                echo date(
                    "Y-m-d"
                );

            ?>"


            required

        >



        <small class="form-help">

            The student's DOB will be used to
            generate the initial login password
            in DDMMYYYY format.

        </small>



        <!-- =============================================
             GENDER
        ============================================= -->


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
                    isset($gender)
                    &&
                    $gender == "Female"
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
                    isset($gender)
                    &&
                    $gender == "Male"
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
                    isset($gender)
                    &&
                    $gender == "Other"
                ) {

                    echo "selected";

                }

                ?>

            >

                Other

            </option>


        </select>



        <!-- =============================================
             DEPARTMENT
        ============================================= -->


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


                $selected = "";


                if (
                    isset($department)
                    &&
                    $department == $dept
                ) {


                    $selected =
                        "selected";


                }


                echo

                "<option
                    value='"
                    .
                    htmlspecialchars($dept)
                    .
                    "'
                    $selected
                >"

                .
                htmlspecialchars($dept)

                .

                "</option>";


            }


            ?>


        </select>



        <!-- =============================================
             YEAR
        ============================================= -->


        <label>

            Year

        </label>


        <select

            name="year"

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


                $selected = "";


                if (
                    isset($year)
                    &&
                    $year
                    ==
                    $year_option
                ) {


                    $selected =
                        "selected";


                }


                echo

                "<option
                    value='"
                    .
                    htmlspecialchars(
                        $year_option
                    )
                    .
                    "'
                    $selected
                >"

                .
                htmlspecialchars(
                    $year_option
                )

                .

                "</option>";


            }


            ?>


        </select>



        <!-- =============================================
             SEMESTER
        ============================================= -->


        <label>

            Semester

        </label>


        <select

            name="semester"

            required

        >


            <option value="">

                Select Semester

            </option>



            <?php


            for (
                $i = 1;
                $i <= 8;
                $i++
            ) {


                $selected = "";


                if (
                    isset($semester)
                    &&
                    $semester == $i
                ) {


                    $selected =
                        "selected";


                }


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



        <!-- =============================================
             EMAIL
        ============================================= -->


        <label>

            Email Address

        </label>


        <input

            type="email"

            name="email"


            value="<?php

                echo isset($email)

                    ? htmlspecialchars($email)

                    : '';

            ?>"


            placeholder="Example: student@gmail.com"


            required

        >



        <!-- =============================================
             PHONE NUMBER
        ============================================= -->


        <label>

            Phone Number

        </label>


        <input

            type="tel"

            name="phone"


            value="<?php

                echo isset($phone)

                    ? htmlspecialchars($phone)

                    : '';

            ?>"


            placeholder="Enter 10-digit phone number"


            pattern="[0-9]{10}"


            maxlength="10"


            title="Please enter exactly 10 digits"


            required

        >



        <!-- =============================================
             ADDRESS
        ============================================= -->


        <label>

            Address

        </label>


        <textarea

            name="address"

            placeholder="Enter student's complete address"

            required

        ><?php

            echo isset($address)

                ? htmlspecialchars(
                    $address
                )

                : '';

        ?></textarea>



        <!-- =============================================
             SUBMIT BUTTON
        ============================================= -->


        <button

            type="submit"

            class="add-student-button"

        >

            ➕ Add Student & Create Login

        </button>


    </form>


</div>


</body>


</html>