<?php
require 'Connection_db.php';

$email = $_COOKIE['email'];
$token = $_COOKIE['token'];
$pseudo = "";
$id_user = "";
$role = "";
$compteur_posts = "";
$global_message_txt ="";

/**
 * config
 */
$req = $pdo->prepare("SELECT * FROM config");
$req->execute();
$rep = $req->fetch(PDO::FETCH_ASSOC);
$status_maintenance = $rep['status_maintenance'];

//echo "status_maintenance :" .$status_maintenance;
//echo "role :".$role;

/**
 * site blocked for everybody
 */
if($status_maintenance == 2)
{
    header("location: site_ferme.php");
    exit();
}

if($token)
{
    $req = $pdo->prepare("
        SELECT * 
        FROM 
           users 
        WHERE 
           token = :token 
    ");
    //email = :email AND token = :token 
    $req->execute([
        //'email' => $email, 
        'token' => $token 
    ]);
    $rep = $req->fetch(PDO::FETCH_ASSOC);

    if($rep['id'] != false)
    {
        //on refresh le timeout des token et email (1h)
        setcookie("token", $token, time() + 3600);
        setcookie("email", $email, time() + 3600);
        $pseudo = $rep['pseudo'];
        $id_user = $rep['id'];
        $compteur_posts = $rep['compteur_posts'];
        $global_message_txt = $rep['global_message'];
        $role = $rep['role'];

        /**
         * site accessible only for admins (role = 5)
         */
        if($status_maintenance == 1)
        {
            //site en maintyenance avec acces uniquement admin
            if($role == 5){
                //ok c'est bon on laisse continuer
            }
            else
            {
                header("location: site_en_maintenance.php");
                exit();
            }
        }        
    }
    else
    {
        header("location: login.php");
        exit();
    }
}
else
{
    header("location: login.php");
    exit();
}
?>
