<?php
session_start();
require "../DBConnect/db.php";


if (!isset($_SESSION["userType"]) || $_SESSION["userType"] != 2) {
    die("Not allowed");
}

$id = $_GET["id"] ?? null;

if (!$id) {
    die("No user selected");
}

// fetch user
$stmt = $db->prepare('SELECT * FROM "User" WHERE "userID" = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found");
}

// handle password update
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $newPassword = $_POST["password"] ?? "";

    if (!empty($newPassword)) {

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        $update = $db->prepare('UPDATE "User" SET password = ? WHERE "userID" = ?');
        $update->execute([$hashed, $id]);

        header("Location: usersPage.php");
        exit();
    }
}
?>

<?php include '../view/modifyUserHeader.php'; ?>
<link rel="stylesheet" href="../styles/modUser.css">
<link rel="stylesheet" href="../styles/main.css">

<main class="modify-user-page">

    <h2>Modify User</h2>

    <!-- USERNAME (read-only) -->
    <div class="user-box">
        <p><strong>Username:</strong> <?= htmlspecialchars($user["userName"]) ?></p>
    </div>

    <!-- PASSWORD UPDATE -->
    <form method="POST" class="modify-form">

        <label>Password</label>
        <input type="password" name="password" placeholder="New password" required>

        <button type="submit" class="btn">Update Password</button>

    </form>

    <!-- ACTION BUTTONS -->
    <div class="source-actions">

        <a href="removeSources.php?id=<?= $user["userID"] ?>" class="btn danger">
            Remove Sources
        </a>

        <a href="modifySources.php?id=<?= $user["userID"] ?>" class="btn">
            Modify Sources
        </a>

    </div>

</main>

<a href="usersPage.php" class="fab"><<</a>

<?php include '../view/footer.php'; ?>