<?php

session_start();

include "db.php";


/* =========================================================
   IF STUDENT IS ALREADY LOGGED IN
========================================================= */

if (isset($_SESSION["student_id"])) {

    header("Location: student_dashboard.php");

    exit();

}


/* =========================================================
   ERROR MESSAGE
========================================================= */

$error = "";


/* =========================================================
   STUDENT LOGIN PROCESS
========================================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    /* Get Registration Number */

    $registration_no = trim($_POST["username"]);


    /* Get Password */

    $password = trim($_POST["password"]);



    /* =====================================================
       SEARCH STUDENT USING REGISTRATION NUMBER
    ===================================================== */

    $sql = "

        SELECT
            id,
            name,
            roll_no,
            registration_no,
            dob,
            gender,
            department,
            year,
            semester,
            email

        FROM students

        WHERE registration_no = ?

        LIMIT 1

    ";


    $stmt = mysqli_prepare($conn, $sql);



    /* =====================================================
       CHECK QUERY
    ===================================================== */

    if ($stmt) {


        /* Attach Registration Number */

        mysqli_stmt_bind_param(

            $stmt,

            "s",

            $registration_no

        );


        /* Execute Query */

        mysqli_stmt_execute($stmt);


        /* Get Result */

        $result = mysqli_stmt_get_result($stmt);


        /* Get Student Row */

        $student = mysqli_fetch_assoc($result);



        /* =================================================
           IF STUDENT EXISTS
        ================================================= */

        if ($student) {


            /*
                DOB is stored in database like:

                2006-06-05

                We convert it into:

                05062006

                DDMMYYYY
            */


            if (!empty($student["dob"])) {


                $dob_password = date(

                    "dmY",

                    strtotime($student["dob"])

                );


            }

            else {


                $dob_password = "";

            }



            /* =================================================
               VERIFY PASSWORD
            ================================================= */

            if ($password === $dob_password) {


                /*
                    Generate a fresh session ID
                    after successful login.
                */

                session_regenerate_id(true);



                /* =============================================
                   SAVE STUDENT INFORMATION IN SESSION
                ============================================= */


                $_SESSION["student_id"] =
                    $student["id"];


                $_SESSION["student_name"] =
                    $student["name"];


                $_SESSION["student_registration"] =
                    $student["registration_no"];


                $_SESSION["student_department"] =
                    $student["department"];


                $_SESSION["student_year"] =
                    $student["year"];


                $_SESSION["student_semester"] =
                    $student["semester"];


                $_SESSION["role"] =
                    "student";



                /* =============================================
                   REDIRECT TO STUDENT DASHBOARD
                ============================================= */

                header(

                    "Location: student_dashboard.php"

                );


                exit();

            }


            /* =================================================
               WRONG PASSWORD
            ================================================= */

            else {


                $error =

                    "Incorrect password. Enter your Date of Birth in DDMMYYYY format.";

            }


        }


        /* =====================================================
           STUDENT DOES NOT EXIST
        ===================================================== */

        else {


            $error =

                "Student account not found. Please check your Registration Number.";

        }



        /* Close Statement */

        mysqli_stmt_close($stmt);


    }


    /* =========================================================
       DATABASE QUERY ERROR
    ========================================================= */

    else {


        $error =

            "Database error. Unable to process student login.";

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
        Student Login - EduTrack
    </title>


    <link
        rel="stylesheet"
        href="css/style.css"
    >


</head>


<body class="student-login-body">



<div class="student-login-wrapper">



    <!-- =====================================================
         LEFT SIDE - BRANDING
    ====================================================== -->


    <div class="student-login-brand">


        <div class="student-brand-content">


            <div class="brand-logo">

                🎓

            </div>


            <h1>

                EduTrack

            </h1>


            <h2>

                Student Academic Portal

            </h2>


            <p>

                Access your academic information
                securely from one convenient portal.

            </p>



            <!-- FEATURES -->


            <div class="login-features">


                <div>

                    📅 View Attendance

                </div>


                <div>

                    📝 Check Marks

                </div>


                <div>

                    🏆 View Grades

                </div>


                <div>

                    📊 Track Performance

                </div>


            </div>


        </div>


    </div>



    <!-- =====================================================
         RIGHT SIDE - LOGIN FORM
    ====================================================== -->


    <div class="student-login-section">


        <div class="student-login-card">



            <!-- ICON -->


            <div class="login-icon">

                🎓

            </div>



            <!-- TITLE -->


            <h2>

                Student Login

            </h2>


            <p class="login-subtitle">

                Sign in using your student credentials.

            </p>



            <!-- =================================================
                 ERROR MESSAGE
            ================================================== -->


            <?php

            if ($error != "") {

            ?>


                <div class="error-message">


                    ⚠️


                    <?php

                    echo htmlspecialchars($error);

                    ?>


                </div>


            <?php

            }

            ?>



            <!-- =================================================
                 LOGIN FORM
            ================================================== -->


            <form method="POST">



                <!-- REGISTRATION NUMBER -->


                <label>

                    Registration Number

                </label>


                <input

                    type="text"

                    name="username"

                    placeholder="Example: 24B91A5701"

                    value="<?php

                        if (isset($_POST["username"])) {

                            echo htmlspecialchars(

                                $_POST["username"]

                            );

                        }

                    ?>"

                    autocomplete="username"

                    required

                >



                <!-- PASSWORD -->


                <label>

                    Password

                </label>



                <div class="password-field">


                    <input

                        type="password"

                        name="password"

                        id="studentPassword"

                        placeholder="Enter your password"

                        autocomplete="current-password"

                        required

                    >



                    <!-- SHOW/HIDE PASSWORD -->


                    <button

                        type="button"

                        class="password-toggle"

                        onclick="toggleStudentPassword()"

                        title="Show or hide password"

                    >

                        👁

                    </button>


                </div>



                <!-- LOGIN BUTTON -->


                <button

                    type="submit"

                    class="student-login-button"

                >

                    Login to Student Portal →

                </button>


            </form>



            <!-- =================================================
                 LOGIN INFORMATION
            ================================================== -->


            <div class="student-login-help">


                <p>

                    <strong>
                        Username:
                    </strong>

                    Your Registration Number

                </p>


                <p>

                    <strong>
                        Initial Password:
                    </strong>

                    Your Date of Birth in
                    DDMMYYYY format

                </p>


                <p>

                    Example:

                    DOB 05-06-2006

                    →

                    <strong>

                        05062006

                    </strong>

                </p>


            </div>



            <!-- =================================================
                 FOOTER
            ================================================== -->


            <div class="login-footer">


                <p>

                    Administrator?

                    <a href="login.php">

                        Admin Login

                    </a>

                </p>


                <p>

                    <a href="index.php">

                        ← Back to Portal Selection

                    </a>

                </p>


            </div>


        </div>


    </div>


</div>



<!-- =========================================================
     JAVASCRIPT
========================================================= -->


<script>


function toggleStudentPassword() {


    const passwordField =

        document.getElementById(

            "studentPassword"

        );


    if (passwordField.type === "password") {


        passwordField.type = "text";


    }

    else {


        passwordField.type = "password";


    }

}


</script>



</body>


</html>