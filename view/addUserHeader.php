<!DOCTYPE html> 
<html lang="en">
    <meta charset="UTF-8">
    <title>Add User</title>
    <body>



    </div>
    <header class="site-header">


        <!-- LEFT SIDE -->
        <div class="header-left">
            <figure style="margin:0; text-align:center;">
                <img src="images/CWULOGO2.png"
                     alt="CWU Logo"
                     class="header-logo"
                     >

                <figcaption style="color:red">Visual Knowledge Discovery Lab </figcaption>
            </figure>



            <h1 class="header-title">
                Add User
            </h1>


        </div>

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
