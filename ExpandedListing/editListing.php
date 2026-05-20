<?php

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

/* UPDATE HANDLER */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = $_POST["title"] ?? "";
    $author = $_POST["author"] ?? "";
    $date = $_POST["date"] ?? "";
    $medium = $_POST["medium"] ?? "";
    $topic = $_POST["topic"] ?? "";
    $abstract = $_POST["abstract"] ?? "";
    $links = $_POST["links"] ?? "";

    $update = $db->prepare('
        UPDATE "Listing"
        SET "title" = ?,
            "author" = ?,
            "date" = ?,
            "medium" = ?,
            "topic" = ?,
            "abstract" = ?,
            "links" = ?
        WHERE "listingID" = ?
    ');

    $update->execute([
        $title,
        $author,
        $date,
        $medium,
        $topic,
        $abstract,
        $links,
        $id
    ]);

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

        <label>Author</label>
        <input type="text" name="author"
               value="<?= htmlspecialchars($listing["author"]) ?>">

        <label>Date</label>
        <input type="text" name="date"
               value="<?= htmlspecialchars($listing["date"]) ?>">

        <label>Medium</label>
        <input type="text" name="medium"
               value="<?= htmlspecialchars($listing["medium"]) ?>">

        <label>Topic</label>
        <input type="text" name="topic"
               value="<?= htmlspecialchars($listing["topic"]) ?>">

        <label>Abstract</label>
        <textarea name="abstract"><?= htmlspecialchars($listing["abstract"]) ?></textarea>

        <label>External Link</label>
        <input type="text" name="links"
               value="<?= htmlspecialchars($listing["links"]) ?>">

        <button type="submit" class="btn edit-save">
            Save Changes
        </button>

    </form>
<a href="controller.php?page=listingInfo&id=<?= $id ?>" class="fab">←</a>
</main>



