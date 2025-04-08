<?php
require "Connection_db.php";

$debug = 0; 

$stmt = $pdo->prepare("
   SELECT * 
   FROM questionnaires
   WHERE actif = 1 
");
$stmt->execute();
$questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
$amount_questionnaires = $stmt->rowCount();
/*
echo "<pre>";
echo "*** Le mode DEBUG de vérification de consistence DB est actuellement BIEN activé ***";
echo "</pre>";
*/

if($debug)
{
    echo "<pre>";
    echo "amount_questionnaires :".$amount_questionnaires;
    echo "</pre>";
}

if($debug)
{
    echo "<pre>";
    echo "----------> TEST 1, on vérifie si tot quest  = tot answ pour chaque questionnaire.";
    echo "</pre>";
}


foreach ($questionnaires as $questionnaire)
{
   //compter les questions *******************************
   $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM `questions` 
        WHERE questionnaire_id = :questionnaire_id; 
    ");
    $stmt->execute(
        [
        'questionnaire_id' => $questionnaire['id']
        ]
    );    
    $stmt->execute();
    $questions = $stmt->fetchColumn();
 
    //echo "<pre>";
    //echo "Nombre de questions :".$questions;
    //echo "</pre>";

    //compter les answers *******************************
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(a.id)
        FROM `answers` a
        WHERE question_id IN (    
            SELECT id    
            FROM `questions`     
            WHERE questionnaire_id = :questionnaire_id
        )
        ORDER BY a.question_id;
    ");
    $stmt->execute(
        [
        'questionnaire_id' => $questionnaire['id']
        ]
    );    
    $stmt->execute();
    $answers = $stmt->fetchColumn();
 
    //echo "<pre>";
    //echo "Nombre de answers :".$answers;
    //echo "</pre>";

    if($questions != $answers)
    {
        echo "<pre>";
        echo "-------------------------> ATTENTION INTEGRITEE VIOLEE <-------------------";
        echo "</pre>";

        echo "<pre>id ".$questionnaire['id']."</pre>";
        echo "<pre>questionnaire name ".$questionnaire['questionnaire_name']."</pre>";

        echo "<pre>";
        echo "Nombre de questions :".$questions;
        echo "</pre>";

        
        echo "<pre>";
        echo "Nombre de answers :".$answers;
        echo "</pre>";
        //si questions < answers => chercher le doublon sur les answers
        //...
        //si questions > answers => chercher le doublon sur les questions
        //...      
        
        if($questions > $answers)
        {
            echo "<pre>";
            echo "**** questions > answers **************on va chercher  l'id lié à ce déséquilibre";
            echo "</pre>";  
            
            $stmt = $pdo->prepare("
                SELECT a.id answerId, q.id questionId
                FROM answers a 
                RIGHT JOIN questions q 
                ON a.question_id = q.id
                WHERE q.questionnaire_id = :questionnaire_id
                AND (a.id IS NULL OR q.id IS NULL);
            ");
            $stmt->execute(
                [
                'questionnaire_id' => $questionnaire['id']
                ]
            );          
            $resultsValues = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "<pre>resultsValues";
            var_dump(count($resultsValues));
            echo "</pre>";

            echo "<pre>resultsValues";
            var_dump($resultsValues);
            echo "</pre>";        
        }
        else
        {
            echo "<pre>";
            echo "**** questions < answers **************on va chercher  l'id lié à ce déséquilibre";
            echo "</pre>";  
            
            $stmt = $pdo->prepare("
                SELECT a.id answerId, q.id questionId
                FROM answers a 
                LEFT JOIN questions q 
                ON a.question_id = q.id
                WHERE q.questionnaire_id = :questionnaire_id
                AND (a.id IS NULL OR q.id IS NULL);
            ");
            $stmt->execute(
                [
                'questionnaire_id' => $questionnaire['id']
                ]
            );          
            $resultsValues = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "<pre>resultsValues";
            var_dump(count($resultsValues));
            echo "</pre>";

            echo "<pre>resultsValues";
            var_dump($resultsValues);
            echo "</pre>";  
        }

        /*
        SELECT a.id answerId, q.id questionId
        FROM answers a 
        RIGHT JOIN questions q 
        ON a.question_id = q.id
        WHERE q.questionnaire_id = 6
        AND (a.id IS NULL OR q.id IS NULL);
        */        
    }
}

if($debug)
{
    echo "<pre>";
    echo "----------> TEST 2, on vérifie maintenant  dans 'cor_user_question' si il existe un id de question ";
    echo "</pre>";
}

/*
echo "<pre>";
echo " qui pointe vers plus de 1 questionnaire, ce qui n'est pas autorisé... ";
echo "</pre>";
*/
$questions = array();

//$questions[10] [5] = "tutu";
//var_dump($array);


$stmt = $pdo->prepare("
    SELECT * FROM `cor_user_question`
");

$stmt->execute();
$cor_user_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($cor_user_questions as $cor_user_question)
{
    $question_id      = $cor_user_question['question_id'];
    $questionnaire_id = $cor_user_question['questionnaire_id'];

    //première apparition de la question => 
    if(!isset($questions[$question_id]))
    {
        //ensuite on lui ajoute le questionnaire id
        $questions[$question_id][$questionnaire_id] = $questionnaire_id;            
    }
    //question déjà rencontrée
    else
    {
        //questionnaire pas encore rencontré
        if(!isset($questions[$question_id][$questionnaire_id]))
        {
            $questions[$question_id][$questionnaire_id] = $questionnaire_id;         
            /*
            echo "<pre>";
            echo "---> Second test test";
            echo "</pre>";
            */
        }
    }
}

//TODO: ajouter les data sur le questionnaire et les questions
//   pour faciliter la recherche...
/*
SELECT * FROM `cor_user_question`
WHERE question_id = 385;
*/
$counter = 0;
foreach($questions as $key=>$question)
{
    if(count($question) > 1)
    {
        echo "<pre>";
        echo "**************Attention une question pointe sur pluq de 1 questionnaire dans cor_user_question *******";
        echo "</pre>";

        $counter++;
        echo "<pre>";
        echo "counter = " . $counter;
        echo "</pre>";

        echo "<pre>";
        echo "question_id = " . $key;
        echo "</pre>";

        echo "<pre>";
        //var_dump($question);
        echo "</pre>";  

        foreach($question as $key=>$questionnaire)
        {
            echo "<pre>";
            echo " - questionnaire_id = " . $key;
            echo "</pre>";
        }
        echo "<pre>";
        echo "*******************************************************";
        echo "</pre>";
    }

}
if($debug)
{
    echo "<pre>";
    echo "----------> TEST 3, on vérifie maintenant  dans 'cor_user_question' ET 'questions'si il existe un id de question ";
    echo "</pre>";
}
/*
echo "<pre>";
echo " qui pointe vers plus de 1 questionnaire, ce qui n'est pas autorisé... ";
echo "</pre>";
*/  

$stmt = $pdo->prepare("
    SELECT 
        q.question_text questText,
        q.id questId, 
        q.questionnaire_id questionnaireId, 
        c.questionnaire_id corQuestionnaireId
    FROM cor_user_question c
    JOIN questions q
    ON c.question_id = q.id
");
/*
    WHERE q.questionnaire_id = 5
    AND c.user_id = 24
*/
$stmt->execute();
$question_cor_user_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($question_cor_user_questions as $question_cor_user_question)
{
    if($question_cor_user_question['questionnaireId'] != $question_cor_user_question['corQuestionnaireId'])
    {
        echo "<pre>";
        echo "***************** ATTENTION ERREUR ****************";
        echo "</pre>";         

        echo "<pre>";
        echo "question_cor_user_question['questId] ".$question_cor_user_question['questId']; 
        echo "</pre>";
        echo "<pre>";
        echo "<pre>";
        echo "question_cor_user_question['questText] ".$question_cor_user_question['questText']; 
        echo "</pre>";

        echo "<pre>----->";
        echo "REPONSE -> ".getAnswerText($pdo,$question_cor_user_question['questId']); 
        echo "-<---------</pre>";

        echo "<pre>";        
        echo "question_cor_user_question['questionnaireId] ".$question_cor_user_question['questionnaireId']; 
        echo "</pre>";   
        echo "<pre>";        
        echo "question_cor_user_question['questionnaireId] ".getQuestionnaireText($pdo,$question_cor_user_question['questionnaireId']); 
        echo "</pre>";    

        echo "<pre>";
        echo "question_cor_user_question['corQuestionnaireId] ".$question_cor_user_question['corQuestionnaireId']; 
        echo "</pre>";  
        echo "<pre>";        
        echo "question_cor_user_question['questionnaireId] ".getQuestionnaireText($pdo,$question_cor_user_question['corQuestionnaireId']); 
        echo "</pre>";          
   
        echo "<pre>";
        echo "*******************************************************";
        echo "</pre>";      
    }
}    
function getQuestionnaireText($pdo,$id_questionnaire)
{
    $stmt = $pdo->prepare("
        SELECT q.questionnaire_name name
        FROM questionnaires q
        WHERE q.id = ".$id_questionnaire."
    ");

    $stmt->execute();
    $question_cor_user_questions = $stmt->fetch(PDO::FETCH_ASSOC);    
    //echo "<pre>";
    //echo "NAME : ".$question_cor_user_questions['name'];
    //echo "</pre>"; 
    return $question_cor_user_questions['name'];
}
function getAnswerText($pdo,$question_id)
{
    $stmt = $pdo->prepare("
        SELECT a.answer_text text
        FROM answers a
        WHERE question_id = ".$question_id."
    ");

    $stmt->execute();
    $answers_answer = $stmt->fetch(PDO::FETCH_ASSOC);    
    //echo "<pre>";
    //echo "NAME : ".$question_cor_user_questions['name'];
    //echo "</pre>"; 
    return $answers_answer['text'];

}    
/* rajouter, mais norm c'est déjà vérif au test 1 -> ,vérifier
SELECT id, COUNT(question_id)
FROM answers
GROUP BY id
HAVING COUNT(question_id) > 1;
---
SELECT COUNT(id), question_id
FROM answers
GROUP BY question_id
HAVING COUNT(id) = 0;
*/