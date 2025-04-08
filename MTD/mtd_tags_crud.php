<?php
require 'mtd_db.php';

// Ajouter un tag
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    $nom = trim($_POST["nom"]);

    if ($_POST["action"] === "create" && !empty($nom)) {
        $stmt = $pdo->prepare("INSERT INTO MTD_Tags (nom) VALUES (:nom)");
        $stmt->execute(["nom" => $nom]);
        exit(json_encode(["success" => true]));
    }

    if ($_POST["action"] === "update" && isset($_POST["id"]) && !empty($nom)) {
        $id = (int)$_POST["id"];
        $stmt = $pdo->prepare("UPDATE MTD_Tags SET nom = :nom WHERE id = :id");
        $stmt->execute(["nom" => $nom, "id" => $id]);
        exit(json_encode(["success" => true]));
    }

    if ($_POST["action"] === "delete" && isset($_POST["id"])) {
        $id = (int)$_POST["id"];
        $stmt = $pdo->prepare("DELETE FROM MTD_Tags WHERE id = :id");
        $stmt->execute(["id" => $id]);
        exit(json_encode(["success" => true]));
    }
    exit(json_encode(["success" => false, "message" => "Requête invalide"]));
}

// Récupérer tous les tags
$stmt = $pdo->query("SELECT * FROM MTD_Tags ORDER BY nom ASC");
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Tags</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="container mt-5">
    <h2 class="mb-4">Gestion des Tags</h2>

    <form id="tagForm" class="mb-3">
        <input type="hidden" id="tag_id">
        <div class="mb-2">
            <label for="tag_name" class="form-label">Nom du tag</label>
            <input type="text" class="form-control" id="tag_name" required>
        </div>
        <button type="submit" class="btn btn-primary">Ajouter</button>
        <button type="button" id="updateBtn" class="btn btn-warning d-none">Modifier</button>
        <button type="button" id="cancelBtn" class="btn btn-secondary d-none">Annuler</button>
    </form>

    <ul id="tagList" class="list-group">
        <?php foreach ($tags as $tag): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><?= htmlspecialchars($tag["nom"]) ?></span>
                <div>
                    <button class="btn btn-sm btn-warning editBtn" data-id="<?= $tag["id"] ?>" data-name="<?= htmlspecialchars($tag["nom"]) ?>">Modifier</button>
                    <button class="btn btn-sm btn-danger deleteBtn" data-id="<?= $tag["id"] ?>">Supprimer</button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <script>
        $(document).ready(function () {
            $("#tagForm").on("submit", function (e) {
                e.preventDefault();
                let id = $("#tag_id").val();
                let nom = $("#tag_name").val().trim();
                let action = id ? "update" : "create";

                if (nom === "") return alert("Le nom du tag est requis");

                $.post("mtd_tags_crud.php", { action, id, nom }, function (data) {
                    if (data.success) location.reload();
                }, "json");
            });

            $(".editBtn").on("click", function () {
                let id = $(this).data("id");
                let name = $(this).data("name");
                $("#tag_id").val(id);
                $("#tag_name").val(name);
                $("#updateBtn").removeClass("d-none");
                $("#cancelBtn").removeClass("d-none");
                $("button[type=submit]").addClass("d-none");
            });

            $("#updateBtn").on("click", function () {
                $("#tagForm").submit();
            });

            $("#cancelBtn").on("click", function () {
                $("#tag_id").val("");
                $("#tag_name").val("");
                $("#updateBtn").addClass("d-none");
                $("#cancelBtn").addClass("d-none");
                $("button[type=submit]").removeClass("d-none");
            });

            $(".deleteBtn").on("click", function () {
                let id = $(this).data("id");
                if (confirm("Voulez-vous vraiment supprimer ce tag ?")) {
                    $.post("mtd_tags_crud.php", { action: "delete", id }, function (data) {
                        if (data.success) location.reload();
                    }, "json");
                }
            });
        });
    </script>
</body>
</html>
