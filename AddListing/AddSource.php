<?php


// Validation for user input
if (!isset($_SESSION['userID'])) {
    die("Not logged in");
}
$fieldErrors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $thumbnailPath = null;
    $filePath = null;


    if (empty($_POST['title'])) {
        $fieldErrors['title'] = "Title is required";
    }


    if (empty($_POST['author'])) {
        $fieldErrors['author'] = "Author is required";
    }

    if (empty($_POST['year'])) {
        $fieldErrors['datePub'] = "Year is required";
    }

    if (empty($_POST['medium'])) {
        $fieldErrors['medium'] = "Please select a medium";
    }



    if (empty($_FILES['file']['name']) && empty($_POST['externalLink'])) {
        $fieldErrors['file'] = "Please upload a file or provide a link";

        if (!empty($month) && ($month < 1 || $month > 12)) {
            $fieldErrors['datePub'] = "Month must be 1-12";
        }

        if (!empty($day) && ($day < 1 || $day > 31)) {
            $fieldErrors['datePub'] = "Day must be 1-31";
        }
    }


    // Thumbnail 
    if (
            !empty($_FILES['thumbnail']['tmp_name']) &&
            $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK
    ) {
        $originalName = basename($_FILES['thumbnail']['name']);
        $safeName = preg_replace('/[^A-Za-z0-9.\-_]/', '_', $originalName);
        $thumbName = time() . "_" . $safeName;

        $thumbnailPath = uploadToSupabase(
                $_FILES['thumbnail']['tmp_name'],
                $thumbName
        );
    }

    $abstract = $_POST['abs'] ?? '';
    $citations = $_POST['citations'] ?? '';
    $sourceLink = !empty($_POST['externalLink']) ? trim($_POST['externalLink']) : null;

    // Main file attachment

    if (!empty($_FILES['file']['name'])) {

        $originalName = basename($_FILES['file']['name']);
        $safeName = preg_replace('/[^A-Za-z0-9.\-_]/', '_', $originalName);
        $fileName = time() . "_" . $safeName;

        $filePath = uploadToSupabase(
                $_FILES['file']['tmp_name'],
                $fileName
        );

        // Uploaded temp file
        $uploadedTmpFile = $_FILES['file']['tmp_name'];

        // Original uploaded filename
        $originalFileName = $_FILES['file']['name'];

        // Python executable
        $python = getenv("PYTHON_BIN") ?: (PHP_OS_FAMILY === "Windows" ? "python" : "python3");

        // Python script path
        $script = realpath(__DIR__ . '/../scripts/scraping/scraper.py');

        // Build command
        $command = $python . ' ' .
                escapeshellarg($script) . ' ' .
                escapeshellarg($uploadedTmpFile) . ' ' .
                escapeshellarg($originalFileName) .
                ' 2>&1';

        // Execute Python
        $output = shell_exec($command);

        $scrapedData = json_decode($output, true);

        //$filePath = null;

        if ($scrapedData) {
            if (empty($abstract) && !empty($scrapedData['abstract'])) {
                $abstract = $scrapedData['abstract'];
            }

            if (empty($citations) && !empty($scrapedData['citations'])) {
                $citations = implode("\n\n", $scrapedData['citations']);
            }
        }

        echo "<pre>";
        echo htmlspecialchars($output);
        echo "</pre>";
    }

    $ch = curl_init("https://zdysuvkcmymlwpernryq.supabase.co/rest/v1/Listing");

    $year = $_POST['year'] ?? null;
    $month = $_POST['month'] ?? null;
    $day = $_POST['day'] ?? null;

    if (empty($year)) {
        $fieldErrors['datePub'] = "Year is required";
        $datePub = null;
    } else {

        // Make sure it has 4 digits 
        $year = str_pad($year, 4, "0", STR_PAD_LEFT);

        // Make sure it has padding
        if (!empty($month)) {
            $month = str_pad($month, 2, "0", STR_PAD_LEFT);
        }

        // Make sure it has padding
        if (!empty($day)) {
            $day = str_pad($day, 2, "0", STR_PAD_LEFT);
        }

        // Building the final string
        if (!empty($month) && !empty($day)) {
            $datePub = "$year-$month-$day";
        } elseif (!empty($month)) {
            $datePub = "$year-$month";
        } else {
            $datePub = "$year";
        }
    }

    $data = [
        "title" => $_POST['title'],
        "author" => $_POST['author'],
        "date" => $datePub,
        "medium" => $_POST['medium'],
        "icon" => $thumbnailPath,
        "file" => $filePath,
        "links" => $sourceLink,
        "abstract" => $abstract,
        "citations" => $citations,
        "userID" => $_SESSION['userID'],
    ];
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o",
        "Authorization: sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ]);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    $insertedRows = json_decode($response, true);
    $primaryListingID = $insertedRows[0]['listingID'] ?? null;

    if (curl_errno($ch)) {
        echo "<pre style='color:red'>CURL ERROR: " . curl_error($ch) . "</pre>";
    } else {
        echo "<div style='color:green; font-weight:bold;'>Uploaded Successfully</div>";
    }
    curl_close($ch);

    // INSERT RELATED CONTENT
    // Related file uploads
    if (!empty($_FILES['relatedFiles']['name'])) {

        foreach ($_FILES['relatedFiles']['name'] as $i => $name) {

            if (
                    !empty($name) &&
                    $_FILES['relatedFiles']['error'][$i] === UPLOAD_ERR_OK
            ) {

                $originalName = basename($name);

                $title = pathinfo($originalName, PATHINFO_FILENAME);

                $safeName = preg_replace(
                        '/[^A-Za-z0-9.\-_]/',
                        '_',
                        $originalName
                );

                $relatedFileName = time() . "_" . $safeName;

                $relatedFilePath = uploadToSupabase(
                        $_FILES['relatedFiles']['tmp_name'][$i],
                        $relatedFileName
                );

                insertRelatedListing(
                        $primaryListingID,
                        $_POST['topic'] ?? null,
                        "file",
                        $title,
                        $relatedFilePath,
                        null
                );
            }
        }
    }
}

