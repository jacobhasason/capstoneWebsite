<?php

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
echo "ID received: " . htmlspecialchars($id) . "<br>";

// Delete related rows first
//$stmt = $db->prepare('DELETE FROM "RelatedListing" WHERE "listingID" = ?');
//$stmt->execute([$id]);
//echo "Related rows deleted: " . $stmt->rowCount() . "<br>";


// Then delete main listing
$stmt = $db->prepare('DELETE FROM "Listing" WHERE "listingID" = ?');
$stmt->execute([$id]);
echo "Listing rows deleted: " . $stmt->rowCount() . "<br>";


/*REDIRECT*/
header("Location: controller.php?page=home");
exit();
?>
