<?php

require "Connection_db.php";
require 'functions.php';
$tableauDesNiveausDeDificultes = ManageQuestion::chargerLesNiveausDeDifficultes($pdo);
//aller chercher le record en db via le token dans l'url GET
$token = $_GET['token'];
//echo $token;

//recup questionnaire et user
$stmt = $pdo->prepare("
   SELECT * FROM qr_code
   WHERE token = :token
");

$stmt->execute([
    'token' => $token
]);
$qr_code = $stmt->fetch(PDO::FETCH_ASSOC);

if(!isset($qr_code['questionnaire_id']))
{
    echo "<pre>";
    echo "Désolé, le token n'existe pas ou plus. Merci de signaler votre problème via info@MindOffice.be";
    echo "</pre>";
    die();;
}

//questionnaire id
$questionnaire_id = $qr_code['questionnaire_id'];
$result_questionnaire = null;

$question_id = $qr_code['question_id'];
$result_question = null;

$answer_id = $qr_code['answer_id'];
$complete_qrcode_name = $qr_code['complete_qrcode_name'];

if
(
    !IS_NULL($questionnaire_id) &&  
    !IS_NULL($question_id) && 
    !IS_NULL($answer_id)
)
{
    /**
     * recup questionnaire
     */
    $stmt = $pdo->prepare("
        SELECT 
                id,
                questionnaire_name, 
                description

        FROM questionnaires
        WHERE actif = 1
        AND id = :questionnaire_id
    ");

    $stmt->execute(
        [
            'questionnaire_id' => $questionnaire_id
        ]
    );
    $result_questionnaire = $stmt->fetch(PDO::FETCH_ASSOC);

    /**
     * recup question
     */
    $stmt = $pdo->prepare("
        SELECT 
            id,
            question_text
        FROM questions
        WHERE id = :question_id
    ");

    $stmt->execute(
        [
            'question_id' => $question_id
        ]
    );
    $result_question = $stmt->fetch(PDO::FETCH_ASSOC);    

    /**
     * recup answer
     */
    $stmt = $pdo->prepare("
        SELECT 
            id,
            answer_text,
            answer_text_more
        FROM answers
        WHERE id = :answer_id
    ");

    $stmt->execute(
        [
            'answer_id' => $answer_id
        ]
    );
    $result_answers = $stmt->fetch(PDO::FETCH_ASSOC);    
}

$current_questionnaire_id = $result_questionnaire['id'];
$questionnaire_name = $result_questionnaire['questionnaire_name'];
$description_questionnaire = $result_questionnaire['description'];

$question_id = $result_question['id'];
$question_text = $result_question['question_text'];

$answer_id = $result_answers['id'];
$answer_text = $result_answers['answer_text'];
$answer_text_more = $result_answers['answer_text_more'];
$answer = $result_answers; //for legacy purposes

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
            box-shadow: 1px 1px 1px <?php echo $tableauDesNiveausDeDificultes['1']['color_code']; ?>;       
        }
        .image_button {
            box-shadow: 1px 5px 14px #021f4a7a;        
        }
        .size_image_select_question {
            height: 20px;
            width: 20px;
            margin-bottom: 17px; 
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
            background: <?php echo $tableauDesNiveausDeDificultes['1']['color_code']; ?>;            /* background: #ffb3ff; */
            border-radius: 50%;
            width: 10px;
            height: 10px;
            /* border: 2px solid #679403; */
            margin: 0px 0px 15px 0px;
            vertical-align: -38em;        
        }         
        #moncercle{
            background: <?php echo $tableauDesNiveausDeDificultes['1']['color_code']; ?>; 
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

            <!-- Question -->
            <div class="diff_levels">
                <span >
                    <div class="moncercle_label"  id="moncercle"></div>
                </span>        
                <span >
                    <label>Question </label>
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
            
            <!-- Liste des couleurs  et leur significations-->
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
                            
            <pre class="readonly-content"><?=$question_text?></pre>

                <div class="diff_levels">
                    <span >
                        <div class="moncercle_label" id="moncercle"></div>
                    </span>      

                    <span >
                        <label>Answer</label>
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

