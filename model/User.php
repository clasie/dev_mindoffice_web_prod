<?php

class User
{
    public $pdo = null;
    public $email = "";
    public $token = "";
    public $pseudo = "";
    public $id_user = "";
    public $role = "";
    public $compteur_posts = "";
    public $global_message_txt = "";
    public $status_maintenance = "";
    public $rep = null;

    public function __construct
    (
        $pdo,
        $email,
        $token        
    )
    {
        $this->pdo = $pdo;
        $this->email = $email;
        $this-> token= $token;
    }
    public function authentification() 
    {      
        /**
         * config
         */
        $req = $this->pdo->prepare("SELECT * FROM config");
        $req->execute();
        $this->rep = $req->fetch(PDO::FETCH_ASSOC);
        $this->status_maintenance = $this->rep['status_maintenance'];
        
        //echo "status_maintenance :" .$status_maintenance;
        //echo "role :".$role;
        
        /**
         * site blocked for everybody
         */
        if($this->status_maintenance == 2)
        {
            header("location: site_ferme.php");
            exit();
        }
        
        if($this->token)
        {
            $req = $this->pdo->prepare("
                SELECT * 
                FROM 
                   users 
                WHERE 
                   token = :token 
            ");
            //email = :email AND token = :token 
            $req->execute([
                //'email' => $email, 
                'token' => $this->token 
            ]);
            $this->rep = $req->fetch(PDO::FETCH_ASSOC);
        
            if($this->rep['id'] != false)
            {
                //on refresh le timeout des token et email (1h)
                setcookie("token", $this->token, time() + 3600);
                setcookie("email", $this->email, time() + 3600);
                $this->pseudo = $this->rep['pseudo'];
                $this->id_user = $this->rep['id'];
                $this->compteur_posts = $this->rep['compteur_posts'];
                $this->global_message_txt = $this->rep['global_message'];
                $this->role = $this->rep['role'];
        
                /**
                 * site accessible only for admins (role = 5)
                 */
                if($this->status_maintenance == 1)
                {
                    //site en maintyenance avec acces uniquement admin
                    if($this->role == 5){
                        //ok c'est bon on laisse continuer
                    }
                    else
                    {
                        header("location: site_en_maintenance.php");
                        exit();
                    }
                }        
            }
            else
            {
                header("location: login.php");
                exit();
            }
        }
        else
        {
            header("location: login.php");
            exit();
        }
    }

    public function getAllUserDataFromRemote()
    {

    }
}
