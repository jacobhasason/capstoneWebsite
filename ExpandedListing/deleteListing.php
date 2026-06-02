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

// Delete related rows where this listing is the primary
$stmt = $db->prepare('DELETE FROM "RelatedListing" WHERE "listingID" = ?');
$stmt->execute([$id]);

// Delete related rows where this listing is the related site listing
$stmt = $db->prepare('DELETE FROM "RelatedListing" WHERE "type" = ? AND "links" = ?');
$stmt->execute(["listing", $id]);


// Then delete main listing
$stmt = $db->prepare('DELETE FROM "Listing" WHERE "listingID" = ?');
$stmt->execute([$id]);
echo "Listing rows deleted: " . $stmt->rowCount() . "<br>";


/*REDIRECT*/
header("Location: controller.php?page=home");
exit();
?>
