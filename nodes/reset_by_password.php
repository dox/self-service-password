<?php
if (isset($_GET['username'])) {
	$cleanUsername = htmlspecialchars($_GET['username']);
}

if (isset($_POST['username'])) {
	$cleanUsername = htmlspecialchars($_POST['username']);
	$cleanPasswordOld = htmlspecialchars($_POST['password_old']);
	$cleanPasswordNew = htmlspecialchars($_POST['password_new']);
	
	$user = LdapRecord\Models\ActiveDirectory\User::findBy('samaccountname', $cleanUsername);
	
	$user->unicodepwd = [$cleanPasswordOld, $cleanPasswordNew];
	
	try {
		if ($user->isEnabled()) {
			$user->save();
			logCreate("password_reset", $cleanUsername . " reset their password");
			echo "<div class=\"alert alert-success\" role=\"alert\">Your password has been successfully updated</div>";
		} else {
			logCreate("password_reset", $cleanUsername . " failed to reset their password because their account was disabled");
			
			exit("The account you have attempted to reset is currently disabled.  Please contact the IT Office by emailing <a href=\"mailto:help@seh.ox.ac.uk\">help@seh.ox.ac.uk</a>");
		}
		// User password reset!
	} catch (\LdapRecord\Exceptions\InsufficientAccessException $ex) {
		// The currently bound LDAP user does not
		// have permission to reset passwords.
	} catch (\LdapRecord\Exceptions\ConstraintException $ex) {
		// The users new password does not abide
		// by the domains password policy.
	} catch (\LdapRecord\LdapRecordException $ex) {
		// Failed resetting password. Get the last LDAP
		// error to determine the cause of failure.
		$error = $ex->getDetailedError();
		
		logCreate("password_reset", $cleanUsername . " failed to reset their password because they entered incorect credentials");
		
		echo "<p>Username or password incorrect</p>";
		echo "<p><a href=\"index.php?node=reset_by_password&username=" . $cleanUsername . "\">Click here</a> to try again</p>";
		
		printArray("Error Code: " . $error->getErrorCode() . "<br />" . $error->getErrorMessage());
		//printArray($error->getDiagnosticMessage());
	}
} else {

echo displayTitle("Reset your SEH Password");

?>

<p><small id="inputPasswordCurrent" class="form-text text-muted ">If you don't know your current password, you can<br /><a class="hidden" href="index.php?node=request_token">request a reset link here</a></small></p>

<form action="#" method="post">
	<div class="mb-3">
		<label for="username" class="form-label">Username</label>
		<input type="text" class="form-control form-control-lg" id="username" name="username" placeholder="Username" value="<?php echo $cleanUsername; ?>" required autofocus autocomplete="off" aria-describedby="usernameHelp">
	</div>
	<div class="mb-3">
		<label for="password_old" class="form-label">Old Password</label>
		<input type="password" class="form-control form-control-lg" id="password_old" name="password_old" placeholder="Old Password" required autocomplete="off">
	</div>
	<div class="mb-3">
		<label for="password_new" class="form-label">New Password</label>
		<input type="password" class="form-control form-control-lg" id="password_new" name="password_new" placeholder="New Password" required autocomplete="off">
		<input type="password" class="form-control form-control-lg" id="password_confirm" name="password_confirm" placeholder="New Password (confirm)" required  autocomplete="off">
	</div>
	<div class="mb-3">
		<ul class="list-unstyled text-start">
			<li id="validate_lower" class="text-danger">
				<svg width="1em" height="1em"><use xlink:href="img/icons.svg#x-circle" id="validate_lower_icon"/></svg> lowercase letter
			</li>
			<li id="validate_upper" class="text-danger">
				<svg width="1em" height="1em"><use xlink:href="img/icons.svg#x-circle" id="validate_upper_icon"/></svg> UPPERCASE letter
			</li>
			<li id="validate_number" class="text-danger">
				<svg width="1em" height="1em"><use xlink:href="img/icons.svg#x-circle" id="validate_number_icon"/></svg> number
			</li>
			<li id="validate_length" class="text-danger">
				<svg width="1em" height="1em"><use xlink:href="img/icons.svg#x-circle" id="validate_length_icon"/></svg> <?php echo PASSWORD_MINIMUM_LENGTH; ?> characters minimum
			</li>
			<li id="validate_match" class="text-danger">
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
}
?>

<script>

</script>