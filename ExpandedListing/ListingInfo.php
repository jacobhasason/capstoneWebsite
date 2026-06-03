<?php

require "DBConnect/db.php";

/* PERMISSION CHECK */
$currentCanModify = (int) ($_SESSION["permisMod"] ?? 1);
$currentUserType = (int)($_SESSION["userType"] ?? 0);
$currentCanManage = (int)($_SESSION["canManage"] ?? 1);

/* GET LISTING */
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

/* FETCH TOPICS FOR THIS LISTING */
$topicStmt = $db->prepare("
    SELECT t.topic_name, c.category_name
    FROM \"ListingTopic\" lt
    JOIN topic t ON lt.topic_id = t.topic_id
    JOIN topic_category c ON t.category_id = c.category_id
    WHERE lt.\"listingID\" = ?
");

$topicStmt->execute([$id]);
$topics = $topicStmt->fetchAll();

?>

<link rel="stylesheet" href="../styles/main.css">

<main class="listing-full">

    <h1><?= htmlspecialchars($listing['title'] ?? 'Untitled') ?></h1>

    <p><strong>Author(s):</strong> <?= htmlspecialchars($listing['author'] ?? 'Unknown') ?></p>

    <p><strong>Date:</strong> <?= htmlspecialchars($listing['date'] ?? 'N/A') ?></p>

    <p><strong>Medium:</strong> <?= htmlspecialchars($listing['medium'] ?? 'N/A') ?></p>

    <!-- UPDATED TOPIC DISPLAY -->
    <p><strong>Topics:</strong></p>

    <?php if (!empty($topics)): ?>

        <ul class="topic-list">

            <?php foreach ($topics as $t): ?>

                <li>
                    <?= htmlspecialchars($t['category_name']) ?>
                    →
                    <?= htmlspecialchars($t['topic_name']) ?>
                </li>

            <?php endforeach; ?>

        </ul>

    <?php else: ?>

        <p>Uncategorized</p>

    <?php endif; ?>

    <hr>

    <p class="abstract">
        <?= nl2br(htmlspecialchars($listing['abstract'] ?? 'No abstract available')) ?>
    </p>

    <!-- IMAGE -->
    <div class="listing-image">

        <?php $iconPath = $listing['icon'] ?? ''; ?>

        <?php if (!empty($iconPath)): ?>

            <img src="<?= htmlspecialchars($iconPath) ?>"
                 alt="Listing Icon"
                 class="listing-img">

        <?php else: ?>

            <div class="no-image">
                Icon Not Found
            </div>

        <?php endif; ?>

    </div>

    <!-- ACTIONS -->
    <div class="listing-actions">

        <div class="action-buttons">

            <a class="btn" id="copyCitation" data-citation="<?= htmlspecialchars($listing['citations'] ?? '') ?>">
                Copy Citation
            </a>

            <a class="btn edit"
               href="controller.php?page=ViewReport&id=<?= $listing['listingID'] ?>">
                View Primary Source & Related Resources
            </a>
            <!-- EDIT BUTTON -->
            <?php if ($currentCanModify === 2): ?>

                <a class="btn edit"
                   href="controller.php?page=editListing&id=<?= $listing['listingID'] ?>">
                    Edit Source
                </a>

            <?php endif; ?>

            <?php if (
                $currentUserType === 2 ||
                ($currentUserType === 1 && $currentCanManage === 2)
            ): ?>

                <a class="btn delete-source"
                   href="controller.php?page=deleteListing&id=<?= $listing['listingID'] ?>"
                   onclick="return confirm('Delete this source permanently?')">

                    Delete Source

                </a>

            <?php endif; ?>

        </div>

    </div>

    <!-- OPTIONAL EXTERNAL LINK -->
    <?php if (!empty($listing['links'])): ?>

        <p class="extra-link">
            <a href="<?= htmlspecialchars($listing['links']) ?>" target="_blank">
                External Link
            </a>
        </p>

    <?php endif; ?>

    <!-- FILE DOWNLOAD -->
    <?php if (!empty($listing['file'])): ?>

        <p class="extra-link">
            <a href="<?= htmlspecialchars($listing['file']) ?>" download>
                Download File
            </a>
        </p>

    <?php endif; ?>

</main>

<a href="controller.php?page=index" class="fab">&lt;&lt;</a>

<script src="../scripts/date.js"></script>

<!-- JAVASCRIPT FOR COPY CITATIONS -->
<script>
                   document.getElementById("copyCitation").addEventListener("click", async function () {

                       const citation = this.dataset.citation;

                       if (!citation || citation.trim() === "") {
                           alert("No citation available.");
                           return;
                       }

                       try {
                           await navigator.clipboard.writeText(citation);
                           // Button changes to 'Copied!' for 2 seconds
                           const originalText = this.textContent;
                           this.textContent = "Copied!";

                           setTimeout(() => {
                               this.textContent = originalText;
                           }, 2000);

                       } catch (err) {
                           console.error(err);
                           alert("Failed to copy citation.");
                       }
                   });
</script>