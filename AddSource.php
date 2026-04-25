<?php include 'view/AddSourceHeader.php'; ?>


<main>
    <body>
        <form action="AddSource.php" method="post" class="input-group" enctype="multipart/form-data">

            <div class="field">
                <label for="title">Project Title:</label>
                <input type="text" id="title" name="title" placeholder="Enter title...">
            </div>

            <div class="field">
                <label for="author">Author Name:</label>
                <input type="text" id="author" name="author" placeholder="e.g. Mike Smith">
            </div>

            <div class="field">
                <label for="datePub">Date Published:</label>
                <input type="text" id="datePub" name="datePub" placeholder="4/20/2026">
            </div>

            <div class="drop-row">

                <div class="drop-group">
                    <label>Icon:</label>
                    <div id="drop-zone-thumb" class="drop-area">
                        <span class="drop-message">Drag & Drop(jpeg/png) <br> Icon</span>
                        <input type="file" name="thumbnail" accept="image/*" hidden>
                    </div>
                </div>

                <div class="drop-group">
                    <label>File:</label>
                    <div id="drop-zone-icon" class="drop-area">
                        <span class="drop-message">Drag & Drop (pdf/mp3/mp4/.wav) <br>File</span>
                        <input type="file" name="icon" accept="image/*" hidden>
                    </div>
                </div>

            </div>

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

            <div class="field">
                <label for=abs">Abstract:</label>
                <input type="text" id="abs" name="abs" placeholder="Enter Text">
            </div>

            <div class="field">
                <label for=links">Links:</label>
                <input type="text" id="links" name="abs" placeholder="Enter Text">
            </div>


            <button type="submit" class="submit-btn">Upload Project</button>
        </form>

        <a href="index.php" class="fab"><<</a>
    </body>
</main>

<script src="scripts/date.js"></script>
<script src="scripts/checkmark.js"></script>

<?php include 'view/footer.php'; ?>