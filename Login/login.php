<?php
// Login screen for Whitelist/Super users

// If already logged in, redirect to users page
if (isset($_SESSION["userID"])) {
    header("Location: controller.php?page=usersPage");
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
            $_SESSION["canManage"] = $user["canManage"] ?? 1;
            $_SESSION["permisMod"] = (int) ($user["permisMod"] ?? 1);

            header("Location: controller.php?page=usersPage");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Please fill in all fields";
    }
}
?>


<main class="login-page">

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

<a href="../controller.php?page=index" class="fab"><<</a>