// Related hyperlinks
if (!empty($_POST['relatedLinks'])) {
    foreach ($_POST['relatedLinks'] as $link) {
        $link = trim($link);

        if (!empty($link)) {
            $title = getPageTitle($link);

            insertRelatedListing(
                $primaryListingID,
                $_POST['topic'] ?? null,
                "link",
                $title,
                null,
                $link
            );
        }
    }
}

// Related existing listings
if (!empty($_POST['relatedListingIDs'])) {
    foreach ($_POST['relatedListingIDs'] as $relatedID) {
        $relatedID = trim($relatedID);

        if (!empty($relatedID)) {
            $title = getListingTitleByID($relatedID);

            insertRelatedListing($primaryListingID, $_POST['topic'] ?? null, $title, "listing", null, $relatedID);
        }
    }
}

// PHP FUNCTIONS:
function getPageTitle($url) {
    $html = @file_get_contents($url);

    if (!$html) {
        return $url;
    }

    if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
        return trim(html_entity_decode($matches[1]));
    }

    return $url;
}

function getListingTitleByID($id) {
    $projectUrl = "https://zdysuvkcmymlwpernryq.supabase.co";
    $key = "sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o";

    $url = $projectUrl . "/rest/v1/Listing"
            . "?select=title"
            . "&listingID=eq." . urlencode($id)
            . "&limit=1";

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $key",
        "Authorization: Bearer $key",
        "Content-Type: application/json"
    ]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $rows = json_decode($response, true);

    return $rows[0]['title'] ?? "Related Listing #" . $id;
}

