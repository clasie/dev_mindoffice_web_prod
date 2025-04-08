<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="./img/exercices.png">    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

    <!-- <link rel="stylesheet" href="vieuw/styles.css">     -->
    <!-- <script src="vieuw/scripts.js"></script> -->
    <title>Dynamic Question Form</title> 
    <style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    padding: 0;
    font-size: <?php echo $hidden_body_font_size?>px;
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
    margin-bottom: 16px;
    white-space: pre-wrap; /* Conserve les retours à la ligne */
    word-wrap: break-word; /* Coupe les longs mots si nécessaire */
    color: #021f4a;
    box-shadow: 1px 5px 14px <?php echo isset($tableauDesNiveausDeDificultes[$question['difficulty_level']]['color_code']) ? $tableauDesNiveausDeDificultes[$question['difficulty_level']]['color_code'] : ";"; ?>
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
    font-size: <?echo $hidden_body_font_size?>px;
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
    box-shadow: 1px 5px 14px <?php echo isset($tableauDesNiveausDeDificultes[$question['difficulty_level']]['color_code']) ? $tableauDesNiveausDeDificultes[$question['difficulty_level']]['color_code'] : ";"; ?>
    margin: 0px 0px 21px 0px;
}       
input[type="checkbox"]:not(:checked), 
input[type="checkbox"]:checked {
position: absolute;
left: -9999%;
}
.check_box_cron_question
{
    width: 80%;
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
    box-shadow: 1px 1px 29px <?php echo isset($tableauDesNiveausDeDificultes[$question['difficulty_level']]['color_code']) ? $tableauDesNiveausDeDificultes[$question['difficulty_level']]['color_code'] : ";"; ?>
}
.image_button {
    box-shadow: 1px 5px 14px #021f4a7a;        
}
.size_image_select_question {
    height: 20px;
    width: 20px;
    margin-bottom: 17px; 
}
.size_image_qrcode {
    width: 250px;
    height: 250px;
    margin-bottom: 17px; 
    box-shadow: 1px 1px 29px <?php echo isset($tableauDesNiveausDeDificultes[$question['difficulty_level']]['color_code']) ? $tableauDesNiveausDeDificultes[$question['difficulty_level']]['color_code'] : ";"; ?>
}        
.size_image_plus_loin {
    height: 20px;
    width: 20px;
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
input[type="checkbox"]:checked + label {
/*border: 1px solid white;*/
color:rgb(15, 3, 68);
background-color: rgb(199, 231, 18);
box-shadow: 1px 5px 14px <?php echo isset($tableauDesNiveausDeDificultes[$question['difficulty_level']]['color_code']) ? $tableauDesNiveausDeDificultes[$question['difficulty_level']]['color_code'] : ";"; ?>
}       
#moncarre{
    background: <?php echo isset($tableauDesNiveausDeDificultes[$question['difficulty_level']]['color_code']) ? $tableauDesNiveausDeDificultes[$question['difficulty_level']]['color_code'] : ";"; ?>
    /* background: #ffb3ff; */
    border-radius: 50%;
    width: 10px;
    height: 10px;
    /* border: 2px solid #679403; */
    margin: 0px 0px 15px 0px;
    vertical-align: -38em;        
}         
#moncercle{
    background: <?php echo isset($tableauDesNiveausDeDificultes[$question['difficulty_level']]['color_code']) ? $tableauDesNiveausDeDificultes[$question['difficulty_level']]['color_code'] : ";"; ?>
    border-radius: 50%;
    width: 18px;
    height: 18px;
    /* border: 2px solid #679403; */
    margin: 0px 0px 14px 0px;         
}             
.moncercle_label{            
    margin: 0px 0px 0px 4px;  
    box-shadow: 1px 5px 14px <?php echo isset($tableauDesNiveausDeDificultes[$question['difficulty_level']]['color_code']) ? $tableauDesNiveausDeDificultes[$question['difficulty_level']]['color_code'] : ";"; ?>
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
pre {
    white-space: pre-wrap;
    white-space: -moz-pre-wrap;
    white-space: -pre-wrap;
    white-space: -o-pre-wrap;
}
.pas_de_questions_alert
{
    color: red;            
}

#loader {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9999;
    display: none; /* caché par défaut */
}

