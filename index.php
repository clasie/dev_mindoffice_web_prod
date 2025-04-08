<?php

//pour la generation du qr code
include  "library/phpqrcode/qrlib.php"; 
include  "library/phpqrcode/qrconfig.php"; 

//echo $_SERVER["HTTP_USER_AGENT"] ;
/**
 * - cas 0 - appel qui n'arrive jamais
 * - cas 1 - appel changement questionnaire  
 * - cas 2 - appel chargement initial non POST du formulaire 
 * - cas 3 - appel d'une réponse 
 * - cas 6 - appel question suivante 
 * - cas 8 - appel inconnu
 * - cas 9 - appel changement question  
 */

/**************************
 * BLOC Debuger
 */
require 'ci.php';

/**************************
 * BLOC Authentification ->
 */
require 'Connection_db.php';
require 'model/User.php';

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
/*
 * <- BLOC Authentification 
 * 
 **************************/

 /**************************
 * BLOC Business
 */
require 'functions.php';

/**************************
 * sauver les datas liées 
 * au contexte du user afin de tracer
 * les activités
 */

/*
$access = new Access();
$access->pdo = $pdo;
$access->user_id     = $user->id_user;   //null
$access->user_mail   = $user->email;     //null
$access->user_name   = $user->pseudo;    //null
$access->type_access = "QR";
$access->collectData();
$access->saveData();
*/

$systemLabels =  System::loadSystemLabels($pdo);

//insérer l'amail du user
$systemLabels['inscrit_daily_question_btn'] = 
   StringManagement::replaceString
      ('__EMAIL_USER__', $user->email, $systemLabels['inscrit_daily_question_btn']);

//$inscrit_daily_question_btn = $systemLabels['inscrit_daily_question_btn'];
//$inscrit_daily_question_btn = str_replace("__EMAIL_USER__", $user->email ,$inscrit_daily_question_btn);
//$systemLabels['inscrit_daily_question_btn'] = $inscrit_daily_question_btn;

//var_dump($systemLabels['inscrit_daily_question_btn'] );
 

//inscrit_daily_question_btn 
//pas_inscrit_daily_question_btn
//$email

/*
echo "<pre>";
var_dump($systemLabels);
echo "</pre>";
*/
$current_questionnaire_cron_mailing_flag = '0';
$tableauDesNiveausDeDificultes = ManageQuestion::chargerLesNiveausDeDifficultes($pdo);
$nombre_total_de_questions = 0;

$filtre_niveau_difficulte = "";
$bool_filtre_niveau_difficulte = false;
$AND_filtre_niveau_difficulte = "";

//!= '1' car '1' c'est l'ID de tous les niveaux!
if(isset($_POST['filtre_niveau_difficulte']) && $_POST['filtre_niveau_difficulte'] != '1')
{
   //echo "OK filtre_niveau_difficulte ".$_POST['filtre_niveau_difficulte'];
   $filtre_niveau_difficulte = $_POST['filtre_niveau_difficulte'];
   $AND_filtre_niveau_difficulte = " AND cor.difficulty_level = :filtre_niveau_difficulte ";
   $bool_filtre_niveau_difficulte = true;
}
else{
    //echo "KO filtre_niveau_difficulte ";
    $filtre_niveau_difficulte = "";
    $bool_filtre_niveau_difficulte = false;
    $AND_filtre_niveau_difficulte = "";    
}

