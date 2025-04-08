<?php
require 'mtd_db.php';

parse_str(file_get_contents("php://input"), $_DELETE);

if ($_SERVER["REQUEST_METHOD"] === "DELETE" && isset($_DELETE["id"])) {
    $id = (int)$_DELETE["id"];

    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM MTD_Tags WHERE id = :id");
            $stmt->execute(["id" => $id]);
            echo json_encode(["success" => true, "message" => "Tag supprimé"]);
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "ID invalide"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Requête invalide"]);
}
?>
