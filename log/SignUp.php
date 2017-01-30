<?php
session_start();
if( isset($_SESSION['user'])!="" ){
    header(dirname(__FILE__).'/index.php');
}
require_once(dirname(dirname(__FILE__)).'/config.php');
require_once(dirname(dirname(__FILE__)).'/class/email.php');
require_once(dirname(dirname(__FILE__)).'/class/entity.php');
$email_validate = new Email();
$user = new Entity();
$error = false;
$email ='';
$pass ='';
$emailError ='';
$passError ='';
$passCheckError ='';
if ( isset($_POST['btn_signup']) ) {

    // clean user inputs to prevent sql injections
    $email = trim($_POST['email']);
    $email = strip_tags($email);
    $email = htmlspecialchars($email);
    $pass = trim($_POST['pass']);
    $pass = strip_tags($pass);
    $pass = htmlspecialchars($pass);
    $pass2 = $_POST['password_check'];
    //basic email validation
    if ( !filter_var($email,FILTER_VALIDATE_EMAIL) ) {
        $error = true;
        $emailError = "Please enter valid email address.";
    } else {
        // check email exist or not
        $user_data = $user->get_user_data_with_email($email);

        if($user_data['count'] != 0){
            $error = true;
            $emailError = "Provided Email is already in use.";
        }
    }
    // password validation
    if (empty($pass)){
        $error = true;
        $passError = "Please enter password.";
    } else if(strlen($pass) < 6) {
        $error = true;
        $passError = "Password must have at least 6 characters.";
    }

    if ($pass != $pass2){
        $error = true;
        $passCheckError = "The passwords are different.";
    }
    // password encrypt using SHA256();
    $password = hash('sha256', $pass);
 //   var_dump($error);
    if( !$error ) {
        $email_hashed =  $email_validate->hash_email($email);

        $inserted_user = $user->insert_new_log_user($email, $password, $email_hashed);
       //If is inserted correctly then send email
        if ($inserted_user) {
            $errMSG = "Successfully registered, now check your email to activate your account.";
            $email_validate ->send_email($email_hashed, $email);
            $email='';
            $pass='';
        } else {
            $errMSG = "Something went wrong, try again later...";
        }
    }
}
?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Login & Registration System</title>
        <link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css"  />
        <link rel="stylesheet" href="style.css" type="text/css" />
    </head>
    <body>
    <div>
        <div id="login-form">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
                <div>
                        <h2>Sign Up.</h2>
                        <?php
                        if ( isset($errMSG) ) {
                            echo $errMSG;
                        }
                    ?>
                    <div>
                            <input type="email" name="email" placeholder="Enter Your Email" maxlength="40" value="<?php echo $email ?>" />
                            <?php echo $emailError; ?>
                    </div>
                    <div>
                            <input type="password" name="pass"  placeholder="Enter Password" maxlength="15" />
                            <?php echo $passError; ?>
                    </div>
                    <div>
                        <input type="password" name="password_check"  placeholder="Repite Password" maxlength="15" />
                        <?php echo $passCheckError; ?>
                    </div>
                    <div>
                            <button type="submit" name="btn_signup">Sign Up</button>
                    </div>
                    <div>
                        <a href="signIn.php">Sign in Here...</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </body>
    </html>
<?php ob_end_flush(); ?>