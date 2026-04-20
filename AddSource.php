<?php
require_once 'model/listings.php';

$fieldErrors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    if (empty($_POST['title'])) {
        $fieldErrors['title'] = "Title is required";
    }

    if (empty($_POST['author'])) {
        $fieldErrors['author'] = "Author is required";
    }

    if (empty($_POST['datePub'])) {
        $fieldErrors['datePub'] = "Date is required";
    }

    if (empty($_POST['medium'])) {
        $fieldErrors['medium'] = "Please select a medium";
    }

    if (empty($_FILES['file']['name'])) {
        $fieldErrors['file'] = "Please upload a file";
    }

  
    if (empty($fieldErrors)) {

        $uploadDir = "uploads/";

        //thumbnail
        $thumbnailPath = "";
        if (!empty($_FILES['thumbnail']['name'])) {
            $thumbnailPath = $uploadDir . time() . "_" . basename($_FILES['thumbnail']['name']);
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnailPath);
        }

        //file
        $filePath = "";
        if (!empty($_FILES['file']['name'])) {
            $filePath = $uploadDir . time() . "_" . basename($_FILES['file']['name']);
            move_uploaded_file($_FILES['file']['tmp_name'], $filePath);
        }

        addListing(
            $_POST['title'],
            $_POST['author'],
            $_POST['datePub'],
            $_POST['medium'],
            $thumbnailPath,
            $filePath,
            $_POST['abs'] ?? '',
            $_POST['links'] ?? ''
        );

        echo "<p style='color:green;'>Saved successfully!</p>";
    }
}
?>

<?php include 'view/AddSourceHeader.php'; ?>

<main>

    <form action="AddSource.php" method="post" class="input-group" enctype="multipart/form-data">

        <!-- TITLE -->
        <div class="field">
            <label for="title">Project Title:</label>

            <div class="field-inline">
                <input type="text" id="title" name="title"
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                       placeholder="Enter title...">

                <?php if (isset($fieldErrors['title'])): ?>
                    <span class="error-text"><?= $fieldErrors['title'] ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- AUTHOR -->
        <div class="field">
            <label for="author">Author Name:</label>

            <div class="field-inline">
                <input type="text" id="author" name="author"
                       value="<?= htmlspecialchars($_POST['author'] ?? '') ?>"
                       placeholder="e.g. Mike Smith">

                <?php if (isset($fieldErrors['author'])): ?>
                    <span class="error-text"><?= $fieldErrors['author'] ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- DATE -->
        <div class="field">
            <label for="datePub">Date Published:</label>

            <div class="field-inline">
                <input type="date" id="datePub" name="datePub"
                       value="<?= htmlspecialchars($_POST['datePub'] ?? '') ?>">

                <?php if (isset($fieldErrors['datePub'])): ?>
                    <span class="error-text"><?= $fieldErrors['datePub'] ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- ICON UPLOAD -->
        <div class="drop-row">

            <div class="drop-group">
                <label>Icon:</label>
                <div id="drop-zone-thumb" class="drop-area">
                    <span class="drop-message">Drag & Drop (jpeg/png) <br> Icon</span>
                    <button type="button" class="remove-file">✕</button>
                    <input type="file" name="thumbnail" accept="image/*" hidden>
                </div>
            </div>

            <div class="drop-group">
                <label>File:</label>
                <div id="drop-zone-icon" class="drop-area">
                    <span class="drop-message">Drag & Drop (pdf/mp3/mp4/wav) <br> File</span>
                    <button type="button" class="remove-file">✕</button>
                    <input type="file" name="file" accept=".pdf,.mp3,.mp4,.wav" hidden>
                    <?php if (isset($fieldErrors['file'])): ?>
                        <span class="error-text"><?= $fieldErrors['file'] ?></span>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- MEDIUM -->
        <div class="field">
            <label for="medium">Select Medium:</label>

            <select id="medium" name="medium">
                <option value="">Select</option>
                <option value="paper" <?= (($_POST['medium'] ?? '') === 'paper') ? 'selected' : '' ?>>
                    Paper
                </option>
                <option value="tutorial" <?= (($_POST['medium'] ?? '') === 'tutorial') ? 'selected' : '' ?>>
                    Tutorial
                </option>
                <option value="presentation" <?= (($_POST['medium'] ?? '') === 'presentation') ? 'selected' : '' ?>>
                    Presentation
                </option>
                <option value="video" <?= (($_POST['medium'] ?? '') === 'video') ? 'selected' : '' ?>>
                    Video
                </option>
                <option value="podcast" <?= (($_POST['medium'] ?? '') === 'podcast') ? 'selected' : '' ?>>
                    Podcast
                </option>
            </select>

            <?php if (isset($fieldErrors['medium'])): ?>
                <span class="error-text"><?= $fieldErrors['medium'] ?></span>
            <?php endif; ?>
        </div>

        <!-- ABSTRACT -->
        <div class="field">
            <label for="abs">Abstract:</label>

            <div class="field-inline">
                <input type="text" id="abs" name="abs"
                       value="<?= htmlspecialchars($_POST['abs'] ?? '') ?>"
                       placeholder="Enter Text">

            </div>
        </div>

        <!-- LINKS -->
        <div class="field">
            <label for="links">Links:</label>

            <div class="field-inline">
                <input type="text" id="links" name="links"
                       value="<?= htmlspecialchars($_POST['links'] ?? '') ?>"
                       placeholder="Enter Text">

            </div>
        </div>

        <button type="submit" class="submit-btn">Upload Project</button>

    </form>

    <a href="index.php" class="fab">&lt;&lt;</a>

</main>

<script src="scripts/date.js"></script>
<script src="scripts/checkmark.js"></script>
<script src="scripts/dragdrop.js"></script>

<?php include 'view/footer.php'; ?>