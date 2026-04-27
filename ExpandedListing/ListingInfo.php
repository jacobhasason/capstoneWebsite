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

    <h1><?= htmlspecialchars($listing['title']) ?></h1>

    <p><strong>Author:</strong> <?= htmlspecialchars($listing['author']) ?></p>
    <p><strong>Date:</strong> <?= htmlspecialchars($listing['date']) ?></p>
    <p><strong>Medium:</strong> <?= htmlspecialchars($listing['medium']) ?></p>
    <p><strong>Topic:</strong> <?= htmlspecialchars($listing['topic']) ?></p>

    <hr>

    <p class="abstract">
        <?= nl2br(htmlspecialchars($listing['abstract'])) ?>
    </p>

    <!-- ACTIONS -->
    <div class="listing-actions">

    <div class="action-buttons">

        <button class="btn">View Abstract</button>
        <button class="btn">Copy Citation</button>

        <a class="btn external"
           href="ViewReport.php?id=<?= $listing['listingID'] ?>">
            View Report & External Resources
        </a>

    </div>

</div>

    <!-- OPTIONAL EXTRA LINKS -->
    <?php if ($listing['links']): ?>
        <p class="extra-link">
            <a href="<?= htmlspecialchars($listing['links']) ?>" target="_blank">
                External Link
            </a>
        </p>
    <?php endif; ?>

    <?php if ($listing['file']): ?>
        <p class="extra-link">
            <a href="../uploads/<?= htmlspecialchars($listing['file']) ?>" download>
                Download File
            </a>
        </p>
    <?php endif; ?>

</main>
</body>
<a href="../index.php" class="fab"><<</a>

<script src="../scripts/date.js"></script>

<?php include '../view/footer.php'; ?>