<?php

class Test {
    //Test::staticMethod();
    public static function staticMethod() {
      echo "Hello World!";
    }
}
class StringManagement {
    public static function replaceString($toRemove, $replaceBy, $into)
    {
       return str_replace($toRemove, $replaceBy ,$into);
    }
}
class Counter {
    /**
     * Trouver à quel numéro d'ordre de question se situe le user
     * Counter::countQuestionPosition();
     */
    public static function countQuestionPosition
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

        $total_one_elements = Counter::analyseCounter
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
     * Counter::analyseCounter(); 
     */
    private static function analyseCounter($cor_user_question_results, $nombre_total_de_questions,$pdo)
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
     * Attention, quand on remet à '1' c'est uniquement pour l'ensemble sélectionné
     * et pas pour tous les niv de diff.
     * 
     * Quid si 'tous les niveaux demandés?
     * 
     * Counter::manageTimesAsked(); 
     */
    public static function manageTimesAsked
    (
        $pdo, 
        $id_user, 
        $current_questionnaire_id, 
        $filtre_niveau_difficulte, 
        $total_one_elements
    )
    {
        //echo "<pre>filtre_niveau_difficulte : ".$filtre_niveau_difficulte."</pre>";
        // on vérifie si pour le questionnaire 
        // il existe au moins un élément times_asked == 1 
        //$total_one_elements = 0;
        /*
        if(strlen($filtre_niveau_difficulte) == 0)
        {
            //echo "<pre>---> pas de niv de diff</pre>";
            $stmt = $pdo->prepare("
            SELECT id
            FROM cor_user_question cor
            WHERE 
                cor.user_id = :id_user  AND
                cor.questionnaire_id = :questionnaire_id AND
                cor.times_asked = 1  
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
            SELECT id
            FROM cor_user_question cor
            WHERE 
                cor.user_id = :id_user                          AND
                cor.questionnaire_id = :questionnaire_id        AND
                cor.times_asked = 1                             AND 
                cor.difficulty_level = :filtre_niveau_difficulte
            ");
                    
            $stmt->execute(
                [
                    'id_user' => $id_user,
                    'questionnaire_id' => $current_questionnaire_id,
                    'filtre_niveau_difficulte' => $filtre_niveau_difficulte         
                ]
            );         
            $total_one_elements = $stmt->rowCount(); 
        }
        */
        //echo "<pre>total_one_elements de valeur 1 : ".$total_one_elements."</pre>";

        
        //tous les elements ont un compteur >1 pour la (sous)selection
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
}

class CronMailing 
{
    
