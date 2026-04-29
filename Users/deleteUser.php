<?php
session_start();
require "../DBConnect/db.php";

// must be logged in as superuser
if (!isset($_SESSION["userType"]) || $_SESSION["userType"] != 2) {
    die("Not allowed");
}

$id = $_GET["id"] ?? null;

if (!$id) {
    die("No user selected");
}

/* -----------------------------
   Prevent deleting superusers
----------------------------- */
$check = $db->prepare('SELECT "userType" FROM "User" WHERE "userID" = ?');
$check->execute([$id]);
$user = $check->fetch();

if (!$user) {
    die("User not found");
}

if ($user["userType"] == 2) {
    die("You cannot delete a superuser");
}

/* -----------------------------
   DELETE USER
----------------------------- */
$stmt = $db->prepare('DELETE FROM "User" WHERE "userID" = ?');
$stmt->execute([$id]);

/* -----------------------------
   BACK TO USERS PAGE
----------------------------- */
header("Location: usersPage.php");
exit();