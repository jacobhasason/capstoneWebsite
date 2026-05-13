<?php
require "../DBConnect/db.php";

// Load listings
$stmt = $db->query('SELECT * FROM "Listing" ORDER BY "listingID" DESC');
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$listings) {
    die("No listings found");
}


  // Carousel indexing

$index = isset($_GET['i']) ? (int) $_GET['i'] : 0;

if ($index < 0) {
    $index = count($listings) - 1;
}
if ($index >= count($listings)) {
    $index = 0;
}

$current = $listings[$index];

 // prev / nexy
$prevIndex = $index - 1;
$nextIndex = $index + 1;

if ($prevIndex < 0) {
    $prevIndex = count($listings) - 1;
}
if ($nextIndex >= count($listings)) {
    $nextIndex = 0;
}
?>

<?php include '../view/reportHeader.php'; ?>

<link rel="stylesheet" href="../styles/main.css">
<link rel="stylesheet" href="../styles/reportPage.css">

<main class="report-page">

    <!-- Carousel -->
    <div class="carousel">

        <!-- Left arrow -->
        <a class="arrow" href="?i=<?= $prevIndex ?>">◀</a>

        <!-- Content -->
        <div class="carousel-content">

            <h2><?= htmlspecialchars($current["title"] ?? 'Untitled') ?></h2>

            <p>
                <strong>Author:</strong>
                <?= htmlspecialchars($current["author"] ?? 'Unknown') ?>
            </p>

            <p>
                <strong>Topic:</strong>
                <?= htmlspecialchars($current["topic"] ?? 'Uncategorized') ?>
            </p>

            <hr>

            <div class="preview">
                <?= nl2br(htmlspecialchars($current["abstract"] ?? 'No description available')) ?>
            </div>

        </div>

        <!-- Right arrow -->
        <a class="arrow" href="?i=<?= $nextIndex ?>">▶</a>

    </div>

    <!-- Control bar -->
    <div class="report-controls">

        <!-- Topic drop down -->
        <select>
            <option selected>
                <?= htmlspecialchars($current["topic"] ?? 'Uncategorized') ?>
            </option>
        </select>

        <!-- Title -->
        <div class="current-title">
            <?= htmlspecialchars($current["title"] ?? 'Untitled') ?>
        </div>

        <!-- Download button -->
        <?php if (!empty($current["file"])): ?>
            <a class="btn"
               href="<?= htmlspecialchars($current["file"]) ?>"
               download>
                Download
            </a>
        <?php else: ?>
            <button class="btn" disabled>No file</button>
        <?php endif; ?>

    </div>

    <!-- Position -->
    <p style="text-align:center; margin-top:10px;">
        <?= $index + 1 ?> / <?= count($listings) ?>
    </p>

    <a href="../index.php" class="fab"><<</a>

</main>

<?php include '../view/footer.php'; ?>
