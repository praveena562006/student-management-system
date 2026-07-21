<?php

session_start();

include "db.php";


if (!isset($_SESSION["admin"])) {

    header("Location: login.php");
    exit();

}


if (isset($_GET["id"])) {

    $id = intval($_GET["id"]);

    $sql = "DELETE FROM students WHERE id = $id";

    mysqli_query($conn, $sql);

}


header("Location: view_students.php");

exit();

?>