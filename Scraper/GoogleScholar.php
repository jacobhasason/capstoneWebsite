<?php

$baseUrl = "https://scholar.google.com/citations?view_op=list_works&hl=en&hl=en&user=k12ONA8AAAAJ";

$allData = [];

for ($start = 0; $start < 1000; $start += 20) {

    $url = $baseUrl . "&cstart=$start&pagesize=20";
    $options = [
        "http" => [
            "header" => "User-Agent: Mozilla/5.0\r\n"
        ]
    ];

    $context = stream_context_create($options);
    $html = file_get_contents($url, false, $context);

    if (!$html) {
        break;
    }

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    $rows = $xpath->query("//tr[@class='gsc_a_tr']");

    // Stops when no results
    if ($rows->length == 0) {
        break;
    }

    foreach ($rows as $row) {

        // Title plus the hyperlink
        $titleNode = $xpath->query(".//a[@class='gsc_a_at']", $row)->item(0);

        $title = $titleNode ? trim($titleNode->nodeValue) : '';
        $link = $titleNode ? "https://scholar.google.com" . $titleNode->getAttribute("href") : null;

        // Authors
        $gray = $xpath->query(".//div[@class='gs_gray']", $row);

        $authors = $gray->item(0) ? trim($gray->item(0)->nodeValue) : '';

        // Year
        $yearNode = $xpath->query(".//span[@class='gsc_a_h gsc_a_hc gs_ibl']", $row)->item(0);
        $year = $yearNode ? trim($yearNode->nodeValue) : '';

        // Citations link only
        $citedNode = $xpath->query(".//a[contains(@href,'cites')]", $row)->item(0);

        if ($citedNode) {

            $href = $citedNode->getAttribute("href");

            // checks if https is not duplicated
            if (str_starts_with($href, "http")) {
                $citations = $href;
            } else {
                $citations = "https://scholar.google.com" . $href;
            }
        } else {
            $citations = null;
        }
        
        // Topics
        if (
            str_contains($text, 'visual') ||
            str_contains($text, 'visualization') ||
            str_contains($text, 'knowledge discovery') ||
            str_contains($text, 'graph') ||
            str_contains($text, 'visual analytics')
        ) {
            $topic = "Visual Knowledge";
        } else {
            $topic = "AI/Machine Learning";
        }
        
        $allData[] = [
            "title" => $title,
            "links" => $link,
            "author" => $authors,
            "date" => $year,
            "citations" => $citations,
            "topic" => $topic
        ];
    }

    // prevent blocking
    sleep(1);
}

header('Content-Type: application/json');
echo json_encode($allData, JSON_PRETTY_PRINT);
?>