<?php

// clear all session data
$_SESSION = [];

// yeet the session completely
session_destroy();

// send user back to home page
header("Location: ../index.php");
exit();
