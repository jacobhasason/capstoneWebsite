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
?>


<link rel="stylesheet" href="../styles/main.css">


<main class="listing-full">

    <h1><?= htmlspecialchars($listing['title'] ?? 'Untitled') ?></h1>


    <p><strong>Author(s):</strong> <?= htmlspecialchars($listing['author'] ?? 'Unknown') ?></p>


    <p><strong>Date:</strong> <?= htmlspecialchars($listing['date'] ?? 'N/A') ?></p>

    <p><strong>Medium:</strong> <?= htmlspecialchars($listing['medium'] ?? 'N/A') ?></p>

    <p><strong>Topic:</strong> <?= htmlspecialchars($listing['topic'] ?? 'Uncategorized') ?></p>

    <hr>

    <p class="abstract">
        <?= nl2br(htmlspecialchars($listing['abstract'] ?? 'No abstract available')) ?>
    </p>

    <!--IMAGE-->

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


</div>

    <!--ACTIONS -->
    <div class="listing-actions">

        <div class="action-buttons">


            

            <button class="btn">Copy Citation</button>

            <a class="btn external"

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

            <!-- DELETE BUTTON -->
            <?php
            if (
                    $currentUserType === 2 ||
                    ($currentUserType === 1 && $currentCanManage === 2)
            ):
                ?>

                <a class="btn delete-source"
                   href="controller.php?page=deleteListing&id=<?= $listing['listingID'] ?>"
                   onclick="return confirm('Delete this source permanently?')">

                    Delete Source

                </a>

            <?php endif; ?>


        </div>

    </div>

    <!--OPTIONAL EXTERNAL LINK -->

    <?php if (!empty($listing['links'])): ?>

        <p class="extra-link">
            <a href="<?= htmlspecialchars($listing['links']) ?>" target="_blank">
                External Link
            </a>
        </p>
    <?php endif; ?>

    <!--FILE DOWNLOAD-->
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


