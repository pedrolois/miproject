<?php
session_start();
require_once(dirname(dirname(__FILE__)).'/config.php');
require_once(dirname(dirname(__FILE__)).'/class/email.php');
require_once(dirname(dirname(__FILE__)).'/class/entity.php');
// it will never let you open index(login) page if session is set
if ( isset($_SESSION['user_log_id'])!="" ) 
{
    header("Location: ../index.php");
    exit;
}
$send_email = new Email();
$user = new Entity();
$error = false;
$email ='';
$pass ='';
$emailError ='';
$passError ='';

if( isset($_POST['btn_login']) ) {
// prevent sql injections/ clear user invalid inputs
    $email = trim($_POST['email']);
    $email = strip_tags($email);
    $email = htmlspecialchars($email);

    $pass = trim($_POST['pass']);
    $pass = strip_tags($pass);
    $pass = htmlspecialchars($pass);

    // prevent sql injections / clear user invalid inputs
    if(empty($email)){
        $error = true;
        $emailError = "Please enter your email address.";
    } elseif ( !filter_var($email,FILTER_VALIDATE_EMAIL) ) {
        $error = true;
        $emailError = "Please enter valid email address.";
    }

    if(empty($pass)){
        $error = true;
        $passError = "Please enter your password.";
    }
    $user_data = $user->get_user_data_with_email($email);
    //print_r($user_data);
    if($user_data['count']==0){
        $emailError = "This email is not in our database. Click in Sign Up.";
        $error = true;
    }
    // if there's no error, continue to Login
    if (!$error) {
        $password = hash('sha256', $pass); // password hashing using SHA256
        if ($user_data['row']['email_validated'] == false) {
            $err_no_activated_email = "This email haven't been ever validated. Click here to regenerate the email.";
        }else{
            if( $user_data['count'] == 1 && $user_data['row']['password']==$password) {

                $_SESSION['user_log_id'] = $user_data['row']['id'];
                header("Location: mioffice/index.php");

            } else {
                $errMSG = "Incorrect Credentials, Try again...<br>";
            }
        }
    }
}

?>

<html>
<head>
</head>
<body>
    <div>
            <h2 class="">Sign In.</h2>
            <hr />
        <?php
        //var_dump($error);
        if (!$error || empty($errMSG)) {
            if (isset($_POST['btn_regenerate_email'])) {
                echo("Email Validation resend it");
                $email_hashed = $send_email->url_validation_of_a_user_email($_POST['email_passed']);
                $send_email->regenerate_email_validation($email_hashed, $email);
            }
        }

            if (!empty($errMSG)) {
                echo $errMSG;
            }
            if (!empty($err_no_activated_email)) {
                echo $err_no_activated_email;
                echo "<form name='form_regenerate' action=".$_SERVER['PHP_SELF']." method='post'>
                        <input type='hidden' name='email_passed' value='$email'>
                        <button type = 'submit' name = 'btn_regenerate_email'>Regenerate email</button>
                      </form>
                      <br>";
            }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>"  method="post">
            <input type="email" name="email"  placeholder="Your Email" value="<?php echo $email; ?>" maxlength="40" />
               <?php echo $emailError; ?>
            <div class="form-group">
                <input type="password" name="pass"  placeholder="Your Password" maxlength="15" />
               <?php echo $passError; ?>
            </div>

                <button type="submit"  name="btn_login">Sign In</button>
                <hr/>
                <a href="SignUp.php">Sign Up Here...</a>

        </form>
    </div>
</body>
</html>
