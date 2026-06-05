<!DOCTYPE html> 
<html lang="en">
    <meta charset="UTF-8">
    <title>Edit Listing</title>
    <body>
        <header>
            <link rel="stylesheet" href="styles/main.css">
            <link rel="stylesheet" href="styles/editListing.css">

            <h2>Edit Listing</h2>

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
        </header>
    </body>
</html>
