<?php
// Page that displays primary source and related sources on a carousel

require "DBConnect/db.php";

$primaryID = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($primaryID <= 0) {
    die("Missing primary listing ID");
}

// Load primary source
$stmt = $db->prepare(
        'SELECT * FROM "Listing" WHERE "listingID" = ?'
);
$stmt->execute([$primaryID]);

$primary = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$primary) {
    die("Primary listing not found");
}

// Load related sources
$stmt = $db->prepare(
        'SELECT * FROM "RelatedListing"
     WHERE "listingID" = ?
     ORDER BY "relatedListingID" ASC'
);
$stmt->execute([$primaryID]);

$relatedRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build carousel items
$listings = [];
$listings[] = [
    "title" => $primary["title"] ?? "Untitled",
    "author" => $primary["author"] ?? "Unknown",
    "topic" => $primary["topic"] ?? "Uncategorized",
    "abstract" => $primary["abstract"] ?? "No description available",
    "file" => $primary["file"] ?? null,
    "links" => $primary["links"] ?? null,
    "type" => "primary"
];

foreach ($relatedRows as $row) {
    if (($row["type"] ?? '') === "listing" && !empty($row["links"])) {

        $relatedListingID = (int) $row["links"];

        $relatedStmt = $db->prepare(
                'SELECT * FROM "Listing" WHERE "listingID" = ?'
        );

        $relatedStmt->execute([$relatedListingID]);

        $relatedListing = $relatedStmt->fetch(PDO::FETCH_ASSOC);

        if (!$relatedListing) {
            continue;
        }

        $listings[] = [
            "title" => $relatedListing["title"] ?? $row["title"],
            "author" => $relatedListing["author"] ?? "Unknown",
            "topic" => $relatedListing["topic"] ?? "Uncategorized",
            "abstract" => $relatedListing["abstract"] ?? "No abstract available",
            "file" => $relatedListing["file"] ?? null,
            "links" => "controller.php?page=listingInfo&id=" . $relatedListingID,
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

// Get the selected topic from the URL.
// Default to "all" if none is selected.
$selectedTopic = $_GET['topic'] ?? 'all';

// Build a list of unique topics from the carousel items.
$topics = array_unique(
        array_filter(
                array_column($listings, 'topic'),
                function ($topic) {
                    return !empty($topic)
                            // Exclude empty topics and "Uncategorized".
                            && strtolower(trim($topic)) !== 'uncategorized';
                }
        )
);

// Sort topics alphabetically for cleaner display.
sort($topics);

// If a specific topic was selected,
// only keep listings that match that topic.
if ($selectedTopic !== 'all') {

    $listings = array_values(
            array_filter($listings, function ($item) use ($selectedTopic) {

                return ($item['topic'] ?? 'Uncategorized') === $selectedTopic;
            })
    );
}

// Prevent carousel errors if no results match.
if (empty($listings)) {
    die("No sources found for this topic.");
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



<main class="report-page">

    <!-- Carousel -->
    <div class="carousel">

        <!-- Left arrow -->

        <?php if (count($listings) > 1): ?>

            <!-- Previous carousel item -->
            <a class="arrow"
               href="controller.php?page=ViewReport&id=<?= $primaryID ?>&topic=<?= urlencode($selectedTopic) ?>&i=<?= $prevIndex ?>">
                ◀
            </a>
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

            <!-- Next carousel item -->
            <a class="arrow"
               href="controller.php?page=ViewReport&id=<?= $primaryID ?>&topic=<?= urlencode($selectedTopic) ?>&i=<?= $nextIndex ?>">
                ▶
            </a>

        <?php else: ?>

            <span class="arrow disabled">▶</span>

        <?php endif; ?>


    </div>

    <!-- Control bar -->
    <div class="report-controls">

        <!-- Topic Filter Dropdown -->
        <select onchange="window.location.href = this.value">

            <!-- Show all topics -->
            <option
                value="controller.php?page=ViewReport&id=<?= $primaryID ?>&topic=all&i=0"
                <?= $selectedTopic === 'all' ? 'selected' : '' ?>>
                All Topics
            </option>

            <!-- Create one option per topic -->
            <?php foreach ($topics as $topic): ?>

                <option
                    value="controller.php?page=ViewReport&id=<?= $primaryID ?>&topic=<?= urlencode($topic) ?>&i=0"
                    <?= $selectedTopic === $topic ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($topic) ?>
                </option>

            <?php endforeach; ?>

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



