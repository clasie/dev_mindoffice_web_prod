<?php
require 'mtd_db.php';

try {
    $stmt = $pdo->query("SELECT * FROM MTD_Tags ORDER BY nom ASC");
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "tags" => $tags]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
?>
