<!DOCTYPE html> 
<html lang="en">
    <meta charset="UTF-8">
    <title>Source Info</title>
    <body>
        <header>
           
            <?php
require "../DBConnect/db.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    die("No listing selected");
}

$stmt = $db->prepare('SELECT * FROM "Listing" WHERE "listingID" = ?');
$stmt->execute([$id]);

$listing = $stmt->fetch();

if (!$listing) {
    die("Listing not found");
}
?>
            <main class="listing-full">

    <h1><?= htmlspecialchars($listing['title'] ?? 'Untitled') ?></h1>

    <p><strong>Author:</strong> <?= htmlspecialchars($listing['author'] ?? 'Unknown') ?></p>

    <p><strong>Date:</strong> <?= htmlspecialchars($listing['date'] ?? 'N/A') ?></p>

    <p><strong>Medium:</strong> <?= htmlspecialchars($listing['medium'] ?? 'N/A') ?></p>

    <p><strong>Topic:</strong> <?= htmlspecialchars($listing['topic'] ?? 'Uncategorized') ?></p>

    <hr>

    <p class="abstract">
        <?= nl2br(htmlspecialchars($listing['abstract'] ?? 'No abstract available')) ?>
    </p>
          
            
<!--            <link rel="stylesheet" href="styles/main.css"> -->
        </header>
    </body>
</html>
