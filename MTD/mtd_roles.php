<?php
require 'mtd_functions.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['create_role'])) {
        createRole($_POST['nom']);
    } elseif (isset($_POST['delete_role'])) {
        deleteRole($_POST['id']);
    } elseif (isset($_POST['assign_role'])) {
        assignRoleToUser($_POST['user_id'], $_POST['role_id']);
    } elseif (isset($_POST['assign_permission'])) {
        assignPermissionToRole($_POST['role_id'], $_POST['permission_id']);
    }
}

$roles = getAllRoles();
$users = getAllUsers();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Rôles</title>
</head>
<body>
    <h1>Rôles</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($roles as $role): ?>
        <tr>
            <td><?= $role['id'] ?></td>
            <td><?= htmlspecialchars($role['nom']) ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $role['id'] ?>">
                    <button type="submit" name="delete_role" onclick="return confirm('Supprimer ?')">Supprimer</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Ajouter un rôle</h2>
    <form method="post">
        <label>Nom: <input type="text" name="nom" required></label>
        <button type="submit" name="create_role">Créer</button>
    </form>

    <h2>Assigner un rôle à un utilisateur</h2>
    <form method="post">
        <label>Utilisateur:
            <select name="user_id">
                <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Rôle:
            <select name="role_id">
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit" name="assign_role">Assigner</button>
    </form>
</body>
</html>
