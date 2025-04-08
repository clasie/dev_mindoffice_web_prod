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
        .checkBox{
            display: inline-flex;
            width: 16%;
            margin-bottom: 15px;
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
        div.bouton-aligne{
            display:inline-block;
            width:45%;
            margin:10px 2.5% 10px 2.5%;
            text-align:center;
        }
        .labe_error{
            color: red;
            font-size: 14px;
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

require 'Connection_db.php';

//var_dump($_POST);

//Memo values
$memo_mail = "";
$memo_password ="";
$memo_status_checked ="";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $email = $_POST['email'];
    $password = $_POST['password'];

    if($email != "" && $password != "")
    {
        $MD5Password = MD5($password);
        $req = $pdo->prepare("SELECT * FROM users WHERE email = :email AND password = :MD5Password ");
        $req->execute(['email' => $email, 'MD5Password' => $MD5Password]);
        $rep = $req->fetch(PDO::FETCH_ASSOC);
        
        if(isset($rep['id'])) // != false)
        {
            //injecter checkbox si selectionné
            $memoriser_credentials = $_POST['memoriser_credentials'];
            $memoriser_credentials = $memoriser_credentials ?? 0;    
            //echo "memoriser_credentials: ".$memoriser_credentials;

            $token = bin2hex(random_bytes(32));
            $pdo->exec("UPDATE users SET token = '$token', memoriser_creds = '$memoriser_credentials' WHERE email = '$email' ");
            setcookie("token", $token, time() + 3600);
            setcookie("email", $email, time() + 3600);

            //checkbox checked
            if($memoriser_credentials)
            {
                setcookie("memo_email", $email, time() + 365*24*3600);
                setcookie("memo_password", $password,time() + 365*24*3600);
                setcookie("memo_memoriser_credentials", "checked", time() + 365*24*3600);
            
            }else{
                setcookie("user_id", "", time() - 7200);
                setcookie("memo_email", "", time() - 7200);
                setcookie("memo_password", "",time() - 7200);
                setcookie("memo_memoriser_credentials", "", time() - 7200);            
            }
            header("location: index.php");
            exit();
        }else{
            $error_msg = "email ou password incorrects";
        }
    }
} 
//Tester l'existence des cookies memory
else
{
    //Memo values
    $memo_mail =  $_COOKIE['memo_email'] ?? "";
    $memo_password = $_COOKIE['memo_password'] ?? "";
    $memo_status_checked = $_COOKIE['memo_memoriser_credentials'] ?? "";
/*
    echo $memo_mail;
    echo $memo_password;
    echo $memo_status_checked; 
*/
}  

?>

<form method="POST" action="">
        
        <p><h1><img src="./img/connexion.png" alt="Formumlaire d'inscription"> Formulaire de connexion </h1></p>

        <?php 
        if($error_msg){
        ?>
        <p>
           <label class="labe_error"><?php echo $error_msg; ?></label>
        </p>
        <?php      
        }
        ?>

        <label for="email">Votre mail</label>
        <input type="email" name="email" value="<?echo $memo_mail?>" placeholder="Entrez votre mail...">
        </br>  
        <label for="password">Votre mot de passe</label>
        <input type="password" value="<?echo $memo_password?>" name="password" placeholder="Entrez votre mot de passe...">
        </br>         
        <div> 
            <input type="submit" value="Se connecter"  name="ok"> 
            <input class="checkBox" <?echo $memo_status_checked?> type="checkbox" id="memoriser_credentials" name="memoriser_credentials" value="1">
            <label class="checkBox" for="vehicle1">Mémoriser</label>
        </div>
        <p>
        <label class="message1">Si vous ne possédez pas de compte cliquez ci-dessous ... </label>            
        </p>          
        <p>
            <a class="message2" href="inscription.php">Créer un compte gratuitement</a>
        </p>      

</form>

</body>
</html>