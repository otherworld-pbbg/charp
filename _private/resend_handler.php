<?php
include_once('generic.php');
if (isset($_POST["email"])&&isset($_POST["type"])) {
	$email = $mysqli->real_escape_string($_POST["email"]);
	$type = $_POST["type"];
	
	if ($type==1) $check = mailActivation($mysqli, $email);
	if ($type==2) $check = mailEmailChange($mysqli, $email);
	if ($type==3) $check = mailPasswordReset($mysqli, $email);
	
	if ($check == 1) {
		include_once('header.php');
		para("The email is on the way! Check your inbox in a moment (and spam folder if it's not in the former) and follow the instructions. Note that it might take a while longer on certain mail providers (mainly gmail), so be patient.");
		$displayForm = false;
	}
	else  {
		include_once('header.php');
		para("Resending activation code failed. Make sure the email address is correct. Of course if there is no pending activation attached to this email address, there is nothing to resend.");
		include_once('resend.php');
	}
}
else {
	include_once('header.php');
	include_once('resend.php');
}
?>
</body>
</html>