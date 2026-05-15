<?php
session_start();
require "../DBConnect/db.php";

/* PERMISSION CHECK*/
$currentUserType = (int)($_SESSION["userType"] ?? 0);
$currentCanManage = (int)($_SESSION["canManage"] ?? 1);

if (
    $currentUserType !== 2 &&
    !($currentUserType === 1 && $currentCanManage === 2)
) {
    die("Not allowed");
}

/*GET ID*/
$id = $_GET["id"] ?? null;

if (!$id) {
    die("No listing selected");
}

/*DELETE LISTING*/
$stmt = $db->prepare('DELETE FROM "Listing" WHERE "listingID" = ?');
$stmt->execute([$id]);

/*REDIRECT*/
header("Location: ../index.php");
exit();
?>
C:\xampp\htdocs\capstoneWebsite-Holden-Testing\ExpandedListing\deleteListing.php