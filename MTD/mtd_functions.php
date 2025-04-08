<?php
require 'mtd_config.php';

/* --- MÉTADONNÉES (déjà présentes) --- */
function createMetadata($nom, $valeur, $domaine_id) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO MTD_MetaData (nom, valeur, domaine_id) VALUES (?, ?, ?)");
    return $stmt->execute([$nom, $valeur, $domaine_id]);
}

function getAllMetadata() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM MTD_MetaData");
    return $stmt->fetchAll();
}

function updateMetadata($id, $nom, $valeur) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE MTD_MetaData SET nom = ?, valeur = ? WHERE id = ?");
    return $stmt->execute([$nom, $valeur, $id]);
}

function deleteMetadata($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM MTD_MetaData WHERE id = ?");
    return $stmt->execute([$id]);
}

/* --- UTILISATEURS --- */
function createUser($username, $password) {
    global $pdo;
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO MTD_Users (username, password) VALUES (?, ?)");
    return $stmt->execute([$username, $hashedPassword]);
}

function getAllUsers() {
    global $pdo;
    $stmt = $pdo->query("SELECT id, username FROM MTD_Users");
    return $stmt->fetchAll();
}

function deleteUser($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM MTD_Users WHERE id = ?");
    return $stmt->execute([$id]);
}

/* --- RÔLES --- */
function createRole($nom) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO MTD_Roles (nom) VALUES (?)");
    return $stmt->execute([$nom]);
}

function getAllRoles() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM MTD_Roles");
    return $stmt->fetchAll();
}

function assignRoleToUser($user_id, $role_id) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO MTD_UserRoles (user_id, role_id) VALUES (?, ?)");
    return $stmt->execute([$user_id, $role_id]);
}

function deleteRole($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM MTD_Roles WHERE id = ?");
    return $stmt->execute([$id]);
}

/* --- PERMISSIONS --- */
function createPermission($nom) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO MTD_Permissions (nom) VALUES (?)");
    return $stmt->execute([$nom]);
}

function assignPermissionToRole($role_id, $permission_id) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO MTD_RolePermissions (role_id, permission_id) VALUES (?, ?)");
    return $stmt->execute([$role_id, $permission_id]);
}
function getAllMetadataWithHierarchy() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT m1.id, m1.nom, m1.valeur, m1.parent_id, m2.nom AS parent_nom
        FROM MTD_MetaData m1
        LEFT JOIN MTD_MetaData m2 ON m1.parent_id = m2.id
    ");
    return $stmt->fetchAll();
}
function getAllDomainsWithHierarchy() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT d1.id, d1.nom, d1.parent_id, d2.nom AS parent_nom
        FROM MTD_Domaines d1
        LEFT JOIN MTD_Domaines d2 ON d1.parent_id = d2.id
    ");
    return $stmt->fetchAll();
}
function addTagToDomaine($domaine_id, $tag_name) {
    global $pdo;

    // Vérifier si le tag existe, sinon l'ajouter
    $stmt = $pdo->prepare("INSERT IGNORE INTO MTD_Tags (nom) VALUES (:tag_name)");
    $stmt->execute(['tag_name' => $tag_name]);

    // Récupérer l'ID du tag
    $stmt = $pdo->prepare("SELECT id FROM MTD_Tags WHERE nom = :tag_name");
    $stmt->execute(['tag_name' => $tag_name]);
    $tag_id = $stmt->fetchColumn();

    // Associer le tag au domaine
    $stmt = $pdo->prepare("INSERT IGNORE INTO MTD_DomaineTags (domaine_id, tag_id) VALUES (:domaine_id, :tag_id)");
    $stmt->execute(['domaine_id' => $domaine_id, 'tag_id' => $tag_id]);
}
function getTagsForDomaine($domaine_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT t.nom 
        FROM MTD_Tags t
        JOIN MTD_DomaineTags dt ON t.id = dt.tag_id
        WHERE dt.domaine_id = :domaine_id
    ");
    $stmt->execute(['domaine_id' => $domaine_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
function addTagToMetadata($metadata_id, $tag_name) {
    global $pdo;

    // Vérifier si le tag existe, sinon l'ajouter
    $stmt = $pdo->prepare("INSERT IGNORE INTO MTD_Tags (nom) VALUES (:tag_name)");
    $stmt->execute(['tag_name' => $tag_name]);

    // Récupérer l'ID du tag
    $stmt = $pdo->prepare("SELECT id FROM MTD_Tags WHERE nom = :tag_name");
    $stmt->execute(['tag_name' => $tag_name]);
    $tag_id = $stmt->fetchColumn();

    // Associer le tag à la métadonnée uniquement si l'association n'existe pas déjà
    $stmt = $pdo->prepare("INSERT IGNORE INTO MTD_MetaDataTags (metadata_id, tag_id) VALUES (:metadata_id, :tag_id)");
    $stmt->execute(['metadata_id' => $metadata_id, 'tag_id' => $tag_id]);
}


?>
