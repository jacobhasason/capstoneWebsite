<?php
require "DBConnect/db.php";

/* FETCH CATEGORIES */
$catStmt = $db->prepare('SELECT category_id, category_name FROM topic_category ORDER BY category_id');
$catStmt->execute();
$categories = $catStmt->fetchAll();

/* FETCH TOPICS */
$topicStmt = $db->prepare('SELECT topic_id, category_id, topic_name FROM topic ORDER BY topic_id');
$topicStmt->execute();
$topics = $topicStmt->fetchAll();

/* BUILD TREE */
$tree = [];

foreach ($categories as $c) {
    $tree[$c['category_id']] = [
        'name' => $c['category_name'],
        'children' => []
    ];
}

foreach ($topics as $t) {
    $cid = $t['category_id'];
    if (!isset($tree[$cid])) continue;

    $tree[$cid]['children'][] = $t;
}
?>

<!DOCTYPE html>
<html lang="en">
<body>

<nav aria-label="Main Navigation">

    <ul class="main-nav">

        <!-- DATE -->
        <li>
            <a href="#">Date</a>
            <ul class="dropdown">

                <li>
                    <a href="#" class="filter-item" data-type="date" data-value="most_recent">
                        <span class="checkbox-box"></span>
                        Most Recent
                    </a>
                </li>

                <li>
                    <a href="#" class="filter-item" data-type="date" data-value="oldest">
                        <span class="checkbox-box"></span>
                        Oldest
                    </a>
                </li>

            </ul>
        </li>

        <!-- TOPIC -->
        <li class="topic-menu">
            <a href="#">Topic</a>

            <ul class="dropdown topic-tree">

                <?php foreach ($tree as $category): ?>

                    <li>

                        <button type="button" class="tree-toggle">
                            <?= htmlspecialchars($category['name']) ?>
                        </button>

                        <?php if (!empty($category['children'])): ?>

                            <ul class="hidden">

                                <?php foreach ($category['children'] as $topic): ?>

                                    <li>
                                        <a href="#"
                                           class="filter-item"
                                           data-type="topic"
                                           data-value="<?= htmlspecialchars($topic['topic_id']) ?>">

                                            <span class="checkbox-box"></span>
                                            <?= htmlspecialchars($topic['topic_name']) ?>

                                        </a>
                                    </li>

                                <?php endforeach; ?>

                            </ul>

                        <?php endif; ?>

                    </li>

                <?php endforeach; ?>

            </ul>
        </li>

        <!-- MEDIUM -->
        <li>
            <a href="#">Medium</a>
            <ul class="dropdown">

                <li>
                    <a href="#" class="filter-item" data-type="medium" data-value="book">
                        <span class="checkbox-box"></span>
                        Book
                    </a>
                </li>

                <li>
                    <a href="#" class="filter-item" data-type="medium" data-value="paper">
                        <span class="checkbox-box"></span>
                        Papers
                    </a>
                </li>

                <li>
                    <a href="#" class="filter-item" data-type="medium" data-value="tutorial">
                        <span class="checkbox-box"></span>
                        Tutorials
                    </a>
                </li>

                <li>
                    <a href="#" class="filter-item" data-type="medium" data-value="presentation">
                        <span class="checkbox-box"></span>
                        Presentations
                    </a>
                </li>

                <li>
                    <a href="#" class="filter-item" data-type="medium" data-value="video">
                        <span class="checkbox-box"></span>
                        Videos
                    </a>
                </li>

                <li>
                    <a href="#" class="filter-item" data-type="medium" data-value="podcast">
                        <span class="checkbox-box"></span>
                        Podcasts
                    </a>
                </li>

                <li>
                    <a href="#" class="filter-item" data-type="medium" data-value="software">
                        <span class="checkbox-box"></span>
                        Software
                    </a>
                </li>

            </ul>
        </li>

    </ul>

</nav>

<script src="scripts/dragdrop.js"></script>
<script src="scripts/tree.js"></script>
<script src="scripts/checkmark.js"></script>

</body>
</html>