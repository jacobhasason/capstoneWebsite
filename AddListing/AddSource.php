<?php include '../view/AddSourceHeader.php'; ?>

<!-- page styles -->
<link rel="stylesheet" href="../styles/AddSource.css">
<link rel="stylesheet" href="../styles/main.css">

<main>
    <body>

        <!-- main upload form -->
        <form action="AddSource.php" method="post" class="input-group" enctype="multipart/form-data">

            <!-- project title input -->
            <div class="field">
                <label for="title">Project Title:</label>
                <input type="text" id="title" name="title" placeholder="Enter title...">
            </div>

            <!-- author input -->
            <div class="field">
                <label for="author">Author Name:</label>
                <input type="text" id="author" name="author" placeholder="e.g. Mike Smith">
            </div>

            <!-- date published input -->
            <div class="field">
                <label for="datePub">Date Published:</label>
                <input type="text" id="datePub" name="datePub" placeholder="4/20/2026">
            </div>

            <!-- upload section (icon + file) -->
            <div class="drop-row">

                <!-- thumbnail upload box -->
                <div class="drop-group">
                    <label>Icon:</label>
                    <div id="drop-zone-thumb" class="drop-area">
                        <span class="drop-message">Drag & Drop(jpeg/png) <br> Icon</span>
                        <input type="file" name="thumbnail" accept="image/*" hidden>
                    </div>
                </div>

                <!-- main file upload box -->
                <div class="drop-group">
                    <label>File:</label>
                    <div id="drop-zone-icon" class="drop-area">
                        <span class="drop-message">Drag & Drop (pdf/mp3/mp4/.wav) <br>File</span>
                        <input type="file" name="icon" accept="image/*" hidden>
                    </div>
                </div>

            </div>

            <!-- medium selector -->
            <div class="field">
                <label for="medium">Select Medium:</label>
                <select id="medium" name="medium">
                    <option value="blank"></option>
                    <option value="paper">Paper</option>
                    <option value="tutorial">Tutorial</option>
                    <option value="presentation">Presentation</option>
                    <option value="video">Video</option>
                    <option value="podcast">Podcast</option>
                </select>
            </div>

            <!-- abstract text input -->
            <div class="field">
                <label for="abs">Abstract:</label>
                <input type="text" id="abs" name="abs" placeholder="Enter Text">
            </div>

            <!-- links input -->
            <!-- note: name was incorrect before (was using abs again) -->
            <div class="field">
                <label for="links">Links:</label>
                <input type="text" id="links" name="links" placeholder="Enter Text">
            </div>

            <!-- submit button -->
            <button type="submit" class="submit-btn">Upload Project</button>

        </form>

    </body>
</main>

<!-- back button -->
<a href="../index.php" class="fab"><<</a>

<!-- scripts -->
<script src="../scripts/date.js"></script>
<script src="../scripts/checkmark.js"></script>

<?php include '../view/footer.php'; ?>