<?php

session_start();


/* REMOVE STUDENT SESSION VALUES */

unset(
    $_SESSION["student_id"]
);

unset(
    $_SESSION["student_name"]
);

unset(
    $_SESSION["student_registration"]
);

unset(
    $_SESSION["student_department"]
);

unset(
    $_SESSION["student_year"]
);

unset(
    $_SESSION["student_semester"]
);

unset(
    $_SESSION["role"]
);


/* DESTROY SESSION */

session_destroy();


/* RETURN TO STUDENT LOGIN */

header(
    "Location: student_login.php"
);


exit();

?>