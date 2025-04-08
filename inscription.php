<?php

include 'Connection_db.php';

$error_message_global ="";
$error_message_pseudo = "";
$error_message_password = "";
$error_message_mail = "";

$pseudo = "";
$password= "";
$email = "";

$user_well_recorded = 0;

if(isset($_POST['ok']))
{
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

    if($rep == false )
    {
        $rep = array('id' => -1);
        //$rep['id'] = -1;
        //echo "est false)";
    }
    else
    {
        //echo "PAS false)";
    }
    //var_dump($rep );
    //$rep['id'] ??= -1;

   try{

        //pseudo vide
        if(strlen(trim($pseudo)) == 0) 
        {
            $error_message_pseudo = "Le pseudo ne peut pas être vide";
            $error_message_global = "Merci de corriger vos valeurs entrées";   
        }
        //pw vide
        if(strlen(trim($password)) == 0) 
        {
            $error_message_password = "Le password ne peut pas être vide";
            $error_message_global = "Merci de corriger vos valeurs entrées";   
        }  

        //Error
        if($rep['id'] != -1){

            //echo "email existe déjà";
            $error_message_mail = "Le mail suivant est déjà utilisé : ".$email;       
            $error_message_global = "Merci de corriger vos valeurs entrées";   
        }
        //OK
        if(strlen($error_message_global) == 0)
        {
            //INSERT
            $requete = $pdo->prepare("
                  INSERT INTO users 
                     VALUES(0, :pseudo, :nom, :prenom, MD5(:password), :email, :token, :role, :memoriser_creds, :compteur_posts, :global_message)
               ");
            $requete->execute(
                array(
                    "pseudo" => $pseudo,
                    "nom" => NULL,
                    "prenom" => NULL,
                    "password" => $password,
                    "email" => $email,
                    "token" => NULL,
                    "role" => 0,
                    "memoriser_creds" => NULL,
                    "compteur_posts" => NULL,
                    "global_message" => NULL
                )
            );
            $user_well_recorded = 1;
            //header("location: login.php");
            //exit();
        }
    }catch(Exception $ex){
        echo $ex;
    }
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
        .message1{
            color: green;
            font-size: 14px;
        }
        .message2{
            color: black;
            font-size: 14px;
        }           
    </style>
</head>

<body>
    <?php
    if($user_well_recorded == 0)
    {
    ?>
    <form method="POST" action="">

        <label class="labe_error" ><?php echo $error_message_global; ?></label>

        <p><h1><img src="./img/pen.png" alt="Formumlaire d'inscription"> Création d'un nouvel utilisateur </h1></p>

        <label for="pseudo">Votre pseudo</label>
        <label  class="labe_error" for="email"><?=$error_message_pseudo?></label> 
        <input type="text" name="pseudo" value="<?=$pseudo?>" placeholder="Entrez votre pseudo..." required >
        </br>   
        <label for="email">Votre mail</label> 
        <label  class="labe_error" for="email"><?=$error_message_mail?></label> 
        <input type="email" name="email" required="required" value="<?=$email?>" placeholder="Entrez votre mail..." required>
        </br>  
        <label for="password">Votre mot de passe</label>
        <label  class="labe_error" for="email"><?=$error_message_password?></label> 
        <input type="password" name="password" value="<?=$password?>" placeholder="Entrez votre mot de passe..." required>
        </br>         
        <input type="submit" value="M'inscrire"  name="ok">
        <p>
        <a href="login.php">Retourner à la page de login</a>
        </p>  
    </form>

    <?php
    }
    else
    {
    ?>
        <form method="POST" action="">
        <p>
        <label class="message1">Félicitation, enregistrement réussi </label>            
        </p>          
        <p>
            <a class="message2" href="index.php">Se rendre à présent sur la page du login...</a>
        </p>   
        </form>
    <?php
    }
    ?>
</body>
</html>

