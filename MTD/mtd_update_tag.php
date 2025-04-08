<?php
require 'mtd_db.php';

parse_str(file_get_contents("php://input"), $_PUT);

if ($_SERVER["REQUEST_METHOD"] === "PUT" && isset($_PUT["id"]) && isset($_PUT["nom"])) {
    $id = (int)$_PUT["id"];
    $nom = trim($_PUT["nom"]);

    if (!empty($nom) && $id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE MTD_Tags SET nom = :nom WHERE id = :id");
            $stmt->execute(["nom" => $nom, "id" => $id]);
            echo json_encode(["success" => true, "message" => "Tag mis à jour"]);
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Données invalides"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Requête invalide"]);
}
?>
