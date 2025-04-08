<?php
include  "./phpqrcode/qrlib.php"; 
include  "./phpqrcode/qrconfig.php"; 

$codeContent = 'https://mindoffice.be/';
$filenName = 'mindoffice.png';
$chemin = $filenName;

if(!file_exists($chemin))
{
    QRcode::png($codeContent, $chemin, QR_ECLEVEL_H, 3);
}



