<header class="site-header">

    <div class="header-left">

        <figure>
            <img src="images/CWULOGO2.png"
                 alt="CWU Logo"
                 class="header-logo">

            <figcaption style="color:red"> Visual Knowledge Discovery Lab </figcaption>
        </figure>



        <h1 class="header-title">
            Artificial Intelligence and Visual Knowledge Discovery (AI-VKD)
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
</header>