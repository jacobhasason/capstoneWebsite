
<?php
include_once('view/header.php');
include_once('view/horizontal_nav_bar.php');
require "DBConnect/db.php";

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

// Get listings
$stmt = $db->prepare('
    SELECT * 
    FROM "Listing" 
    ORDER BY "listingID" DESC 
    LIMIT :limit
');

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();

$listings = $stmt->fetchAll();

// Count total listings
$totalStmt = $db->prepare('SELECT COUNT(*) FROM "Listing"');
$totalStmt->execute();

$totalListings = $totalStmt->fetchColumn();
?>



<body>

<main>

    <div class="listings-container">

        <?php foreach ($listings as $listing): ?>

            <div class="source-item">

                <a href="controller.php?page=listingInfo&id=<?= $listing['listingID'] ?>" class="item-link">

                    <div class="thumbnail">
                        <img src="<?= $listing['icon'] ?? 'default.jpg' ?>">
                    </div>

                    <div class="source-details">
                        <h3><?= htmlspecialchars($listing['title']) ?></h3>
                        <p><?= htmlspecialchars($listing['author']) ?></p>
                        <p><?= htmlspecialchars($listing['date']) ?></p>
                    </div>

                    <div class="source-icon">
                        <span class="icon">

                            <?php
                            switch ($listing['medium']) {

                                case "video":
                                    echo "🎥";
                                    break;

                                case "podcast":
                                    echo "🎧";
                                    break;

                                case "paper":
                                    echo "📄";
                                    break;

                                case "tutorial":
                                    echo "📘";
                                    break;

                                case "presentation":
                                    echo "📊";
                                    break;

                                default:
                                    echo "📚";
                                    break;
                            }
                            ?>

                        </span>
                    </div>

                </a>

            </div>

        <?php endforeach; ?>

    </div>

    <?php if ($limit < $totalListings): ?>

        <div class="show-more-container">

            <a href="?limit=<?= $limit + 20 ?>" class="show-more-btn">
                Show More
            </a>

        </div>

    <?php endif; ?>

    <?php if (
        isset($_SESSION['userType']) &&
        ($_SESSION['userType'] == 1 || $_SESSION['userType'] == 2)
    ): ?>

        <a href="controller.php?page=AddSource" class="fab">+</a>

    <?php endif; ?>

</main>

</body>

<script src="scripts/date.js"></script>
<script src="scripts/checkmark.js"></script>

