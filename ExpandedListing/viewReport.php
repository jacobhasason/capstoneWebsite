<?php
require "../DBConnect/db.php";

$id = $_GET["id"] ?? null;

if (!$id) {
    die("No listing selected");
}

$stmt = $db->prepare('SELECT * FROM "Listing" WHERE "listingID" = ?');
$stmt->execute([$id]);

$current = $stmt->fetch();

if (!$current) {
    die("Listing not found");
}
?>

<?php include '../view/reportHeader.php'; ?>
<link rel="stylesheet" href="../styles/main.css">
<link rel="stylesheet" href="../styles/reportPage.css">

<main class="report-page">

    <!-- CAROUSEL (STATIC VISUAL ONLY) -->
    <div class="carousel">

        <!-- LEFT ARROW -->
        <div class="arrow">◀</div>

        <!-- CONTENT -->
        <div class="carousel-content">

            <h2><?= htmlspecialchars($current["title"]) ?></h2>

            <p><strong>Author:</strong> <?= htmlspecialchars($current["author"]) ?></p>
            <p><strong>Topic:</strong> <?= htmlspecialchars($current["topic"]) ?></p>

            <hr>

            <div class="preview">
                <?= nl2br(htmlspecialchars($current["abstract"])) ?>
            </div>

        </div>

        <!-- RIGHT ARROW -->
        <div class="arrow">▶</div>

    </div>

    <!-- CONTROL BAR -->
    <div class="report-controls">

        <!-- TOPIC DROPDOWN -->
        <select>
            <option selected><?= htmlspecialchars($current["topic"]) ?></option>
            <option>AI/Machine Learning</option>
            <option>Visual Knowledge</option>
        </select>

        <!-- TITLE -->
        <div class="current-title">
            <?= htmlspecialchars($current["title"]) ?>
        </div>

        <!-- DOWNLOAD BUTTON (ALWAYS VISIBLE, NOT WIRED) -->
        <button class="btn">
            Download
        </button>

    </div>
<a href="../index.php" class="fab"><<</a>
</main>

<?php include '../view/footer.php'; ?>