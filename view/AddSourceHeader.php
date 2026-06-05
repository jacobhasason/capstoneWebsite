<!DOCTYPE html> 
<html lang="en">
    <meta charset="UTF-8">
    <title>Add Source</title>
    <body>
        <header class="site-header">

            
             <div class="header-right">
   
        <a href="https://www.cwu.edu/about/directory/computer-science/boris-kovalerchuk.php"
           class="contact-btn"
           title="Contact">

            Contact

        </a>
        
        <a href="controller.php?page=about"
           class="about-btn"
           title="About">

            ?

        </a>

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
