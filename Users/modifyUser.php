<?php
session_start();
require "../DBConnect/db.php";

$currentUserType = (int) ($_SESSION["userType"] ?? 1);
$currentCanManage = (int) ($_SESSION["canManage"] ?? 1);

$canAccess = ($currentUserType === 2) ||
        ($currentUserType === 1 && $currentCanManage === 2);

if (!$canAccess) {
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

// FORCE SUPERUSER PERMISSION TO ALWAYS BE ENABLED
if ($user["userType"] == 2) {
    $user["permisMod"] = 2;
}

// handle password update
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $newPassword = $_POST["password"] ?? "";

    if (!empty($newPassword)) {

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        $update = $db->prepare('
            UPDATE "User"
            SET password = ?
            WHERE "userID" = ?
        ');

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

    <!-- USERNAME -->
    <div class="user-box">
        <p>
            <strong>Username:</strong>
            <?= htmlspecialchars($user["userName"]) ?>
        </p>
    </div>

    <!-- PASSWORD UPDATE -->
    <form method="POST" class="modify-form">

        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="New password"
            required
            >

        <button type="submit" class="btn">
            Update Password
        </button>

    </form>

    <!-- PERMISSION TOGGLE -->
    <div class="source-actions">

        <?php if ($user["userType"] == 2): ?>

            <div class="btn disabled">
                Modify Always Enabled (Superuser)
            </div>

        <?php else: ?>

            <a href="#"
               class="btn toggle-mod"
               data-id="<?= $user["userID"] ?>"
               data-state="<?= $user["permisMod"] ?>">

                <?=
                $user["permisMod"] == 2 ? "Modify Enabled" : "Modify Disabled"
                ?>

            </a>

        <?php endif; ?>

    </div>


    <!-- CAN MANAGE WHITELIST TOGGLE -->
    <div class="source-actions">

        <?php
        if (
                $_SESSION["userType"] == 2 ||
                ($_SESSION["userType"] == 1 && $_SESSION["canManage"] == 2)
        ):
            ?>

            <a href="#"
               class="manage-btn <?= $user["canManage"] == 2 ? '' : 'danger' ?>"
               data-id="<?= $user["userID"] ?>"
               data-state="<?= $user["canManage"] ?>">

                <?=
                $user["canManage"] == 2 ? "Can Manage Sources" : "Cannot Manage Sources"
                ?>

            </a>

<?php endif; ?>

    </div> 






</main>



<a href="usersPage.php" class="fab">←</a>

<script>
    document.querySelectorAll(".toggle-mod").forEach((btn) => {

        btn.addEventListener("click", async (e) => {
            e.preventDefault();

            const id = btn.dataset.id;

            try {
                const res = await fetch(`toggleModifyPermission.php?id=${id}`);
                const text = await res.text();

                console.log("SERVER RESPONSE:", text);

                // ONLY flip UI if server actually responded
                if (res.ok) {
                    const isEnabled = btn.dataset.state == "2";

                    if (isEnabled) {
                        btn.dataset.state = "1";
                        btn.classList.add("danger");
                        btn.textContent = "Modify Disabled";
                    } else {
                        btn.dataset.state = "2";
                        btn.classList.remove("danger");
                        btn.textContent = "Modify Enabled";
                    }
                }

            } catch (err) {
                console.error("FETCH FAILED:", err);
            }
        });

    });
</script>

<script>
    document.querySelectorAll(".manage-btn").forEach(btn => {

        btn.addEventListener("click", async (e) => {
            e.preventDefault();

            const id = btn.dataset.id;

            const res = await fetch(`toggleCanManage.php?id=${id}`);
            const newState = (await res.text()).trim();

            btn.dataset.state = newState;

            if (newState == "2") {
                btn.classList.remove("danger");
                btn.textContent = "Can Manage Sources";
            } else {
                btn.classList.add("danger");
                btn.textContent = "Cannot Manage Sources";
            }
        });

    });


</script>

<?php include '../view/footer.php'; ?>
