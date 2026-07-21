<?php

session_start();

include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM admin WHERE username='$username' AND password='$password'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {

        $_SESSION["admin"] = $username;

        header("Location: dashboard.php");
        exit();

    } else {

        $message = "Invalid username or password!";

    }
}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Admin Login</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="login-container">

    <h1>Student Management System</h1>

    <h2>Admin Login</h2>

    <?php

    if ($message != "") {
        echo "<p class='error'>$message</p>";
    }

    ?>

    <form method="POST">

        <label>Username</label>

        <input
            type="text"
            name="username"
            placeholder="Enter username"
            required
        >

        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Enter password"
            required
        >

        <button type="submit">
            Login
        </button>

    </form>

</div>

</body>

</html>