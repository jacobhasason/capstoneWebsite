<?php
require "DBConnect/db.php";

/* READ AJAX JSON */
$data = json_decode(file_get_contents("php://input"), true) ?? [];

$date = $data['date'] ?? null;
$topics = $data['topic'] ?? [];
$mediums = $data['medium'] ?? [];
$limit = (int) ($data['limit'] ?? 10);

/* BASE QUERY */
$sql = 'SELECT DISTINCT l.* FROM "Listing" l';

$where = [];
$params = [];

/* TOPIC FILTER (UPDATED FOR MANY-TO-MANY) */
if (!empty($topics)) {
    $topicConditions = [];
    foreach ($topics as $t) {
        $topicConditions[] = '
            EXISTS (
                SELECT 1
                FROM "ListingTopic" lt
                JOIN topic t2 ON t2.topic_id = lt.topic_id
                WHERE lt."listingID" = l."listingID"
                AND LOWER(TRIM(t2.topic_name)) = LOWER(TRIM(?))
            )
        ';
        $params[] = $t;
    }
    $where[] = '(' . implode(' OR ', $topicConditions) . ')';
}

/* MEDIUM FILTER */

if (!empty($mediums)) {

    $mediumConditions = [];

    foreach ($mediums as $m) {

        // --- TRANSLATION LAYER ---
        // If the user clicks the UI element for "books", map it to what the DB uses
        $m = trim(strtolower($m));
        if ($m === 'books') {
            $m = 'book'; // Change this to match your DB value (e.g., 'book', 'default', etc.)
        }

        $mediumConditions[] = 'LOWER(l.medium) = LOWER(?)';
        $params[] = $m;
    }

    $where[] = '(' . implode(' OR ', $mediumConditions) . ')';
}

/* BUILD WHERE CLAUSE */
$whereSql = "";
if (!empty($where)) {
    $whereSql = " WHERE " . implode(" AND ", $where);
}
$sql .= $whereSql;

/* --- COUNT TOTAL MATCHING LISTINGS BEFORE APPLYING LIMIT --- */
//replicate the exact same filters to see how many total items exist
$countSql = 'SELECT COUNT(DISTINCT l."listingID") FROM "Listing" l' . $whereSql;
$countStmt = $db->prepare($countSql);
// Execute count query with current parameters
$countStmt->execute($params);
$totalListings = (int) $countStmt->fetchColumn();

/* DATE SORTING */
if ($date === "Most Recent") {
    $sql .= ' ORDER BY l."date" DESC';
} elseif ($date === "Oldest") {
    $sql .= ' ORDER BY l."date" ASC';
} else {
    $sql .= ' ORDER BY l."date" DESC';
}

/* LIMIT */
$sql .= ' LIMIT ?';
$params[] = $limit;

/* PREPARE + BIND FOR LISTINGS */
$stmt = $db->prepare($sql);

foreach ($params as $index => $param) {
    $type = is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($index + 1, $param, $type);
}

/* EXECUTE */
$stmt->execute();
$listings = $stmt->fetchAll();
?>

<div class="listings-container">

    <?php if (empty($listings)): ?>
        <p>No listings found matching your criteria.</p>
    <?php else: ?>
        <?php foreach ($listings as $listing): ?>

            <div class="source-item">
                <a href="controller.php?page=listingInfo&id=<?= $listing['listingID'] ?>" class="item-link">
                    <div class="thumbnail">
                        <img src="<?= htmlspecialchars($listing['icon'] ?? 'cocoNut.jpg') ?>" alt="">
                    </div>

                    <div class="source-details">
                        <h3><?= htmlspecialchars($listing['title']) ?></h3>
                        <p><?= htmlspecialchars($listing['author']) ?></p>
                        <p><?= htmlspecialchars($listing['date'] ?? '') ?></p>
                    </div>

                    <div class="source-icon">
                        <span class="icon">
                            <?php
                            // Added trim() here as well to sanitize the AJAX output
                            switch (trim(strtolower($listing['medium']))) {
                                case "video":
                                    echo "🎥";
                                    break;

                                case "podcast":
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
    <?php endif; ?>

    <?php if ($limit < $totalListings): ?>
        <div class="show-more-container">
            <button type="button" id="show-more-btn" class="show-more-btn">
                Show More
            </button>
        </div>
    <?php endif; ?>

</div>
