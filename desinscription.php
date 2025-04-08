<?php
/**
 * script qui retire un user et toutes ses données.
 * 
 * Si c'est un GET on verifie le token et son expiration
 * Si c'est un post on vérifie que le user est bien loggé.
 * 
 * On retire
 * 
 * - les cookies
 * - toutes les entrées
 * 
 */
include 'Connection_db.php';
include 'functions.php';

$error_message_global ="";
$error_message_pseudo = "";
$error_message_password = "";
$error_message_mail = "";

$pseudo = "";
$password= "";
$email = "";

$token_to_delete_value = "";

$result_message = "";
$is_a_post = 0;
/**
 * afficher la demande de confirmation de desinscription
 */
if(isset($_POST['token_to_delete_name']))
{
    $result_message 
       = ManageDesinscription::tryDeleteWithToken($pdo, $_POST['token_to_delete_name']);
       /*
       echo "<pre> dans le POST ... ";
       echo $result_message ;
       echo "<pre>";       
       */
      //retirer le bouton
      $is_a_post = 1;
}
else if(isset($_GET['token']))
{
    $token_to_delete_value = $_GET['token'];
    /*
    echo "<pre> dans le GET ... ";
    echo $token_to_delete_value;
    echo "<pre>";
    */
}
else
{
    $error_message_global = "Le token n'est pas présent.";
    /*
    echo "<pre> dans le POST ... ";
    echo $$error_message_global ;
    echo "<pre>";       
    */
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="./img/exercices.png">  
    <title>Dynamic Question Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;

            color:black;
            background-color:white;
            background-image:url(img/fond_2.png);            
        }     
        form {
            max-width: 600px;
            margin: auto;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        label, select, input {
            display: block;
            width: 100%;
            margin-bottom: 15px;
        }
        .labe_error{
            color: red;
            font-size: 14px;
        }
        input[type="submit"] {
            width: auto;
            padding: 10px 15px;
        }
        .readonly-content {
            padding: 10px;
            background-color: #eee;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 15px;
            white-space: pre-wrap; /* Conserve les retours à la ligne */
            word-wrap: break-word; /* Coupe les longs mots si nécessaire */
        }
    </style>
</head>

<body>
    <form method="POST" action="">
        <h1><img src="./img/pen.png" alt="Formumlaire de désinscription"> Désinscription </h1>
        <!-- <label class="labe_error" ><?=$error_message_global?></label> -->
        <label ><?=$result_message?> </label> 
        <?php
        if($is_a_post == 0)
        {
        ?>
        <input type="hidden" id="token_to_delete_id" name="token_to_delete_name" value="<?=$token_to_delete_value?>">
        <input type="submit" value="Me désinscrire totalement du site"  name="token_to_delete">
        <?php
        }
        ?>        
        <!-- <a href="login.php">Retourner à la page de login</a> -->
    </form>        
</body>
</html>

