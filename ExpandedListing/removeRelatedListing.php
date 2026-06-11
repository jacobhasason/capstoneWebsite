<?php
require "DBConnect/db.php";

$relatedListingID = $_GET['relatedListingID'] ?? null;

if (!$relatedListingID) {
    die("Missing relatedListingID");
}

$stmt = $db->prepare('
    DELETE FROM "RelatedListing"
    WHERE "relatedListingID" = ?
');

$stmt->execute([(int)$relatedListingID]);

echo "Deleted rows: " . $stmt->rowCount();
exit;