<header class="site-header">

    <link rel="stylesheet" href="styles/main.css">

    <!-- LEFT SIDE -->
    <div class="header-left">

    <a href="../controller.php?page=about"
       class="about-btn"
       title="About">
        ?
    </a>

</div>

    <!-- CENTER -->
    <div class="header-center">
        <h1 class="header-title">CWU Research Hub</h1>
        <img src="images/cwu.jpg" alt="CWU Logo" class="header-logo">
    </div>

    <!-- RIGHT SIDE -->
    <div class="header-right">

        <?php if (isset($_SESSION["userID"])): ?>

            <a href="../controller.php?page=usersPage"
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