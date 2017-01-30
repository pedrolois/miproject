<?php
require_once(dirname(dirname(__FILE__)).'/config.php');
require_once((dirname(__FILE__)).'/entity.php');

class Email
{

    function __construct()
    {

    }

    function send_email($email_hashed, $email){
        global $CONFIG;
        require '../PHPMailer/PHPMailerAutoload.php';
        $mail             = new PHPMailer();
        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->Host       = "mail.yourdomain.com"; // SMTP server
      //  $mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
        // 1 = errors and messages
        // 2 = messages only
        $mail->SMTPAuth   = true;                  // enable SMTP authentication
        $mail->SMTPSecure = "tls";                 // sets the prefix to the servier
        $mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
        $mail->Port       = 587;                   // set the SMTP port for the GMAIL server
        $mail->Username   = "kineoemailer";             // GMAIL username
        $mail->Password   = "kineoemailer1";            // GMAIL password


        $mail->SetFrom('pedro.garcia@kineo.com', 'miOffice, Validation email');

        //$mail->AddReplyTo("pedro.garcia@kineo.com","Testing email");

        $mail->Subject    = "Hi, $email";
        //$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
        $body =$CONFIG->site."/email/index.php?validation_number=".$email_hashed."&email=".$email;
        $mail->MsgHTML($body);
        //var_dump($email);

        if (isset($CONFIG->admin_email))
        {
            $email = $CONFIG->admin_email;
        }

        $mail->AddAddress($email, "Pedro");

        //$mail->AddAttachment("images/phpmailer.gif");      // attachment
        //$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

        if(!$mail->Send()) {
           // echo "Mailer Error: " .$mail->ErrorInfo;
        } else {
            //echo "Message sent!";
        }
    }
    function validation_account ($email_hashed, $email)
    {
        global $db;

        $last_date = $this -> check_validation_time($email_hashed);
        $user = new Entity();
        $data_user = $user->get_user_data_with_email($email);
        //var_dump($data_user);
        if ($data_user['row']['email_validated']!=true) { //if is not validated, so do the validation
            //if the validation date was send it to the user more than a day ago
            if ((time() - (60 * 60 * 24)) < $last_date) {

                $stmt = $db->prepare("UPDATE user_log SET  email_validated=true WHERE validate_url=:email_hashed");
                $stmt->bindValue(':email_hashed', $email_hashed);
                if ($stmt->execute()) {
                    echo "Hi $email,<br>Your account has been validated.<br>";
                }

            } else {
                //$this->regenerate_email_validation($email_hashed, $email);
                echo "Hi $email,<br> Your validate action has been expired, we have send you a new validation email <br>";
            }
        }else{
            echo("This account was already validated.");
        }
    }
    // if the email has been experid, the will regenerate a new email_hashed, it will be saved in the db and will send it to the user again
    function regenerate_email_validation($email_hashed, $email){
        global $db;
        //$email_hashed = $this->hash_email($email_hashed);
        $stmt = $db->prepare("UPDATE user_log SET  email_validated=false, validate_url=:new_email_hashed, validate_url_date =:time  WHERE email=:email");
        $new_email_with_date =  $this->hash_email($email_hashed);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':new_email_hashed', $new_email_with_date);
        $stmt->bindValue(':time', time());
        $this -> send_email($new_email_with_date, $email);
        $stmt->execute();
    }
    //this function is to check if has been expired the validation time account
    function check_validation_time ($email_hashed){
        global $db;
        //$email_hashed = $this->hash_email($email);
        $stmt = $db-> prepare("SELECT validate_url_date FROM user_log WHERE validate_url=:email_hashed");
        $stmt->bindValue(':email_hashed', $email_hashed);

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return  $row['validate_url_date'];
    }
    //this is the unique validation account that have each user
    function url_validation_of_a_user_email($email){
        global $db;

        $stmt = $db-> prepare("SELECT validate_url FROM user_log WHERE email=:email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return  $row['validate_url'];
    }
    function hash_email($email){

        $email_hashed = hash('sha256', $email.time());
        return $email_hashed;

    }

}
?>