    /**
     * Gérer le cas où on doit sauver une demande d'inscription au daily mailing.
     * Mettre une limite de cron ?
     * return $current_questionnaire['cron_mailing_flag']    '1' ou '0'
     * 
     * CronMailing::sauverCronMailing();
     */
    public static  function sauverCronMailing($id_user, $pdo, $id_questionnaire, $cruds, $pseudo, $current_questionnaire)
    {   

        //on n'a pas de questionaire id, ce qui est possible
        // => rien à faire sans id du questionnaire.
        if($id_questionnaire == "")
        {
            /*
            echo "<pre>";
            echo "error in---> sauverCronMailing(), pas d'id questionnaire";
            echo "</pre>";
            */
            return '0';
        }
        
        //vérifions si il existe déjà une entrée dans cor_user_questionnaire
        $existe_en_db = 0;
        $stmt = $pdo->prepare
        ("
            SELECT *
            FROM  cor_user_questionnaire
            WHERE
                user_id = :user_id AND
                questionnaire_id = :id_questionaire
        ");

        $stmt->execute(
            [
                'id_questionaire' => $id_questionnaire,
                'user_id' => $id_user
            ]
        );

        $cor_user_questionnaire = $stmt->fetch(PDO::FETCH_ASSOC);
        /*
        echo "<pre> test existence bug -----> ";
        var_dump($cor_user_questionnaire);
        echo "</pre>";    
        */
        //déjà en db
        if ($cor_user_questionnaire)
        {
            /*
            echo "<pre>";
            echo "in---> sauverCronMailing(), result exists";
            echo "</pre>";
            */
            $existe_en_db = 1;
        
        }
        else
        {
            /*
            echo "<pre>";
            echo "in---> sauverCronMailing(), result n'exists pas encore en db";
            echo "</pre>";        
            */
            $existe_en_db = 0;
        }

        //n'est pas coché
        if(!isset($_POST['daily_question_cron_cbx']))
        {
            /*
            echo "<pre>";
            echo "in---> sauverCronMailing(), pas coché";
            echo "</pre>";  
            */
            //return;
            //existe en db et a été décoché => update
            if($existe_en_db == 1){
                /*
                echo "<pre>";
                echo "in---> sauverCronMailing(), existe en db";
                echo "</pre>";  
                */
                $stmt = $pdo->prepare
                ("
                    UPDATE cor_user_questionnaire
                    SET cron_mailing_flag = 0
                    WHERE
                        user_id = :user_id AND
                        questionnaire_id = :id_questionaire
                ");
            
                $stmt->execute(
                    [
                        'id_questionaire' => $id_questionnaire,
                        'user_id' => $id_user
                    ]
                );        
                return '0';
            }
            //existe pas en db et pas coché
            else
            {
                /*
                echo "<pre>";
                echo "in---> sauverCronMailing(), existe pas en db";
                echo "</pre>";  
                */
                return '0';
                
            }
        }
        //a été coché
        else
        {
            /*
            echo "<pre>";
            echo "in---> sauverCronMailing(), est coché";
            echo "</pre>";  
            */
            //return;

            //existe en db et a été coché => update
            if($existe_en_db == 1)
            {
                /*
                echo "<pre>";
                echo "in---> sauverCronMailing(), existe en db";
                echo "</pre>";  
                */
                $stmt = $pdo->prepare
                ("
                    UPDATE cor_user_questionnaire
                    SET cron_mailing_flag = 1
                    WHERE
                        user_id = :user_id AND
                        questionnaire_id = :id_questionaire
                ");
            
                $stmt->execute(
                    [
                        'id_questionaire' => $id_questionnaire,
                        'user_id' => $id_user
                    ]
                );        
                return '1';
            }
            //existe PAS en db et a été coché => insert
            else
            {
                /*
                echo "<pre>";
                echo "in---> sauverCronMailing(), existe pas db ---->INSERT";
                echo "</pre>";  
                */

                $questionnaire_name = $current_questionnaire ['questionnaire_name'];

                $stmt = $pdo->prepare("
                INSERT INTO cor_user_questionnaire(user_id,questionnaire_id,cron_mailing_flag,user_name,questionnaire_label)
                VALUES(:id_user,:questionnaire_id,:cron_mailing_flag,:user_name,:questionnaire_label);
                ");
                $stmt->execute(
                    [
                        'id_user' => $id_user,    
                        'questionnaire_id' => $id_questionnaire,
                        'cron_mailing_flag' => 1,
                        'user_name' => $pseudo,
                        'questionnaire_label' => $questionnaire_name 
                    ]
                ); 
                return '1';
            }
        }        
    }    
}

class GlobalMessage
{
    static public function sauverGlobalMessage($id_user, $pdo)
    { 
        //echo "sauverGlobalMessage()".$_POST['global_message_txt']."</br>";
        if(isset($_POST['global_message_txt']))
        {
            $stmt = $pdo->prepare
            ("
                UPDATE users 
                SET global_message = :global_message
                WHERE id = :user_id 
            ");
        
            $stmt->execute(
              [
                'global_message' => $_POST['global_message_txt'],
                'user_id' => $id_user
              ]
            );  
        }
    }
}
class ManageQuestion
{
    public static function sauverChangementsLorsAppelQuestionSuivante(
      $pdo,$id_user,$question_id, $definir_niveau_difficulte)
    {
        GlobalMessage::sauverGlobalMessage($id_user, $pdo);

        if(isset($_POST['hidden_questionaire_id'])) //prendre le précédent questionnaire
        {
            //$questionnaire_id = $_POST['questionnaire_id'];
            $questionnaire_id = $_POST['hidden_questionaire_id'];
        }
        else
        {
            $questionnaire_id = -1;
        }
        /*
        echo "<pre>";
        echo "1- sauverChangementsLorsAppelQuestionSuivante -> on va sauver en DB les val suivantes";
        echo "</pre>";

        echo "<pre>";
        echo "id_user : ".$id_user;
        echo "</pre>";
        echo "<pre>";
        echo "question_id : ".$question_id;
        echo "</pre>";
        echo "<pre>";
        echo "questionnaire_id : ".$questionnaire_id;
        echo "</pre>";
        */


        $stmt = $pdo->prepare
        ("
            UPDATE cor_user_question 
            SET difficulty_level = :definir_niveau_difficulte,
            questionnaire_id = :questionnaire_id
            WHERE question_id = :question_id  
            AND user_id = :user_id 
        ");

        $stmt->execute(
            [
                'definir_niveau_difficulte' => $definir_niveau_difficulte,
                'questionnaire_id' => $questionnaire_id,
                'question_id' => $question_id,
                'user_id' => $id_user
            ]
        );  

        /**
         * On vérifie si il y a un message à sauver: 'message_exists'
         */   

        $partage_admin = 0;
        $message = "";   
        $message_post_exists = false;

        //cbx
        if(isset($_POST['message_cbx']))
        {
            //echo "1.0 -> ";
            //var_dump($_POST['message_cbx']);
            $partage_admin = 1;
        }else
        {
            //echo "Pas de message_cbx";
        }
        //message
        if(isset($_POST['message_txt']))
        {
            //echo "2.0 ->";
            //var_dump($_POST['message_txt']);
            $message = trim($_POST['message_txt']);
            if(strlen($message) > 0)
            {
                $message_post_exists = true;
            }
        }
        else{
            //echo "Pas de message_txt";
        }

        //On doit sauver un message du user
        if(true)
        {
            //echo "Message existe 3.0";
            //Verif si ce sera un insert ou un update
            $stmt = $pdo->prepare("
                SELECT id
                FROM messages
                WHERE questionnaire_id = :questionnaire_id  
                AND   question_id = :question_id  
                AND   user_id = :user_id 
            ");
            $stmt->execute(
                [
                'questionnaire_id' => $questionnaire_id,
                'question_id' => $question_id,
                'user_id' => $id_user 
                ]
            );           
            $res_messages = $stmt->fetch(PDO::FETCH_ASSOC);
            $res_messages_id = $res_messages['id'] ?? -1; 

            $answer_id = "";            
            if(isset($_POST['answer_id'])){
                $answer_id = $_POST['answer_id'];
            }else{
                echo "Attention: answer_id Non trouvé!";
            }

            //UPDATE
            if($res_messages_id != -1)
            {
                if($message_post_exists)//pas vide dans le formulaire post
                {
                    //echo "UPDATE, Message existe déjà dans la DB";
                    $stmt = $pdo->prepare
                    ("
                        UPDATE messages 
                        SET 
                        message = :message,
                        partage_avec_admin = :partage_avec_admin,
                        answer_id = :answer_id
                        WHERE id = :res_messages_id  
                    ");
                
                    $stmt->execute(
                        [
                            'res_messages_id' => $res_messages_id,
                            'partage_avec_admin' => $partage_admin,
                            'answer_id' => $answer_id,
                            'message' => $message
                        ]
                    ); 
                }
                //on delete le message existant
                else
                {
                    $stmt = $pdo->prepare
                    ("
                        DELETE FROM messages 
                        WHERE id = :res_messages_id  
                    ");
                
                    $stmt->execute(
                        [
                            'res_messages_id' => $res_messages_id
                        ]
                    ); 
                } 
            }
            //INSERT
            else
            {
                if($message_post_exists){

                    //echo "INSERT, Message n'existe pas déjà dans la DB";
                    $stmt = $pdo->prepare("
                        INSERT INTO messages(user_id,question_id,questionnaire_id,partage_avec_admin,message,answer_id)
                        VALUES(:id_user,:question_id,:questionnaire_id,:partage_avec_admin,:message,:answer_id);
                    ");
                    $stmt->execute(
                        [
                            'id_user' => $id_user,    
                            'question_id' => $question_id,
                            'questionnaire_id' => $questionnaire_id,
                            'partage_avec_admin' => $partage_admin,
                            'message' => $message,
                            'answer_id' => $answer_id
                        ]
                    ); 
                }
            }
            //...
        }
        else
        {
            //echo "Message n' existe pas 4.0, => rien à sauver";
        }
    }
    public static function chargementLorsqueJusteLaQuestionEstDemandee (
        $pdo, 
        $current_questionnaire_id, 
        $id_user,
        $AND_filtre_niveau_difficulte, 
        $bool_filtre_niveau_difficulte,
        $filtre_niveau_difficulte,
        $answer
         )
    {
        //var_dump("function: chargementLorsqueJusteLaQuestionEstDemandee");
        if( 
            (isset($_POST['question_id']) && isset($_POST['custId']))
            &&
            ($_POST['question_id'] != $_POST['custId'])
        )
        {
            //var_dump("function: chargementLorsqueJusteLaQuestionEstDemandee 1.0");
            $stmt = $pdo->prepare("
                SELECT 
                    quest.id id, 
                    quest.question_text question_text, 
                    COALESCE(cor.times_asked, 0) AS timeAsked, 
                    COALESCE(cor.difficulty_level,1)AS difficulty_level 
                FROM questions quest
                LEFT JOIN cor_user_question cor
                ON quest.id = cor.question_id 
                AND cor.user_id = :id_user
                WHERE quest.questionnaire_id = :questionnaire_id AND quest.id = :question_id 
                $AND_filtre_niveau_difficulte
                ORDER BY COALESCE(cor.times_asked, 0) ASC, id ASC
            ");
            if($bool_filtre_niveau_difficulte)
            {
                $stmt->execute(
                    [
                    'questionnaire_id' => $current_questionnaire_id,
                    'question_id' => $_POST['question_id'],
                    'id_user' => $id_user ,
                    'filtre_niveau_difficulte' => $filtre_niveau_difficulte
                    ]
                );
            }
            else{
                $stmt->execute(
                    [
                    'questionnaire_id' => $current_questionnaire_id,
                    'question_id' => $_POST['question_id'],
                    'id_user' => $id_user 
                    ]
                );
            }                 
        }
        else{
            //var_dump("function: chargementLorsqueJusteLaQuestionEstDemandee 1.1");
            //cas appel question suivante, a été fait au début
            
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
                    COALESCE(cor.difficulty_level,1)AS difficulty_level 
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
                $stmt->execute(
                    [
                        'questionnaire_id' => $current_questionnaire_id,
                        'id_user' => $id_user ,
                        'filtre_niveau_difficulte' => $filtre_niveau_difficulte
                    ]
                );
            }
            else{
                $stmt->execute(
                    [
                        'questionnaire_id' => $current_questionnaire_id,
                        'id_user' => $id_user 
                    ]
                );
            }                 
        }
    
        $question = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $question;
    }
    public static function chargerLesNiveausDeDifficultes ($pdo)
    {
        $tableauDesNiveausDeDificultes;
        $sous_tableauDesNiveausDeDificultes;
    
        $stmt = $pdo->prepare
        ("
            SELECT 
                id, 
                name,
                color_code,
                name_short
            FROM niveaux_de_difficulte
         ");
        $stmt->execute();  
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res as $q)
        { 
            $sous_tableauDesNiveausDeDificultes["name"] = $q["name"];
            $sous_tableauDesNiveausDeDificultes["color_code"] = $q["color_code"];
            $sous_tableauDesNiveausDeDificultes["name_short"] = $q["name_short"];
    
            $tableauDesNiveausDeDificultes[$q["id"]] = $sous_tableauDesNiveausDeDificultes;
        }
        return $tableauDesNiveausDeDificultes;
    }    
}
class ManageQuestionnaire
{
    public static function getGlobalText($pdo, $id_user){
        $req = $pdo->prepare("
           SELECT id, global_message 
           FROM users 
           WHERE id = :id_user 
        ");
        $req->execute([
            'id_user' => $id_user
        ]);
    
        $rep = $req->fetch(PDO::FETCH_ASSOC);
    
        if($rep['id'] != false)
        {
            $noteGlobale = $rep['global_message'];
    
            if(strlen($noteGlobale) > 0)
            {
               return $rep['global_message'];
            }
            else
            {
                return "";
            }
    
        }
        else
        {
            return "Un problème est survenu dans la méthode getGlobalText()...";
        }    
    }
}
class System
{
    /*
    * Charger les labels systèmes
    *
        array(1) {
        [0]=>
        array(2) {
            ["label"]=>
            string(18) "titre_principal_lb"
            ["version"]=>
            string(36) "Questionnaire dynamique configurable"
        }
        }
    */
    public static function loadSystemLabels($pdo)
    {
        $labels = array();

        $systeme_labels = $pdo->query("
        SELECT 
            nom_label as label,
            version_fr as version 
        FROM system_labels
        ")->fetchAll(PDO::FETCH_ASSOC);
    /*
        echo "<pre>";
        var_dump($systeme_labels);
        echo "</pre>";
    */
        foreach ($systeme_labels as $systeme_label)
        {
            $keyLabel   = $systeme_label['label'];
            $valueLabel = $systeme_label['version'];

            $labels[$keyLabel] = $valueLabel;   
        }   
        return $labels; 
    }
}
/**
 * Trace les acces a l'appli.
 */
class Access
{
    public $pdo         = null;     
    public $type_access = null; 
    public $url         = null;         

    public $user_id     = null;   //null
    public $web_client  = null;   //null
    public $user_mail   = null;   //null
    public $user_name   = null;   //null
    public $notes       = null;   //null
    public $token       = null;   //null

    public $questionnaire_id    = null;   //null
    public $question_id         = null;   //null
    public $url_complete        = null;   //null
    public $questionnaire_label = null;   //null                

    public function collectData()
    {
        $this->web_client = $_SERVER["HTTP_USER_AGENT"];
        $this->web_client .= " - " .$this->getRealIP();

        $this->url = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . 
                     "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $this->token = isset($_GET['token']) ? $_GET['token'] : null;

        if(!is_null($this->token))
        {
           $this->collectMaxData();
        }
        else
        {
            //echo "token est null";
        }
    }
    private function getRealIP(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
          $ip = $_SERVER['HTTP_CLIENT_IP'];
        }else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
          $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
          $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    private function getClientIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
           return $_SERVER['HTTP_CLIENT_IP']; // IP from shared internet
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // IP from proxies, could be a comma-separated list
           return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
           return $_SERVER['REMOTE_ADDR']; // IP from remote address
        }
        return 'Unknown IP';
        }
    private function collectMaxData()
    {
        //echo "token est PAS  null";

        $stmt = $this->pdo->prepare("
            SELECT *        
            FROM cron_mailing        
            WHERE token = :token 
        ");
        $stmt->execute([
            'token' => $this->token
        ]);
        $cron_mailing = $stmt->fetch(PDO::FETCH_ASSOC);
        /*
        ["id"]=>
        int(496)
        ["id_user"]=>
        int(24)
        ["id_questionnaire"]=>
        int(4)
        ["token"]=>
        string(64) "8b9710f53dd6f78d62c260207f023a094cd2f501a16e981128e754ad9397e121"
        ["diff_level"]=>
        int(1)
        ["id_question"]=>
        int(466)
        ["mail_pris_en_compte"]=>
        int(1)
        ["note"]=>
        string(0) ""
        ["pseudo_user"]=>
        string(6) "claude"
        ["time_stamp"]=>
        string(19) "2025-03-11 03:37:01"
        ["questionnaire_label"]=>
        string(27) "1- NL - Onregelmatig verbum"
        ["url_complete"]=>
        string(131) "https://www.mindoffice.be/accessibility/question_du_jour.php?token=8b9710f53dd6f78d62c260207f023a094cd2f501a16e981128e754ad9397e121"        
        */
        $this->user_id             = $cron_mailing['id_user'];
        $this->user_name           = $cron_mailing['pseudo_user'];
        $this->questionnaire_id    = $cron_mailing['id_questionnaire'];
        $this->question_id         = $cron_mailing['id_question'];    
        $this->url_complete        = $cron_mailing['url_complete'];
        $this->questionnaire_label = $cron_mailing['questionnaire_label'];
        /*
        echo "<pre>";
        var_dump($cron_mailing);
        echo "</pre>";
        //$cron_mailing[''];
        */

    }
    public function saveData()
    {
        $stmt = $this->pdo->prepare("

            INSERT INTO access_log
            (
                type_access,
                url,
                timestamp,
                user_id, 
                web_client,               
                user_mail,
                user_name,
                notes, 
                token,
                questionnaire_id,
                question_id,
                url_complete,
                questionnaire_label                
            )
            VALUES(
                :type_access,
                :url,
                now(),
                :user_id,                
                :web_client,
                :user_mail,
                :user_name,
                :notes, 
                :token,

                :questionnaire_id,
                :question_id,
                :url_complete,
                :questionnaire_label                            
            );
        ");
        $stmt->execute(
            [
                'type_access' => $this->type_access, 
                'url'         => $this->url, 
                'user_id'     => $this->user_id,                 
                'web_client'  => $this->web_client, 
                'user_mail'   => $this->user_mail, 
                'user_name'   => $this->user_name, 
                'notes'       => $this->notes, 
                'token'       => $this->token,                            
                'questionnaire_id'    => $this->questionnaire_id,  
                'question_id'         => $this->question_id,   
                'url_complete'        => $this->url_complete,   
                'questionnaire_label' => $this->questionnaire_label                                                                   
            ]
        );
    }
}
class ManageDesinscription
{
    /**
     * 
     */
    public static function tryDeleteWithToken($pdo, $token ) 
    {
        //echo "isTokenValid()";
        $id_user = ManageDesinscription::getTokenData($pdo, $token);

        if($id_user != -1)
        {
            ManageDesinscription::deleteAllDataUser($pdo, $id_user);
            $message_trace_procedure = "Vous êtes désincrit et toutes vos données sont détruites.";
        }
        else
        {
            $message_trace_procedure  = "Le token utilisé n'existe pas ou n'existe plus. ";
            $message_trace_procedure .= "La désinscription a échoué. ";
            $message_trace_procedure .= "Merci d'utiliser un lien de désinscription plus récent.";
            /*
            echo "<pre> ";
            echo "Le token utilisé n'existe pas ou n'existe plus."; 
            echo "</pre>";
            echo "<pre> ";
            echo "La désinscription a échoué."; ;
            echo "</pre>";     
            echo "<pre> ";
            echo "Merci d'utiliser un lien de désinscription plus récent."; ;
            echo "</pre>";                   
            */
        }
        return $message_trace_procedure;
    }
    
    public static function getTokenData($pdo, $token)
    {
        $stmt = $pdo->prepare("
            SELECT id_user
            FROM cron_mailing
            WHERE token = :token  
        ");
        $stmt->execute(
            [
            'token' => $token
            ]
        );           
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        $res_id_user = $res['id_user'] ?? -1; 
        return $res_id_user;
    }
    public static function deleteAllDataUser($pdo, $id_user)
    {
        /*
        echo "<pre> ";
        echo "Boum, delete running ...";
        echo "</pre>";        
        */
                
        //return;

        try {  

            $pdo->beginTransaction();

            //access_log/user_id
            $stmt = $pdo->prepare("DELETE FROM access_log WHERE user_id = :id ");
            $stmt->execute(['id' => $id_user]);     
            //cor_user_question/user_id
            $stmt = $pdo->prepare("DELETE FROM cor_user_question WHERE user_id = :id ");
            $stmt->execute(['id' => $id_user]);     
            //cron_mailing/id_user
            $stmt = $pdo->prepare("DELETE FROM cron_mailing WHERE id_user = :id ");
            $stmt->execute(['id' => $id_user]);         
            //history_management/user_id
            $stmt = $pdo->prepare("DELETE FROM history_management WHERE user_id = :id ");
            $stmt->execute(['id' => $id_user]);    
            //messages/user_id
            $stmt = $pdo->prepare("DELETE FROM messages WHERE user_id = :id ");
            $stmt->execute(['id' => $id_user]);       

            //cor_user_questionnaire/user_id
            $stmt = $pdo->prepare("DELETE FROM cor_user_questionnaire WHERE user_id = :id ");
            $stmt->execute(['id' => $id_user]);  

            //users/id
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id ");
            $stmt->execute(['id' => $id_user]);  

            $pdo->commit();
            //echo "Done"; 
          
          } catch (Exception $e) {
            $pdo->rollBack();
            echo "Failed: " . $e->getMessage();
          }     
    }    
}