$question = null;
$context_response = false;
// Récupération des questionnaires disponibles
$questionnaires = $pdo->query("
    SELECT id, questionnaire_name, description 
    FROM questionnaires
    WHERE actif = 1 
    ORDER BY ordre_affichage ASC
")->fetchAll(PDO::FETCH_ASSOC);

//recup des niveaux de difficulté
$niveaux_de_dificultes = $pdo->query("SELECT id, value, name FROM niveaux_de_difficulte ORDER BY value ASC")->fetchAll(PDO::FETCH_ASSOC);

// Détermination de la questionnaires courante
$current_questionnaire_id = $_POST['questionnaire_id'] ?? $questionnaires[0]['id'];

$stmt = $pdo->prepare("
   SELECT 
        questionnaire_name, 
        description,
        cron_mailing_flag,
        quest.id questionnaire_id

   FROM questionnaires quest

   LEFT JOIN cor_user_questionnaire cor
   ON cor.questionnaire_id = quest.id
   AND cor.user_id = :id_user   

   WHERE quest.id = :questionnaire_id 
");
$stmt->execute([
   'questionnaire_id' => $current_questionnaire_id,
   'id_user' => $id_user
]);
$current_questionnaire = $stmt->fetch(PDO::FETCH_ASSOC);

if(
    isset($_POST['definir_niveau_difficulte']) && 
    isset($_POST['question_id'])
)
{
    ManageQuestion::sauverChangementsLorsAppelQuestionSuivante(
        $pdo,
        $id_user,
        $_POST['question_id'], 
        $_POST['definir_niveau_difficulte']
    );
}

$stmt = $pdo->prepare("
    SELECT 
        quest.id id, 
        quest.question_text question_text, 
        COALESCE(cor.times_asked, 0) AS timeAsked, 
        COALESCE(cor.difficulty_level,1)AS difficulty_level,
        quest.questionnaire_id

    FROM questions quest
    LEFT JOIN cor_user_question cor
    ON quest.id = cor.question_id 
    AND cor.user_id = :id_user

    WHERE quest.questionnaire_id = :questionnaire_id
    $AND_filtre_niveau_difficulte
    ORDER BY COALESCE(cor.times_asked, 0) ASC, id ASC
");

if($bool_filtre_niveau_difficulte)
{
    //echo 1;
    $stmt->execute(
        [
         'questionnaire_id' => $current_questionnaire_id,
         'id_user' => $id_user ,
         'filtre_niveau_difficulte' => $filtre_niveau_difficulte
        ]
    );
}
else{
    //echo 2;
    $stmt->execute(
        [
        'questionnaire_id' => $current_questionnaire_id,
         'id_user' => $id_user 
        ]
    );
}

$available_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
$nombre_total_de_questions = $stmt->rowCount();

//pagination
$total_one_elements = 
   Counter::countQuestionPosition
   (
        $pdo, 
        $id_user, 
        $current_questionnaire_id, 
        $filtre_niveau_difficulte, 
        $nombre_total_de_questions
    ); 
//$page_courante = $nombre_total_de_questions - $total_one_elements +1;
$page_courante = $total_one_elements; // +1;
$affichage_pagination = $page_courante."/".$nombre_total_de_questions;
/*
echo "<pre>";
//var_dump($available_questions);
echo "<pre>nombre_total_de_questions : ".$nombre_total_de_questions."</pre>";
echo "<pre>total_one_elements : ".$total_one_elements."</pre>";
echo "<pre>page_courante : ".$page_courante."</pre>";
echo "<pre>affichage_pagination : ".$affichage_pagination."</pre>";
echo "</pre>";
*/

// Gestion de l'affichage de la question et de la réponse
$question = null;
$answer = null;

//Appel changement de questionnaire OU manuellement de question
if (
    isset($_POST['questionnaire_id']) && 
   !isset($_POST['next_question'])    &&
   !isset($_POST['show_answer'])) {

    if(isset($_POST['hidden_questionaire_id']) && $_POST['hidden_questionaire_id'] != $current_questionnaire_id )
    {
       // echo "ln(192) - > **************************** changement questionnaire  - cas 1 -";

        //echo "------> post changed: ".$_POST['daily_question_cron_cbx'] ;
        //var_dump("Cas Appel changement de questionnaires OU manuellement de question");
        $question = ManageQuestion::chargementLorsqueJusteLaQuestionEstDemandee(
            $pdo, 
            $current_questionnaire_id, 
            $id_user,
            $AND_filtre_niveau_difficulte, 
            $bool_filtre_niveau_difficulte,  
            $filtre_niveau_difficulte,
            $answer
        );
        GlobalMessage::sauverGlobalMessage($id_user, $pdo);
        //gestion cron flag
        $_POST['daily_question_cron_cbx'] = 0; //car ne pas tenir compte de l'historique du questionnaire précédent.
    }
    elseif(isset($_POST['hidden_questionaire_id']) && $_POST['hidden_questionaire_id'] == $current_questionnaire_id )
    {
        //echo "ln(207) - > **************************** changement question  - cas 9 -";
        //var_dump("Cas Appel changement de questionnaires OU manuellement de question");
        $question = ManageQuestion::chargementLorsqueJusteLaQuestionEstDemandee(
            $pdo, 
            $current_questionnaire_id, 
            $id_user,
            $AND_filtre_niveau_difficulte, 
            $bool_filtre_niveau_difficulte,  
            $filtre_niveau_difficulte,
            $answer
        );
        GlobalMessage::sauverGlobalMessage($id_user, $pdo);      
    }     
}
//Appel d'une réponse
elseif (isset($_POST['question_id']) && !isset($_POST['next_question'])) {

    //echo "ln(224) - > **************************** Appel d'une réponse - cas 3 -";
    GlobalMessage::sauverGlobalMessage($id_user, $pdo);

    $context_response = true;
    //var_dump("IF -> Cas Appel d'une réponse");
    // Une question spécifique a été sélectionnée ou affichée
    $question_id = (int)$_POST['question_id'];

    // Récupération des détails de la question
    $stmt = $pdo->prepare("
        SELECT 
            quest.id            id, 
            quest.question_text question_text, 

            COALESCE(cor.times_asked, 0) AS timeAsked, 
            COALESCE(cor.difficulty_level,1)AS difficulty_level,

            COALESCE(mess.message, '') message_txt,
            mess.partage_avec_admin    partage_avec_admin

        FROM questions quest

        LEFT JOIN cor_user_question cor
        ON quest.id = cor.question_id 
        AND cor.user_id = :id_user
        
        LEFT JOIN messages mess
        ON  quest.id = mess.question_id 
        AND mess.user_id = :id_user

        WHERE quest.id = :question_id 
        $AND_filtre_niveau_difficulte
        ORDER BY COALESCE(cor.times_asked, 0) ASC, id ASC
    ");
    
    if($bool_filtre_niveau_difficulte)
    {
        $stmt->execute(
            [
            'question_id' => $_POST['question_id'],
            'id_user' => $id_user ,
            'filtre_niveau_difficulte' => $filtre_niveau_difficulte
            ]
        );
    }
    else{
        $stmt->execute(
            [
            'question_id' => $question_id,
            'id_user' => $id_user 
            ]
        );
    } 

    $question = $stmt->fetch(PDO::FETCH_ASSOC);
    //$nombre_total_de_questions = $stmt->rowCount();

    //echo "<pre>";
    //var_dump($question);
    //echo "</pre>";

    // Récupération de la réponse si demandée
    if (isset($_POST['show_answer'])) 
    {
        //var_dump("Récupération de la réponse si demandée");
        $stmt = $pdo->prepare("
           SELECT 
              id, 
              answer_text,
              answer_text_more
           FROM answers 
           WHERE question_id = :question_id
        ");
        $stmt->execute(['question_id' => $question_id]);
        //$answer = $stmt->fetchColumn();
        $answer = $stmt->fetch(PDO::FETCH_ASSOC);
/*
        echo "<pre>";
        var_dump($answer);
        echo "</pre>";
*/
        $stmt = $pdo->prepare("
            SELECT id
            FROM cor_user_question cor
            WHERE cor.question_id = :question_id  
            AND user_id = :user_id 
        ");
        $stmt->execute(
            [
            'question_id' => $question_id,
            'user_id' => $id_user 
            ]
        );  

        $cor_res = $stmt->fetch(PDO::FETCH_ASSOC);

        if(isset($_POST['questionnaire_id']))
        {
            $questionnaire_id = $_POST['questionnaire_id'];
        }
        else
        {
            $questionnaire_id = -1;
        }

        $cor_res_id = $cor_res['id'] ?? -1;

        /**
         * cor_user_question
         */

        //INSERT cor_user_question, car pas encore dans la table 'cor_user_question' - //todo_modif_counter
        if($cor_res_id == -1)
        {
            
            $stmt = $pdo->prepare("
               INSERT INTO cor_user_question(user_id, question_id,times_asked,questionnaire_id)
               VALUES(:id_user,:question_id,:times_asked,:questionnaire_id);
            ");
            $stmt->execute(
                [
                    'id_user' => $id_user,    
                    'question_id' => $question_id,
                    'times_asked' => '1',
                    'questionnaire_id' => $questionnaire_id
                ]
            );
        }
        //UPDATE cor_user_question
        else
        {
            
            //todo_modif_counter
            $stmt = $pdo->prepare("
               UPDATE cor_user_question 
                  SET times_asked = 2 , questionnaire_id = :questionnaire_id
               WHERE id = :id_cor
            ");
            //$cor_res_id
            $stmt->execute(
                [
                'questionnaire_id' => $questionnaire_id,
                'id_cor' => $cor_res_id,
                ]
            );
            $total_one_elements = 
                Counter::countQuestionPosition
               (
                    $pdo, 
                    $id_user, 
                    $current_questionnaire_id, 
                    $filtre_niveau_difficulte, 
                    $nombre_total_de_questions
               ); 

            Counter::manageTimesAsked
            (
                $pdo, 
                $id_user, 
                $questionnaire_id, 
                $filtre_niveau_difficulte, 
                $total_one_elements
            );            
        }
        //gérer le flag cron mail: daily_question_cron_cbx
        $current_questionnaire['cron_mailing_flag'] = 
           CronMailing::sauverCronMailing($id_user, $pdo, $questionnaire_id, 1, $pseudo,$current_questionnaire);
    }
    else
    {
        //var_dump("cas qui n'arrives jamais, à déterminer...");
        //echo "ln(189) - > **************************** cas 0 qui n'arrive jamais";
    }
// Chargement initial non POST du formulaire
} 
else if(!isset($_POST['next_question']))
{   
    //echo "ln(189) - > ******************* Chargement initial non POST du formulaire - cas 2 ";
    //var_dump("Cas Chargement initial non POST du formulaire"); 
    $question = ManageQuestion::chargementLorsqueJusteLaQuestionEstDemandee(
        $pdo, 
        $current_questionnaire_id, 
        $id_user,
        $AND_filtre_niveau_difficulte, 
        $bool_filtre_niveau_difficulte,
        $filtre_niveau_difficulte,
        $answer
     );
     $_POST['daily_question_cron_cbx'] = 0; //car ne pas tenir compte de l'historique du questionnaire précédent.
}
else if(isset($_POST['next_question'])){  
    //echo "ln(375) - > **************************** appel question suivante - cas 6 ";
    $question = ManageQuestion::chargementLorsqueJusteLaQuestionEstDemandee(
        $pdo, 
        $current_questionnaire_id, 
        $id_user,
        $AND_filtre_niveau_difficulte, 
        $bool_filtre_niveau_difficulte,
        $filtre_niveau_difficulte,
        $answer
     );    
     $current_questionnaire['cron_mailing_flag'] = 
        CronMailing::sauverCronMailing($id_user, $pdo, $current_questionnaire_id, 1,$pseudo, $current_questionnaire);
}else
{
    //echo "ln(387) - > **************************** appel inconnu- cas 8 ";
    $question = ManageQuestion::chargementLorsqueJusteLaQuestionEstDemandee(
        $pdo, 
        $current_questionnaire_id, 
        $id_user,
        $AND_filtre_niveau_difficulte, 
        $bool_filtre_niveau_difficulte,
        $filtre_niveau_difficulte,
        $answer
     );        
}

if (isset($_POST['hidden_body_font_size']))
{
    $hidden_body_font_size = $_POST['hidden_body_font_size'];
}
else
{
    $body_font_size = 16;
}
//QR code process
include 'model/processQrCode.php';
include 'model/QrCodeResult.php';

$qrCodeResult = QRcodeHandler::manageQrCode
(        
    $pdo, 
    $current_questionnaire_id, 
    isset($question['id'])? $question['id']: null,
    isset($answer['id'])? $answer['id']: null,
    $base_qrcode_name, //form creds.php
    $base_qrcode_directory, //from creds.php
    $url_de_base_clone //from creds.php
);

/**
 *  fuction GestionHistoryManagement
 * 
 *  Cette fonction installe à chaque chargement 
 *  de la page la bonne valeur du compteur qui 
 *  ira dans dans l'hidden value.
 * 
 *  Le test de cohérence sera fait ailleurs 
 *  via AJAX en se basant sur cette valeur du compteur
 *  installé dans la page contre celui récupéré dans
 *  la DB via ajax.
 * 
 *  Ce test ce déclenchera au click du bouton
 *  submit de la page. Le submit sera interrompu au bénéfice
 *  du test AJAX.
 * 
 *   Si le test AJAX est ok on laisse l'événement
 *   du submit faire son office.
 * 
 * 1- verif si existe dejà en db
 * 2- instaurer la valeur dans l'hidden value
 */
function GestionHistoryManagement ($pdo, $id_user)
{
    $history_check_value = "";
    $stmt = $pdo->prepare("
        SELECT 
        id, 
        user_id,
        amount_counter	
        FROM history_management 
        WHERE user_id = :user_id
    ");
    $stmt->execute([
        'user_id' => $id_user
    ]);

    $history = $stmt->fetch(PDO::FETCH_ASSOC);
    
    //trouvé
    if(isset($history["id"]))
    {
        //update counter
        $stmt = $pdo->prepare("
            UPDATE history_management 
                SET amount_counter = amount_counter + 1
            WHERE id = :id
        ");
        //$cor_res_id
        $stmt->execute(
            [
            'id' => $history["id"]
            ]
        );  
        $history_check_value =  $history["amount_counter"] + 1;       
    }
    else
    {
        //ajouter compteur
        $stmt = $pdo->prepare("
            INSERT INTO history_management(user_id, amount_counter)
            VALUES(:user_id,:amount_counter);
        ");
        $stmt->execute(
            [
                'user_id' => $id_user,    
                'amount_counter' => 1
            ]
        );    
        $history_check_value = 1;
    }
    return $history_check_value;
}

//effectué à chaque chargement de page
$history_check_value = GestionHistoryManagement ($pdo, $id_user);

$complete_qrcode_name = $qrCodeResult->fullName;
$url_qrcode = $qrCodeResult->url;

require 'vieuw/index_layout.php';
?>
