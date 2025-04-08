<?php
//require "QrCodeResult.php";

/**
 * Gestion QRCode
 */
class QRcodeHandler
{
    /**
     * return ful path qr code image
     * xor
     * return "";
     */
    public static function manageQrCode
    (
        $pdo, 
        $questionnaire_id, 
        $question_id,
        $answer_id,
        $base_qrcode_name,
        $base_qrcode_directory,
        $url_de_base_clone
    )
    {
        //receptacle valeur de retour
        $qrCodeResult = new QrCodeResult();

        /**
         * Pas de QRCode sur 
         * une question incomplete.
         */
        if(is_null($questionnaire_id) || 
           is_null($question_id) || 
           is_null($answer_id))
        {
            //echo "Valeurs incomplètes ... <br>";
            return $qrCodeResult;
        }
        $complete_qrcode_name = 
            QRcodeHandler::getQRCodeName
                    (
                        $base_qrcode_directory,
                        $base_qrcode_name,
                        $questionnaire_id, 
                        $question_id,
                        $answer_id
                    );

        //echo getcwd();
        //echo "<br>". $complete_qrcode_name."<br>";

        //si existe déjà -> rien à faire
        if(file_exists($complete_qrcode_name))
        {
            //aller chercher le token qui existe déjà
            $token = QRcodeHandler::getBackExistingToken
            (
                $pdo, 
                $questionnaire_id, 
                $question_id,
                $answer_id
            );
            $qrCodeResult->fullName = $complete_qrcode_name;
            $qrCodeResult->url = $url_de_base_clone.$token;
        }
        //si non
        else
        {
            //token
            $token = bin2hex(random_bytes(32));
            //construire data pour l'url
            $url = $url_de_base_clone.$token;

            //mettre en db token + autres valeurs +
            QRcodeHandler::fillUpDb
            (
                $pdo, 
                $questionnaire_id, 
                $question_id,
                $answer_id,
                $url,
                $token,
                $complete_qrcode_name
            );

            //echo "File " .$complete_qrcode_name. " do not exists <br>";
            QRcodeHandler::createQRCodeImage($url, $complete_qrcode_name);

            $qrCodeResult->fullName = $complete_qrcode_name;
            $qrCodeResult->url = $url;
        }
        return $qrCodeResult;
    }
    /**
     * construire url + token
     */
    private static function fillUpDb
    (
        $pdo, 
        $questionnaire_id, 
        $question_id,
        $answer_id,
        $url_complete,
        $token,
        $complete_qrcode_name
    )
    {
        $stmt = $pdo->prepare("
            INSERT INTO qr_code(questionnaire_id,question_id,answer_id,qrcode_url,token,complete_qrcode_name)
            VALUES(:questionnaire_id,:question_id,:answer_id,:qrcode_url,:token,:complete_qrcode_name);
        ");
        $stmt->execute(
            [
                'questionnaire_id' => $questionnaire_id,    
                'question_id' => $question_id,
                'answer_id' => $answer_id,
                'qrcode_url' => $url_complete,
                'token' => $token,
                'complete_qrcode_name' => $complete_qrcode_name,
            ]
        );
    }
    private static function getBackExistingToken
    (
        $pdo, 
        $questionnaire_id, 
        $question_id,
        $answer_id
    )
    {
        $stmt = $pdo->prepare("
            SELECT token
            FROM qr_code 
            WHERE 
                questionnaire_id = :questionnaire_id AND
                question_id = :question_id AND
                answer_id = :answer_id ;
        ");
        $stmt->execute(
            [
                'questionnaire_id' => $questionnaire_id,    
                'question_id' => $question_id,
                'answer_id' => $answer_id
            ]
        );
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['token'];        
    }
    private static function getQRCodeName
    (
        $base_qrcode_directory,
        $base_qrcode_name,
        $questionnaire_id, 
        $question_id,
        $answer_id
    )
    {
        return    $base_qrcode_directory.
                   $base_qrcode_name.
                    $questionnaire_id."_".
                     $question_id."_".
                      $answer_id.".png";
    }
    private static function createQRCodeImage($codeContent, $chemin)
    {
        QRcode::png($codeContent, $chemin, QR_ECLEVEL_H, 3);
    }
}