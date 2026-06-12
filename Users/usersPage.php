<?php
require "DBConnect/db.php";

/* AUTH CHECK */
if (!isset($_SESSION["userID"])) {
    header("Location: controller.php?page=login");
    exit();
}

/* SESSION DATA */
$currentUserID = (int) $_SESSION["userID"];
$currentUserType = (int) ($_SESSION["userType"] ?? 1);
$currentUserName = $_SESSION["userName"] ?? "User";

/* ROLE CHECK */
$isSuperUser = ((int) $currentUserType === 2);
?>
<title> User Page </title>

<main>
    
    <!-- USER BAR (ALWAYS SHOWN) -->
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

    <!-- NON-SUPERUSER VIEW -->
    <?php if (!$isSuperUser): ?>

        <div class="user-card">
            <p>You cannot manage other users.</p>
        </div>

    <?php else: ?>

        <!-- SUPERUSER VIEW -->
        <h2>Users</h2>

        <?php
        $stmt = $db->prepare('SELECT * FROM "User" WHERE "userID" != ?');
        $stmt->execute([$currentUserID]);
        $users = $stmt->fetchAll();
        ?>

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
                                if ($user["userType"] == 2) {
                                    echo "Superuser";
                                } elseif ($user["userType"] == 1) {
                                    echo "Whitelist";
                                } else {
                                    echo "Standard User";
                                }
                                ?>
                            </p>

                        </div>

                        <?php if ((int) $user["userType"] !== 2): ?>
                            <div class="user-actions">

                                <a href="controller.php?page=modifyUser&id=<?= $user["userID"] ?>"
                                   class="btn">
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

        <!-- CREATE USER PANEL (NOW AT BOTTOM) -->
        <div class="create-user-panel">

            <h3>Create User</h3>

            <a href="controller.php?page=addUser&type=1"
               class="btn">

                Add User (Whitelist)

            </a>

        </div>

        <!-- TOPIC MANAGEMENT PANEL -->
        <div class="create-user-panel">

            <h3>Topics & Sub-Topics</h3>

            <a href="controller.php?page=manageTopics"
               class="btn">

                Manage

            </a>

        </div>




    <?php endif; ?>

</main>

<a href="controller.php?page=index" class="fab">&lt;&lt;</a>