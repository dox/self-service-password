<?php
$cleanToken = htmlspecialchars($_GET['token']);

$database_token = tokenCheck($cleanToken);

if (isset($_POST['reset_by_token'])) {
	$cleanUsername = htmlspecialchars($_POST['username']);
	$cleanPasswordNew = htmlspecialchars($_POST['password_new']);
	
	
	$user = LdapRecord\Models\ActiveDirectory\User::findBy('samaccountname', $cleanUsername);
	$attributes = $user->getAttributes();

	$ldapMail = $attributes['mail'][0];
	
	if (isset($database_token['date_used'])) {
		exit("The reset code you have entered has already been used.");
	}
	
	if (!$database_token['email'] == $ldapMail) {
		exit("The reset code you have entered does not match the email address we have on file for this account.");
	}
	
	$user->unicodepwd = $cleanPasswordNew;
	
	try {
		if ($user->isEnabled()) {
			$user->save();
			tokenUse($database_token['token']);
			echo "<div class=\"alert alert-success\" role=\"alert\">Your password has been successfully updated</div>";
			
			tokenRemoveOldUsed();
			tokenRemoveOldUnused();
		} else {
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
	
		echo $error->getErrorCode();
		echo $error->getErrorMessage();
		echo $error->getDiagnosticMessage();
	}
} else {

?>

<form action="#" method="post">
	<div class="mb-3">
		<label for="username" class="form-label">Username</label>
		<input type="text" class="form-control" id="username" name="username" placeholder="Username" required autofocus autocomplete="off">
		<div class="form-text">Normally this is just the first part of your SSO (i.e. sedm1234)</div>
	</div>
	<div class="mb-3">
		<label for="password_old" class="form-label">Reset Code</label>
		<input type="text" class="form-control" id="token" name="token" placeholder="Reset Code" value="<?php echo $cleanToken;?>" required autocomplete="off">
	</div>
	<div class="mb-3">
		<label for="password_new" class="form-label">New Password</label>
		<input type="password" class="form-control" id="password_new" name="password_new" placeholder="New Password" required autocomplete="off" onkeyup="checkFormInput()">
		<input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="New Password (confirm)" required onkeyup="checkFormInput()">
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
				<svg width="1em" height="1em"><use xlink:href="img/icons.svg#x-circle" id="validate_length_icon"/></svg> <?php echo PASSWORD_MINIMUM_LENGTH; ?> characters minimum
			</li>
			<li id="validate_match" class="invalid">
				<svg width="1em" height="1em"><use xlink:href="img/icons.svg#check-circle" id="validate_match_icon"/></svg> passwords match
			</li>
		</ul>
	</div>
	<div class="mb-3">
		<button class="btn btn-lg btn-primary w-100" id="password_submit" type="submit" disabled="disabled">Reset Password</button>
		<input type="hidden" id="reset_by_token" name="reset_by_token">
	</div>
</form>
<?php
}
?>