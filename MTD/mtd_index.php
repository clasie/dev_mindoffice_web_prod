<?php
require 'mtd_functions.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['create'])) {
        createMetadata($_POST['nom'], $_POST['valeur'], $_POST['domaine_id']);
    } elseif (isset($_POST['update'])) {
        updateMetadata($_POST['id'], $_POST['nom'], $_POST['valeur']);
    } elseif (isset($_POST['delete'])) {
        deleteMetadata($_POST['id']);
    }
}

$metadataList = getAllMetadata();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Métadonnées</title>
</head>
<body>
    <h1>Liste des Métadonnées</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Valeur</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($metadataList as $meta): ?>
        <tr>
            <td><?= $meta['id'] ?></td>
            <td><?= htmlspecialchars($meta['nom']) ?></td>
            <td><?= htmlspecialchars($meta['valeur']) ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $meta['id'] ?>">
                    <input type="text" name="nom" value="<?= htmlspecialchars($meta['nom']) ?>">
                    <input type="text" name="valeur" value="<?= htmlspecialchars($meta['valeur']) ?>">
                    <button type="submit" name="update">Modifier</button>
                </form>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $meta['id'] ?>">
                    <button type="submit" name="delete" onclick="return confirm('Supprimer ?')">Supprimer</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Ajouter une métadonnée</h2>
    <form method="post">
        <label>Nom: <input type="text" name="nom" required></label>
        <label>Valeur: <input type="text" name="valeur" required></label>
        <label>ID Domaine: <input type="number" name="domaine_id" required></label>
        <button type="submit" name="create">Ajouter</button>
    </form>
</body>
</html>
