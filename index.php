<?php include 'view/header.php'; ?>
<?php include 'view/horizontal_nav_bar.php'; ?>

<?php
require 'DB/db.php';

echo "Connected successfully!";
?>
<body>
    <main>
        <!-- start of listing container 1 -->
        <div class="source-item">
            <a href="ExpandedListing/ListingInfo.php?id=1" class="item-link">

                <div class="thumbnail">
                <img src="cocoNut.jpg" alt="Zhu Curve Preview">
                </div>

                <div class="source-details">
                    <h3><strong>Title:</strong> Exploring the Zhu Curve</h3>
                    <p><strong>Author:</strong> Saul Rodriguez</p>
                    <p><strong>Date:</strong> 4/8/2026</p>
                </div>

                <div class="source-icon">
                    <span class="icon">🔊</span>
                </div>

            </a>
        </div>
        <!-- end of listing container 1 -->

        <!-- start of listing container 2 -->
        <div class="source-item">
            <a href="ExpandedListing/ListingInfo.php?id=2" class="item-link">

                <div class="thumbnail">
                    <img src="cocoNut.jpg" alt="Another Source">
                </div>

                <div class="source-details">
                    <h3><strong>Title:</strong> How Holden Got Away With Stuff Isak and Saul Got Points Taken For </h3>
                    <p><strong>Author:</strong> Saul Rodriguez</p>
                    <p><strong>Date:</strong> 3/28/2026</p>
                </div>

                <div class="source-icon">
                    <span class="icon">📄</span>
                </div>

            </a>
        </div>
           <!-- end of listing container 2 -->

           <!-- this is the button in the lower right -->
        <a href="AddSource.php" class="fab">+</a>
    </main>
</body>
<script src="scripts/date.js"></script>
<script src="scripts/checkmark.js"></script>

<?php include 'view/footer.php'; ?>