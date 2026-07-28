<?php

session_start();

include "db.php";


/* =========================================================
   ADMIN LOGIN CHECK
========================================================= */

if (!isset($_SESSION["admin"])) {

    header("Location: login.php");

    exit();

}


$message = "";
$error = "";


/* =========================================================
   SUCCESS MESSAGE FROM EDIT / DELETE
========================================================= */

if (isset($_GET["message"])) {

    if ($_GET["message"] == "updated") {

        $message = "Subject updated successfully!";

    }

    elseif ($_GET["message"] == "deleted") {

        $message = "Subject deleted successfully!";

    }

}


/* =========================================================
   ADD SUBJECT
========================================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $subject_code =
        trim($_POST["subject_code"]);


    $subject_name =
        trim($_POST["subject_name"]);


    $department =
        trim($_POST["department"]);


    $year =
        trim($_POST["year"]);


    $semester =
        intval($_POST["semester"]);


    $credits =
        intval($_POST["credits"]);



    /* =====================================================
       VALIDATION
    ===================================================== */

    if (
        $subject_code == "" ||
        $subject_name == "" ||
        $department == "" ||
        $year == "" ||
        $semester < 1 ||
        $semester > 8 ||
        $credits < 1
    ) {

        $error =
            "Please enter all subject information correctly.";

    }

    else {


        /* =================================================
           CHECK DUPLICATE SUBJECT CODE
        ================================================= */

        $check_sql = "

            SELECT id

            FROM subjects

            WHERE subject_code = ?

            LIMIT 1

        ";


        $check_stmt =
            mysqli_prepare(
                $conn,
                $check_sql
            );


        mysqli_stmt_bind_param(

            $check_stmt,

            "s",

            $subject_code

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


            $error =
                "This Subject Code already exists.";


        }

        else {


            /* =============================================
               INSERT SUBJECT
            ============================================= */

            $sql = "

                INSERT INTO subjects

                (
                    subject_code,
                    subject_name,
                    department,
                    year,
                    semester,
                    credits
                )

                VALUES (?, ?, ?, ?, ?, ?)

            ";


            $stmt =
                mysqli_prepare(
                    $conn,
                    $sql
                );


            mysqli_stmt_bind_param(

                $stmt,

                "ssssii",

                $subject_code,

                $subject_name,

                $department,

                $year,

                $semester,

                $credits

            );


            if (
                mysqli_stmt_execute(
                    $stmt
                )
            ) {


                $message =
                    "Subject added successfully!";


            }

            else {


                $error =
                    "Unable to add subject: "
                    . mysqli_error($conn);


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



/* =========================================================
   SEARCH AND FILTER
========================================================= */

$search =
    isset($_GET["search"])
    ? trim($_GET["search"])
    : "";


$filter_department =
    isset($_GET["department"])
    ? trim($_GET["department"])
    : "";


$filter_semester =
    isset($_GET["semester"])
    ? intval($_GET["semester"])
    : 0;



/* =========================================================
   GET SUBJECTS
========================================================= */

$sql = "

    SELECT *

    FROM subjects

    WHERE 1 = 1

";


$params = [];

$types = "";


/* SEARCH */

if ($search != "") {


    $sql .= "

        AND
        (
            subject_code LIKE ?

            OR

            subject_name LIKE ?
        )

    ";


    $search_value =
        "%" . $search . "%";


    $params[] =
        $search_value;


    $params[] =
        $search_value;


    $types .= "ss";

}


/* DEPARTMENT FILTER */

if ($filter_department != "") {


    $sql .= "

        AND department = ?

    ";


    $params[] =
        $filter_department;


    $types .= "s";

}


/* SEMESTER FILTER */

if ($filter_semester > 0) {


    $sql .= "

        AND semester = ?

    ";


    $params[] =
        $filter_semester;


    $types .= "i";

}


$sql .= "

    ORDER BY
        semester ASC,
        subject_code ASC

";


$list_stmt =
    mysqli_prepare(
        $conn,
        $sql
    );


if (!empty($params)) {


    mysqli_stmt_bind_param(

        $list_stmt,

        $types,

        ...$params

    );

}


mysqli_stmt_execute(
    $list_stmt
);


$subjects =
    mysqli_stmt_get_result(
        $list_stmt
    );

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
        Subject Management - EduTrack
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body class="edutrack-admin">


<!-- =========================================================
     NAVBAR
========================================================= -->

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
            href="logout.php"
            class="logout-btn"
        >
            Logout
        </a>


    </div>


</div>



<!-- =========================================================
     PAGE CONTENT
========================================================= -->

<div class="main-layout">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <div class="sidebar">


        <h3>
            ADMIN PORTAL
        </h3>


        <a href="dashboard.php">

            🏠 Dashboard

        </a>


        <a href="add_student.php">

            ➕ Add Student

        </a>


        <a href="view_students.php">

            👥 Students

        </a>


        <a href="attendance.php">

            📅 Attendance

        </a>


        <a href="attendance_history.php">

            🕒 Attendance History

        </a>


        <a href="marks.php">

            📝 Marks

        </a>


        <a
            href="subjects.php"
            class="active"
        >

            📚 Subjects

        </a>


        <a href="reports.php">

            📊 Reports

        </a>


    </div>



    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <div class="main-content">


        <!-- PAGE TITLE -->


        <div class="page-header">


            <div>


                <h1>

                    📚 Subject Management

                </h1>


                <p>

                    Add, update and manage
                    academic subjects.

                </p>


            </div>


        </div>



        <!-- =================================================
             SUCCESS MESSAGE
        ================================================== -->


        <?php if ($message != "") { ?>


            <div class="success-message">


                ✓


                <?php

                echo htmlspecialchars(
                    $message
                );

                ?>


            </div>


        <?php } ?>



        <!-- =================================================
             ERROR MESSAGE
        ================================================== -->


        <?php if ($error != "") { ?>


            <div class="error-message">


                ⚠️


                <?php

                echo htmlspecialchars(
                    $error
                );

                ?>


            </div>


        <?php } ?>



        <!-- =================================================
             ADD SUBJECT
        ================================================== -->


        <div class="marks-form-card">


            <h2>

                ➕ Add New Subject

            </h2>


            <form method="POST">



                <label>

                    Subject Code

                </label>


                <input

                    type="text"

                    name="subject_code"

                    placeholder="Example: CSBS501"

                    required

                >



                <label>

                    Subject Name

                </label>


                <input

                    type="text"

                    name="subject_name"

                    placeholder="Example: Web Technologies"

                    required

                >



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


                    <option value="CSBS">
                        CSBS
                    </option>


                    <option value="CSE">
                        CSE
                    </option>


                    <option value="ECE">
                        ECE
                    </option>


                    <option value="EEE">
                        EEE
                    </option>


                    <option value="Mechanical">
                        Mechanical
                    </option>


                    <option value="Civil">
                        Civil
                    </option>


                </select>



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


                    <option value="1st Year">
                        1st Year
                    </option>


                    <option value="2nd Year">
                        2nd Year
                    </option>


                    <option value="3rd Year">
                        3rd Year
                    </option>


                    <option value="4th Year">
                        4th Year
                    </option>


                </select>



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

                    ?>


                        <option
                            value="<?php echo $i; ?>"
                        >

                            Semester
                            <?php echo $i; ?>

                        </option>


                    <?php

                    }

                    ?>


                </select>



                <label>

                    Credits

                </label>


                <select
                    name="credits"
                    required
                >


                    <option value="1">
                        1 Credit
                    </option>


                    <option value="2">
                        2 Credits
                    </option>


                    <option
                        value="3"
                        selected
                    >
                        3 Credits
                    </option>


                    <option value="4">
                        4 Credits
                    </option>


                    <option value="5">
                        5 Credits
                    </option>


                </select>



                <button
                    type="submit"
                    class="add-student-button"
                >

                    + Add Subject

                </button>


            </form>


        </div>



        <!-- =================================================
             SEARCH / FILTER
        ================================================== -->


        <div class="student-table-card">


            <h2>

                🔎 Search Subjects

            </h2>


            <form
                method="GET"
                class="subject-filter-form"
            >


                <input

                    type="text"

                    name="search"

                    placeholder="Search code or subject..."

                    value="<?php

                    echo htmlspecialchars(
                        $search
                    );

                    ?>"

                >



                <select name="department">


                    <option value="">

                        All Departments

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
                        as $department
                    ) {

                    ?>


                        <option

                            value="<?php
                            echo $department;
                            ?>"

                            <?php

                            if (
                                $filter_department
                                == $department
                            ) {

                                echo "selected";

                            }

                            ?>

                        >

                            <?php
                            echo $department;
                            ?>

                        </option>


                    <?php

                    }

                    ?>


                </select>



                <select name="semester">


                    <option value="0">

                        All Semesters

                    </option>


                    <?php

                    for (
                        $i = 1;
                        $i <= 8;
                        $i++
                    ) {

                    ?>


                        <option

                            value="<?php
                            echo $i;
                            ?>"

                            <?php

                            if (
                                $filter_semester
                                == $i
                            ) {

                                echo "selected";

                            }

                            ?>

                        >

                            Semester
                            <?php echo $i; ?>

                        </option>


                    <?php

                    }

                    ?>


                </select>



                <button
                    type="submit"
                    class="add-student-button"
                >

                    Search

                </button>


                <a
                    href="subjects.php"
                    class="secondary-button"
                >

                    Clear

                </a>


            </form>


        </div>



        <!-- =================================================
             SUBJECT TABLE
        ================================================== -->


        <div class="student-table-card">


            <h2>

                📚 Available Subjects

            </h2>


            <table class="modern-table">


                <thead>


                    <tr>


                        <th>
                            Code
                        </th>


                        <th>
                            Subject
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
                            Credits
                        </th>


                        <th>
                            Actions
                        </th>


                    </tr>


                </thead>



                <tbody>


                <?php


                if (
                    $subjects &&
                    mysqli_num_rows(
                        $subjects
                    ) > 0
                ) {


                    while (
                        $subject =
                        mysqli_fetch_assoc(
                            $subjects
                        )
                    ) {

                ?>


                    <tr>


                        <td>


                            <strong>


                                <?php

                                echo htmlspecialchars(
                                    $subject[
                                        "subject_code"
                                    ]
                                );

                                ?>


                            </strong>


                        </td>



                        <td>


                            <?php

                            echo htmlspecialchars(
                                $subject[
                                    "subject_name"
                                ]
                            );

                            ?>


                        </td>



                        <td>


                            <span
                                class="department-badge"
                            >


                                <?php

                                echo htmlspecialchars(
                                    $subject[
                                        "department"
                                    ]
                                );

                                ?>


                            </span>


                        </td>



                        <td>


                            <?php

                            echo htmlspecialchars(
                                $subject["year"]
                            );

                            ?>


                        </td>



                        <td>


                            Semester

                            <?php

                            echo intval(
                                $subject[
                                    "semester"
                                ]
                            );

                            ?>


                        </td>



                        <td>


                            <?php

                            echo intval(
                                $subject[
                                    "credits"
                                ]
                            );

                            ?>


                        </td>



                        <td>


                            <a

                                href="edit_subject.php?id=<?php
                                echo intval(
                                    $subject["id"]
                                );
                                ?>"

                                class="edit-btn"

                            >

                                ✏️ Edit

                            </a>



                            <a

                                href="delete_subject.php?id=<?php
                                echo intval(
                                    $subject["id"]
                                );
                                ?>"

                                class="delete-btn"

                                onclick="
                                    return confirm(
                                        'Are you sure you want to delete this subject?'
                                    );
                                "

                            >

                                🗑 Delete

                            </a>


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
                            class="no-data"
                        >

                            📚 No subjects found.

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