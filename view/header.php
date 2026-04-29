<?php
//if (session_status() === PHP_SESSION_NONE) {
//    session_start();
//}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Capstone Website</title>
    <link rel="stylesheet" href="styles/main.css">
</head>

<body>

<header class="site-header">

    <div class="header-left">
      
        <h2>Artificial Intelligence and Visual Knowledge Discovery (AI-VKD)</h2>
    </div>

    
    <div class="header-right">

        <?php if (isset($_SESSION["userID"])): ?>
            <a href="Users/usersPage.php" class="user-icon-btn" title="My Account">
                👤
            </a>
        <?php else: ?>
            <a href="Login/login.php" class="user-icon-btn" title="Login">
                👤
            </a>
        <?php endif; ?>

    </div>

</header>
