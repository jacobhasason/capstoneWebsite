<?php
require "DBConnect/db.php";

/* AUTH */
if (!isset($_SESSION['userID'])) {
    die("Not logged in");
}

$fieldErrors = [];

/* FETCH TOPICS */
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

// This reads the hidden field populated by JSON.stringify() in JavaScript
$topicArray = json_decode($_POST['topic'] ?? '[]', true);
if (!is_array($topicArray)) {
    $topicArray = [];
}

/* POST PROCESSING */
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
    }

    /* Thumbnail Processing */
    if (!empty($_FILES['thumbnail']['tmp_name']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $originalName = basename($_FILES['thumbnail']['name']);
        $safeName = preg_replace('/[^A-Za-z0-9.\-_]/', '_', $originalName);
        $thumbName = time() . "_" . $safeName;
        $thumbnailPath = uploadToSupabase($_FILES['thumbnail']['tmp_name'], $thumbName);
    }

    $abstract = $_POST['abs'] ?? '';
    $citations = $_POST['citations'] ?? '';
    $sourceLink = !empty($_POST['externalLink']) ? trim($_POST['externalLink']) : null;

    /* Main File Upload */
    if (!empty($_FILES['file']['name'])) {
        $originalName = basename($_FILES['file']['name']);
        $safeName = preg_replace('/[^A-Za-z0-9.\-_]/', '_', $originalName);
        $fileName = time() . "_" . $safeName;
        $filePath = uploadToSupabase($_FILES['file']['tmp_name'], $fileName);
    }

    /* Date Builder */
    $year = $_POST['year'] ?? null;
    $month = $_POST['month'] ?? null;
    $day = $_POST['day'] ?? null;

    if ($year) {
        $year = str_pad($year, 4, "0", STR_PAD_LEFT);
        if ($month)
            $month = str_pad($month, 2, "0", STR_PAD_LEFT);
        if ($day)
            $day = str_pad($day, 2, "0", STR_PAD_LEFT);
        $datePub = ($month && $day) ? "$year-$month-$day" : ($month ? "$year-$month" : "$year");
    } else {
        $datePub = null;
    }

    if (empty($fieldErrors)) {
        /* INSERT LISTING */
        $ch = curl_init("https://zdysuvkcmymlwpernryq.supabase.co/rest/v1/Listing");
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

        curl_setopt_array($ch, [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                "apikey: sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o",
                "Authorization: Bearer sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o",
                "Content-Type: application/json",
                "Prefer: return=representation"
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true
        ]);

        $response = curl_exec($ch);
        $insertedRows = json_decode($response, true);
        $primaryListingID = $insertedRows[0]['listingID'] ?? null;
        curl_close($ch);

        /* RELATION PLACEMENT: LISTING TOPICS */
        if ($primaryListingID && !empty($topicArray)) {
            $stmt = $db->prepare("
                INSERT INTO \"ListingTopic\" (\"listingID\", topic_id)
                VALUES (?, ?)
            ");
            foreach ($topicArray as $topicId) {
                if (!is_numeric($topicId))
                    continue;
                $stmt->execute([$primaryListingID, (int) $topicId]);
            }
        }

        /* Pass first topic ID as context fallback for Related Items table if necessary */
        $topicContext = !empty($topicArray) ? (int) $topicArray[0] : null;

        /* RELATED FILES */
        if ($primaryListingID && !empty($_FILES['relatedFiles']['name'])) {
            foreach ($_FILES['relatedFiles']['name'] as $i => $name) {
                if (!empty($name) && $_FILES['relatedFiles']['error'][$i] === UPLOAD_ERR_OK) {
                    $originalName = basename($name);
                    $title = pathinfo($originalName, PATHINFO_FILENAME);
                    $safeName = preg_replace('/[^A-Za-z0-9.\-_]/', '_', $originalName);
                    $relatedFileName = time() . "_" . $safeName;

                    $relatedFilePath = uploadToSupabase($_FILES['relatedFiles']['tmp_name'][$i], $relatedFileName);

                    // Fixed Parameters order: listingID, topic, title, type, file, link
                    insertRelatedListing($primaryListingID, $topicContext, $title, "file", $relatedFilePath, null);
                }
            }
        }

        /* RELATED LINKS */
        if ($primaryListingID && !empty($_POST['relatedLinks'])) {
            foreach ($_POST['relatedLinks'] as $link) {
                $link = trim($link);
                if (!empty($link)) {
                    $title = getPageTitle($link);
                    // Fixed Parameters order: listingID, topic, title, type, file, link
                    insertRelatedListing($primaryListingID, $topicContext, $title, "link", null, $link);
                }
            }
        }

        /* RELATED LISTINGS - BIDIRECTIONAL */
        if ($primaryListingID && !empty($_POST['relatedListingIDs'])) {
            foreach ($_POST['relatedListingIDs'] as $relatedID) {
                $relatedID = trim($relatedID);

                if (!empty($relatedID) && (int) $relatedID !== (int) $primaryListingID) {

                    // Title of the existing listing selected in the dropdown
                    $relatedTitle = getListingTitleByID($relatedID);

                    // Title of the newly created primary listing
                    $primaryTitle = $_POST['title'] ?? ("Related Listing #" . $primaryListingID);

                    // Direction 1: primary -> related
                    insertRelatedListing(
                            $primaryListingID,
                            $topicContext,
                            $relatedTitle,
                            "listing",
                            null,
                            (int) $relatedID
                    );

                    // Direction 2: related -> primary
                    insertRelatedListing(
                            (int) $relatedID,
                            $topicContext,
                            $primaryTitle,
                            "listing",
                            null,
                            $primaryListingID
                    );
                }
            }
        }

        // Redirect upon successful upload structure assignment
        header("Location: controller.php?page=index");
        exit;
    }
}

/* API CORES AND HELPER FUNCTIONS */

function getPageTitle($url) {
    $html = @file_get_contents($url);
    if (!$html)
        return $url;
    if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
        return trim(html_entity_decode($matches[1]));
    }
    return $url;
}

