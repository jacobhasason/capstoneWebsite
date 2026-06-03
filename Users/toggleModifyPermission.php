<?php

if (!isset($_SESSION["userType"]) || $_SESSION["userType"] != 2) {
    exit();
}

$id = $_GET["id"] ?? null;

if (!$id) {
    exit();
}

/* fetch target user */
$stmt = $db->prepare('SELECT "userType", "permisMod" FROM "User" WHERE "userID" = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    exit();
}

/* superusers locked */
if ($user["userType"] == 2) {
    exit();
}

/* toggle */
$newValue = ($user["permisMod"] == 2) ? 1 : 2;

$update = $db->prepare('
    UPDATE "User"
    SET "permisMod" = ?
    WHERE "userID" = ?
');

$update->execute([$newValue, $id]);

exit();
?>