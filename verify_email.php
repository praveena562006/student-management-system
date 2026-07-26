<?php

require_once "db.php";

$message = "";
$success = false;


/* ============================================
   CHECK IF TOKEN EXISTS IN URL
============================================ */

if (!isset($_GET["token"]) || empty($_GET["token"])) {

    $message = "Invalid verification link.";

} else {

    $token = $_GET["token"];


    /* ========================================
       FIND STUDENT USING TOKEN
    ======================================== */

    $sql = "
        SELECT id, name, email, email_verified
        FROM students
        WHERE verification_token = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $token
    );

    mysqli_stmt_execute($stmt);

    $result =
        mysqli_stmt_get_result($stmt);


    /* ========================================
       CHECK WHETHER TOKEN IS VALID
    ======================================== */

    if (mysqli_num_rows($result) === 1) {

        $student =
            mysqli_fetch_assoc($result);


        /* Already verified */
        if ($student["email_verified"] == 1) {

            $message =
                "Your email address is already verified.";

            $success = true;

        } else {

            $student_id =
                $student["id"];


            /* =================================
               VERIFY ACCOUNT
            ================================= */

            $update_sql = "
                UPDATE students
                SET
                    email_verified = 1,
                    verification_token = NULL
                WHERE id = ?
            ";

            $update_stmt =
                mysqli_prepare(
                    $conn,
                    $update_sql
                );

            mysqli_stmt_bind_param(
                $update_stmt,
                "i",
                $student_id
            );


            if (
                mysqli_stmt_execute(
                    $update_stmt
                )
            ) {

                $message =
                    "Email verified successfully! Your EduTrack account is now active.";

                $success = true;

            } else {

                $message =
                    "Something went wrong while verifying your account.";

            }

            mysqli_stmt_close(
                $update_stmt
            );
        }

    } else {

        $message =
            "This verification link is invalid or has already been used.";
    }


    mysqli_stmt_close($stmt);
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

    <title>Email Verification - EduTrack</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body>


<div class="login-page">

    <div class="login-container">


        <h1>
            🎓 EduTrack
        </h1>


        <h2>
            Email Verification
        </h2>


        <?php if ($success) { ?>


            <div class="success-message">

                ✓ <?php echo htmlspecialchars($message); ?>

            </div>


            <p>
                Your student account is ready.
                You can now sign in using your
                Registration Number and password.
            </p>


            <a
                href="student_login.php"
                class="login-button"
            >
                Go to Student Login
            </a>


        <?php } else { ?>


            <div class="error-message">

                ⚠ <?php echo htmlspecialchars($message); ?>

            </div>


            <p>
                Please contact the administrator
                if you believe this is an error.
            </p>


        <?php } ?>


    </div>

</div>


</body>

</html>