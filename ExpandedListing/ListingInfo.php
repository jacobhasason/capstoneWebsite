<?php include '../view/header.php'; ?>
<link rel="stylesheet" href="../styles/main.css">

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

<body>

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
    
    <div class="listing-image">

        <?php
        $iconPath = $listing['icon'] ?? '';

        if (!empty($iconPath) && file_exists($_SERVER['DOCUMENT_ROOT'] . $iconPath)):
        ?>

            <img src="<?= htmlspecialchars($iconPath) ?>"
                 alt="Listing Icon"
                 class="listing-img">

        <?php else: ?>

            <div class="no-image">
                Icon Not Found
            </div>

        <?php endif; ?>

    </div>
    
    
    <!-- Actions -->
    <div class="listing-actions">

        <div class="action-buttons">

            <button class="btn">View Abstract</button>
            <button class="btn">Copy Citation</button>

            <a class="btn external"
               href="ViewReport.php?id=<?= $listing['listingID'] ?>">
                View Primary Source & Related Resources
            </a>

        </div>

    </div>

    <!-- OPtional links -->
    <?php if (!empty($listing['links'])): ?>
        <p class="extra-link">
            <a href="<?= htmlspecialchars($listing['links']) ?>" target="_blank">
                External Link
            </a>
        </p>
    <?php endif; ?>

    <!-- File download -->
    <?php if (!empty($listing['file'])): ?>
        <p class="extra-link">
            <a href="<?= htmlspecialchars($listing['file']) ?>" download>
                Download File
            </a>
        </p>
    <?php endif; ?>


    
    
    
    
</main>

</body>

<a href="../index.php" class="fab"><<</a>

<script src="../scripts/date.js"></script>

<?php include '../view/footer.php'; ?>
