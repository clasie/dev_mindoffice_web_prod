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