<?php
session_start();
require "../DBConnect/db.php";

if (!isset($_SESSION["userID"])) {
    header("Location: ../Login/login.php");
    exit();
}

$currentUserID = $_SESSION["userID"];
$currentUserType = $_SESSION["userType"];
$currentUserName = $_SESSION["userName"];

/* =========================
   FETCH USERS ONLY FOR SUPERUSERS
========================= */
$users = [];

if ($currentUserType == 2) {
    $stmt = $db->prepare('SELECT * FROM "User" WHERE "userID" != ?');
    $stmt->execute([$currentUserID]);
    $users = $stmt->fetchAll();
}
?>

<?php include '../view/userPageHeader.php'; ?>

<link rel="stylesheet" href="../styles/main.css">
<link rel="stylesheet" href="../styles/userpage.css">

<main>

    <!-- USER INFO BAR -->
    <div class="user-bar">

        <div class="user-left">
            Logged in as:
            <strong>
                <?= htmlspecialchars($currentUserName) ?>
            </strong>
        </div>

        <div class="user-right">
            <a href="../Logout/logout.php" class="logout-btn">
                Log out
            </a>
        </div>

    </div>

    <!-- PAGE CONTENT -->
    <h2>Users</h2>

    <?php if ($currentUserType == 2): ?>

        <!-- SUPERUSER VIEW (FULL LIST) -->
        <div class="users-list">

            <?php if (empty($users)): ?>
                <p>No other users found.</p>
            <?php else: ?>

                <?php foreach ($users as $user): ?>

                    <div class="user-card">

                        <div class="user-info">

                            <p><strong>Username:</strong>
                                <?= htmlspecialchars($user["userName"]) ?>
                            </p>

                            <p><strong>Type:</strong>
                                <?php
                                    if ($user["userType"] == 1) {
                                        echo "Whitelist";
                                    } elseif ($user["userType"] == 2) {
                                        echo "Superuser";
                                    } else {
                                        echo "Standard User";
                                    }
                                ?>
                            </p>

                        </div>

                        <?php if ($user["userType"] == 1): ?>
                            <div class="user-actions">

                                <a href="modifyUser.php?id=<?= $user["userID"] ?>" class="btn">
                                    Modify
                                </a>

                                <a href="deleteUser.php?id=<?= $user["userID"] ?>" class="btn danger"
                                   onclick="return confirm('Are you sure?')">
                                    Delete
                                </a>

                            </div>
                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    <?php else: ?>

        <!-- WHITELIST VIEW (LIMITED) -->
        <div class="user-card">

            <p>You are logged in as a Whitelist user.</p>
            <p>You do not have access to manage other users.</p>

        </div>

    <?php endif; ?>

    <!-- CREATE USER PANEL -->
    <div class="create-user-panel">

        <h3>Create User</h3>

        <?php if ($currentUserType == 2): ?>

            <a href="addUser.php?type=1" class="btn">
                Add User (Whitelist)
            </a>

        

        <?php else: ?>

            <p class="muted">
                You do not have permission to create users.
            </p>

        <?php endif; ?>

    </div>

</main>

<a href="../index.php" class="fab"><<</a>

<?php include '../view/footer.php'; ?>