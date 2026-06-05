<header class="site-header">

    <!-- LEFT SIDE -->
    <div class="header-left">

        <img src="images/CWULOGO2.png"
             alt="CWU Logo"
             class="header-logo">

        <h1 class="header-title">
            Modify User
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
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/modUser.css">
</header>