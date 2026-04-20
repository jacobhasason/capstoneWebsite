<?php
require_once 'db.php';

createTable();

function createTable() {
    $db = getDB();

    $db->exec("
        CREATE TABLE IF NOT EXISTS listings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT,
            author TEXT,
            date TEXT,
            medium TEXT,
            thumbnail_path TEXT,
            file_path TEXT,
            abstract TEXT,
            url TEXT
        )
    ");
}

function addListing(
    $title,
    $author,
    $date,
    $medium,
    $thumbnail_path,
    $file_path,
    $abstract,
    $url
) {
    $db = getDB();

    $stmt = $db->prepare("
        INSERT INTO listings (
            title, author, date, medium,
            thumbnail_path, file_path,
            abstract, url
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $title,
        $author,
        $date,
        $medium,
        $thumbnail_path,
        $file_path,
        $abstract,
        $url
    ]);
}

function getListings() {
    $db = getDB();
    return $db->query("SELECT * FROM listings");
}
?>