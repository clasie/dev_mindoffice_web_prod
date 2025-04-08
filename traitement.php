<?php

include 'Connection_db.php';

if(isset($_POST['ok'])){
    //var_dump($_POST);
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $pseudo = $_POST['pseudo'];
    $password= $_POST['password'];
    $email = $_POST['email'];

    //Test unicity du mail + mettre contrainte unicity email en DB
    $req = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1 ");
    $req->execute(
        array(
            'email' => $email
        )
    );
    $rep = $req->fetch(PDO::FETCH_ASSOC);

    $rep['id'] ??= -1;

    //echo $rep['id'] ;

    try{
        //Error
        if($rep['id'] != -1){
            //echo "email existe déjà";
            header("location: inscription.php?error_email=email_already_exists");
            exit();
        }
        //OK
        else
        {
            //INSERT
            $requete = $pdo->prepare("INSERT INTO users VALUES(0,:pseudo, :nom, :prenom, MD5(:password), :email, :token, :role , :memoriser_creds)");
            $requete->execute(
                array(
                    "pseudo" => $pseudo,
                    "nom" => $nom,
                    "prenom" => $prenom,
                    "password" => $password,
                    "email" => $email,
                    "token" => NULL,
                    "role" => NULL,
                    "memoriser_creds" => NULL
                )
            );
            header("location: login.php");
            exit();
        }
    }catch(Exception $ex){
        echo $ex;
    }

    //echo "Inscription réussie!";
    //header("Location: xxx.php");
}