<?php
require_once 'db.php';

$db = getDB();

$stmt = $db->prepare("DELETE FROM listings WHERE id = ?");
$stmt->execute([1]);

echo "Deleted ID";
?>