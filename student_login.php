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

    $password = $_POST["password"];


    /* =====================================================
       BASIC VALIDATION
    ===================================================== */

    if ($registration_no == "" || $password == "") {

        $error = "Please enter your Registration Number and password.";

    }

    else {


        /* =================================================
           SEARCH STUDENT
        ================================================= */

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
                email,
                password,
                email_verified

            FROM students

            WHERE registration_no = ?

            LIMIT 1

        ";


        $stmt = mysqli_prepare($conn, $sql);


        if ($stmt) {


            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $registration_no
            );


            mysqli_stmt_execute($stmt);


            $result =
                mysqli_stmt_get_result($stmt);


            $student =
                mysqli_fetch_assoc($result);



            /* =============================================
               CHECK WHETHER STUDENT EXISTS
            ============================================= */

            if ($student) {


                /* =========================================
                   VERIFY PASSWORD HASH
                ========================================= */

                if (
                    password_verify(
                        $password,
                        $student["password"]
                    )
                ) {


                    /* =====================================
                       CHECK EMAIL VERIFICATION
                    ===================================== */

                    if (
                        (int)$student["email_verified"] !== 1
                    ) {


                        $error =
                            "Your email address has not been verified yet. "
                            .
                            "Please open the verification email sent by EduTrack and verify your account before logging in.";


                    }

                    else {


                        /* =================================
                           LOGIN SUCCESSFUL
                        ================================= */


                        /*
                            Prevent session fixation by
                            generating a new session ID.
                        */

                        session_regenerate_id(true);



                        /* =============================
                           SAVE STUDENT SESSION
                        ============================= */

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


                        $_SESSION["student_email"] =
                            $student["email"];


                        $_SESSION["role"] =
                            "student";


                        /* =============================
                           REDIRECT TO DASHBOARD
                        ============================= */

                        header(
                            "Location: student_dashboard.php"
                        );

                        exit();

                    }


                }

                else {


                    /* =================================
                       INCORRECT PASSWORD
                    ================================= */

                    $error =
                        "Incorrect password. "
                        .
                        "For a newly created account, the initial password is your Date of Birth in DDMMYYYY format.";


                }


            }

            else {


                /* =====================================
                   STUDENT NOT FOUND
                ===================================== */

                $error =
                    "Student account not found. Please check your Registration Number.";


            }


            mysqli_stmt_close($stmt);


        }

        else {


            $error =
                "Database error. Unable to process student login.";


        }

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


                <div>

                    ✉ Email Verified Accounts

                </div>


            </div>


        </div>


    </div>



    <!-- =====================================================
         RIGHT SIDE - LOGIN FORM
    ====================================================== -->

    <div class="student-login-section">


        <div class="student-login-card">


            <div class="login-icon">

                🎓

            </div>


            <h2>

                Student Login

            </h2>


            <p class="login-subtitle">

                Sign in using your verified student account.

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


                    <button

                        type="button"

                        class="password-toggle"

                        onclick="toggleStudentPassword()"

                        title="Show or hide password"

                    >

                        👁

                    </button>


                </div>



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

                    Your Date of Birth in DDMMYYYY format

                </p>


                <p>

                    Example:

                    DOB 05-06-2006

                    →

                    <strong>

                        05062006

                    </strong>

                </p>


                <p>

                    <strong>
                        Email Verification:
                    </strong>

                    New student accounts must verify
                    their email before logging in.

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