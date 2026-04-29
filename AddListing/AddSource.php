<?php
session_start();

<?php include '../view/AddSourceHeader.php'; ?>

<link rel="stylesheet" href="../styles/AddSource.css">
<link rel="stylesheet" href="../styles/main.css">





// Validation for user input
if (!isset($_SESSION['userID'])) {
    die("Not logged in");
}
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

        $thumbnailPath = null;
        $filePath = null;

        //Upload files to superbase function
        $fileName = time() . "_" . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $_FILES['file']['name']);

        function uploadToSupabase($tmpFile, $fileName, $bucket = "uploads") {

            $projectUrl = "https://zdysuvkcmymlwpernryq.supabase.co";

            $fileName = rawurlencode($fileName);

            $uploadUrl = "$projectUrl/storage/v1/object/$bucket/$fileName";

            $ch = curl_init($uploadUrl);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "apikey: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InpkeXN1dmtjbXltbHdwZXJucnlxIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY4ODA4MjMsImV4cCI6MjA5MjQ1NjgyM30.bDlADBNjnNoiTC6L5fjb13pK6YB1uHp5yZqHGIHLOuQ",
                "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InpkeXN1dmtjbXltbHdwZXJucnlxIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY4ODA4MjMsImV4cCI6MjA5MjQ1NjgyM30.bDlADBNjnNoiTC6L5fjb13pK6YB1uHp5yZqHGIHLOuQ",
                "Content-Type: application/octet-stream"
            ]);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($tmpFile));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                echo "UPLOAD ERROR: " . curl_error($ch);
            }

            curl_close($ch);
            return "$projectUrl/storage/v1/object/public/$bucket/$fileName";
        }

        // Thumbnail 
        if (!empty($_FILES['thumbnail']['name'])) {
            $thumbName = time() . "_" . basename($_FILES['thumbnail']['name']);
            $thumbnailPath = uploadToSupabase($_FILES['thumbnail']['tmp_name'], $thumbName);
        }

        // Main file
        if (!empty($_FILES['file']['name'])) {
            $fileName = time() . "_" . basename($_FILES['file']['name']);
            $filePath = uploadToSupabase($_FILES['file']['tmp_name'], $fileName);
        }

        $ch = curl_init("https://zdysuvkcmymlwpernryq.supabase.co/rest/v1/Listing");

        $data = [
            "title" => $_POST['title'],
            "author" => $_POST['author'],
            "date" => $_POST['datePub'],
            "medium" => $_POST['medium'],
            "icon" => $thumbnailPath,
            "file" => $filePath,
            "abstract" => $_POST['abs'] ?? '',
            "links" => $_POST['links'] ?? '',
            "userID" => $_SESSION['userID'],
        ];
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InpkeXN1dmtjbXltbHdwZXJucnlxIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY4ODA4MjMsImV4cCI6MjA5MjQ1NjgyM30.bDlADBNjnNoiTC6L5fjb13pK6YB1uHp5yZqHGIHLOuQ",
            "Authorization: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InpkeXN1dmtjbXltbHdwZXJucnlxIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY4ODA4MjMsImV4cCI6MjA5MjQ1NjgyM30.bDlADBNjnNoiTC6L5fjb13pK6YB1uHp5yZqHGIHLOuQ",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ]);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo "<pre style='color:red'>CURL ERROR: " . curl_error($ch) . "</pre>";
        } else {
            echo "<div style='color:green; font-weight:bold;'>Uploaded Successfully</div>";
        }
        curl_close($ch);
    }
}
?>



<main>
    <body>

        <form action="AddSource.php" method="post" class="input-group" enctype="multipart/form-data">

            <div class="field">
                <label for="title">Project Title:</label>
                <input type="text" id="title" name="title"
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                <span class="error-text"><?= $fieldErrors['title'] ?? '' ?></span>
            </div>

            <div class="field">
                <label for="author">Author Name:</label>
                <input type="text" id="author" name="author"
                       value="<?= htmlspecialchars($_POST['author'] ?? '') ?>">
                <span class="error-text"><?= $fieldErrors['author'] ?? '' ?></span>
            </div>

            <div class="field">
                <label for="datePub">Date Published:</label>
                <input type="date" id="datePub" name="datePub"
                       value="<?= htmlspecialchars($_POST['datePub'] ?? '') ?>">
                <span class="error-text"><?= $fieldErrors['datePub'] ?? '' ?></span>
            </div>

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


            <div class="field">
                <label>Medium:</label>
                <select name="medium">
                    <option value="">Select</option>
                    <option value="paper">Paper</option>
                    <option value="tutorial">Tutorial</option>
                    <option value="presentation">Presentation</option>
                    <option value="video">Video</option>
                    <option value="podcast">Podcast</option>
                </select>
                <span class="error-text"><?= $fieldErrors['medium'] ?? '' ?></span>
            </div>

            <div class="field">
                <label>Abstract:</label>
                <input type="text" name="abs">
            </div>

            <div class="field">
                <label>Links:</label>
                <input type="text" name="links">
            </div>

            <button type="submit">Upload Project</button>

        </form>

    </body>
</main>

<a href="../index.php" class="fab"><<</a>
<script src="../scripts/dragdrop.js"></script>
<?php include '../view/footer.php'; ?>