function uploadToSupabase($tmpFile, $fileName, $bucket = "uploads") {

    $projectUrl = "https://zdysuvkcmymlwpernryq.supabase.co";

    $fileName = rawurlencode($fileName);

    $uploadUrl = "$projectUrl/storage/v1/object/$bucket/$fileName";

    $ch = curl_init($uploadUrl);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o",
        "Authorization: Bearer sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o",
        "Content-Type: application/octet-stream"
    ]);

    if (empty($tmpFile) || !file_exists($tmpFile)) {
        die("Invalid upload file path");
    }

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

function insertRelatedListing($listingID, $topic, $title, $type, $file = null, $link = null) {
    $ch = curl_init("https://zdysuvkcmymlwpernryq.supabase.co/rest/v1/RelatedListing");

    $data = [
        "listingID" => (int) $listingID,
        "topic" => $topic,
        "title" => $title,
        "type" => $type,
        "file" => $file,
        "links" => $link
    ];

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o",
        "Authorization: Bearer sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ]);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    /*
     * DEBUG
      echo "<pre>";
      echo "RelatedListing HTTP Code: " . $httpCode . "\n";
      echo "Payload:\n";
      print_r($data);
      echo "Response:\n";
      echo htmlspecialchars($response);
      echo "</pre>";
     */
 
}else{
        echo "<pre style='color:red'>Related insert CURL error: " . curl_error($ch) . "</pre>";
    }

    curl_close($ch);
}
?>
<?php include '../view/AddSourceHeader.php'; ?>

<!-- HTML -->
<link rel="stylesheet" href="../styles/AddSource.css">
<link rel="stylesheet" href="../styles/main.css">



<main>
    <body>

