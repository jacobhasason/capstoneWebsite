<?php
//require_once "controller.php";
require "DBConnect/db.php";
?>
<link rel="stylesheet" href="styles/main.css">

<?php
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

// Get initial listings
$stmt = $db->prepare('
    SELECT * FROM "Listing" 
    ORDER BY "listingID" DESC 
    LIMIT :limit
');

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();

$listings = $stmt->fetchAll();

// Count total listings for initial load
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
                            <img src="<?= !empty($listing['icon']) ? htmlspecialchars($listing['icon']) : 'cocoNut.jpg' ?>">                         
                        </div>

                        <div class="source-details">
                            <h3><?= htmlspecialchars($listing['title']) ?></h3>
                            <p><?= htmlspecialchars($listing['author']) ?></p>
                            <p><?= htmlspecialchars($listing['date']) ?></p>
                        </div>

                        <div class="source-icon">
                            <span class="icon">
                                <?php
                                // trim() here to strip out accidental hidden database spaces
                                switch (trim(strtolower($listing['medium']))) {
                                    case "video":
                                        echo "🎥";
                                        break;

                                    case "podcast":
                                    case "podcasts":
                                        echo "🎧";
                                        break;
                                    case "Book":
                                    case "Books":
                                    case "book":
                                    case "books":
                                        echo "📖";
                                        break;

                                    case "Articles":
                                    case "Article":
                                    case "articles":
                                    case "article":
                                    case "paper":
                                    case "papers":
                                        echo "📄";
                                        break;
                                    case "Tutorial":
                                    case "Tutorials":
                                    case "tutorials":
                                    case "tutorial":
                                        echo "📘";
                                        break;

                                    case "presentation":
                                    case "presentations":
                                        echo "📊";
                                        break;

                                    case "software":
                                        echo "💻";
                                        break;

                                    default:
                                        echo "🐒";
                                        break;
                                }
                                ?>
                            </span>
                        </div>
                    </a>
                </div>

            <?php endforeach; ?>

            <?php if ($limit < $totalListings): ?>
                <div class="show-more-container">
                    <button type="button" id="show-more-btn" class="show-more-btn">
                        Show More
                    </button>
                </div>
            <?php endif; ?>

        </div>

        <?php
        if (
                isset($_SESSION['userType']) &&
                ($_SESSION['userType'] == 1 || $_SESSION['userType'] == 2)
        ):
            ?>
            <a href="controller.php?page=AddSource" class="fab">+</a>
        <?php endif; ?>

    </main>

</body>

<script src="scripts/date.js"></script>
<script src="scripts/checkmark.js"></script>
