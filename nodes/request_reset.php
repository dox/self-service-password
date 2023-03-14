<?php
echo displayTitle("Request a reset link for your SEH Password");

if (isset($_POST['email'])) {
	$cleanEmail = htmlspecialchars($_POST['email']);
		
	// check if a user exists with that email address in LDAP
	$user = LdapRecord\Models\ActiveDirectory\User::findBy('mail', $cleanEmail);
	if (isset($user)) {
		$token = bin2hex(random_bytes(18));
		tokenCreate($cleanEmail, $token);
		
		$link = "https://www.seh.ox.ac.uk/it/password/index.php?node=reset_token&token=" . $token;
		$body  = "<p>Please <a href=\"" . $link . "\">click here</a> to reset your SEH IT Password.</p>";
		$body .= "<p>If you did not request this reset, please just delete this email.</p>";
		
		sendMail("SEH Password Reset", $cleanEmail, $body);
	} else (
		logCreate("token_fail", $cleanEmail . " did not exist in LDAP")
	)
?>
<p>If the email address you have entered is registered on our systems, you will have been sent an email.</p>

<?php } else { ?>

<p><small id="inputPasswordCurrent" class="form-text text-muted">Enter your Oxford email address to request a request link.  Check your email for a link.</small></p>

<!--<p><small id="inputPasswordCurrent" class="form-text text-muted ">If you already know your current password, you can <a class="hidden" href="index.php?node=change_password">reset it to something new here</a></small></p>-->

<form method="post" id="request_email" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	<label for="login" class="visually-hidden">Oxford Email Address</label>
	<input type="email" id="email" name="email" class="form-control" placeholder="Oxford Email Address" value="<?php echo htmlentities($login) ?>" required autofocus autocomplete="off">
	<div class="form-text">Please enter your full Oxford email address (not your SSO)</div>
	<br />
	
	<button class="btn btn-lg btn-primary w-100" type="submit">Request Reset Link</button>
</form>

<?php } ?>