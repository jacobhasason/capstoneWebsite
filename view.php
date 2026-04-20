<?php
require_once 'model/db.php';

$db = getDB();
$result = $db->query("SELECT * FROM listings");

echo "<h2>Saved Listings</h2>";

foreach ($result as $row) {

    echo "<div style='border:1px solid #ccc; padding:10px; margin-bottom:10px;'>";

    echo "<b>Title:</b> " . htmlspecialchars($row['title']) . "<br>";
    echo "<b>Author:</b> " . htmlspecialchars($row['author']) . "<br>";
    echo "<b>Date:</b> " . htmlspecialchars($row['date']) . "<br>";
    echo "<b>Medium:</b> " . htmlspecialchars($row['medium']) . "<br>";

    if (!empty($row['thumbnail_path'])) {
        echo "<b>Thumbnail:</b><br>";
        echo "<img src='" . $row['thumbnail_path'] . "' width='150'><br>";
    }

    if (!empty($row['file_path'])) {
        echo "<b>File:</b><br>";
        echo "<a href='" . $row['file_path'] . "' target='_blank'>Open / Download File</a><br>";
    }

    echo "<b>Abstract:</b> " . htmlspecialchars($row['abstract']) . "<br>";
    echo "<b>URL:</b> " . htmlspecialchars($row['url']) . "<br>";

    echo "</div>";
}
?>