<?php
//This is the index page send it to the user. From this page the system will check the email and the validation number.
//If is not validated and is not expired the period, it will validate the account
//If is validated already,it will let the user know
//If is expired will send a new email validation link
//
require_once(dirname(dirname(__FILE__)).'/class/email.php');
$validation_email = new Email();
$validation_email -> validation_account($_GET['validation_number'], $_GET['email']);

?>
<br><a href='../log/SignIn.php'>Sign In Here...</a>
<br><a href="../log/SignUp.php">Sign Up Here...</a>
