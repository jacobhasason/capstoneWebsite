<?php include '../view/sourceExpandHeader.php'; ?>
<link rel="stylesheet" href="../styles/main.css">



<body>

<main class="listing-full">

  
    <!-- Actions -->
    <div class="listing-actions">

        <div class="action-buttons">

            <button class="btn">View Abstract</button>
            <button class="btn">Copy Citation</button>

            <a class="btn external"
               href="ViewReport.php?id=<?= $listing['listingID'] ?>">
                Primary Source & Related Resources
            </a>

        </div>

    </div>

    <!-- OPtional links -->
    <?php if (!empty($listing['links'])): ?>
        <p class="extra-link">
            <a href="<?= htmlspecialchars($listing['links']) ?>" target="_blank">
                External Link
            </a>
        </p>
    <?php endif; ?>

<!-- File download -->
<?php if (!empty($listing['file'])): ?>
    <p class="extra-link">
        <a href="<?= htmlspecialchars($listing['file']) ?>" download>
            Download File
        </a>
    </p>
<?php endif; ?>

</main>

</body>

<a href="../index.php" class="fab"><<</a>

<script src="../scripts/date.js"></script>

<?php include '../view/footer.php'; ?>
