<?php
require "DBConnect/db.php";

/* AUTH CHECK (must be logged in */
if (!isset($_SESSION["userID"])) {
    header("Location: controller.php?page=login");
    exit();
}

/* SAFE SESSION READS */
$currentUserID = (int) ($_SESSION["userID"]);
$currentUserType = (int) ($_SESSION["userType"] ?? 1);
$currentUserName = $_SESSION["userName"] ?? "User";
$currentCanManage = (int) ($_SESSION["canManage"] ?? 1);

/* PERMISSION CHECK (ONLY FOR USER LIST) */
$canViewUsers = ($currentUserType === 2) ||
        ($currentUserType === 1 && $currentCanManage === 2);

/* FETCH USERS */
$users = [];

if ($canViewUsers) {
    $stmt = $db->prepare('SELECT * FROM "User" WHERE "userID" != ?');
    $stmt->execute([$currentUserID]);
    $users = $stmt->fetchAll();
}
?>



<main>

    <!--USER INFO BAR (ALWAYS SHOWN) -->
    <div class="user-bar">

        <div class="user-left">
            Logged in as:
            <strong><?= htmlspecialchars($currentUserName) ?></strong>
        </div>

        <div class="user-right">
            <a href="controller.php?page=logout" class="logout-btn">
                Log out
            </a>
        </div>

    </div>

    <h2>Users</h2>

    <!--USER LIST (PROTECTED)-->
    <?php if ($canViewUsers): ?>

        <?php if (!empty($users)): ?>

            <div class="users-list">

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

                                <a href="controller.php?page=modifyUser&id=<?= $user["userID"] ?>" class="btn">
                                    Modify
                                </a>

                                <a href="controller.php?page=deleteUser&id=<?= $user["userID"] ?>"
                                    class="btn danger"
                                    onclick="return confirm('Are you sure?')">
                                     Delete
                                </a>

                            </div>
                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <p>No users found.</p>

        <?php endif; ?>

    <?php else: ?>

        <!-- NO ACCESS MESSAGE -->
        <div class="user-card">
            <p>You do not have permission to view user management.</p>
        </div>

    <?php endif; ?>

    <!--CREATE USER PANEL -->
    <div class="create-user-panel">

        <h3>Create User</h3>

        <?php if ($currentUserType === 2): ?>

            <a href="controller.php?page=addUser&type=1" class="btn">
                Add User (Whitelist)
            </a>

        <?php else: ?>

            <p class="muted">
                You do not have permission to create users.
            </p>

        <?php endif; ?>

    </div>

</main>

<a href="controller.php?page=index" class="fab">&lt;&lt;</a>

