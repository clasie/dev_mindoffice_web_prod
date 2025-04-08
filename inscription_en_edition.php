<?php

//require 'testTokenEmail.php';
//var_dump($_POST);
/**************************
* BLOC Authentification ->
*/
require 'Connection_db.php';
require 'model/User.php';
require 'functions.php';

$user = new User
(
   $pdo,    
   $_COOKIE['email'],
   $_COOKIE['token']
);

$user->authentification();

$pseudo = $user->pseudo;  //todo passer aux appels via l'objet '$user'...
$id_user = $user->id_user;
$role = $user->role;
$compteur_posts = $user->compteur_posts;
$global_message_txt = $user->global_message_txt;
$token = $user->token;
$rep = $user->rep;
/*
* <- BLOC Authentification 
* 
**************************/

$error_message_global ="";
$error_message_pseudo = "";
$error_message_mail = "";

$db_email = "";
$db_pseudo = "";

if(isset($_POST['ok'] )) //quitter_edition
{

    /**********************************
     * aller chercher les creds en db
     */    
     $req = $pdo->prepare("
        SELECT * 
        FROM 
            users 
        WHERE 
            token = :token 
    ");
    $req->execute([
        'token' => $token 
    ]);

    if($rep['id'] != false){

        $db_email = $rep['email'];
        $db_pseudo = $rep['pseudo'];
    }
    else
    {
        throw new Exception("Creds non trouvés en DB");
    }    
    /*
    * aller chercher les creds en db
    **********************************/  

    $erreur_existe = 0;
    $pseudo_new = $_POST['pseudo_new'];
    $email_new = $_POST['email_new'];

    try
    {
        //pseudo vide
        if(strlen(trim($pseudo_new)) == 0) 
        {
            $error_message_pseudo = "Le pseudo ne peut pas être vide";
            $erreur_existe = 1;
        }
        
        if($erreur_existe)
        {
            //on affiche la page et les erreurs en dessous.
        }      
        //ok pas d'erreurs
        else
        {            
            //Test unicity du mail + mettre contrainte unicity email en DB
            if($db_email != $email_new)
            {
                $req = $pdo->prepare("
                   SELECT id 
                   FROM users 
                   WHERE email = :email LIMIT 1 
                ");

                $req->execute(
                    array(
                        'email' => $email_new
                    )
                );

                $rep = $req->fetch(PDO::FETCH_ASSOC);
                $rep['id'] ??= -1;     

                //nouveau mail déjà utilisé en db       
                if($rep['id'] != -1)
                {
                    $error_message_mail = "Le mail suivant est déjà utilisé : ".$email_new;
                    $error_message_global = "Merci de corriger vos valeurs entrées"; 
                }
                //ok, on va updater le user
                else
                {
                    $requete = $pdo->prepare("
                        UPDATE users 
                        SET 
                           pseudo = :pseudo,
                           email = :email_new 
                        WHERE                 
                          id = :id_user
                    ");

                    echo"<pre>";
                    echo "pseudo_new,".$pseudo_new;
                    echo "email_new,".$email_new;
                    echo "id_user,".$id_user;
                    echo"</pre>";

                    $requete->execute(
                        array(
                            "pseudo" => $pseudo_new,
                            "email_new" => $email_new,
                            "id_user" => $id_user 
                        )
                    );
                }
            }
            //le mail est le même => on update
            else
            {
                $requete = $pdo->prepare("
                    UPDATE users 
                    SET 
                      pseudo = :pseudo
                    WHERE                 
                      id = :id_user
                ");

                $requete->execute(
                    array(
                        "pseudo" => $pseudo_new,
                        "id_user" => $id_user
                    )
                );
            }
        }
        //pour remplir le form
        $db_email = $email_new;
        $db_pseudo = $pseudo_new;     

    }
    catch(Exception $ex)
    {
        echo $ex;
    }
}
//desinscription totale du user
else if(isset($_POST['desinscription'] )) //quitter_edition
{
   ManageDesinscription::deleteAllDataUser($pdo,$id_user);
   header("location: vousetesdesinscrit.php");
}
else
{
    /**********************************
     * aller chercher les creds en db
     */
    
    $req = $pdo->prepare("
        SELECT * 
        FROM 
            users 
        WHERE 
            token = :token 
    ");
    $req->execute([
        'token' => $token 
    ]);

    if($rep['id'] != false){

        $db_email = $rep['email'];
        $db_pseudo = $rep['pseudo'];
    }
    else
    {
        throw new Exception("Creds non trouvés en DB");
    }
    
    /*
    * aller chercher les creds en db
    **********************************/    
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="./img/exercices.png">  
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        .delete_btn {
            background-color: pink;
        }

        /* Le fond sombre du modale */
        .modal {
            display: none; /* Cache le modal par défaut */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
            padding-top: 100px;
        }

        /* Le contenu du modal */
        .modal-content {
            background-color: white;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Les boutons du modal */
        .modal-buttons {
            text-align: center;
            margin-top: 20px;
        }

        .modal-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            margin: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .modal-btn-yes {
            background-color:rgb(214, 49, 20);
            color: white;
            padding: 10px 20px;
            margin: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .modal-btn:hover {
            background-color: #45a049;
        }     
        .yes_desinscription {
            background-color:rgb(175, 132, 76);
        } 
    </style>
    <script>
    $(document).ready(function() {
        // Attacher un événement de soumission au formulaire
        $('form').on('submit', function(event) {
            // Vérifie si le bouton de désinscription est celui qui a été cliqué
            if ($('input[name="desinscription"]').is(':focus')) {
                // Empêche la soumission immédiate du formulaire
                event.preventDefault();

                // Afficher la modale de confirmation
                $('#confirmationModal').show();
            }
        });

        // Lorsque l'utilisateur clique sur "Oui"
        $('#confirmYes').on('click', function() {
            // Ajouter un champ caché avec le nom du bouton cliqué
            var inputDesinscription = $('<input>').attr({
                type: 'hidden',
                name: 'desinscription',
                value: '1' // vous pouvez mettre une valeur personnalisée si nécessaire
            });
            
            // Ajouter ce champ au formulaire
            $('form').append(inputDesinscription);
            
            // Soumettre le formulaire après confirmation
            $('form')[0].submit();
        });

        // Lorsque l'utilisateur clique sur "Non"
        $('#confirmNo').on('click', function() {
            // Fermer la modale sans soumettre le formulaire
            $('#confirmationModal').hide();
            return false;
        });

        // S'assurer que le bouton de désinscription reçoit le focus
        $('input[name="desinscription"]').on('click', function() {
            $(this).focus();
        });
    });
    </script> 
</head>
<body>
    <form method="POST" action="">
        <div id="confirmationModal" class="modal">
            <div class="modal-content">
                <h2>Confirmation</h2>
                <p>Êtes-vous sûr de vouloir vous désinscrire définitivement du site ? Cette action est irréversible.</p>
                <div class="modal-buttons">
                <button id="confirmYes" class="modal-btn-yes" >Oui</button>
                <button id="confirmNo" class="modal-btn">Non</button>
                </div>
            </div>
        </div>        
        <label class="labe_error"><?php echo $error_message_global; ?></label>

        <p><h1><img src="./img/pen.png" alt="Formulaire d'inscription"> Edition de vos credentials</h1></p>

        <label for="pseudo">Votre pseudo</label>
        <label class="labe_error" for="email"><?=$error_message_pseudo?></label> 
        <input type="text" name="pseudo_new" value="<?=$db_pseudo?>" placeholder="Entrez votre pseudo..." required>
        </br>   
        <label for="email">Votre mail</label> 
        <label class="labe_error" for="email"><?=$error_message_mail?></label> 
        <input type="email" name="email_new" required="required" value="<?=$db_email?>" placeholder="Entrez votre mail..." required>
        </br>         
        <input type="submit" value="Sauver les modifications" name="ok">
        <p>
        <a href="index.php">Retourner au questionnaire</a>
        </p>  
        <input class="delete_btn" type="submit" value="Me désinscrire définitivement du site" name="desinscription">
    </form> 
</body>
</html>

