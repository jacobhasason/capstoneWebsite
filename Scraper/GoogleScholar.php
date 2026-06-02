<?php

$SUPABASE_URL = "https://zdysuvkcmymlwpernryq.supabase.co";
$SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InpkeXN1dmtjbXltbHdwZXJucnlxIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY4ODA4MjMsImV4cCI6MjA5MjQ1NjgyM30.bDlADBNjnNoiTC6L5fjb13pK6YB1uHp5yZqHGIHLOuQ";

error_reporting(0);
ini_set('display_errors', 0);

/*
|--------------------------------------------------------------------------
| IEeE Citation Helpers (unchanged)
|--------------------------------------------------------------------------
*/

function ieeeAuthors(string $authors): string
{
    $names = array_map('trim', explode(',', $authors));
    $formatted = [];

    foreach ($names as $name) {

        if (empty($name)) continue;

        $parts = preg_split('/\s+/', trim($name));

        if (count($parts) < 2) {
            $formatted[] = $name;
            continue;
        }

        $last = array_pop($parts);

        $initials = [];

        foreach ($parts as $part) {
            foreach (str_split($part) as $letter) {
                $initials[] = strtoupper($letter) . '.';
            }
        }

        $formatted[] = implode(' ', $initials) . ' ' . $last;
    }

    if (count($formatted) > 1) {
        $last = array_pop($formatted);
        return implode(', ', $formatted) . ', and ' . $last;
    }

    return $formatted[0] ?? '';
}

function buildIEEECitation($title, $authors, $source, $year)
{
    $authorText = ieeeAuthors($authors);

    if (!empty($year)) {
        $source = preg_replace(
            '/,\s*' . preg_quote($year, '/') . '\s*$/',
            '',
            $source
        );
    }

    $citation = $authorText;

    if ($title) $citation .= ', "' . $title . '"';
    if ($source) $citation .= ', ' . $source;
    if ($year) $citation .= ', ' . $year;

    return $citation . '.';
}

function detectMedium($source, $title, $link)
{
    $source = strtolower($source);
    $title = strtolower($title);
    $link = strtolower($link);

    if (
        str_contains($source, 'springer') ||
        str_contains($source, 'kluwer') ||
        str_contains($source, 'press')
    ) return "Book";

    if (preg_match('/\d+\s*\(\d+\)/', $source)) return "Paper";

    if (
        str_contains($title, 'tutorial') ||
        str_contains($source, 'tutorial')
    ) return "Tutorial";

    if (
        str_contains($source, 'conference') ||
        str_contains($source, 'proceedings')
    ) return "Presentation";

    if (str_contains($link, 'youtube')) return "Video";

    return "Paper";
}

/*
|--------------------------------------------------------------------------
| HTTP Fetch with Anti-blocking
|--------------------------------------------------------------------------
*/

$userAgents = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/122 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 Safari/605.1.15",
    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/121 Safari/537.36"
];

function fetchHTML($url, $ua)
{
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => $ua,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => [
            "Accept-Language: en-US,en;q=0.9"
        ]
    ]);

    $html = curl_exec($ch);
    curl_close($ch);

    return $html;
}

/*
|--------------------------------------------------------------------------
| Scraping Loop
|--------------------------------------------------------------------------
*/

$baseUrl = "https://scholar.google.com/citations?view_op=list_works&hl=en&user=k12ONA8AAAAJ";

$allData = [];
$failCount = 0;

for ($start = 0; $start < 1000; $start += 20) {

    $url = $baseUrl . "&cstart=$start&pagesize=20";
    $ua = $userAgents[array_rand($userAgents)];

    echo "Fetching: $url\n";

    $html = fetchHTML($url, $ua);

    if (
        !$html ||
        stripos($html, "captcha") !== false ||
        stripos($html, "unusual traffic") !== false
    ) {
        echo "Blocked or empty response\n";

        $failCount++;

        if ($failCount >= 3) {
            echo "Stopping due to repeated blocking\n";
            break;
        }

        sleep(10);
        continue;
    }

    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $dom->loadHTML($html);

    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $rows = $xpath->query("//tr[@class='gsc_a_tr']");

    if ($rows->length == 0) {
        echo "No rows found - stopping\n";
        break;
    }

    foreach ($rows as $row) {

        $titleNode = $xpath->query(".//a[@class='gsc_a_at']", $row)->item(0);

        $title = $titleNode ? trim($titleNode->nodeValue) : '';

        $link = $titleNode
            ? "https://scholar.google.com" . $titleNode->getAttribute("href")
            : '';

        $gray = $xpath->query(".//div[@class='gs_gray']", $row);

        $authors = $gray->item(0) ? trim($gray->item(0)->nodeValue) : '';
        $source  = $gray->item(1) ? trim($gray->item(1)->nodeValue) : '';

        $yearNode = $xpath->query(".//span[@class='gsc_a_h gsc_a_hc gs_ibl']", $row)->item(0);
        $year = $yearNode ? trim($yearNode->nodeValue) : '';

        $citation = buildIEEECitation($title, $authors, $source, $year);
        $medium = detectMedium($source, $title, $link);

        $allData[] = [
            "title" => $title,
            "links" => $link,
            "author" => $authors,
            "date" => $year,
            "citations" => $citation,
            "medium" => $medium
        ];
    }

    sleep(rand(3, 8));
}

/*
|--------------------------------------------------------------------------
| Save local backup
|--------------------------------------------------------------------------
*/

file_put_contents(
    "scholar.json",
    json_encode($allData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

/*
|--------------------------------------------------------------------------
| Supabase Upload
|--------------------------------------------------------------------------
*/

$payload = json_encode($allData);

$ch = curl_init($SUPABASE_URL . "/rest/v1/Listing");

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "apikey: $SUPABASE_KEY",
        "Authorization: Bearer $SUPABASE_KEY",
        "Prefer: return=representation"
    ]
]);

$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

/*
|--------------------------------------------------------------------------
| Output
|--------------------------------------------------------------------------
*/

echo json_encode([
    "success" => $status >= 200 && $status < 300,
    "status" => $status,
    "records_found" => count($allData),
    "supabase_response" => json_decode($response, true)
], JSON_PRETTY_PRINT);

?>