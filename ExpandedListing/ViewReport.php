<?php
require "DBConnect/db.php";

/* Get all topics for a listing through ListingTopic -> topic */
function getListingTopics($db, $listingID) {
    $stmt = $db->prepare(
        'SELECT t."topic_name"
         FROM "ListingTopic" lt
         JOIN "topic" t ON lt."topic_id" = t."topic_id"
         WHERE lt."listingID" = ?'
    );

    $stmt->execute([$listingID]);
    $topics = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return !empty($topics) ? implode(", ", $topics) : "Uncategorized";
}

/* Get topic array for dropdown/filtering */
function getListingTopicArray($db, $listingID) {
    $stmt = $db->prepare(
        'SELECT t."topic_name"
         FROM "ListingTopic" lt
         JOIN "topic" t ON lt."topic_id" = t."topic_id"
         WHERE lt."listingID" = ?'
    );

    $stmt->execute([$listingID]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$primaryID = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($primaryID <= 0) {
    die("Missing primary listing ID");
}

/* Load primary source */
$stmt = $db->prepare('SELECT * FROM "Listing" WHERE "listingID" = ?');
$stmt->execute([$primaryID]);
$primary = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$primary) {
    die("Primary listing not found");
}

/* Load related sources */
$stmt = $db->prepare(
    'SELECT *
     FROM "RelatedListing"
     WHERE "listingID" = ?
     ORDER BY "relatedListingID" ASC'
);
$stmt->execute([$primaryID]);
$relatedRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Build carousel items */
$listings = [];

/* Primary source */
$primaryTopics = getListingTopicArray($db, $primaryID);

$listings[] = [
    "title" => $primary["title"] ?? "Untitled",
    "author" => $primary["author"] ?? "Unknown",
    "topic" => !empty($primaryTopics) ? implode(", ", $primaryTopics) : "Uncategorized",
    "topicArray" => $primaryTopics,
    "abstract" => $primary["abstract"] ?? "No description available",
    "file" => $primary["file"] ?? null,
    "links" => $primary["links"] ?? null,
    "type" => "primary"
];

/* Related sources */
foreach ($relatedRows as $row) {

    /* Related source is another listing on the site */
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

        $relatedTopics = getListingTopicArray($db, $relatedListingID);

        $listings[] = [
            "title" => $relatedListing["title"] ?? ($row["title"] ?? "Related Listing"),
            "author" => $relatedListing["author"] ?? "Unknown",
            "topic" => !empty($relatedTopics) ? implode(", ", $relatedTopics) : "Uncategorized",
            "topicArray" => $relatedTopics,
            "abstract" => $relatedListing["abstract"] ?? "No abstract available",
            "file" => $relatedListing["file"] ?? null,
            "links" => "controller.php?page=listingInfo&id=" . $relatedListingID,
            "type" => "listing"
        ];

    } else {

        /* Related source is an uploaded file or hyperlink */
        $fallbackTopics = getListingTopicArray($db, $primaryID);

        $listings[] = [
            "title" => $row["title"] ?? ucfirst($row["type"] ?? "Related Source"),
            "author" => "Unknown",
            "topic" => !empty($fallbackTopics) ? implode(", ", $fallbackTopics) : "Uncategorized",
            "topicArray" => $fallbackTopics,
            "abstract" => ucfirst($row["type"] ?? "source"),
            "file" => $row["file"] ?? null,
            "links" => $row["links"] ?? null,
            "type" => $row["type"] ?? "related"
        ];
    }
}

/* Topic filter from URL */
$selectedTopic = $_GET['topic'] ?? 'all';

/* Build unique dropdown topics */
$topics = [];

foreach ($listings as $item) {
    foreach (($item["topicArray"] ?? []) as $topic) {
        $topic = trim($topic);

        if ($topic !== "" && strtolower($topic) !== "uncategorized") {
            $topics[] = $topic;
        }
    }
}

$topics = array_unique($topics);
sort($topics);

/* Filter carousel by selected topic */
if ($selectedTopic !== 'all') {
    $listings = array_values(
        array_filter($listings, function ($item) use ($selectedTopic) {
            return in_array($selectedTopic, $item["topicArray"] ?? []);
        })
    );
}

if (empty($listings)) {
    die("No sources found for this topic.");
}

/* Carousel index */
$index = isset($_GET['i']) ? (int) $_GET['i'] : 0;

if ($index < 0) {
    $index = count($listings) - 1;
}

if ($index >= count($listings)) {
    $index = 0;
}

$current = $listings[$index];

$totalItems = count($listings);

/* Arrow index logic */
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

    <div class="carousel">

        <?php if (count($listings) > 1): ?>
            <a class="arrow"
               href="controller.php?page=ViewReport&id=<?= $primaryID ?>&topic=<?= urlencode($selectedTopic) ?>&i=<?= $prevIndex ?>">
                ◀
            </a>
        <?php else: ?>
            <span class="arrow disabled">◀</span>
        <?php endif; ?>

        <div class="carousel-content">

            <h2><?= htmlspecialchars($current["title"] ?? 'Untitled') ?></h2>

            <p>
                <strong>Author:</strong>
                <?= htmlspecialchars($current["author"] ?? 'Unknown') ?>
            </p>

            <p>
                <strong>Topic(s):</strong>
                <?= htmlspecialchars($current["topic"] ?? 'Uncategorized') ?>
            </p>

            <hr>

            <div class="preview">
                <?= nl2br(htmlspecialchars($current["abstract"] ?? 'No description available')) ?>
            </div>

        </div>

        <?php if (count($listings) > 1): ?>
            <a class="arrow"
               href="controller.php?page=ViewReport&id=<?= $primaryID ?>&topic=<?= urlencode($selectedTopic) ?>&i=<?= $nextIndex ?>">
                ▶
            </a>
        <?php else: ?>
            <span class="arrow disabled">▶</span>
        <?php endif; ?>

    </div>

    <div class="report-controls">

        <select onchange="window.location.href = this.value">

            <option
                value="controller.php?page=ViewReport&id=<?= $primaryID ?>&topic=all&i=0"
                <?= $selectedTopic === 'all' ? 'selected' : '' ?>>
                All Topics
            </option>

            <?php foreach ($topics as $topic): ?>
                <option
                    value="controller.php?page=ViewReport&id=<?= $primaryID ?>&topic=<?= urlencode($topic) ?>&i=0"
                    <?= $selectedTopic === $topic ? 'selected' : '' ?>>
                    <?= htmlspecialchars($topic) ?>
                </option>
            <?php endforeach; ?>

        </select>

        <div class="current-title">
            <?= htmlspecialchars($current["title"] ?? 'Untitled') ?>
        </div>

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

    <p style="text-align:center; margin-top:10px;">
        <?= $index + 1 ?> / <?= count($listings) ?>
    </p>

    <a href="controller.php?page=index" class="fab"><<</a>

</main>