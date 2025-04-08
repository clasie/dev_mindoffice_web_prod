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
    </style>
</head>

<body>
    <form method="POST" action="">

        <label class="labe_error" ><?php echo $error_message_global; ?></label>

        <p><h1><img src="./img/pen.png" alt="Formumlaire d'inscription"> Félicitation!</h1></p>

        <label for="pseudo">Vous êtes à présent complètement désinscrit.</label>
        <p>
        <a href="login.php">Retourner à la page de login</a>
        </p>  
    </form>
</body>
</html>

