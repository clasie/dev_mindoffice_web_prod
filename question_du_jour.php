<?php

require "Connection_db.php";
require 'functions.php';

$tableauDesNiveausDeDificultes = ManageQuestion::chargerLesNiveausDeDifficultes($pdo);
/*
echo "<pre>";
var_dump($tableauDesNiveausDeDificultes['1']['color_code']);
echo "</pre>";
*/
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

//echo "-0-";
//aller chercher le record en db via le token dans l'url GET
$token = $_GET['token'];
//echo $token;

//recup questionnaire et user
$stmt = $pdo->prepare("
   SELECT * FROM cron_mailing
   WHERE token = :token
");

$stmt->execute([
    'token' => $token
]);
//echo "-2-";
$cron_mailing = $stmt->fetch(PDO::FETCH_ASSOC);
//var_dump($cron_mailing);
if(!isset($cron_mailing['id_questionnaire']))
{
    echo "<pre>";
    echo "Désolé, le token n'existe pas ou plus. Merci de signaler votre problème via info@MindOffice.be";
    echo "</pre>";
    die();;
}

//Gestion niv de difficulté
$filtre_niveau_difficulte = "";
$bool_filtre_niveau_difficulte = false;
$AND_filtre_niveau_difficulte = "";
$diff_level = $cron_mailing['diff_level'];
/*
echo "<pre> diff_level ";
var_dump($diff_level);
echo "</pre>";
*/
if($diff_level != 1)
{
   //echo "OK filtre_niveau_difficulte ".$diff_level;
   $filtre_niveau_difficulte = $diff_level; //$_POST['filtre_niveau_difficulte'];
   $AND_filtre_niveau_difficulte = " AND cor.difficulty_level = :filtre_niveau_difficulte ";
   $bool_filtre_niveau_difficulte = true;
}
else{
    //echo "KO filtre_niveau_difficulte ";
    $filtre_niveau_difficulte = "";
    $bool_filtre_niveau_difficulte = false;
    $AND_filtre_niveau_difficulte = "";    
}

//echo "-3-";
/*
echo "<pre> cron_mailing ";
var_dump($cron_mailing);
echo "</pre>";
*/

//récup nombre de questions + filtre
$current_questionnaire_id = $cron_mailing['id_questionnaire'];
$AND_filtre_niveau_difficulte = "";
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

if($AND_filtre_niveau_difficulte)
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
         'id_user' => $cron_mailing['id_user']
        ]
    );
}
$available_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
$nombre_total_de_questions = $stmt->rowCount();
//fin recup questions