function getListingTitleByID($id) {
    // SECURITY PATCH: Force conversion to integer to eliminate parameter injection vectors and send JOHN PORK TO HACKERS HOUSE
    $id = (int) $id;

    $projectUrl = "https://zdysuvkcmymlwpernryq.supabase.co";
    $key = "sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o";

    $url = $projectUrl . "/rest/v1/Listing?select=title&listingID=eq." . urlencode($id) . "&limit=1";

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
    curl_setopt_array($ch, [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => [
            "apikey: sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o",
            "Authorization: Bearer sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o",
            "Content-Type: application/octet-stream"
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => file_get_contents($tmpFile),
        CURLOPT_RETURNTRANSFER => true
    ]);

    curl_exec($ch);
    curl_close($ch);
    return "$projectUrl/storage/v1/object/public/$bucket/$fileName";
}

function insertRelatedListing($listingID, $topic, $title, $type, $file = null, $link = null) {
    $ch = curl_init("https://zdysuvkcmymlwpernryq.supabase.co/rest/v1/RelatedListing");

    $data = [
        "listingID" => (int) $listingID,
        "topic" => $topic !== null ? (int) $topic : null,
        "title" => $title,
        "type" => $type,
        "file" => $file,
        "links" => $link
    ];

    curl_setopt_array($ch, [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => [
            "apikey: sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o",
            "Authorization: Bearer sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => true
    ]);

    curl_exec($ch);
    curl_close($ch);
}
?>

<main>
    <form action="controller.php?page=AddSource" method="post" class="input-group" enctype="multipart/form-data">

        <div class="field">
            <label for="title">Title:</label>
            <textarea id="title" name="title" class="textarea-input-style"><?= htmlspecialchars($_POST['title'] ?? '') ?></textarea>
            <span class="error-text"><?= $fieldErrors['title'] ?? '' ?></span>
        </div>

        <div class="field">
            <label for="author">Author(s):</label>
            <textarea id="author" name="author" class="textarea-input-style"><?= htmlspecialchars($_POST['author'] ?? '') ?></textarea>
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
            <div class="drop-group">
                <label>Icon:</label>
                <div id="drop-zone-thumb" class="drop-area">
                    <span class="drop-message">Drag & Drop (jpeg/png)<br>Icon</span>
                    <button type="button" class="remove-file">✕</button>
                    <input type="file" name="thumbnail" accept="image/*" hidden>
                </div>
            </div>

            <div class="file-column">
                <label>File:</label>
                <div id="drop-zone-icon" class="drop-area">
                    <span class="drop-message">Drag & Drop (pdf/docx/mp3/mp4/wav)<br>File</span>
                    <button type="button" class="remove-file">✕</button>
                    <input type="file" name="file" accept=".pdf,.docx,.mp3,.mp4,.wav" hidden>
                </div>

                <div class="link-field">
                    <label for="externalLink">OR External Link:</label>
                    <input type="url" id="externalLink" name="externalLink" value="<?= htmlspecialchars($_POST['externalLink'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="field">
            <label>Medium:</label>
            <select name="medium">
                <option value="">Select</option>
                <option value="article">Article</option>
                <option value="book">Book</option>
                <option value="tutorial">Tutorial</option>
                <option value="presentation">Presentation</option>
                <option value="video">Video</option>
                <option value="podcast">Podcast</option>
                <option value="software">Software</option>
            </select>
            <span class="error-text"><?= $fieldErrors['medium'] ?? '' ?></span>
        </div>

        <div class="field">
            <label>Topics:</label>
            <input type="hidden" name="topic" id="topicHidden">

            <div class="topic-tree">
                <ul>
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <button type="button" class="tree-toggle">
                                <span class="arrow">▶</span>
                                <span class="topic-category-header">
                                    <?= htmlspecialchars($category['category_name']) ?>
                                </span>
                            </button>

                            <ul class="hidden">
                                <?php foreach ($topics as $topic): ?>
                                    <?php if ($topic['category_id'] == $category['category_id']): ?>
                                        <li>
                                            <label>
                                                <input type="checkbox" class="topic-box real-topic" value="<?= htmlspecialchars($topic['topic_id']) ?>">
                                                <?= htmlspecialchars($topic['topic_name']) ?>
                                            </label>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="field field-top-align">
            <label for="abs">Abstract:</label>
            <textarea id="abs" name="abs" class="textarea-field"><?= htmlspecialchars($_POST['abs'] ?? '') ?></textarea>
        </div>

        <div class="field field-top-align">
            <label for="citations">Citations:</label>
            <textarea id="citations" name="citations" class="textarea-field"><?= htmlspecialchars($_POST['citations'] ?? '') ?></textarea>
        </div>

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
            <br>

            <div class="related-category">
                <div id="relatedLinkList">
                    <div class="related-link">
                        <label>Related Link:</label>
                        <input type="url" name="relatedLinks[]" placeholder="https://example.com">
                    </div>
                </div>
                <button type="button" onclick="addRelatedLink()">+</button>
            </div>
            <br>

            <div class="related-category">
                <div id="relatedListingList">
                    <div class="related-listing">
                        <label>Related Listing:</label>
                        <select name="relatedListingIDs[]" class="related-listing-select">
                            <option value="">Select a topic first</option>
                        </select>
                    </div>
                </div>
                <button type="button" onclick="addRelatedListing()">+</button>
            </div>
            <br>
        </div>

        <button type="submit">Upload Project</button>
    </form>

    <a href="controller.php?page=index" class="fab">&lt;&lt;</a>

    <script src="scripts/dragdrop.js"></script>
    <script src="scripts/relatedListings.js"></script>

    <script>
                    /* 1. Toggle tree collapse functionality */
                    document.querySelectorAll(".tree-toggle").forEach(btn => {
                        btn.addEventListener("click", () => {
                            const ul = btn.parentElement.querySelector("ul");
                            if (!ul)
                                return;
                            ul.classList.toggle("hidden");
                            const isOpen = !ul.classList.contains("hidden");
                            btn.querySelector(".arrow").textContent = isOpen ? "▼" : "▶";
                        });
                    });

                    /* 2. Synchronize selected topic IDs to hidden field payload string */
                    function syncTopics() {
                        const checkedTopics = [
                            ...document.querySelectorAll(".real-topic:checked")
                        ].map(cb => cb.value);

                        const hiddenInput = document.getElementById("topicHidden");
                        if (hiddenInput) {
                            hiddenInput.value = JSON.stringify(checkedTopics);
                        }

                        if (typeof updateRelatedListings === "function") {
                            updateRelatedListings();
                        }
                    }

                    /* 3. Global window hooks to fix the inline HTML onclick attributes */
                    window.addRelatedFile = function () {
                        const container = document.getElementById("relatedFileList");
                        const div = document.createElement("div");
                        div.className = "related-upload";
                        div.innerHTML = `
                <label>Related Attachment:</label>
                <input type="file" name="relatedFiles[]">
                <button type="button" onclick="this.parentElement.remove()">✕</button>
            `;
                        container.appendChild(div);
                    };

                    window.addRelatedLink = function () {
                        const container = document.getElementById("relatedLinkList");
                        const div = document.createElement("div");
                        div.className = "related-link";
                        div.innerHTML = `
                <label>Related Link:</label>
                <input type="url" name="relatedLinks[]" placeholder="https://example.com">
                <button type="button" onclick="this.parentElement.remove()">✕</button>
            `;
                        container.appendChild(div);
                    };

                    window.addRelatedListing = function () {
                        const container = document.getElementById("relatedListingList");
                        const div = document.createElement("div");
                        div.className = "related-listing";
                        div.innerHTML = `
                <label>Related Listing:</label>
                <select name="relatedListingIDs[]" class="related-listing-select">
                    <option value="">Select Related Listing</option>
                </select>
                <button type="button" onclick="this.parentElement.remove()">✕</button>
            `;
                        container.appendChild(div);
                        syncTopics();
                    };

                    /* 4. Event Listeners for topic tree changes */
                    document.addEventListener("change", function (e) {
                        if (e.target.classList.contains("real-topic") || e.target.classList.contains("topic-box")) {
                            syncTopics();
                        }
                    });

                    /* 5. Initialize state evaluation on startup */
                    window.addEventListener("DOMContentLoaded", () => {
                        syncTopics();
                    });
    </script>
</main>