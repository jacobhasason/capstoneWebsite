<?php
require "DBConnect/db.php";

/* AUTH CHECK 🐒 */
if (!isset($_SESSION["userID"])) {
    header("Location: controller.php?page=login");
    exit();
}

/* SUPERUSER ONLY 🐒*/
if ((int)$_SESSION["userType"] !== 2) {
    echo "<main><p>You do not have permission to access this page.</p></main>";
    exit();
}

/* FETCH DATA 🐒*/
$catStmt = $db->query('
    SELECT category_id, category_name
    FROM topic_category
    ORDER BY category_id
');
$categories = $catStmt->fetchAll();

$topicStmt = $db->query('
    SELECT topic_id, category_id, topic_name
    FROM topic
    ORDER BY topic_id
');
$topics = $topicStmt->fetchAll();

/*  ADD CATEGORY 🐒 */
if (isset($_POST['add_category'])) {

    $name = trim($_POST['category_name'] ?? '');

    if ($name !== '') {
        $stmt = $db->prepare('INSERT INTO topic_category (category_name) VALUES (?)');
        $stmt->execute([$name]);
    }

    header("Location: controller.php?page=manageTopics");
    exit();
}

/* ADD TOPIC 🐒*/
if (isset($_POST['add_topic'])) {

    $name = trim($_POST['topic_name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);

    if ($name !== '' && $category_id > 0) {
        $stmt = $db->prepare('INSERT INTO topic (category_id, topic_name) VALUES (?, ?)');
        $stmt->execute([$category_id, $name]);
    }

    header("Location: controller.php?page=manageTopics");
    exit();
}

/* DELETE CATEGORY 🐒*/
if (isset($_POST['delete_category'])) {

    $id = (int)($_POST['delete_category_id'] ?? 0);

    if ($id > 0) {
        $stmt = $db->prepare('DELETE FROM topic_category WHERE category_id = ?');
        $stmt->execute([$id]);
    }

    header("Location: controller.php?page=manageTopics");
    exit();
}

/*  DELETE TOPIC 🐒 */
if (isset($_POST['delete_topic'])) {

    $id = (int)($_POST['delete_topic_id'] ?? 0);

    if ($id > 0) {
        $stmt = $db->prepare('DELETE FROM topic WHERE topic_id = ?');
        $stmt->execute([$id]);
    }

    header("Location: controller.php?page=manageTopics");
    exit();
}
?>

<!-- HTML + UI -->

<link rel="stylesheet" href="../styles/manage.css">

<main class="manage-page">



    <div class="manage-content">

        <!--  LEFT: STRUCTURE VIEW -->
        <section class="structure-section">

            <h3>Current Structure</h3>

            <?php foreach ($categories as $c): ?>

                <div class="category-block">

                    <h4><?= htmlspecialchars($c['category_name']) ?></h4>

                    <ul>
                        <?php foreach ($topics as $t): ?>
                            <?php if ($t['category_id'] == $c['category_id']): ?>
                                <li>
                                    <?= htmlspecialchars($t['topic_name']) ?>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>

                </div>

            <?php endforeach; ?>

        </section>

        <!-- RIGHT: CONTROL PANEL -->
        <section class="form-section">

            <!--  ADD TOPIC -->
            <div class="manage-box">

                <h3>Add Topic</h3>

                <form method="POST">

                    <input type="text"
                           name="category_name"
                           placeholder="New topic name"
                           required>

                    <button type="submit"
                            name="add_category"
                            class="btn">
                        Add Topic
                    </button>

                </form>

            </div>

            

            <!-- ADD SUB TOPIC -->
            <div class="manage-box">

                <h3>Add Sub-Topic</h3>

                <form method="POST">

                    <input type="text"
                           name="topic_name"
                           placeholder="New Sub-Topic name"
                           required>

                    <div class="topic-row">

                        <select name="category_id" required>
                            <option value="">Select Category</option>

                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['category_id'] ?>">
                                    <?= htmlspecialchars($c['category_name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                        <button type="submit"
                                name="add_topic"
                                class="btn">
                            Add Sub-Topic
                        </button>

                    </div>

                </form>

            </div>
            <!-- DELETE TOPIC -->
            <div class="manage-box">

                <h3>Delete Topic</h3>

                <form method="POST">

                    <select name="delete_category_id" required>
                        <option value="">Select Category</option>

                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['category_id'] ?>">
                                <?= htmlspecialchars($c['category_name']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <button type="submit"
                            name="delete_category"
                            class="btn delete-btn">
                        Delete Topic
                    </button>

                </form>

            </div>
            <!-- DELETE SUB TOPIC -->
            <div class="manage-box">

                <h3>Delete Sub-Topic</h3>

                <form method="POST">

                    <select name="delete_topic_id" required>
                        <option value="">Select Topic</option>

                        <?php foreach ($topics as $t): ?>
                            <option value="<?= $t['topic_id'] ?>">
                                <?= htmlspecialchars($t['topic_name']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <button type="submit"
                            name="delete_topic"
                            class="btn delete-btn">
                        Delete Sub-Topic
                    </button>

                </form>

            </div>

        </section>

    </div>

</main>

<a href="controller.php?page=index" class="fab">&lt;&lt;</a>
