<?php
$q = $_GET['q'] ?? '';

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$projectUrl = "https://zdysuvkcmymlwpernryq.supabase.co";
$key = "sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o";

$url = $projectUrl . "/rest/v1/Listing"
    . "?select=listingID,title,author"
    . "&title=ilike.*" . rawurlencode($q) . "*"
    . "&limit=3";

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $key",
    "Authorization: Bearer $key",
    "Content-Type: application/json"
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode([]);
    exit;
}

curl_close($ch);

header("Content-Type: application/json");
echo $response;