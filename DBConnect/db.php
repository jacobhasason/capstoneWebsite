<?php
// this here is the black magic page
$host = "aws-1-us-east-2.pooler.supabase.com";
$port = "5432";
$dbname = "postgres";
$user = "postgres.zdysuvkcmymlwpernryq";
$password = "Of9Be0I27sUugiTP";

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

try {
    $db = new PDO($dsn, $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
