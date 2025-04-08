<?php
header("Cache-Control: no-cache, must-revalidate"); 
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 

include  "./phpqrcode/qrlib.php";
QRcode::png('https://www.mindoffice.be/accessibility/question_du_jour.php?token=6fed15ae809791763a5fc54c93cc310ec3296e8ce25f35a769b9578c08697331');
//QRcode::png('https://www.youtube.com');
