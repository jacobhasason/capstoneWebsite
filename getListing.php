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
                AND LOWER(t2.topic_name) = LOWER(?)
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

        $mediumConditions[] = 'LOWER(l.medium) = LOWER(?)';

        $params[] = $m;
    }

    $where[] = '(' . implode(' OR ', $mediumConditions) . ')';
}

/* BUILD WHERE CLAUSE */
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

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

/* PREPARE + BIND */
$stmt = $db->prepare($sql);

foreach ($params as $index => $param) {
    $type = is_int($param) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($index + 1, $param, $type);
}

/* EXECUTE */
$stmt->execute();

$listings = $stmt->fetchAll();

/* OUTPUT HTML */
foreach ($listings as $listing):
    ?>

    <div class="source-item">

        <a href="controller.php?page=listingInfo&id=<?= $listing['listingID'] ?>"
           class="item-link">

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
                    switch (strtolower($listing['medium'])) {
                        case "video": echo "🎥";
                            break;
                        case "podcast": echo "🎧";
                            break;
                        case "paper": echo "📄";
                            break;
                        case "tutorial": echo "📘";
                            break;
                        case "presentation": echo "📊";
                            break;
                        default: echo "📚";
                            break;
                    }
                    ?>
                </span>
            </div>

        </a>

    </div>

<?php endforeach; ?>