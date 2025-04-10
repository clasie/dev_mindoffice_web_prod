<?php
/*
TODO
   1- le cron construit l'url + token + les injecte dans DB + crée et send le mail
      Vérifier si il y en a ou pas ...
      ... si le flag mail_pris_en_compte est 1 => on ne fait rien.
   2- la page du jour quand elle se charge
   2.1 Si l'id de la question existe déjà => on charge les data et la page
   2.1.   Et commence par chercher les question dispo avec le niveau de diff le + grand.
   2.2 SiNon => la page va chercher l'id, fait + 1 et charge la page
*/
require "Connection_db.php"; 

$my_url_de_base = $url_de_base;
$my_url_de_base_descinscription = $url_de_base_desinscription;
/**
 * 1- On va voir si il y a des candidats au cron dans 'cor_user_questionnaire'
 */
$stmt = $pdo->prepare("
   SELECT * FROM cor_user_questionnaire
   WHERE cron_mailing_flag = 1
");
$stmt->execute();
$cor_user_questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

//on a trouvé des candidats
foreach ($cor_user_questionnaires as $cor_user_questionnaire)
{
   echo "user_id ".$cor_user_questionnaire['user_id'];
   echo "questionnaire_id ".$cor_user_questionnaire['questionnaire_id'];
   echo "cron_mailing_flag ".$cor_user_questionnaire['cron_mailing_flag'];

   //ajouter une entrée dans 'cron_mailing' si elle n'existe pas déjà
   //comme candidate

   //test d'existence
   $stmt = $pdo->prepare("
      SELECT * 
      FROM cron_mailing 
      WHERE 
         id_user = :id_user AND
         id_questionnaire = :id_questionnaire AND
         token IS NULL AND
         mail_pris_en_compte = 0
   ");
   
   $stmt->execute
   ([
      'id_user' => $cor_user_questionnaire['user_id'],
      'id_questionnaire' => $cor_user_questionnaire['questionnaire_id']
   ]);
   
   $cron_mailings_exist = $stmt->fetchAll(PDO::FETCH_ASSOC);

   echo "<pre> cron_mailing ";
   var_dump($cron_mailings_exist);
   echo "</pre>";

   if($cron_mailings_exist)
   {
      echo " element exists <br>";
   }
   else{
      echo " element do not exists <br>";
      $stmt = $pdo->prepare("
         INSERT INTO cron_mailing(id_user,id_questionnaire,pseudo_user,questionnaire_label)
         VALUES(:id_user, :id_questionnaire, :pseudo_user, :questionnaire_label);
      ");
      
      $stmt->execute
      ([
         'id_user' => $cor_user_questionnaire['user_id'],
         'id_questionnaire' => $cor_user_questionnaire['questionnaire_id'],
         'pseudo_user' 
            => isset($cor_user_questionnaire['user_name']) ? $cor_user_questionnaire['user_name']: "" ,
         'questionnaire_label' 
            => isset($cor_user_questionnaire['questionnaire_label']) ? $cor_user_questionnaire['questionnaire_label'] : ""
      ]);      
   }
}

/**
 * 2- On va construire et lancer les mails
 */
//recup questionaires et user
$stmt = $pdo->prepare("
   SELECT * FROM cron_mailing 
   WHERE mail_pris_en_compte = 0
");
$stmt->execute();
$cron_mailings = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre> cron_mailing ";
var_dump($cron_mailings);
echo "</pre>";

//loop sur les mails à creer envoyer
foreach ($cron_mailings as $cron_mailing)
{
   $token = bin2hex(random_bytes(32));

   //prendre les elements d'une config
   $url_for_mail = $my_url_de_base;
   $url_for_mail .= $token;

   $url_for_mail_desinscription = $my_url_de_base_descinscription;
   $url_for_mail_desinscription .= $token;

   $stmt = $pdo->prepare("
      UPDATE cron_mailing 
      SET 
         token = :token, 
         mail_pris_en_compte = 1,
         url_complete = :url_complete
      WHERE id = :id
   ");
   $stmt->execute([
      'token' => $token,
      'id' => $cron_mailing['id'],
      'url_complete' => $url_for_mail
   ]);
   //prendre les elements d'une config
   echo "<pre>";
   echo $url_for_mail;
   echo "<pre>";

   echo "<pre>";
   echo $url_for_mail_desinscription;
   echo "<pre>";

   //aller chercher le mail du user
   $stmt = $pdo->prepare("
      SELECT * FROM users 
      WHERE id = :id_user
   ");
   $stmt->execute([
      'id_user' => $cron_mailing['id_user']
   ]);
   $users = $stmt->fetch(PDO::FETCH_ASSOC);

   echo "<pre> users  1.0";

   var_dump($users);
   var_dump($users['email']);
   
   echo "</pre>";

   $date = new DateTime();
   $fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
   $fmt->setPattern('EEEE dd MMMM yyyy'); // Format : lundi 12 septembre 2025

   $objet = "Bonjour ".$users['pseudo']. ", voici votre question du jour de ce " . $fmt->format($date);
   if(isset($users['email']))
   {
      $email_destinataire = $users['email'];
      sendMailTest($email_destinataire, $url_for_mail, $objet,$domaine_name, $url_for_mail_desinscription);
   }
   else{
      echo "mail not found";
   }
}

//-----------------------
$log_message = ""; 
$error_message = "vide";

function log_in_db($pdo, $log_message, $error_message)
{

   $t = time();
   $today = date("Y-m-d", $t);
   
   $stmt = $pdo->prepare("
      INSERT INTO cron_call_log(date_reception,error_message,log_message)
      VALUES(now(), :error_message, :log_message);
   ");
   
   $stmt->execute(
    [
        'error_message' => $error_message,
        'log_message' => $log_message
    ]
   ); 
}

function sendMailTest($email_destinataire, $url_destination, $subject, $domaine_name, $url_for_mail_desinscription)
{
   //$to = "claudesiefers@gmail.com"; 
   $to = $email_destinataire; //"claude_siefers@hotmail.com";
   //$to = "claudesiefers@gmail.com"; 
   $subject = $subject; //"Test 11.0 mail PHP HTML";

   $html_content_debut = <<<MySQL_QUERY
      <!DOCTYPE html5>
      <html lang="en">
      <head>
         <meta charset="UTF-8">
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
         <title>Dynamic Question Form</title>       
      </head>
      <body style="background-image: url(https://www.{$domaine_name}/accessibility/img/fond_mail_cron2.png);background-repeat: no-repeat">
      <br><br><br><br><br><br><br><br><br>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <a href="
      MySQL_QUERY;

   $html_content_milieu = $url_destination;

   $html_content_fin = <<<MySQL_QUERY
      ">Ma question du jour ...</a>       
      <br><br><br><br><br><br><br><br><br><br><br><br><br>
   MySQL_QUERY;

   //---------------
   $html_content_url_desinscription = <<<MySQL_QUERY
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <a href="
      MySQL_QUERY;

   $html_content_milieu_desinscription  = $url_for_mail_desinscription;

   $html_content_fin_desinscription  = <<<MySQL_QUERY
      ">Me désinscrire ...</a>  
      </body>
      </html>
   MySQL_QUERY;   
   //---------------
   $html_global = $html_content_debut.
                  $html_content_milieu.
                  $html_content_fin.
                  $html_content_url_desinscription.
                  $html_content_milieu_desinscription.
                  $html_content_fin_desinscription;

   echo "<pre> html_global ";
   echo $html_global;
   echo "</pre> ";


   $content = $html_global; 

   //$content = "tutu";
   $headers = "MIME-Version: 1.0\r\n";
   $headers .= "Content-Type: text/html; charset=UTF-8\r\n";   
   //$headers .= "Content-type:text/html;charset=UTF-8" . "\r\b";

   $headers .= "From: ".$domaine_name ." \r\nReply-To: info@".$domaine_name;

   echo $headers;
   
   if (mail($to, $subject, $content, $headers)) //remettre ceci
      echo "The email has been sent successfully! 1.0";
   else
      echo "Email did not leave correctly!";
}
      