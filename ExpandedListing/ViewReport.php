<?php

require "../DBConnect/db.php";

$primaryID = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($primaryID <= 0) {
    die("Missing primary listing ID");
}

// Load primary source
$stmt = $db->prepare('SELECT * FROM "Listing" WHERE "listingID" = ?');
$stmt->execute([$primaryID]);
$primary = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$primary) {
    die("Primary listing not found");
}

// Load related sources for this primary listing
$stmt = $db->prepare('SELECT * FROM "RelatedListing" WHERE "listingID" = ? ORDER BY "relatedListingID" ASC');
$stmt->execute([$primaryID]);
$relatedRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build carousel items: primary first, related after
$listings = [];

$listings[] = [
    "title" => $primary["title"] ?? "Untitled",
    "author" => $primary["author(s)"] ?? "Unknown",
    "topic" => $primary["topic"] ?? "Uncategorized",
    "abstract" => $primary["abstract"] ?? "No description available",
    "file" => $primary["file"] ?? null,
    "links" => $primary["links"] ?? null,
    "type" => "primary"
];

foreach ($relatedRows as $row) {
    if (($row["type"] ?? '') === "listing" && !empty($row["links"])) {
        $relatedListingID = (int) $row["links"];

        $listings[] = [
            "title" => $row["title"] ?? ucfirst($row["type"] ?? "Related Source"),
            "author" => "Unknown",
            "topic" => $row["topic"] ?? ($primary["topic"] ?? "Uncategorized"),
            "abstract" => $primary['abstract'] ?? "Click below to view this related listing.",
            "file" => null,
            "links" => "ListingInfo.php?id=" . $relatedListingID,
            "type" => "listing"
        ];
    } else {
        $listings[] = [
            "title" => ucfirst($row["type"] ?? "Related Source"),
            "author" => "Unknown",
            "topic" => $row["topic"] ?? ($primary["topic"] ?? "Uncategorized"),
            "abstract" => ($row["type"] ?? "source"),
            "file" => $row["file"] ?? null,
            "links" => $row["links"] ?? null,
            "type" => $row["type"] ?? "related"
        ];
    }
}


$index = isset($_GET['i']) ? (int) $_GET['i'] : 0;

if ($index < 0) {
    $index = count($listings) - 1;
}
if ($index >= count($listings)) {
    $index = 0;
}

$current = $listings[$index];


$totalItems = count($listings);

// Disable Arrows if there is only a primary source
if ($totalItems <= 1) {
    $prevIndex = 0;
    $nextIndex = 0;
} else {
    $prevIndex = $index - 1;
    $nextIndex = $index + 1;

    if ($prevIndex < 0) {
        $prevIndex = $totalItems - 1;
    }

    if ($nextIndex >= $totalItems) {
        $nextIndex = 0;
    }
}
?>

<?php include '../view/reportHeader.php'; ?>

<link rel="stylesheet" href="../styles/main.css">
<link rel="stylesheet" href="../styles/reportPage.css">


<main class="report-page">

    <!-- Carousel -->
    <div class="carousel">

        <!-- Left arrow -->

        <?php if (count($listings) > 1): ?>
            <a class="arrow" href="?id=<?= $primaryID ?>&i=<?= $prevIndex ?>">◀</a>
        <?php else: ?>
            <span class="arrow disabled">◀</span>
        <?php endif; ?>


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

        <?php if (count($listings) > 1): ?>
            <a class="arrow" href="?id=<?= $primaryID ?>&i=<?= $nextIndex ?>">▶</a>
        <?php else: ?>
            <span class="arrow disabled">▶</span>
        <?php endif; ?>


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


        <?php elseif (!empty($current["links"])): ?>

            <a class="btn"
               href="<?= htmlspecialchars($current["links"]) ?>"
               <?= ($current["type"] ?? '') === "listing" ? '' : 'target="_blank" rel="noopener noreferrer"' ?>>
                   <?= ($current["type"] ?? '') === "listing" ? 'View Related Listing' : 'Visit Source' ?>
            </a>

        <?php else: ?>

            <button class="btn" disabled>No attachment</button>


        <?php endif; ?>

    </div>

    <!-- Position -->
    <p style="text-align:center; margin-top:10px;">
        <?= $index + 1 ?> / <?= count($listings) ?>
    </p>


    <a href="controller.php?page=index" class="fab"><<</a>

</main>