//id_question est NON vide
//on se contente de récupérer sur les id donnés
$current_questionnaire_id ="";
if(isset($cron_mailing['id_question']))
{
    //echo "id question PAS vide";  
    //recup des data
    $current_questionnaire_id = $cron_mailing['id_questionnaire'];
    $id_user = $cron_mailing['id_user'];

    //aller chercher la question
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
        AND quest.id = :id_question

    ");

    $stmt->execute(
        [
            'questionnaire_id' => $current_questionnaire_id,
            'id_user' => $id_user,
            'id_question' => $cron_mailing['id_question']
        ]
    );

    $question = $stmt->fetch(PDO::FETCH_ASSOC);
    $question_id = $question['id'];
    /*
    echo "<pre> question ";
    var_dump($question);
    echo "</pre>";
    */
    // Récupération des informations sur le questionnaire courant
    $stmt = $pdo->prepare
        ("
            SELECT questionnaire_name, description 
            FROM questionnaires 
            WHERE id = :questionnaire_id
         ");
    $stmt->execute(['questionnaire_id' => $current_questionnaire_id]);
    $current_questionnaire = $stmt->fetch(PDO::FETCH_ASSOC);


    
    /*
    echo "<pre> Questionnaire ";
    var_dump($current_questionnaire); 
    echo "</pre>";
    */
    //recup la réponse
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
    echo "<pre> answer ";
    var_dump($answer);
    echo "</pre>";  
    */
}
//id_question est vide
//on va devoir aller récupérer la bonne question 
// ... ET incrémenter le compte de celle-ci
else
{
    //echo "id question vide";    
    //recup des data
    $current_questionnaire_id = $cron_mailing['id_questionnaire'];
    $id_user = $cron_mailing['id_user'];

    //aller chercher la question
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

        ORDER BY COALESCE(cor.times_asked, 0) ASC, id ASC
    ");

    $stmt->execute(
    [
        'questionnaire_id' => $current_questionnaire_id,
        'id_user' => $id_user 
    ]
    );

    $question = $stmt->fetch(PDO::FETCH_ASSOC);
    $question_id = $question['id'];
    /*
    echo "<pre> question ";
    var_dump($question);
    echo "</pre>";
    */
    // Récupération des informations sur le questionnaire courant
    $stmt = $pdo->prepare("SELECT questionnaire_name, description FROM questionnaires WHERE id = :questionnaire_id");
    $stmt->execute(['questionnaire_id' => $current_questionnaire_id]);
    $current_questionnaire = $stmt->fetch(PDO::FETCH_ASSOC);
    /*
    echo "<pre> Questionnaire ";
    var_dump($current_questionnaire); 
    echo "</pre>";
    */
    //recup la réponse
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
    echo "<pre> answer ";
    var_dump($answer);
    echo "</pre>";
    */
    //recup la réponse
    $stmt = $pdo->prepare("
    SELECT *
    FROM cor_user_question 
    WHERE 
        user_id = :id_user AND
        question_id = :question_id AND
        questionnaire_id = :questionnaire_id
    ");
    $stmt->execute(
        [
            'id_user' => $id_user,
            'question_id' => $question_id,
            'questionnaire_id' => $current_questionnaire_id        
        ]
    );
    $cor_user_question = $stmt->fetch(PDO::FETCH_ASSOC);
    /*
    echo "<pre> cor_user_question  AVANT increm";
    var_dump($cor_user_question);
    echo "</pre>";
    */
    
    //UPDATE mail_cron avec l'id trouvé"
    $stmt = $pdo->prepare("
        UPDATE cron_mailing 
        SET id_question = :id_question
        WHERE 
            token = :token
    ");
    //$cor_res_id
    $stmt->execute(
        [
            'id_question' => $question_id,
            'token' => $token        
        ]
    );    

    /*
    SELECT * FROM `cor_user_question` 
    WHERE 
    user_id = 38 AND
    question_id = 1022 AND
    questionnaire_id = 32
    */    
    /**
     * incrémenter le commpteur de la question
     */
    //voir si existe entrée dans la table cor
    //ATTENTION tenir compte du flag ...TODO
    $stmt = $pdo->prepare("
    SELECT *
    FROM cor_user_question 
    WHERE 
        user_id = :id_user AND
        question_id = :question_id AND
        questionnaire_id = :questionnaire_id
    ");
    $stmt->execute(
        [
            'id_user' => $id_user,
            'question_id' => $question_id,
            'questionnaire_id' => $current_questionnaire_id        
        ]
    );
    $cor_user_question = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = $stmt->rowCount();

    /*
    echo "<pre> on reg ce qu'il y a dans: cor_user_question  APRES increm";
    var_dump($cor_user_question);    
    var_dump("total ".$total);  
    var_dump("id_user ".$id_user);
    var_dump("question_id ".$question_id);
    var_dump("current_questionnaire_id ".$current_questionnaire_id);  
    echo "</pre>";  
    */

    // existe entrée dans la table cor
    if($total >0)
    {
        /*
        echo "<pre> ok, entrée présente: cor_user_question  APRES increm";
        var_dump($cor_user_question);
        var_dump($cor_user_question);
        var_dump($id_user);
        var_dump($question_id);
        var_dump($current_questionnaire_id);        
        echo "</pre>";    
        */

        //echo "<pre> entrée présente: cor_user_question </pre>";
        /*
        $times_asked_good_calculated_value_to_save = 0;
        $counter_time_asked = $cor_user_question['times_asked'];
        if($counter_time_asked > 1)
        {
            $times_asked_good_calculated_value_to_save = 2;
        }
        else
        {
            $times_asked_good_calculated_value_to_save = 2;
        }
        */

        //echo "<pre> times_asked_good_calculated_value_to_save: ".$times_asked_good_calculated_value_to_save." </pre>";
        //echo "<pre> question_id : ".$question_id."</pre";

        //todo_modif_counter
        //SET times_asked = :times_asked_amound
        $stmt = $pdo->prepare("
            UPDATE cor_user_question 
            SET times_asked = :times_asked_amound
            WHERE 
                user_id = :id_user AND
                question_id = :question_id AND
                questionnaire_id = :questionnaire_id

        ");
        //$cor_res_id
        $stmt->execute(
            [
                'id_user' => $id_user,
                'question_id' => $question_id,
                'questionnaire_id' => $current_questionnaire_id,
                'times_asked_amound' => 2
            ]
        );

        $total_one_elements = 
            countQuestionPosition
            (
                $pdo, 
                $id_user, 
                $current_questionnaire_id, 
                $filtre_niveau_difficulte, 
                $nombre_total_de_questions
            ); 

        // 'times_asked_amound' => $times_asked_good_calculated_value_to_save         
        manageTimesAsked
        (
            $pdo, 
            $id_user, 
            $current_questionnaire_id,
            $filtre_niveau_difficulte, 
            $total_one_elements            
        );
    }
    else
    {
        /*
        echo "<pre> pas entrée présente: -> insert , cor_user_question  APRES increm";
        var_dump($cor_user_question);
        var_dump($id_user);
        var_dump($question_id);
        var_dump($current_questionnaire_id);
        echo "</pre>"; 
        */
        $stmt = $pdo->prepare("
        INSERT INTO cor_user_question(user_id, question_id,times_asked,questionnaire_id)
        VALUES(:id_user,:question_id,:times_asked,:questionnaire_id);
     ");

     //todo_modif_counter
     $stmt->execute(
         [
             'id_user' => $id_user,    
             'question_id' => $question_id,
             'times_asked' => '1',
             'questionnaire_id' => $current_questionnaire_id
         ]
     ); 
    }
} //fin //id_question est vide

/*
//récup nombre de questions + filtre
$AND_filtre_niveau_difficulte = "";
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

if($AND_filtre_niveau_difficulte)
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

$page_courante = "?";
$affichage_pagination = $page_courante."/".$nombre_total_de_questions;
//fin recup questions
*/

//pagination
$total_one_elements = 
   countQuestionPosition
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

$current_questionnaire_id = $cron_mailing['id_questionnaire'];
$questionnaire_name = $current_questionnaire['questionnaire_name'];
$description_questionnaire = $current_questionnaire['description'];

$question_text = $question['question_text'];
$question_id = $question['id'];

$answer_text = $answer['answer_text'];
$answer_id = $answer['id'];


/**************************
 * sauver les datas liées 
 * au contexte du user
 */
$access = new Access();
$access->pdo = $pdo;
$access->type_access = "QDJ";
$access->collectData();
$access->saveData();


/**
 * Trouver à quel numéro d'ordre de question se situe le user
 */
function countQuestionPosition
(
    $pdo, 
    $id_user, 
    $current_questionnaire_id, 
    $filtre_niveau_difficulte, 
    $nombre_total_de_questions
)
{
    //echo "<pre>filtre_niveau_difficulte : ".$filtre_niveau_difficulte."</pre>";
    // on vérifie si pour le questionnaire 
    // il existe au moins un élément times_asked == 1 
    $total_one_elements = 0;
    
    if(strlen($filtre_niveau_difficulte) == 0)
    {
        //echo "<pre>---> pas de niv de diff</pre>";
        $stmt = $pdo->prepare("
        SELECT id, times_asked
        FROM cor_user_question cor
        WHERE 
            cor.user_id = :id_user  AND
            cor.questionnaire_id = :questionnaire_id
        ");
        $stmt->execute(
            [
                'id_user' => $id_user,
                'questionnaire_id' => $current_questionnaire_id         
            ]
        );            
        $total_one_elements = $stmt->rowCount();
    }
    else
    {
        //echo "<pre>---> il y a un niv de diff</pre>";
        $stmt = $pdo->prepare("
        SELECT id, times_asked
        FROM cor_user_question cor
        WHERE 
            cor.user_id = :id_user                          AND
            cor.questionnaire_id = :questionnaire_id        AND
            cor.difficulty_level = :filtre_niveau_difficulte
        ");
                
        $stmt->execute(
            [
                'id_user' => $id_user,
                'questionnaire_id' => $current_questionnaire_id,
                'filtre_niveau_difficulte' => $filtre_niveau_difficulte         
            ]
        );         
        //$total_one_elements = $stmt->rowCount(); 
    }
    $cor_user_question_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_one_elements = analyseCounter
    (
        $cor_user_question_results, 
        $nombre_total_de_questions,
        $pdo
    );

    //echo "<pre>------------> total_one_elements";
    //var_dump($total_one_elements);
    //echo "</pre>";    

    return $total_one_elements;
}

/**
 * 
 * Déterminer si on est en amorçe
 * 
 * si oui == le nombre d'entrées dans le cor_ est < au total des questions...
 *    => on retourne le nombre de 1 et on ajoute 1 (car il y a un temps de retard)
 * 
 * si non == le nombre d'entrées dans le cor_ est == au total des questions...
 *    => on compte le nombre de > 1 et on retire ce nombre du total des questions
 * 
 */
function analyseCounter($cor_user_question_results, $nombre_total_de_questions,$pdo)
{
    //echo "<pre>********************dans : analyseCounter()*******************</pre>";
    $counterLoop = 0;
    $counterSup_1 = 0;
    $counterEqual_1 = 0;
    foreach ($cor_user_question_results as $q)
    {
        $counterLoop++;

        if(1 == $q['times_asked'])
        {
           $counterEqual_1++;
        }
        elseif(1 < $q['times_asked'])
        {
           $counterSup_1++;
        }

        /*
        On a 3 tours sur les questions d'un formulaire (questionnaire: 35)

        1- -> 1er tour, rien dans cor_ au démarrage == AMORCAGE
                -> EN AMORCAGE liste complete
                -> EN AMORCAGE sous liste  (BUG !)

                   QUESTION: pourquoi à l'amorçage d'un sous questionnaire
                   le premier counter est 0/2 (dans ce cas-ci) et revient à 
                   1/2 au second tour lors de l'appel de la première qouq question ?

                   -> id question 
                       - 1175 (label 2)
                       - 1177 (label 4)
                   SOLUTION: comparer les vals en DB dans les 2 cas (2 tours) avant
                   le chargement de la première question, il doivent forcément 
                   être différents.

        2- -> 2ème tour, début à partir d'un cor_ premier pré-initialisé
        3- -> 3ème tour partant de tout dans cor_ au mais à partir d'un cor_initialisé == CROISIERE
        */

        /*        
        echo "<pre>**********************>************************************</pre>";
        echo "<pre>nombre_total_de_questions : ".$nombre_total_de_questions. "</pre>";
        echo "<pre>id question: ".$q['id']. "</pre>";
        echo "<pre>times_asked: ".$q['times_asked']. "</pre>";
        echo "<pre>counterLoop: ".$counterLoop."</pre>";
        echo "<pre>counterEqual_1: ".$counterEqual_1."</pre>";
        echo "<pre>counterSup_1: ".$counterSup_1."</pre>";
        echo "<pre>**********************<*************************************</pre>";
        */
    }
    //EN AMORCAGE
    if($counterLoop < $nombre_total_de_questions)
    {
        //echo "<pre>********* EN AMORCAGE*********</pre>";
        $current_question_position = $counterLoop +1;
        //echo "<pre>********* current_question_position : ".$current_question_position."</pre>";
        return $current_question_position;
    }
    //EN CROISIERE
    else
    {
        //echo "<pre>En CROISIERE</pre>";
        //echo "<pre>********* PAS EN AMORCAGE*********</pre>";
        $current_question_position = $nombre_total_de_questions - $counterEqual_1 + 1;
        //echo "<pre>********* current_question_position : ".$current_question_position."</pre>";
        if(0 == $counterEqual_1)
        {
            //cas où on a des 2 partout
            if(0 == $counterEqual_1 && ($counterSup_1 == $nombre_total_de_questions))
            {
                /////mettre tout à 1
                foreach ($cor_user_question_results as $q)
                {
                    $stmt = $pdo->prepare("
                    UPDATE cor_user_question 
                        SET times_asked = 1
                    WHERE 
                        id = :id
                    ");
                    //$cor_res_id
                    $stmt->execute(
                        [
                            'id' => $q['id']
                        ]
                    );      
                }
                return 1;
            }
            else
            {
                echo "<pre>CAS IMPOSSIBLE...à voir...</pre>";
                return 0;
            }
        }
        return $current_question_position;
    }
}

/**
 *
 * A faire en dev
 *
 * Au dessus le système a trouvé un id de question. On va lui faire un  + 1.
 * Ensuite on vérifie si il y a saturation (== toutes les valeurs sont > 1). Si oui on nettoie et on remt tout à 1
 * 
 * cor_user_question
 *
 * A tous les endroits "Phase +1", avant de faire le  + 1 
 * 
 *    - si toutes les questions sont > 1 => remettre 'times_asked' tout à 1
 *    - si toutes les 'sous' questions sont > 1 => remettre 'times_asked' à 1
 *
 *     Process
 *      
 *     - si il existe au moins une question avec 'times_asked' < 2, 
 *        => on peut égaliser tous les autres qui sont > 1 à 2.
 *
 *     Quand on fait + 1 on verifie si la valeur à updater est > 1 => alors on met 2.
 *     Si non on fait le + 1.
 *
 * Où se trouvent les + 1  OU les 'times_asked' => '1' ? -> //todo_modif_counter
 *
 *    - question_du_jour   (1)
 *    - index              (1)    
 * 
 */
function manageTimesAsked
(
    $pdo, 
    $id_user, 
    $current_questionnaire_id, 
    $filtre_niveau_difficulte, 
    $total_one_elements
)
{
    /*
    // on vérifie si pour le questionnaire 
    // il existe au moins un élément times_asked == 1 
    $stmt = $pdo->prepare("
    SELECT id
    FROM cor_user_question 
    WHERE 
        user_id = :id_user  AND
        questionnaire_id = :questionnaire_id AND
        times_asked = 1  
    ");
    $stmt->execute(
        [
            'id_user' => $id_user,
            'questionnaire_id' => $current_questionnaire_id         
        ]
    );            
    $total_one_elements = $stmt->rowCount();
    */
    
    if($total_one_elements == 0)
    {
        //echo "<pre>On n'a PLUS du times_asked == 1 => on va tout remettre à 1</pre>";
        // ->                 'filtre_niveau_difficulte' => $filtre_niveau_difficulte

        //pas de filtre sur difficult level
        if(strlen($filtre_niveau_difficulte) == 0)
        {
            //echo "<pre>Avant UPDATE sans niv de diff</pre>";
            $stmt = $pdo->prepare("
                UPDATE cor_user_question 
                    SET times_asked = 1
                WHERE 
                    user_id = :id_user AND
                    questionnaire_id = :questionnaire_id 
            ");
            //$cor_res_id
            $stmt->execute(
                [
                    'id_user' => $id_user,
                    'questionnaire_id' => $current_questionnaire_id
                ]
            );      
        }  
        //attention, filtre sur difficult level
        else
        {
            //echo "<pre>Avant UPDATE avec niv de diff</pre>";
            $stmt = $pdo->prepare("
                UPDATE cor_user_question cor
                    SET times_asked = 1
                WHERE 
                    cor.user_id = :id_user                   AND
                    cor.questionnaire_id = :questionnaire_id AND
                    cor.difficulty_level = :filtre_niveau_difficulte 
            ");
            //$cor_res_id
            $stmt->execute(
                [
                    'id_user' => $id_user,
                    'questionnaire_id' => $current_questionnaire_id,
                    'filtre_niveau_difficulte' => $filtre_niveau_difficulte
                ]
            );   
        }
    }
    else
    {
        //echo "<pre>On a encore du times_asked == 1 => on ne fait rien";
    }
    // si oui on met le courant à 2.
    // si non on doit remettre tout à 1 et le cycle redémarre.
    return $total_one_elements;
}
//die();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="./img/exercices.png">   
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <title>Question du jour</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
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
            color:  #021f4a;
            /*border-radius: 5px;*/
        }
        .remarqueSurUneQuestion{
            display: block;
            width: auto;
            margin-bottom: 15px;
            color:  #021f4a
        }
        .label_select_input {
            display: block;
            width: 100%;
            margin-bottom: 15px;
        }        
        input[type="submit"] {
            width: auto;
            padding: 12px 15px;
            margin-top: 24px;
        }
        .readonly-content {
            padding: 10px;
            background-color: #eee;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 15px;
            white-space: pre-wrap; /* Conserve les retours à la ligne */
            word-wrap: break-word; /* Coupe les longs mots si nécessaire */
            color: #021f4a;
            box-shadow: 1px 5px 14px <?php echo $tableauDesNiveausDeDificultes['1']['color_code']; ?>;      
        }
        .readonly-content_desc_questionnaire {
            padding: 10px;
            background-color: #eee;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 15px;
            white-space: pre-wrap; /* Conserve les retours à la ligne */
            word-wrap: break-word; /* Coupe les longs mots si nécessaire */
            color: #021f4a;
            box-shadow: 1px 5px 14px #021f4a7a;
        }        
        h2 {
            color: #021f4a;
        }

        .select {
        }
        .button-container {
            display: flex; /* Activer Flexbox */
            justify-content: center; /* Centrer horizontalement */
            gap: 51px; /* Espacement entre les éléments */
            margin-top: 2px;
        }
        .button-container span {
            display: flex; /* Pour aligner le contenu (bouton) correctement */
            align-items: center; /* Centrer verticalement */
        }
        .button-container button {
            padding: 10px 20px;
            font-size: 16px;
        }   
        textarea {
            font-size: 0.8rem;
            letter-spacing: 1px;
        }
        textarea {
            padding: 10px;
            max-width: 100%;
            line-height: 1.5;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-shadow: 1px 5px 14px <?php echo $tableauDesNiveausDeDificultes['1']['color_code']; ?>;          
            margin: 0px 0px 21px 0px;
        }       
        input[type="checkbox"]:not(:checked), 
        input[type="checkbox"]:checked {
             position: absolute;
             left: -9999%;
        }

        input[type="checkbox"] + label {
            display: inline-block;
            padding: 10px;
            cursor: pointer;
            /* border: 1px solid #9f7f7f; */
            color: black;
            /* background-color: white; */
            margin-bottom: 10px;
            width: 80%;
            box-shadow: 1px 1px 1px <?php echo $tableauDesNiveausDeDificultes['1']['color_code']; ?>;       }
        .image_button {
            box-shadow: 1px 5px 14px #021f4a7a;        
        }
        .size_image_select_question {
            height: 20px;
            width: 20px;
            margin-bottom: 17px; 
        }
        .size_image_question {
            height: 30px;
            width: 30px;
            margin-bottom: 17px; 
            display: flex;
            align-items: center;
        }   
        .size_image_exclamation {
            height: 25px;
            width: 25px;
            margin-bottom: 17px; 
            display: flex;
            align-items: center;
        }          
        .size_image_logo_titre {
            height: 38px;
            width: 38px;
            margin-bottom: 17px;
            box-shadow: 1px 5px 14px #cf761b99;
        }        
        .size_image_user_icon {
            height: 20px;
            width: 20px;
            margin: 0px 0px 15px 5px;
            /*box-shadow: 1px 5px 14px #cf761b99;*/
        }      
        .size_image_plus_loin {
            height: 20px;
            width: 20px;
            margin-bottom: 17px; 
            display: flex;
            align-items: center;
        }            
        input[type="checkbox"]:checked + label {
        /*border: 1px solid white;*/
        color:rgb(15, 3, 68);
        background-color: rgb(199, 231, 18);
        box-shadow: 1px 5px 14px <?php echo $tableauDesNiveausDeDificultes['1']['color_code']; ?>;      
        }       
        #moncarre{
            background:<?php echo $tableauDesNiveausDeDificultes['1']['color_code']; ?>;           /* background: #ffb3ff; */
            border-radius: 50%;
            width: 10px;
            height: 10px;
            /* border: 2px solid #679403; */
            margin: 0px 0px 15px 0px;
            vertical-align: -38em;        
        }         
        #moncercle{
            background:<?php echo $tableauDesNiveausDeDificultes['1']['color_code']; ?>;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            /* border: 2px solid #679403; */
            margin: 0px 0px 14px 0px;         
        }             
        .moncercle_label{            
            margin: 0px 0px 0px 4px;  
            box-shadow: 1px 5px 14px <?php echo $tableauDesNiveausDeDificultes['1']['color_code']; ?>;
        }    
        .moncercle_label_petit{
            font-size : 10px; 
        } 
        .diff_levels {
            display: flex; /* Activer Flexbox */
            /*justify-content: center; */ /* Centrer horizontalement */
            gap: 5px; /* Espacement entre les éléments */
            margin-top: 20px;          
            margin-bottom: 14px;
        }
        .diff_levels span {
            display: flex; /* Pour aligner le contenu (bouton) correctement */
            align-items: center; /* Centrer verticalement */
        }
        .config{
            box-shadow: 1px 5px 14px #728b77;
            padding: 10px;
            max-width: 100%;
            line-height: 1.5;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin: 0px 0px 21px 0px;
        }
        
    </style>
    <script>
        $(document).ready(function() {
            $("#ajouterCommentaire").click(function() {
                $("#textPlace").toggle();
            });      
            $("#infoSupplemantaires").click(function() {
                $("#textPlaceSupplementaire").toggle();
            });                
        });
    </script>   
</head>
<body>    

    <form method="POST">     
        <div class="button-container">
            <span >
                <img  class="image_button"  alt="plus" id="increase" src="./img/plus.png">
            </span>
            <span >
                <img  class="image_button"  alt="moins" id="decrease" src="./img/moins.png">
            </span>           
        </div>        
    <!--
        <h2><img src="./img/run.png"> Question du jour</h2>     
    -->
        <div class="diff_levels">
            <span >
                <img class="size_image_logo_titre" alt="" src="./img/run.png">
            </span>   
            <Label class="remarqueSurUneQuestion" ><h2>Question du jour</h2></label>                        
       </div>            

        <!-- Selectionner un questionaire -->
        <div class="diff_levels">
            <span >
               <img class="size_image_select_question"  alt="Lire votre remarque" src="./img/questionnaire.png">
            </span>             
            <span >
               <label for="questionnaire_id">Le questionnaire sélectionné</label>
            </span>                              
        </div>           
      
        <select disabled autocomplete="off" class="select"  id="questionnaire_id" name="questionnaire_id" required onchange="this.form.submit()">
                 <option value="<?=$current_questionnaire_id?>"  selected >
                    <?=$questionnaire_name?>              
                 </option>                
        </select>

        <label>Déscription du questionnaire</label>
        <div class="readonly-content_desc_questionnaire"><?=$description_questionnaire?></div>

       <!-- config -->
       <div id="config_details" class="config" style="display:none;">                        
            
            <!-- Questionnaire ID -->
            <div class="diff_levels">
                <span >
                <img class="size_image_select_question" src="./img/id.png">
                </span>             
                <span >
                <label>Questionnaire ID</label>  
                </span>                              
            </div>  
            
            <input class="readonly-contentx" value="<?=$current_questionnaire_id?>" type="text" readonly value="4">
        
            <!-- Filtre Niveau difficulté $filtre_niveau_difficulte -->

            <div class="diff_levels">
                <span >
                <img class="size_image_select_question"  alt="Lire votre remarque"  src="./img/filtre.png">
                </span>             
                <span >
                    <label for="filtre_niveau_difficulte">Filtrer le questionnaire</label>
                </span>                              
            </div>  

    
            <select autocomplete="off"  onchange="this.form.submit()" class="select" id="filtre_niveau_difficulte" name="filtre_niveau_difficulte" >
                                    <option value="1" 
                                        >
                        Tous les niveaux                    </option>
                                    <option value="2" 
                                        >
                        Niveau facile                    </option>
                                    <option value="3" 
                                        >
                        Niveau moyen                    </option>
                                    <option value="4" 
                                        >
                        Niveau difficile                    </option>
                                    <option value="5" 
                                        >
                        Momentanément infaisable                    </option>
                            </select>  


            <div class="diff_levels">
                <span >
                <img class="size_image_select_question"  alt="Lire votre remarque" src="./img/questionnaire.png">
                </span>             
                <span >
                <label for="question_id">Questions disponibles ... </label>
                </span>                              
            </div>  

            <select autocomplete="off"  class="select" id="question_id" name="question_id" required onchange="this.form.submit()">
                    <option value="<?=$question_id?>" 
                        selected>                    
                        <?=$question_text?> &#8594; &#123; Tous les niveaux &#125;  id 261                  
                    </option>
            </select>  

                <!-- Question ID -->
                
                <div class="diff_levels">
                    <span >
                        <img class="size_image_select_question" src="./img/id.png">
                    </span>             
                    <span >
                        <label>Question ID</label>
                    </span>                              
                </div>  
                        

                <input class="readonly-contentx" type="text" readonly value="261">
        </div> <!-- fin details config -->

            <!-- Liste des couleurs  et leur significations-->
            <!--
            <div class="diff_levels">
                <?php foreach ($tableauDesNiveausDeDificultes as $r):?>
                    <span >
                        <div id="moncarre" style="background: <?=$r['color_code']?>"></div>
                    </span>        
                    <span >
                        <label class="moncercle_label_petit" ><?= htmlspecialchars($r['name_short']) ?></label>
                    </span>    
                <?php endforeach; ?>    
            </div>    
            -->
            <!-- Question -->
            <div class="diff_levels">
                <span >
                    <div class="moncercle_label"  id="moncercle"></div>
                </span>        
                <span >
                    <label>Question </label>
                </span>     
                <span >
                    <img class="size_image_question" src="./img/question.png">
                </span>                  
                <!-- bug 
                <span >
                    <label style="margin: 0px 0px 19px 3px;">&#8721;</label>
                </span>                       
                <span >
                    <label><?= $affichage_pagination ?> </label>
                </span>     
                -->               
            </div>            
                            
            <pre class="readonly-content"><?=$question_text?></pre>

                <div class="diff_levels">
                    <span >
                        <div class="moncercle_label" id="moncercle"></div>
                    </span>      

                    <span >
                        <label>Answer</label>
                    </span>      
                    <span >
                        <img class="size_image_exclamation" src="./img/exclamation.png">
                    </span>                                       
                </div>  

        <!-- voire réponse-->        
        <div class="diff_levels">
            <span >
                <img class="size_image_select_question" alt="Lire votre remarque" id="ajouterCommentaire" src="./img/+.png">
            </span>   
            <Label class="remarqueSurUneQuestion" >Voir la réponse du jour</label>              
       </div>                   

       <div id="textPlace" style="display:none;">                        
          <pre class="readonly-content"><?=$answer_text?></pre>

        <!-- info supplem. -->
        <div class="diff_levels">                
            <!-- choisir l'icone à cliquer-->
            <?php if(isset($answer['answer_text_more']) && strlen($answer['answer_text_more']) > 0) 
            { ?>
                <Label class="remarqueSurUneQuestion" >Pour aller plus loin ...</label>  
                <span >
                    <img class="size_image_plus_loin"  prop_id_answer="<?=$answer['id']?>"       
                        alt="Lire votre remarque" id="infoSupplemantaires" src="./img/lumiere.png">
                </span>   
            <?php 
            }  
            ?>
        </div>                
        <!-- info supplem. -->
        <div id="textPlaceSupplementaire" style="display:none;">                                    
            <pre class="readonly-content"><?= $answer['answer_text_more'] ?></pre>
        </div>

       </div>

    </form>

    <script>
        // Sélectionne l'élément body
        const bodyElement = document.body;

        // Fonction pour obtenir la taille actuelle de la police
        const getFontSize = () => {
            const computedStyle = window.getComputedStyle(bodyElement);
            return parseFloat(computedStyle.fontSize); // Convertit '80px' en 80
        };

        // Fonction pour définir une nouvelle taille de police
        const setFontSize = (newSize) => {
            bodyElement.style.fontSize = `${newSize}px`;

            let styleSheet = document.styleSheets[0];
            let rules = styleSheet.cssRules || styleSheet.rules;

            // Parcourir les règles CSS pour trouver `.maClasse`
            for (let i = 0; i < rules.length; i++) {
                if (rules[i].selectorText === ".select") {
                    // Modifier dynamiquement la propriété font-size
                    rules[i].style.fontSize = newSize + "px";
                    return;
                }
            }
        };

        // Ajoute les événements aux boutons
        document.getElementById("increase").addEventListener("click", () => {
            const currentSize = getFontSize();
            setFontSize(currentSize + 2); // Augmente la taille de 5px
            //Enregistrer la donnee
            const hiddenInput = document.getElementById("hidden_body_font_size");
            hiddenInput.value = currentSize + 2;
        });

        document.getElementById("decrease").addEventListener("click", () => {
            const currentSize = getFontSize();
            if (currentSize > 5) { // Empêche une taille trop petite
                setFontSize(currentSize - 2); // Réduit la taille de 5px
                //Enregistrer la donnee
                const hiddenInput = document.getElementById("hidden_body_font_size");
                hiddenInput.value = currentSize - 2;
            }
        });

    </script>
</body>
</html>

