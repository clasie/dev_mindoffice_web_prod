<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="./img/exercices.png">  
    <title>Dynamic Question Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;

            color:black;
            background-color:white;
            background-image:url(img/fond_2.png);              
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
        }
        .labe_error{
            color: red;
            font-size: 14px;
        }
        input[type="submit"] {
            width: auto;
            padding: 10px 15px;
        }
        .readonly-content {
            padding: 10px;
            background-color: #eee;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 15px;
            white-space: pre-wrap; /* Conserve les retours à la ligne */
            word-wrap: break-word; /* Coupe les longs mots si nécessaire */
        }
        .site_maintenance_ou_ferme{
            margin: 135px 0px 0px 182px;
        }        
    </style>
</head>

<body>
    <div class="site_maintenance_ou_ferme">
        <p><h1><img src="./img/maintenance.png" >Ouverture prochainement...quelques réglages finaux à faire.</h1></p>
    </div>
</body>
</html>