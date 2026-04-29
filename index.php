<?php
session_start();

include 'view/header.php';
include 'view/horizontal_nav_bar.php';

require "DBConnect/db.php";



// initial load (before any filters are used)
$stmt = $db->prepare('SELECT * FROM "Listing" ORDER BY "listingID" DESC');
$stmt->execute();
$listings = $stmt->fetchAll();
?>
<link rel="stylesheet" href="styles/main.css">
<body>
    <main>
    
        <div class="listings-container">

            <?php foreach ($listings as $listing): ?>

                <div class="source-item">

                    <a href="ExpandedListing/ListingInfo.php?id=<?= $listing['listingID'] ?>" class="item-link">

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
         
         <?php if (
            isset($_SESSION['userType']) &&
            ($_SESSION['userType'] == 1 || $_SESSION['userType'] == 2)
         ): ?>
            <a href="AddListing/AddSource.php" class="fab">+</a>
        <?php endif; ?>
        
          
    </main>
</body>
<script src="scripts/date.js"></script>
<script src="scripts/checkmark.js"></script>

<?php include 'view/footer.php'; ?>
