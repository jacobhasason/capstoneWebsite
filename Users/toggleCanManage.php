<?php
session_start();
require "../DBConnect/db.php";

/*AUTH CHECK */
if (!isset($_SESSION["userType"])) {
    exit();
}


if ($_SESSION["userType"] == 1 && $_SESSION["canManage"] != 2) {
    exit(); // whitelist user without permission cannot toggle
}

$id = $_GET["id"] ?? null;

if (!$id) {
    exit();
}

/* fetch target user */
$stmt = $db->prepare('SELECT "userType", "canManage" FROM "User" WHERE "userID" = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    exit();
}

/* superusers can always proceed */
if ($user["userType"] == 2) {
    exit();
}

/* toggle */
$newValue = ($user["canManage"] == 2) ? 1 : 2;

$update = $db->prepare('
    UPDATE "User"
    SET "canManage" = ?
    WHERE "userID" = ?
');

$update->execute([$newValue, $id]);

echo $newValue;
exit();
?>