<form action="controller.php?page=addSource" method="post" enctype="multipart/form-data">
            <div class="field">
                <label for="title">Title:</label>

                <input type="text" id="title" name="title"
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                <span class="error-text"><?= $fieldErrors['title'] ?? '' ?></span>
            </div>

            <div class="field">

                <label for="author">Author(s):</label>

                <input type="text" id="author" name="author"
                       value="<?= htmlspecialchars($_POST['author'] ?? '') ?>">
                <span class="error-text"><?= $fieldErrors['author'] ?? '' ?></span>
            </div>

            <div class="field">

                <label>Date Published:</label>

                <div class="date-row">
                    <input type="number" name="year" placeholder="Year" min="1000" max="9999">
                    <input type="number" name="month" placeholder="Month" min="1" max="12">
                    <input type="number" name="day" placeholder="Day" min="1" max="31">
                    <span class="error-text"><?= $fieldErrors['datePub'] ?? '' ?></span>
                </div>


            </div>

            <div class="drop-row">


                <!-- ICON COLUMN -->
                <div class="drop-group">

                    <label>Icon:</label>

                    <div id="drop-zone-thumb" class="drop-area">

                        <span class="drop-message">
                            Drag & Drop (jpeg/png)
                            <br>
                            Icon
                        </span>

                        <button type="button" class="remove-file">✕</button>

                        <input
                            type="file"
                            name="thumbnail"
                            accept="image/*"
                            hidden
                            >

                    </div>

                </div>


                <!-- FILE COLUMN -->
                <div class="file-column">

                    <label>File:</label>

                    <div id="drop-zone-icon" class="drop-area">

                        <span class="drop-message">
                            Drag & Drop (pdf/docx/mp3/mp4/wav)
                            <br>
                            File
                        </span>

                        <button type="button" class="remove-file">✕</button>

                        <input
                            type="file"
                            name="file"
                            accept=".pdf,.docx,.mp3,.mp4,.wav"
                            hidden
                            >

                        <?php if (isset($fieldErrors['file'])): ?>
                            <span class="error-text">
                                <?= $fieldErrors['file'] ?>
                            </span>
                        <?php endif; ?>

                    </div>

                    <!-- LINK BELOW FILE -->
                    <div class="link-field">

                        <label for="externalLink">
                            OR External Link:
                        </label>

                        <input
                            type="url"
                            id="externalLink"
                            name="externalLink"
                            placeholder="https://example.com/research-paper"
                            value="<?= htmlspecialchars($_POST['externalLink'] ?? '') ?>"
                            >

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

                <label>Topic:</label>
                <select name="topic">
                    <option value="">Select</option>
                    <option value="AI/Machine Learning">AI/Machine Learning</option>
                    <option value="Visual Knowledge Discovery">Visual Knowledge Discovery</option>
                </select>
                <span class="error-text"><?= $fieldErrors['topic'] ?? '' ?></span>
            </div>

            <div class="field">


                <label>Abstract:</label>
                <input type="text" name="abs">
            </div>

            <div class="field">
                <label>Citations:</label>

                <input type="text" name="citations">
            </div>

            <br> </br>

            <div id="relatedSources">
                <h3>Related Sources</h3>

                <div class="related-category">
                    <div id="relatedFileList">
                        <div class="related-upload">
                            <label>Related Attachment:</label>
                            <input type="file" name="relatedFiles[]">
                        </div>
                    </div>

                    <button type="button" onclick="addRelatedFile()">+</button>
                </div>
                <br> </br>

                <div id="relatedLinkList">
                    <div class="related-link">
                        <label>Related Link:</label>
                        <input type="url" name="relatedLinks[]" placeholder="https://example.com">
                    </div>
                </div>
                <button type="button" onclick="addRelatedLink()">+</button>
                <br> </br>

                <div id="relatedListingList">
                    <div class="related-listing">
                        <label>Related Listing:</label>

                        <div class="listing-search-wrap">
                            <input type="text" class="listing-search" placeholder="Search listing by title">
                            <input type="hidden" name="relatedListingIDs[]">
                            <div class="listing-results"></div>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="addRelatedListing()">+</button>
                <br>

            </div>

            <!-- Inline script for adding related sources -->
            <script>
                function addRelatedFile() {
                    document.getElementById("relatedFileList").insertAdjacentHTML("beforeend", `
        <div class="related-upload">
            <label>Related Attachment:</label>
            <input type="file" name="relatedFiles[]">
        </div>
    `);
                }

                function addRelatedLink() {
                    document.getElementById("relatedLinkList").insertAdjacentHTML("beforeend", `
                     <div class="related-link">
                         <label>Related Link:</label>
                         <input type="url" name="relatedLinks[]" placeholder="https://example.com">
                     </div>
                             `);
                }

                function addRelatedListing() {
                alert("addRelatedListing clicked");    
                document.getElementById("relatedListingList").insertAdjacentHTML("beforeend", `
        <div class="related-listing">
            <label>Related Listing:</label>

            <div class="listing-search-wrap">
                <input
                    type="text"
                    class="listing-search"
                    placeholder="Search listing by title"
                >

                <input type="hidden" name="relatedListingIDs[]">

                <div class="listing-results"></div>
            </div>
        </div>
    `);
                }


                document.addEventListener("input", async function (e) {
                    if (!e.target.classList.contains("listing-search"))
                        return;

                    const searchBox = e.target;
                    const query = searchBox.value.trim();
                    const wrapper = searchBox.closest(".listing-search-wrap") || searchBox.parentElement;
                    const hiddenInput = wrapper.querySelector('input[type="hidden"]');
                    const resultsBox = wrapper.querySelector(".listing-results");

                    hiddenInput.value = "";
                    resultsBox.innerHTML = "";

                    if (query.length < 2)
                        return;

                    const response = await fetch(`SearchListings.php?q=${encodeURIComponent(query)}`);
                    const data = await response.json();
                    console.log(data);

                    data.forEach(listing => {
                        const item = document.createElement("div");
                        item.className = "listing-result";
                        item.textContent = `${listing.title} by ${listing.author}`;
                        item.onclick = function () {
                            searchBox.value = listing.title;
                            hiddenInput.value = listing.listingID;
                            resultsBox.innerHTML = "";
                        };
                        resultsBox.appendChild(item);
                    });
                });
            </script>

            <br>

            <button type="submit">Upload Project</button>

        </form>

    </body>
</main>


<a href="controller.php?page=index" class="fab"><<</a>
<script src="scripts/dragdrop.js"></script>


