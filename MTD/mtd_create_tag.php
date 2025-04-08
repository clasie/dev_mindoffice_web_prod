<?php
require 'mtd_db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["nom"])) {
    $nom = trim($_POST["nom"]);

    if (!empty($nom)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO MTD_Tags (nom) VALUES (:nom)");
            $stmt->execute(["nom" => $nom]);
            echo json_encode(["success" => true, "message" => "Tag ajouté avec succès"]);
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Le nom du tag ne peut pas être vide."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Requête invalide."]);
}
?>
