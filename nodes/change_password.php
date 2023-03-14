<?php
//$_POST['username'] = "2017test";
$sanitisedUsername = escape($_POST['username']);

$user = LdapRecord\Models\ActiveDirectory\User::findBy('samaccountname', $sanitisedUsername);

if ($user) {
	// user found in LDAP
	
	if ($user->userAccountControl[0] == "514") {
		// user is locked in LDAP
		echo "Your account has been locked.  This is generally due to an expired password.  Please email the IT Office at help@seh.ox.ac.uk for assistance";
	
	} else {
		// user found and account is live
		$user->unicodepwd = [$_POST['password_old'], $_POST['password_new']];
		
		try {
			$user->save();
		
			// User password changed!
		} catch (\LdapRecord\Exceptions\InsufficientAccessException $ex) {
			// The currently bound LDAP user does not
			// have permission to change passwords.
			echo "Permission not granted";
		} catch (\LdapRecord\Exceptions\ConstraintException $ex) {
			// The users new password does not abide
			// by the domains password policy.
			echo "Password policy prevent this password from being accepted";
		} catch (\LdapRecord\LdapRecordException $ex) {
			// Failed changing password. Get the last LDAP
			// error to determine the cause of failure.
			echo "general error";
			$error = $ex->getDetailedError();
		
			echo $error->getErrorCode();
			echo $error->getErrorMessage();
			echo $error->getDiagnosticMessage();
		}
		
		echo "Password change succesful!";
	}
	
	
} else {
	echo "NO USER";
}



echo displayTitle("Change your SEH Password");


?>

<p><small id="inputPasswordCurrent" class="form-text text-muted">Enter your Oxford email address to request a request link.  Check your email for a link or <a href="index.php?node=reset_token">click here</a> to enter the token manually</small></p>

<p><small id="inputPasswordCurrent" class="form-text text-muted">If you don't know your current password, you can <a href="index.php?node=request_reset">reset it to something new here</a></small></p>

<form action="#" method="post">
	<div class="mb-3">
		<label for="username" class="form-label">Username</label>
		<input type="text" class="form-control" id="username" name="username" placeholder="Username" required autofocus autocomplete="off">
	</div>
	<div class="mb-5">
		<label for="password_old" class="form-label">Old Password</label>
		<input type="text" class="form-control" id="password_old" name="password_old" placeholder="Old Password" required autocomplete="off">
	</div>
	<div class="mb-3">
		<label for="password_new" class="form-label">New Password</label>
		<input type="text" class="form-control form-group-top" id="password_new" name="password_new" placeholder="New Password" required autocomplete="off" onkeyup="checkFormInput()">
		<input type="text" class="form-control form-group-bottom" id="password_confirm" name="password_confirm" placeholder="New Password (confirm)" required  autocomplete="off" onkeyup="checkFormInput()">
	</div>
	<div class="mb-3">
		<ul class="list-unstyled text-start">
			<li id="validate_lower" class="invalid">
				<svg width="1em" height="1em"><use xlink:href="img/icons.svg#x-circle" id="validate_lower_icon"/></svg> lowercase letter
			</li>
			<li id="validate_upper" class="invalid">
				<svg width="1em" height="1em"><use xlink:href="img/icons.svg#x-circle" id="validate_upper_icon"/></svg> UPPERCASE letter
			</li>
			<li id="validate_number" class="invalid">
				<svg width="1em" height="1em"><use xlink:href="img/icons.svg#x-circle" id="validate_number_icon"/></svg> number
			</li>
			<li id="validate_length" class="invalid">
				<svg width="1em" height="1em"><use xlink:href="img/icons.svg#x-circle" id="validate_length_icon"/></svg> <?php echo autoPluralise(" character", " characters", PASSWORD_MINIMUM_LENGTH, true); ?> minimum
			</li>
			<li id="validate_match" class="invalid">
				<svg width="1em" height="1em"><use xlink:href="img/icons.svg#check-circle" id="validate_match_icon"/></svg> passwords match
			</li>
		</ul>
	</div>
	<div class="mb-3">
		<button class="btn btn-lg btn-primary w-100" id="password_submit" type="submit" disabled="disabled">Reset Password</button>
		<input type="hidden" id="reset_by_password" name="reset_by_password">
	</div>
</form>



<?php
// Get all objects:
$objects = LdapRecord\Models\Entry::get();

// Get a single object:
$object = LdapRecord\Models\Entry::find('CN=2017 Test,OU=2017,OU=SEH Students,DC=SEH,DC=ox,DC=ac,DC=uk');

// Getting attributes:
foreach ($object->memberof as $group) {
	//echo $group;
}
?>