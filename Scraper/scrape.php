<?php

header("Content-Type: application/json");

/* =========================
   FETCH HTML
========================= */
function fetchHTML($url)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => "Mozilla/5.0",
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $html = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode([
            "success" => false,
            "message" => curl_error($ch)
        ]);
        exit;
    }

    curl_close($ch);

    return $html;
}

/* =========================
   LOAD PAGE
========================= */
$url = "https://borisk.dreamhosters.com/public_html/";

$html = fetchHTML($url);

if (!$html) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch page"
    ]);
    exit;
}

/* =========================
   PARSE HTML
========================= */
libxml_use_internal_errors(true);

$dom = new DOMDocument();
$dom->loadHTML($html);

libxml_clear_errors();

$xpath = new DOMXPath($dom);

/* =========================
   STEP 1: SEGMENTATION
========================= */

$nodes = $xpath->query("//text()[normalize-space()]");

$blocks = [];
$current = "";
$capturing = false;

foreach ($nodes as $node) {

    $text = trim(
        preg_replace('/\s+/', ' ', $node->nodeValue)
    );

    if (strlen($text) < 3) {
        continue;
    }

    /* =========================
       START CONDITION
    ========================= */
    if (
        preg_match('/(Kovalerchuk|B\.)/', $text) ||
        preg_match('/Tutorial|Proceedings|Springer|IEEE|Book|Conference/i', $text)
    ) {
        $capturing = true;
    }

    if (!$capturing) {
        continue;
    }

    /* =========================
       REMOVE NOISE
    ========================= */
    if (
        preg_match(
            '/cv|email|phone|fax|department|lab|web page|curriculum/i',
            $text
        )
    ) {
        continue;
    }

    $current .= " " . $text;

    /* =========================
       END CONDITION
    ========================= */
    if (
        preg_match('/\b(19|20)\d{2}\b/', $text) &&
        strlen($current) > 80
    ) {
        $blocks[] = trim($current);

        $current = "";
        $capturing = false;
    }
}

/* =========================
   STEP 2: FIELD EXTRACTION
========================= */

$results = [];

foreach ($blocks as $entry) {

    $entry = preg_replace('/\s+/', ' ', $entry);

    /* YEAR */
    preg_match('/(19|20)\d{2}/', $entry, $year);

    /* AUTHORS */
    preg_match(
        '/^([A-Z][^0-9]{5,100}?)(?=Tutorial|Proceedings|Book|Conference|:|Springer|IEEE)/',
        $entry,
        $authors
    );

    /* TITLE */
    preg_match(
        '/(Tutorial|Proceedings|Book|Conference|:)\s*(.{10,150}?)(?=Springer|IEEE|,|$)/',
        $entry,
        $title
    );

    $titleText = trim($title[2] ?? "");
    $authorText = trim($authors[1] ?? "");

    /* FALLBACKS */
    if ($titleText === "" && strlen($entry) > 40) {
        $titleText = substr($entry, 0, 90);
    }

    if ($authorText === "" && strpos($entry, "Kovalerchuk") !== false) {
        $authorText = "B. Kovalerchuk";
    }

    /* MEDIUM */
    $medium = "book";

    if (stripos($entry, "tutorial") !== false) {
        $medium = "tutorial";
    }

    if (stripos($entry, "seminar") !== false) {
        $medium = "seminar";
    }

    if (stripos($entry, "panel") !== false) {
        $medium = "panel";
    }

    /* TOPIC */
    $topic = "AI / Visualization";

    if (stripos($entry, "machine learning") !== false) {
        $topic = "Machine Learning";
    }

    if (stripos($entry, "finance") !== false) {
        $topic = "Finance";
    }

    if (stripos($entry, "visual") !== false) {
        $topic = "Visualization";
    }

    $results[] = [
        "title" => $titleText,
        "author" => $authorText,
        "date" => $year[0] ?? "",
        "medium" => $medium,
        "topic" => $topic
    ];
}

/* =========================
   SUPABASE INSERT DEBUG
========================= */

$SUPABASE_URL = "https://zdysuvkcmymlwpernryq.supabase.co";
$SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InpkeXN1dmtjbXltbHdwZXJucnlxIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY4ODA4MjMsImV4cCI6MjA5MjQ1NjgyM30.bDlADBNjnNoiTC6L5fjb13pK6YB1uHp5yZqHGIHLOuQ";

foreach ($results as $item) {

    if (empty($item["title"])) {
        continue;
    }

    $payload = json_encode([
        "title" => $item["title"],
        "author" => $item["author"],
        "medium" => $item["medium"],
        "topic" => $item["topic"],
        "userID" => 1
    ]);

    echo "<pre>";
    echo "PAYLOAD:\n";
    print_r($payload);

    $ch = curl_init(
        $SUPABASE_URL . "/rest/v1/Listing"
    );

    curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,

    // SSL FIX
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,

    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "apikey: $SUPABASE_KEY",
        "Authorization: Bearer $SUPABASE_KEY",
        "Prefer: return=representation"
    ]
]);

    $response = curl_exec($ch);

    echo "\n\nRESPONSE:\n";
    print_r($response);

    if (curl_errno($ch)) {
        echo "\n\nCURL ERROR:\n";
        echo curl_error($ch);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    echo "\n\nHTTP STATUS:\n";
    echo $status;

    echo "</pre>";

    curl_close($ch);
}

/* =========================
   FINAL JSON OUTPUT
========================= */

echo json_encode([
    "success" => true,
    "count" => count($results),
    "results" => $results
]);