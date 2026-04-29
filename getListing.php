<?php
require "DBConnect/db.php";

// read JSON from AJAX
$data = json_decode(file_get_contents("php://input"), true) ?? [];

$date = $data['date'] ?? null;
$topics = $data['topic'] ?? [];
$mediums = $data['medium'] ?? [];

// base query
$sql = 'SELECT * FROM "Listing"';
$where = [];
$params = [];

/* ----------------------
   TOPIC FILTER (FIXED)
   case-insensitive match
---------------------- */
if (!empty($topics)) {
    $topicConditions = [];

    foreach ($topics as $t) {
        $topicConditions[] = 'LOWER(topic) = LOWER(?)';
        $params[] = $t;
    }

    $where[] = '(' . implode(' OR ', $topicConditions) . ')';
}

/* ----------------------
   MEDIUM FILTER
---------------------- */
if (!empty($mediums)) {
    $placeholders = implode(',', array_fill(0, count($mediums), '?'));
    $where[] = "medium IN ($placeholders)";
    $params = array_merge($params, $mediums);
}

/* ----------------------
   BUILD WHERE CLAUSE
---------------------- */
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

/* ----------------------
   DATE SORTING
---------------------- */
if ($date === "Most Recent") {
    $sql .= ' ORDER BY "listingID" DESC';
} elseif ($date === "Oldest") {
    $sql .= ' ORDER BY "listingID" ASC';
} else {
    $sql .= ' ORDER BY "listingID" DESC';
}

/* ----------------------
   EXECUTE QUERY
---------------------- */
$stmt = $db->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();

/* ----------------------
   OUTPUT HTML
---------------------- */
foreach ($listings as $listing): ?>

    <div class="source-item">

        <a href="ExpandedListing/ListingInfo.php?id=<?= $listing['listingID'] ?>" class="item-link">

            <div class="thumbnail">
                <img src="<?= $listing['icon'] ?? 'default.jpg' ?>" alt="">
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
                        case "video": echo "🎥"; break;
                        case "podcast": echo "🎧"; break;
                        case "paper": echo "📄"; break;
                        case "tutorial": echo "📘"; break;
                        case "presentation": echo "📊"; break;
                        default: echo "📚"; break;
                    }
                    ?>
                </span>
            </div>

        </a>

    </div>

<?php endforeach; ?>