<?php
require "DBConnect/db.php";

/* PERMISSION CHECK */
if (!isset($_SESSION["permisMod"]) || (int) $_SESSION["permisMod"] !== 2) {
    die("Not allowed");
}

/* GET LISTING ID */
$id = $_GET["id"] ?? null;

if (!$id) {
    die("No listing selected");
}

/* FETCH LISTING */
$stmt = $db->prepare('SELECT * FROM "Listing" WHERE "listingID" = ?');
$stmt->execute([$id]);
$listing = $stmt->fetch();

if (!$listing) {
    die("Listing not found");
}

/* FETCH TOPIC DATA  */
$catStmt = $db->query("
    SELECT category_id, category_name
    FROM topic_category
    ORDER BY category_name
");
$categories = $catStmt->fetchAll();

$topicStmt = $db->query("
    SELECT topic_id, category_id, topic_name
    FROM topic
    ORDER BY topic_name
");
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
    if (!isset($tree[$t['category_id']]))
        continue;

    $tree[$t['category_id']]['children'][] = $t;
}

/* GET EXISTING TOPICS FOR LISTING  */
$existingTopicStmt = $db->prepare("
    SELECT topic_id
    FROM \"ListingTopic\"
    WHERE \"listingID\" = ?
");

$existingTopicStmt->execute([$id]);

$existingTopics = array_column(
        $existingTopicStmt->fetchAll(),
        'topic_id'
);

/* UPDATE HANDLER */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = $_POST["title"] ?? "";
    $author = $_POST["author"] ?? "";
    $date = $_POST["date"] ?? "";
    $medium = $_POST["medium"] ?? "";
    $abstract = $_POST["abstract"] ?? "";
    $links = $_POST["links"] ?? "";

    /* UPDATED QUERY */
    $update = $db->prepare('
        UPDATE "Listing"
        SET "title" = ?,
            "author" = ?,
            "date" = ?,
            "medium" = ?,
            "abstract" = ?,
            "links" = ?
        WHERE "listingID" = ?
    ');

    $update->execute([
        $title,
        $author,
        $date,
        $medium,
        $abstract,
        $links,
        $id
    ]);

    /* SYNC TOPICS (NEW MANY-TO-MANY SYSTEM) */
    $db->prepare('DELETE FROM "ListingTopic" WHERE "listingID" = ?')
            ->execute([$id]);

    if (!empty($_POST['topics'])) {

        $insert = $db->prepare('
            INSERT INTO "ListingTopic" ("listingID", topic_id)
            VALUES (?, ?)
        ');

        foreach ($_POST['topics'] as $topicId) {
            $insert->execute([$id, (int) $topicId]);
        }
    }

    header("Location: controller.php?page=listingInfo&id=" . $id);
    exit();
}
?>

<main class="edit-listing">

    <h1>Edit Listing</h1>

    <form method="POST" class="edit-form">

        <label>Title</label>
        <input type="text" name="title"
               value="<?= htmlspecialchars($listing["title"]) ?>">

        <label>Author(s)</label>
        <input type="text" name="author"
               value="<?= htmlspecialchars($listing["author"]) ?>">

        <label>Date</label>
        <input type="text" name="date"
               value="<?= htmlspecialchars($listing["date"]) ?>">

        <label>Medium</label>
        <input type="text" name="medium"
               value="<?= htmlspecialchars($listing["medium"]) ?>">


        <label>Topics</label>

        <div class="topic-dropdown">

            <?php foreach ($tree as $category): ?>

                <div class="topic-category">


                    <?php
                    $categoryTopicIds = array_map(
                            fn($t) => $t['topic_id'],
                            $category['children']
                    );

                    $categoryChecked = !array_diff($categoryTopicIds, $existingTopics) && !empty($categoryTopicIds);
                    ?>

                    <label style="font-weight:bold; display:block;">
                        <input type="checkbox"
                               class="category-box"
                               data-children="<?= htmlspecialchars(json_encode($categoryTopicIds)) ?>"
                               <?= $categoryChecked ? 'checked' : '' ?>>

                        <?= htmlspecialchars($category['name']) ?>
                    </label>

                    <!-- SUBTOPICS -->
                    <div class="topic-sublist" style="margin-left:15px;">

                        <?php foreach ($category['children'] as $topic): ?>

                            <?php $checked = in_array($topic['topic_id'], $existingTopics); ?>

                            <label style="display:block;">

                                <input type="checkbox"
                                       name="topics[]"
                                       value="<?= (int) $topic['topic_id'] ?>"
                                       class="topic-box"
                                       <?= $checked ? 'checked' : '' ?>>

                                <?= htmlspecialchars($topic['topic_name']) ?>

                            </label>

                        <?php endforeach; ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

        <label>Abstract</label>
        <textarea name="abstract"><?= htmlspecialchars($listing["abstract"] ?? '') ?></textarea>
        <label>External Link</label>
        <input type="text" name="links"
               value="<?= htmlspecialchars($listing["links"]) ?>">

        <button type="submit" class="btn edit-save">
            Save Changes
        </button>

    </form>

    <a href="controller.php?page=listingInfo&id=<?= $id ?>" class="fab">←</a>
</main>
<script>
    document.addEventListener("change", function (e) {

        /* CATEGORY CLICKED */
        if (e.target.classList.contains("category-box")) {

            const categoryBox = e.target;

            let children = [];

            try {
                children = JSON.parse(categoryBox.dataset.children || "[]");
            } catch (err) {
                return;
            }

            const isChecked = categoryBox.checked;

            /* toggle all subtopics */
            children.forEach(id => {

                const child = document.querySelector(
                        `.topic-box[value="${id}"]`
                        );

                if (child) {
                    child.checked = isChecked;
                }
            });

            return;
        }

        /* OPTIONAL: if individual topic changes, update category state */
        if (e.target.classList.contains("topic-box")) {

            const topic = e.target;
            const wrapper = topic.closest(".topic-category");

            if (!wrapper)
                return;

            const categoryBox = wrapper.querySelector(".category-box");
            const children = wrapper.querySelectorAll(".topic-box");

            const checked = wrapper.querySelectorAll(".topic-box:checked");

            /* if all checked → category checked */
            categoryBox.checked = (children.length === checked.length);

            /* if none checked → category unchecked */
            if (checked.length === 0) {
                categoryBox.checked = false;
            }
        }

    });
</script>