<?php session_start(); ?>

<!DOCTYPE html>

<html>
    <head>
        <meta charset="utf-8" />
        <title>Logout</title>
    </head>
    <body><?php
        session_destroy();
        setcookie(session_name(), '', 1);
        header("Location: login.php"); ?>
    </body>
</html>