.spinner {
    border: 4px solid #f3f3f3; /* couleur de fond */
    border-top: 4px solid #3498db; /* couleur du "sablier" */
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
    </style>

    <script>

function showLoader() {
    $('#loader').fadeIn();  // Affiche le loader
}

$(document).ready(function() {
    $("#ajouterCommentaire").click(function() {
        $("#textPlace").toggle();
        //alert("ok");
    });
    $("#ajouterCommentaireGlobal").click(function() {
        $("#textPlaceGlobalNote").toggle();
        //alert("ok");
    }); 
    $("#voire_details").click(function() {
        $("#config_details").toggle();
        //alert("ok");
    }); 
    $("#infoSupplemantaires").click(function() {
        $("#textPlaceSupplementaire").toggle();
        //alert("ok");
    });
    $("#daily_question_cron_cbx").click(function() {
        //$("#textPlaceSupplementaire").toggle();
        var isChecked = $("#daily_question_cron_cbx").is(":checked");
        if(isChecked)
        {
            $("#daily_question_cron_label").
               html("<?=$systemLabels['inscrit_daily_question_btn']?>");                    
        }
        else
        {
            $("#daily_question_cron_label").
               html("<?=$systemLabels['pas_inscrit_daily_question_btn']?>");  
        }
    });            
    $("#message_cbx").click(function() {
        //$("#textPlaceSupplementaire").toggle();
        var isChecked = $("#message_cbx").is(":checked");
        if(isChecked)
        {
            $("#message_lbl").
               html("<?=$systemLabels['annuler_signaler_message_a_admin_btn']?>");                    
        }
        else
        {
            $("#message_lbl").
               html("<?=$systemLabels['signaler_message_a_admin_btn']?>");  
        }
    });  
    $("#qrcode").click(function() {
        $("#qrcode_imagePlace").toggle();
        //alert("ok");
    });   
    $("form").submit(function (event) 
    {
        showLoader();
        var debug = 0;
        var formData = {
            id_user_current: $("#id_user_current").val(),
            history_check_counter: $("#history_check_counter").val(),
        };
        if(debug == 1){
           alert("on va envoyer les data suivantes: id_user_current = " + $("#id_user_current").val() +  " history_check_counter = " + $("#history_check_counter").val());
        }
        $.ajax({
            type: "POST",
            url: "testhistory/check_history.php",
            data: formData,
            dataType: "json",
            encode: true,
            async: false
        }).done(function (data) {
            if(debug == 1){ alert("Avant verif...");}
            if (!data.success) 
            {
                if(debug == 1){ alert("Erreur detected inside");}
                //if (data.errors.name) 
                //{
                    //alert(data.errors.name);
                    //event.preventDefault();
                    $('#error_detected').val('error_found');
                    if(debug == 1){ alert($('#error_detected').val());}
                //}
            }
            else
            {
                 //alert(data.message);
                 if(debug == 1){ alert("Pas d'erreur detected inside");}
            }
        });

        var valueHidde = $('#error_detected').val();
        if(valueHidde.length > 0)
        {
            if(debug == 1){
                alert("preventDefault");
                alert($('#error_detected').val());
            }
            $('#error_detected').val('');//nettoyer
            event.preventDefault();
            location.reload(true);
            location.replace("https://www.cinqjoursdesilence.be/accessibility");
        }
        else
        {
            if(debug == 1){
                alert("1- NO preventDefault");
                alert($('#error_detected').val());
            }
            $('#error_detected').val('');//nettoyer
        }
    });    
});
    </script>
</head>
<body>
    <!--
    <label>REQUEST</label>        
    <pre><?php var_dump($global_message_txt); ?></pre>
    -->
    <!--
    <label>POST</label>
    <pre><?php var_dump($_POST); ?></pre>    
    -->

    <form method="POST">
        <!-- debug trace -->
        <!--  
        <span >
                <label>questionnaire_id = <?=$current_questionnaire_id?></label>

                <?php if(isset($question)){ ?>
                   <label>question_id = <?=$question['id'] ?></label>
                <?php } ?>

                <?php if(isset($answer)){ ?>
                   <label>answer_id = <?=$answer['id']?></label>
                <?php } ?>
                
                <label>history_check_value = <?=$history_check_value?></label> 
        </span>
        -->
        
         <div class="button-container">

            <span >
                <label style="color:red" > <font size="3px">Version Beta 1.0</font></label>
            </span>
         
        </div> 

        <div id="loader" style="display: none;">
            <div class="spinner"></div>
        </div>

        <div class="button-container">
            <span >
            <a class="image_button"  href="inscription_en_edition.php"><img alt="User" src="./img/edit_user.png"></a> 
            </span>
            <span >
                <img  class="image_button"  alt="plus" id="increase" src="./img/plus.png">
            </span>
            <span >
                <img  class="image_button"  alt="moins" id="decrease" src="./img/moins.png">
            </span>
            <span >
              <a class="image_button"  href="Deconnection.php"><img alt="Deconnection" src="./img/close.png"></a> 
            </span>              
        </div>        

        <div class="diff_levels">
            <span >
            <img class="size_image_user_icon"  src="./img/user.png">
            </span>        
            <span >
            <label><?echo $pseudo?></label>
            </span>                       
        </div>  

        <input type="hidden" id="id_user_current" name="id_user_current" value="<?=$id_user?>">
        <input type="hidden" id="history_check_counter" name="history_check_counter" value="<?=$history_check_value?>">
        <input type="hidden" id="error_detected" name="error_detected" value="">
        <input type="hidden" id="hidden_questionaire_id" name="hidden_questionaire_id" value="<?=$current_questionnaire_id?>">
        <input type="hidden" id="answer_id" name="answer_id" value="<?=$answer['id']?>">
        <input type="hidden" id="compteur_posts" name="compteur_posts" value="<?=$compteur_posts?>">
        <input type="hidden" id="custId" name="custId" value="<?=$question['id']?>">
        <input type="hidden" id="hidden_body_font_size" name="hidden_body_font_size" value="<?echo $hidden_body_font_size?>">
    <!--
        <h2><img src="./img/run.png"> Questionnaire dynamique configurable</h2>
    -->
        <div class="diff_levels">
            <span >
                <img class="size_image_logo_titre" alt="" src="./img/run.png">
            </span>   
            <Label class="remarqueSurUneQuestion" ><h2><?=$systemLabels['titre_principal_lb']?></h2></label>                        
       </div>        

        <!-- déplier -> global notes (ci-dessous) -->        
        <div class="diff_levels">
            <span >
                <img class="size_image_select_question" alt="Lire votre remarque" id="ajouterCommentaireGlobal" src="./img/+.png">
            </span>   
            <Label class="remarqueSurUneQuestion" ><?=$systemLabels['pense_bete_global_lb']?></label>                        
       </div>
       
       <!-- global notes -->
       <div id="textPlaceGlobalNote" style="display:none;">                        
                <textarea class="config"  id="global_message_txt" name="global_message_txt" rows="5" placeholder="Ecrivez ici vos notes globales. Ces données sont indépendantes de tout questionnaire." cols="33"><?=ManageQuestionnaire::getGlobalText($pdo, $id_user)?></textarea>
       </div>  

        <!-- Selectionner un questionaire -->
        <div class="diff_levels">
            <span >
               <img class="size_image_select_question"  alt="Lire votre remarque" src="./img/questionnaire.png">
            </span>             
            <span >
               <label for="questionnaire_id"><?=$systemLabels['selection_du_questionnaire_lb']?></label>
            </span>                              
        </div>           
      
        <select autocomplete="off" class="select"  id="questionnaire_id" name="questionnaire_id" required onchange="this.form.submit()">
            <?php foreach ($questionnaires as $v): ?>
                <option value="<?= htmlspecialchars($v['id']) ?>" <?= $v['id'] == $current_questionnaire_id ? ' selected ' : ' ' ?> >
                    <?= htmlspecialchars($v['questionnaire_name']) ?>  
                    <?= $v['id'] == $current_questionnaire_id ? " &#8594; &#123; Tot quest :".$nombre_total_de_questions." &#125; " : ' ' ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label><?=$systemLabels['description_questionnaire_lbl']?></label>
        <div class="readonly-content_desc_questionnaire"><?= htmlspecialchars($current_questionnaire['description'] ?? 'Unknown') ?></div>


        <!-- déplier config -->        
        <div class="diff_levels">
            <span >
                <img class="size_image_select_question" alt="Lire votre remarque" id="voire_details" src="./img/+.png">
            </span>   
            <Label class="remarqueSurUneQuestion" ><?=$systemLabels['details_questionnaire_lbl']?></label>              
       </div>
       
       <!-- config -->
       <div id="config_details" class="config" style="display:none;">                        
            
            <!-- Questionnaire ID -->
            <div class="diff_levels">
                <span >
                <img class="size_image_select_question" src="./img/id.png">
                </span>             
                <span >
                <label><?=$systemLabels['questionnaire_id_lbl']?></label>  
                </span>     
   
                <span >
                    <div id="textPlace_cron_mail">                        
                        <?php 
                            $messageCheckBox = "";
                            $checkedNonCheckedCron = ""; //daily_question_cron_cbx

                            //est déjà inscrit
                            if($current_questionnaire['cron_mailing_flag'] == '1')
                            {
                                $checkedNonCheckedCron = " checked ";
                                $messageCheckBox = $systemLabels['inscrit_daily_question_btn'];
                            }    
                            else
                            {
                                $messageCheckBox = $systemLabels['pas_inscrit_daily_question_btn'];
                            }                        
                        ?>   
                        <input type="checkbox" id="daily_question_cron_cbx" name="daily_question_cron_cbx" <?php echo $checkedNonCheckedCron;?> value="1">
                        <label id="daily_question_cron_label" for="daily_question_cron_cbx" class="check_box_cron_question" ><?=$messageCheckBox?></label>
                    </div>
                </span>                                   
            </div>  
            
            <input class="readonly-contentx" type="text" readonly value="<?echo $current_questionnaire_id?>">
        
            <!-- Filtre Niveau difficulté $filtre_niveau_difficulte -->

            <div class="diff_levels">
                <span >
                <img class="size_image_select_question"  alt="Lire votre remarque"  src="./img/filtre.png">
                </span>             
                <span >
                    <label for="filtre_niveau_difficulte"><?=$systemLabels['filtrer_questionnaire_niv_diff']?></label>
                </span>                              
            </div>  

    
            <select autocomplete="off"  onchange="this.form.submit()" class="select" id="filtre_niveau_difficulte" name="filtre_niveau_difficulte" >
                <?php foreach ($niveaux_de_dificultes as $q): ?>
                    <option value="<?= htmlspecialchars($q['id']) ?>" 
                    <?= $filtre_niveau_difficulte != "" && $q['id'] == $filtre_niveau_difficulte ? 'selected' : ''?>
                    >
                        <?= htmlspecialchars($q['name']) ?>
                    </option>
                <?php endforeach; ?>
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
                <?php foreach ($available_questions as $q): ?>
                    <option value="<?= htmlspecialchars($q['id']) ?>" 
                        <?= isset($question) && $q['id'] == $question['id'] ? 'selected' : '' ?>>
                    
                        <?= htmlspecialchars($q['question_text']) ?>
                        <?php 
                            $idLevel = $q['difficulty_level'];
                            //echo "idLevel :".$idLevel;
                            $levelLabel = $tableauDesNiveausDeDificultes[$idLevel]["name"];
                            echo " &#8594; &#123; $levelLabel &#125; ";         
                            //echo " id ".$q['id']." ";         

                        ?>                 
                    </option>
                <?php endforeach; ?>
            </select>  

            <!-- Question ID -->
            <?php if ($question){ ?>

                <div class="diff_levels">
                    <span >
                        <img class="size_image_select_question" src="./img/id.png">
                    </span>             
                    <span >
                        <label><?=$systemLabels['question_id_lbl']?></label>
                    </span>                              
                </div>                          
                <input class="readonly-contentx" type="text" readonly value="<?= htmlspecialchars($question['id']) ?>">
            <?php } ?>

       </div> <!-- fin details config -->

       <?php 
           if(!isset($question['question_text'])) { 
        ?>
            <p class="pas_de_questions_alert" >Pas de questions trouvées, vérifier les filtre que vous avez mis sur ce questionnaire.</p>
        <?php 
           } else { 
        ?>
            <!-- Question -->
            <div class="diff_levels">
                <span >
                    <div class="moncercle_label"  id="moncercle"></div>
                </span>        
                <span >
                    <label><?=$systemLabels['question_lbl']?></label>
                </span>       
                <span >
                    <label style="margin: 0px 0px 19px 3px;">&#8721;</label>
                </span>                       
                <span >
                    <label><?= $affichage_pagination ?> </label>
                </span>                                
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
                        
            <!-- <div id="moncercle"><label class="moncercle_label" >Question</label></div> -->
            <pre class="readonly-content"><?=$question['question_text']?></pre>
            
            <!-- Attribuer un autre niveau de difficulté à cette question -->
            <?php if($context_response){ 
                //var_dump("question['difficulty_level'] = ".$question['difficulty_level']);
            ?>
                <label for="definir_niveau_difficulte">Attribuer un autre niveau de difficulté à cette question</label>
                <select autocomplete="off"  class="select" id="definir_niveau_difficulte" name="definir_niveau_difficulte" >
                    <?php foreach ($niveaux_de_dificultes as $q):?>
                        <?php 
                        if($q['id'] != 1) //id == 1 c'est l'id de 'Tous les niveaux'
                        {
                        ?>
                            <option 
                            value="<?= htmlspecialchars($q['id']) ?>" 
                            <?= isset($question['difficulty_level']) && $q['id'] == $question['difficulty_level']? 'selected' : ''?> 
                            >
                                <?= htmlspecialchars($q['name']) ?>
                            </option>
                        <?php 
                        } 
                        ?>
                    <?php endforeach; ?>
                </select>            
            <?php } ?>    
                        
            <?php if ($answer): ?>    

                <div class="diff_levels">
                    <span >
                        <div class="moncercle_label" id="moncercle"></div>
                    </span>        
                    <span >
                        <label>Answer</label>
                    </span>                       
                </div>  
                
                <!-- <label class="moncercle_label">Answer</label> -->
                <pre class="readonly-content"><?=$answer['answer_text']?></pre>


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

                <div class="diff_levels"> 
                    <!-- choisir l'icone à cliquer-->
                    <?php if(strlen($question['message_txt']) == 0) 
                    { ?>
                        <Label class="remarqueSurUneQuestion" >Cliquez sur le stylo pour placer une remarque ...</label>  
                        <span >
                        <img class="size_image_select_question"  prop_id_answer="<?=$answer['id']?>"       alt="Lire votre remarque" id="ajouterCommentaire" src="./img/pen.png">
                        </span>   
                    <?php 
                    } 
                    else 
                    { ?>
                        <Label class="remarqueSurUneQuestion" >Cliquez sur le chapeau magique pour relire votre remarque ...</label>
                        <span >
                        <img class="size_image_select_question" prop_id_answer="<?=$answer['id']?>"    alt="Ecrire une remarque" id="ajouterCommentaire" src="./img/chapeau.png">
                        </span>  
                    <?php 
                    }    
                    ?>
                </div>

                <div id="textPlace" style="display:none;">                        
                    <?php 
                        $checkedNonChecked = "";
                        $buttonMessageAdmin = "";
                        if($question['partage_avec_admin'] == '1')
                        {
                            $checkedNonChecked = " checked ";
                            $buttonMessageAdmin = $systemLabels['annuler_signaler_message_a_admin_btn'];
                        }
                        else
                        {
                            $buttonMessageAdmin = $systemLabels['signaler_message_a_admin_btn'];
                        }
                    ?>   
                    <textarea id="message_txt" placeholder="Ecrivez ici vos remarques, moyens mnémotechniques, etc... liés à cette question en particulier." name="message_txt" rows="5" cols="33"><?= htmlspecialchars($question['message_txt'])?></textarea>

                    <input type="checkbox" id="message_cbx" name="message_cbx" <?php echo $checkedNonChecked;?> value="1">
                    <label id="message_lbl"  for="message_cbx"><?=$systemLabels['signaler_message_a_admin_btn']?></label>
                </div>

                <!-- QRCode -->
                <div class="diff_levels"> 
 
                    <span >
                    <img class="size_image_select_question" alt="qrcode" id="qrcode" src="./img/qrcode.png">
                    </span>   
                    <Label class="remarqueSurUneQuestion" >QR code </label>                     
                </div>

                <!-- qrcode image -->
                <div id="qrcode_imagePlace" style="display:none;">                               

                       <a target="_blank" href="<?=$url_qrcode?>"><img class="size_image_qrcode" 
                       alt="qrcode" id="qrcode" 
                       src="<?=$complete_qrcode_name?>"></a>
                </div>

                <input type="submit" name="next_question" value="<?=$systemLabels['save_and_next_question_btn']?>">
                
            <?php else: ?>
               <input type="submit" name="show_answer" value="<?=$systemLabels['save_and_show_answer_btn']?>">
            <?php endif; ?>
        <?php 
          }
        ?>
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
