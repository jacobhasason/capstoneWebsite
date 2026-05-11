<?php

require_once __DIR__ . '/../Model/Listing.php';

function fetchHTML($url) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");

    // 🔥 FIX: SSL issue (LOCAL DEV ONLY)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $html = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "CURL ERROR: " . curl_error($ch) . PHP_EOL;
    }

    curl_close($ch);

    echo "HTML LENGTH: " . strlen($html) . PHP_EOL;

    return $html;
}

function scrapeSite($url) {

    $html = fetchHTML($url);

    if (!$html) {
        echo "NO HTML RETURNED" . PHP_EOL;
        return [];
    }

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    $items = $xpath->query("//article");

    echo "ARTICLES FOUND: " . $items->length . PHP_EOL;

    $results = [];

    foreach ($items as $item) {

        $titleNode = $xpath->query(".//h1 | .//h2", $item)->item(0);
        $authorNode = $xpath->query(".//*[contains(@class,'author')]", $item)->item(0);
        $dateNode = $xpath->query(".//time", $item)->item(0);

        $title = $titleNode ? trim($titleNode->textContent) : null;
        $author = $authorNode ? trim($authorNode->textContent) : null;
        $date = $dateNode ? $dateNode->getAttribute("datetime") : null;

        echo "TITLE: " . ($title ?? "NULL") . PHP_EOL;

        if ($title) {
            $results[] = [
                "title" => $title,
                "author" => $author,
                "date" => $date
            ];
        }
    }

    return $results;
}

// RUN SCRAPER
$url = "https://scholar.google.com/citations?user=k12ONA8AAAAJ&hl=en&oi=ao";

$data = scrapeSite($url);

echo "FINAL DATA:\n";
var_dump($data);

// INSERT INTO SUPABASE
foreach ($data as $item) {

    echo "INSERTING: " . $item['title'] . PHP_EOL;

    addListing(
        $item['title'],
        $item['author'],
        $item['date'],
        null,
        "article",
        null,
        $url,
        null,
        1,
        null,
        null,
        "scraped"
    );
}

echo "DONE";