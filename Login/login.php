<?php
session_start();
require "../DBConnect/db.php";

// If already logged in, redirect to users page
if (isset($_SESSION["userID"])) {
    header("Location: ../Users/usersPage.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username && $password) {

        // fetch user
        $stmt = $db->prepare('SELECT * FROM "User" WHERE "userName" = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // verify hashed password
        if ($user && password_verify($password, $user["password"])) {

            // set session data
            $_SESSION["userID"] = $user["userID"];
            $_SESSION["userName"] = $user["userName"];
            $_SESSION["userType"] = $user["userType"];

             header("Location: ../Users/usersPage.php");
            exit();

        } else {
            $error = "Invalid username or password";
        }

    } else {
        $error = "Please fill in all fields";
    }
}
?>

<?php include '../view/LoginHeader.php'; ?>

<link rel="stylesheet" href="../styles/main.css">

<main>

    <div class="login-container">

        <h2>Login</h2>

        <?php if ($error): ?>
            <p style="color:red;">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <form method="POST" class="login-form">

            <input type="text" name="username" placeholder="Username" required>

            <input type="password" name="password" placeholder="Password" required>

            <button type="submit">Login</button>

        </form>

    </div>

</main>

<a href="../index.php" class="fab"><<</a>

<?php include '../view/footer.php'; ?>