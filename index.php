<?php

session_start();


/*
If Admin is already logged in,
take Admin directly to dashboard.
*/

if (isset($_SESSION["admin"])) {

    header("Location: dashboard.php");

    exit();
}


/*
If Student is already logged in,
take Student directly to
Student Dashboard.
*/

if (isset($_SESSION["student_id"])) {

    header("Location: student_dashboard.php");

    exit();
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
        EduTrack - Academic Management System
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body class="portal-body">


<div class="portal-container">


    <!-- ==========================================
         LOGO
    =========================================== -->


    <div class="portal-logo">

        🎓

    </div>


    <h1 class="portal-title">

        EduTrack

    </h1>


    <p class="portal-subtitle">

        Student Academic Management System

    </p>


    <p class="portal-description">

        Select your portal to continue

    </p>



    <!-- ==========================================
         PORTAL CARDS
    =========================================== -->


    <div class="portal-selection">


        <!-- ======================================
             ADMIN PORTAL
        ======================================= -->


        <a
            href="login.php"
            class="portal-card"
        >


            <div class="portal-card-icon admin-icon">

                👨‍💼

            </div>


            <h2>

                Admin Portal

            </h2>


            <p>

                Manage students, attendance,
                marks and academic reports.

            </p>


            <div class="portal-features">


                <span>
                    ✓ Student Management
                </span>


                <span>
                    ✓ Attendance Management
                </span>


                <span>
                    ✓ Marks & Grades
                </span>


                <span>
                    ✓ Reports & Analytics
                </span>


            </div>


            <div class="portal-button admin-portal-button">

                Admin Login →

            </div>


        </a>



        <!-- ======================================
             STUDENT PORTAL
        ======================================= -->


        <a
            href="student_login.php"
            class="portal-card student-portal-card"
        >


            <div class="portal-card-icon student-icon">

                🎓

            </div>


            <h2>

                Student Portal

            </h2>


            <p>

                Access your attendance,
                marks, grades and academic
                performance.

            </p>


            <div class="portal-features">


                <span>
                    ✓ View My Attendance
                </span>


                <span>
                    ✓ Check My Marks
                </span>


                <span>
                    ✓ View Grades
                </span>


                <span>
                    ✓ Track Performance
                </span>


            </div>


            <div class="portal-button student-portal-button">

                Student Login →

            </div>


        </a>


    </div>



    <!-- ==========================================
         FOOTER
    =========================================== -->


    <div class="portal-footer">


        <p>

            🔐 Secure Role-Based Academic Portal

        </p>


        <small>

            EduTrack Student Management System

        </small>


    </div>


</div>


</body>

</html>