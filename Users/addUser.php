<?php
session_start();
require "../DBConnect/db.php";

// must be logged in
if (!isset($_SESSION["userID"])) {
    die("Not allowed");
}

$type = $_GET["type"] ?? null;

// validate type
if ($type !== "1" && $type !== "2") {
    die("Invalid user type");
}

// whitelist users cannot create superusers
if ($type == "2" && $_SESSION["userType"] != 2) {
    die("Not allowed");
}

$error = "";

// handle form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username && $password) {

        // check duplicate username
        $check = $db->prepare('SELECT * FROM "User" WHERE "userName" = ?');
        $check->execute([$username]);

        if ($check->fetch()) {
            $error = "Username already exists";
        } else {

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare('
                INSERT INTO "User" ("userName", "password", "userType")
                VALUES (?, ?, ?)
            ');

            $stmt->execute([$username, $hashed, $type]);

            header("Location: usersPage.php");
            exit();
        }

    } else {
        $error = "Please fill in all fields";
    }
}
?>

<?php include '../view/header.php'; ?>

<link rel="stylesheet" href="../styles/main.css">

<main class="add-user-page">

    <h2>
        Create
        <?= $type == 2 ? "Superuser" : "User (Whitelist)" ?>
    </h2>

    <?php if ($error): ?>
        <p style="color:red;">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form method="POST" class="add-user-form">

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" class="btn">
            Create User
        </button>

    </form>

</main>

<a href="usersPage.php" class="fab"><<</a>

<?php include '../view/footer.php'; ?>