<?php
require 'mtd_functions.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['create'])) {
        createUser($_POST['username'], $_POST['password']);
    } elseif (isset($_POST['delete'])) {
        deleteUser($_POST['id']);
    }
}

$users = getAllUsers();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Utilisateurs</title>
</head>
<body>
    <h1>Utilisateurs</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                    <button type="submit" name="delete" onclick="return confirm('Supprimer ?')">Supprimer</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Ajouter un utilisateur</h2>
    <form method="post">
        <label>Nom: <input type="text" name="username" required></label>
        <label>Mot de passe: <input type="password" name="password" required></label>
        <button type="submit" name="create">Créer</button>
    </form>
</body>
</html>
