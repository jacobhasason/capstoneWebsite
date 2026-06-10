<?php
$relatedListingID = $_GET['relatedListingID'] ?? null;

if (!$relatedListingID) {
    die("Missing relatedListingID");
}

$projectUrl = "https://zdysuvkcmymlwpernryq.supabase.co";
$key = "sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o";

$url = $projectUrl . "/rest/v1/RelatedListing"
    . "?relatedListingID=eq." . urlencode($relatedListingID);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST => "DELETE",
    CURLOPT_HTTPHEADER => [
        "apikey: $key",
        "Authorization: Bearer $key",
        "Prefer: return=representation"
    ],
    CURLOPT_RETURNTRANSFER => true
]);

$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP $code: $response";
exit;