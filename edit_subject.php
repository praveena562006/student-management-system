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


/* =========================================================
   GET SUBJECT ID
========================================================= */

if (!isset($_GET["id"])) {

    header("Location: subjects.php");

    exit();

}


$id =
    intval($_GET["id"]);


$error = "";



/* =========================================================
   GET SUBJECT
========================================================= */

$sql = "

    SELECT *

    FROM subjects

    WHERE id = ?

    LIMIT 1

";


$stmt =
    mysqli_prepare(
        $conn,
        $sql
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


$subject =
    mysqli_fetch_assoc(
        $result
    );


mysqli_stmt_close(
    $stmt
);


if (!$subject) {

    header("Location: subjects.php");

    exit();

}



/* =========================================================
   UPDATE SUBJECT
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
       CHECK DUPLICATE CODE
    ===================================================== */

    $check_sql = "

        SELECT id

        FROM subjects

        WHERE subject_code = ?

        AND id != ?

        LIMIT 1

    ";


    $check_stmt =
        mysqli_prepare(
            $conn,
            $check_sql
        );


    mysqli_stmt_bind_param(

        $check_stmt,

        "si",

        $subject_code,

        $id

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
            "Another subject already uses this Subject Code.";


    }

    else {


        /* =================================================
           UPDATE
        ================================================= */

        $update_sql = "

            UPDATE subjects

            SET

                subject_code = ?,

                subject_name = ?,

                department = ?,

                year = ?,

                semester = ?,

                credits = ?

            WHERE id = ?

        ";


        $update_stmt =
            mysqli_prepare(
                $conn,
                $update_sql
            );


        mysqli_stmt_bind_param(

            $update_stmt,

            "ssssiii",

            $subject_code,

            $subject_name,

            $department,

            $year,

            $semester,

            $credits,

            $id

        );


        if (
            mysqli_stmt_execute(
                $update_stmt
            )
        ) {


            header(

                "Location: subjects.php?message=updated"

            );


            exit();


        }

        else {


            $error =
                "Unable to update subject.";


        }


        mysqli_stmt_close(
            $update_stmt
        );

    }


    mysqli_stmt_close(
        $check_stmt
    );



    /*
        Keep entered values on screen
        if an error occurs.
    */

    $subject["subject_code"] =
        $subject_code;


    $subject["subject_name"] =
        $subject_name;


    $subject["department"] =
        $department;


    $subject["year"] =
        $year;


    $subject["semester"] =
        $semester;


    $subject["credits"] =
        $credits;

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

        Edit Subject - EduTrack

    </title>


    <link
        rel="stylesheet"
        href="css/style.css"
    >


</head>


<body>



<div class="navbar">


    <h2>

        🎓 EduTrack

    </h2>


    <div>


        <a
            href="subjects.php"
            class="nav-link"
        >

            Subjects

        </a>


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



<div class="form-container">


    <h1>

        ✏️ Edit Subject

    </h1>


    <p>

        Update the academic subject information.

    </p>



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



    <form method="POST">



        <label>

            Subject Code

        </label>


        <input

            type="text"

            name="subject_code"

            value="<?php

            echo htmlspecialchars(
                $subject[
                    "subject_code"
                ]
            );

            ?>"

            required

        >



        <label>

            Subject Name

        </label>


        <input

            type="text"

            name="subject_name"

            value="<?php

            echo htmlspecialchars(
                $subject[
                    "subject_name"
                ]
            );

            ?>"

            required

        >



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
                as $department
            ) {

            ?>


                <option

                    value="<?php
                    echo $department;
                    ?>"

                    <?php

                    if (
                        $subject[
                            "department"
                        ]
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
                as $year
            ) {

            ?>


                <option

                    value="<?php
                    echo $year;
                    ?>"

                    <?php

                    if (
                        $subject["year"]
                        == $year
                    ) {

                        echo "selected";

                    }

                    ?>

                >

                    <?php
                    echo $year;
                    ?>

                </option>


            <?php

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

            ?>


                <option

                    value="<?php
                    echo $i;
                    ?>"

                    <?php

                    if (
                        intval(
                            $subject[
                                "semester"
                            ]
                        )
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



        <label>

            Credits

        </label>


        <select
            name="credits"
            required
        >


            <?php


            for (
                $i = 1;
                $i <= 5;
                $i++
            ) {

            ?>


                <option

                    value="<?php
                    echo $i;
                    ?>"

                    <?php

                    if (
                        intval(
                            $subject[
                                "credits"
                            ]
                        )
                        == $i
                    ) {

                        echo "selected";

                    }

                    ?>

                >

                    <?php echo $i; ?>

                    Credit<?php

                    if ($i > 1) {

                        echo "s";

                    }

                    ?>

                </option>


            <?php

            }

            ?>


        </select>



        <button
            type="submit"
        >

            💾 Update Subject

        </button>



        <a
            href="subjects.php"
            class="secondary-button"
        >

            ← Cancel

        </a>


    </form>


</div>


</body>

</html>