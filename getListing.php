<?php
require "DBConnect/db.php";

/* READ AJAX JSON */
$data = json_decode(file_get_contents("php://input"), true) ?? [];

$date = $data['date'] ?? null;
$topics = $data['topic'] ?? []; // Array of numerical topic_ids from front-end checkbox values
$mediums = $data['medium'] ?? [];
$limit = (int) ($data['limit'] ?? 10);

/* BASE QUERY */
$sql = 'SELECT DISTINCT l.* FROM public."Listing" l';

$where = [];
$params = [];

/* 1. TOPIC FILTER (MANY-TO-MANY JUNCTION QUERY) */
if (!empty($topics)) {
    $topicConditions = [];
    foreach ($topics as $t) {
       
        $topicConditions[] = '
            EXISTS (
                SELECT 1
                FROM public."ListingTopic" lt
                WHERE lt."listingID" = l."listingID"
                AND lt.topic_id = ?
            )
        ';
        $params[] = (int) $t; // Safely bind as integer to match primary key configuration
    }
    // Match listings that contain any of the selected topics (OR logic within topics)
    $where[] = '(' . implode(' OR ', $topicConditions) . ')';
}

/* 2. MEDIUM FILTER (STRICT VARIATION NORMALIZATION) */
if (!empty($mediums)) {
    $mediumConditions = [];
    foreach ($mediums as $m) {
        $m = trim(strtolower($m));

        // Match both singular and plural forms based on data-value attribute inputs
        if ($m === 'book' || $m === 'books') {
            $mediumConditions[] = 'LOWER(TRIM(l.medium)) IN (\'book\', \'books\')';
        }
        // Intercept 'paper' from data-value and check against all article/paper string forms
        elseif ($m === 'paper' || $m === 'papers' || $m === 'article' || $m === 'articles') {
            $mediumConditions[] = 'LOWER(TRIM(l.medium)) IN (\'paper\', \'papers\', \'article\', \'articles\')';
        } elseif ($m === 'tutorial' || $m === 'tutorials') {
            $mediumConditions[] = 'LOWER(TRIM(l.medium)) IN (\'tutorial\', \'tutorials\')';
        } elseif ($m === 'presentation' || $m === 'presentations') {
            $mediumConditions[] = 'LOWER(TRIM(l.medium)) IN (\'presentation\', \'presentations\')';
        } elseif ($m === 'podcast' || $m === 'podcasts') {
            $mediumConditions[] = 'LOWER(TRIM(l.medium)) IN (\'podcast\', \'podcasts\')';
        } elseif ($m === 'video' || $m === 'videos') {
            $mediumConditions[] = 'LOWER(TRIM(l.medium)) IN (\'video\', \'videos\')';
        } else {
            // Standard fallback direct parameter binding
            $mediumConditions[] = 'LOWER(TRIM(l.medium)) = ?';
            $params[] = $m;
        }
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
$countSql = 'SELECT COUNT(DISTINCT l."listingID") FROM public."Listing" l' . $whereSql;
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

/* LIMIT INJECTION */
$sql .= ' LIMIT ?';
$params[] = $limit;

/* PREPARE STATEMENT */
$stmt = $db->prepare($sql);

/* BIND ALL PARAMETERS DYNAMICALLY */
foreach ($params as $index => $param) {
    $type = is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($index + 1, $param, $type);
}

/* EXECUTE AND FETCH RESULTS */
$stmt->execute();
$listings = $stmt->fetchAll();

/* --- OUTPUT RENDERING ENGINE --- */
if (empty($listings)):
    ?>
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
                    <p><?= htmlspecialchars($listing['author'] ?? '') ?></p>
                    <p><?= htmlspecialchars($listing['date'] ?? '') ?></p>
                </div>

                <div class="source-icon">
                    <span class="icon">
        <?php
        switch (trim(strtolower($listing['medium'] ?? ''))) {
            case "video": echo "🎥";
                break;
            case "podcast":
            case "podcasts": echo "🎧";
                break;
            case "book":
            case "books": echo "📖";
                break;
            case "article":
            case "articles":
            case "paper":
            case "papers": echo "📄";
                break;
            case "tutorial":
            case "tutorials": echo "📘";
                break;
            case "presentation":
            case "presentations": echo "📊";
                break;
            case "software": echo "💻";
                break;
            default: echo "🐒";
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