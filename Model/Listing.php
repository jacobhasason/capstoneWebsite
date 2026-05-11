<?php
require "../DBConnect/db.php";


function addListing($title, $author, $date, $icon, $medium, $abstract, $links, $file, $userID, $topic, $citation) {

    $data = [
        "title" => $title,
        "author" => $author,
        "date" => $date,
        "icon" => $icon,
        "medium" => $medium,
        "abstract" => $abstract,
        "links" => $links,
        "file" => $file,
        "userID" => $userID,
        "topic" => $topic,
        "citation" => $citation
    ];

    $ch = curl_init("https://zdysuvkcmymlwpernryq.supabase.co/rest/v1/Listing");

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
    curl_close($ch);

    return $response;
}

function getListing() {


    $url = $config['url'] . "/rest/v1/Listing?select=*&order=date.desc";

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o",
        "Authorization: sb_publishable_LaDWDoLk-FfeKFFqYmoviw_6kpXsK-o",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return json_encode(["error" => "Failed to fetch listings"]);
    }

    curl_close($ch);

    return $response;
}