<!DOCTYPE html> 
<html lang="en">
    <meta charset="UTF-8">
    <title>Add Source</title>
    <body>
        <header class="site-header">

            <!-- LEFT SIDE -->
            <div class="header-left">

                <img src="images/CWULOGO2.png"
                     alt="CWU Logo"
                     class="header-logo">

                <h1 class="header-title">
                    Add Source
                </h1>

            </div>
            <!-- RIGHT SIDE -->
            <div class="header-right">

                <!-- ABOUT -->
                <a href="controller.php?page=about"
                   class="about-btn"
                   title="About">

                    ?

                </a>

                <!-- USER -->
                <?php if (isset($_SESSION["userID"])): ?>

                    <a href="controller.php?page=usersPage"
                       class="user-icon-btn"
                       title="My Account">

                        👤

                    </a>

                <?php else: ?>

                    <a href="controller.php?page=login"
                       class="user-icon-btn"
                       title="Login">

                        👤

                    </a>

                <?php endif; ?>

            </div>
            <link rel="stylesheet" href="styles/AddSource.css">
            <link rel="stylesheet" href="styles/main.css"> 
        </header>
    </body>
</html>
