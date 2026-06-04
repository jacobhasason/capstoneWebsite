<?php
require "DBConnect/db.php";
/*monke fix, COMPUTA give monke banaenae 🍌🍌🍌*/
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

/* TOPIC FILTER (MANY-TO-MANY) 🐒🐒🐒*/
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

/* MEDIUM FILTER 🐒🐒*/
if (!empty($mediums)) {
    $mediumConditions = [];
    foreach ($mediums as $m) {
        // --- TRANSLATION LAYER ---
        $m = trim(strtolower($m));
        if ($m === 'books') {
            $m = 'book';
        }

        $mediumConditions[] = 'LOWER(l.medium) = LOWER(?)';
        $params[] = $m;
    }
    $where[] = '(' . implode(' OR ', $mediumConditions) . ')';
}

/* BUILD WHERE CLAUSE 🐒🐒 */
$whereSql = "";
if (!empty($where)) {
    $whereSql = " WHERE " . implode(" AND ", $where);
}
$sql .= $whereSql;

/* --- COUNT TOTAL MATCHING LISTINGS BEFORE APPLYING LIMIT --- */
$countSql = 'SELECT COUNT(DISTINCT l."listingID") FROM "Listing" l' . $whereSql;
$countStmt = $db->prepare($countSql);
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

/* LIMIT 🐒🐒🐒*/
$sql .= ' LIMIT ?';
$params[] = $limit;

/* PREPARE + BIND FOR LISTINGS */
$stmt = $db->prepare($sql);

foreach ($params as $index => $param) {
    $type = is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($index + 1, $param, $type);
}

/* EXECUTE🐒🐒🐒🐒🐒🐒🐒🐒 */
$stmt->execute();
$listings = $stmt->fetchAll();

/* --- OUTPUT RENDERING --- Redundant outer div wrapper has been removed here to fix nested layout corruption 🐒🐒🐒🐒🐒🐒🐒🐒🐒🍌🍌🍌🍌🍌 */
if (empty($listings)): ?>
    <p>No listings found matching your criteria.</p>
<?php else: ?>
    <?php foreach ($listings as $listing): ?>

        <div class="source-item">
            <a href="controller.php?page=listingInfo&id=<?= $listing['listingID'] ?>" class="item-link">
                <div class="thumbnail">
                    <img src="<?= !empty($listing['icon']) ? htmlspecialchars($listing['icon']) : 'cocoNut.jpg' ?>" alt="">
                </div>

                <div class="source-details">
                    <h3><?= htmlspecialchars($listing['title']) ?></h3>
                    <p><?= htmlspecialchars($listing['author']) ?></p>
                    <p><?= htmlspecialchars($listing['date'] ?? '') ?></p>
                </div>

                <div class="source-icon">
                    <span class="icon">
                        <?php
                        switch (trim(strtolower($listing['medium']))) {
                            case "video":
                                echo "🎥";
                                break;
                            case "podcast":
                            case "podcasts":
                                echo "🎧";
                                break;
                            case "book":
                            case "books":
                                echo "📖";
                                break;
                            case "article":
                            case "articles":
                            case "paper":
                            case "papers":
                                echo "📄";
                                break;
                            case "tutorial":
                            case "tutorials":
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
