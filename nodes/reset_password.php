<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

printArray($_POST);

if (isset($_POST['username'])) {
	$cleanUsername = htmlspecialchars($_POST['username']);
	$cleanPasswordOld = htmlspecialchars($_POST['password_old']);
	$cleanPasswordNew = htmlspecialchars($_POST['password_new']);
	
	$user = LdapRecord\Models\ActiveDirectory\User::findBy('samaccountname', $cleanUsername);
	
	$user->unicodepwd = [$cleanPasswordOld, $cleanPasswordNew];
	
	try {
		//$user->save();
		echo "DONE";
	
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
}




?>
<form action="#" method="post">
	<div class="mb-3">
		<label for="username" class="form-label">Username</label>
		<input type="text" class="form-control" id="username" name="username" placeholder="Username" required autofocus autocomplete="off" aria-describedby="usernameHelp">
	</div>
	<div class="mb-3">
		<label for="password_old" class="form-label">Old Password</label>
		<input type="text" class="form-control" id="username" name="password_old" placeholder="Old Password" required autocomplete="off">
	</div>
	<div class="mb-3">
		<label for="password_new" class="form-label">New Password</label>
		<input type="text" class="form-control" id="password_new" name="password_new" placeholder="New Password" required autocomplete="off" onkeyup="checkFormInput()">
		<input type="text" class="form-control" id="password_confirm" name="password_confirm" placeholder="New Password (confirm)" required  autocomplete="off" onkeyup="checkFormInput()">
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
		<input type="hidden" id="reset_by_password" name="reset_by_password">
	</div>
</form>
		

<script>
var validate_lower = document.getElementById("validate_lower");
var validate_upper = document.getElementById("validate_upper");
var validate_number = document.getElementById("validate_number");
var validate_length = document.getElementById("validate_length");
var validate_match = document.getElementById("validate_match");
var password_submit_button = document.getElementById("password_submit");

var validate_lower_icon = document.getElementById("validate_lower_icon");
var validate_upper_icon = document.getElementById("validate_upper_icon");
var validate_number_icon = document.getElementById("validate_number_icon");
var validate_length_icon = document.getElementById("validate_length_icon");
var validate_match_icon = document.getElementById("validate_match_icon");



//validate_lower.className = 'invalid';

// When the user starts to type something inside the password field
document.onkeyup = function() {
	//evt = evt || window.event;
	//var charCode = evt.keyCode || evt.which;
	//var charStr = String.fromCharCode(charCode);
	
	// Fetch the values of the new password (new and confirm)
	var password_new = document.getElementById("password_new").value;
	var password_confirm = document.getElementById("password_confirm").value;
	var password_old = document.getElementById("password_old").value;
	
	// Validate lowercase letters
	if (hasLowerCase(password_new)) {
		validate_lower.className = 'valid';
		validate_lower_icon.setAttribute("xlink:href", "img/icons.svg#check-circle");
		check_lower = true;
	} else {
		validate_lower.className = 'invalid';
		validate_lower_icon.setAttribute("xlink:href", "img/icons.svg#x-circle");
		check_lower = false;
	}
	
	// Validate uppercase letters
	if (hasUpperCase(password_new)) {
		validate_upper.className = 'valid';
		validate_upper_icon.setAttribute("xlink:href", "img/icons.svg#check-circle");
		check_upper = true;
	} else {
		validate_upper.className = 'invalid';
		validate_upper_icon.setAttribute("xlink:href", "img/icons.svg#x-circle");
		check_upper = false;
	}
	
	// Validate numbers
	if (hasNumber(password_new)) {
		validate_number.className = 'valid';
		validate_number_icon.setAttribute("xlink:href", "img/icons.svg#check-circle");
		check_number = true;
	} else {
		validate_number.className = 'invalid';
		validate_number_icon.setAttribute("xlink:href", "img/icons.svg#x-circle");
		check_number = false;
	}
	
	// Validate length
	if (hasLength(password_new)) {
		validate_length.className = 'valid';
		validate_length_icon.setAttribute("xlink:href", "img/icons.svg#check-circle");
		check_length = true;
	} else {
		validate_length.className = 'invalid';
		validate_length_icon.setAttribute("xlink:href", "img/icons.svg#x-circle");
		check_length = false;
	}
	
	// Validate match
	if (hasMatch(password_new, password_confirm)) {
		validate_match.className = 'valid';
		validate_match_icon.setAttribute("xlink:href", "img/icons.svg#check-circle");
		check_match = true;
	} else {
		validate_match.className = 'invalid';
		validate_match_icon.setAttribute("xlink:href", "img/icons.svg#x-circle");
		check_match = false;
	}
	
	if (check_lower && check_upper && check_number && check_length && check_match) {
		password_submit_button.disabled = false;
	} else {
		password_submit_button.disabled = true;
	}
};

function hasLowerCase(str) {
	return (/[a-z]/.test(str));
}

function hasUpperCase(str) {
	return (/[A-Z]/.test(str));
}

function hasNumber(str) {
	return (/[0-9]/.test(str));
}

function hasLength(str) {
	var length = str.length;
	var minLength = <?php echo PASSWORD_MINIMUM_LENGTH; ?>
	
	if (length >= minLength) {
		return true;
	} else {
		return false;
	}
}

function hasMatch(str, str2) {
	if (str && str2 && str == str2) {
		return true;
	} else {
		return false;
	}
}
</script>