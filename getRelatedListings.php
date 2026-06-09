<?php
require "DBConnect/db.php";

header('Content-Type: application/json');

// Read the raw input stream instead of $_POST
$rawInput = file_get_contents('php://input');
$requestData = json_decode($rawInput, true);

// Extract topics safely
$topicIDs = $requestData['topics'] ?? [];

if (!is_array($topicIDs)) {
    $topicIDs = [];
}

// Strip out any accidental empty strings/null values, ensuring strict integers
$topicIDs = array_filter(array_map('intval', $topicIDs));

if (empty($topicIDs)) {
    echo json_encode([]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($topicIDs), '?'));

$sql = "
SELECT DISTINCT
    l.\"listingID\",
    l.title
FROM \"Listing\" l
JOIN \"ListingTopic\" lt
    ON l.\"listingID\" = lt.\"listingID\"
WHERE lt.topic_id IN ($placeholders)
ORDER BY l.title
";

$stmt = $db->prepare($sql);
$stmt->execute($topicIDs);

$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($listings);