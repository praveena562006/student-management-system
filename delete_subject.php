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
   CHECK SUBJECT ID
========================================================= */

if (!isset($_GET["id"])) {

    header("Location: subjects.php");

    exit();

}


$id =
    intval($_GET["id"]);


if ($id <= 0) {

    header("Location: subjects.php");

    exit();

}


/* =========================================================
   DELETE SUBJECT
========================================================= */

$sql = "

    DELETE FROM subjects

    WHERE id = ?

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


if (
    mysqli_stmt_execute(
        $stmt
    )
) {


    mysqli_stmt_close(
        $stmt
    );


    header(

        "Location: subjects.php?message=deleted"

    );


    exit();


}


mysqli_stmt_close(
    $stmt
);


header(
    "Location: subjects.php"
);


exit();

?>