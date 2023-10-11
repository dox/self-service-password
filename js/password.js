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
		validate_lower.className = 'text-success';
		validate_lower_icon.setAttribute("xlink:href", "img/icons.svg#check-circle");
		check_lower = true;
	} else {
		validate_lower.className = 'text-danger';
		validate_lower_icon.setAttribute("xlink:href", "img/icons.svg#x-circle");
		check_lower = false;
	}
	
	// Validate uppercase letters
	if (hasUpperCase(password_new)) {
		validate_upper.className = 'text-success';
		validate_upper_icon.setAttribute("xlink:href", "img/icons.svg#check-circle");
		check_upper = true;
	} else {
		validate_upper.className = 'text-danger';
		validate_upper_icon.setAttribute("xlink:href", "img/icons.svg#x-circle");
		check_upper = false;
	}
	
	// Validate numbers
	if (hasNumber(password_new)) {
		validate_number.className = 'text-success';
		validate_number_icon.setAttribute("xlink:href", "img/icons.svg#check-circle");
		check_number = true;
	} else {
		validate_number.className = 'text-danger';
		validate_number_icon.setAttribute("xlink:href", "img/icons.svg#x-circle");
		check_number = false;
	}
	
	// Validate length
	if (hasLength(password_new)) {
		validate_length.className = 'text-success';
		validate_length_icon.setAttribute("xlink:href", "img/icons.svg#check-circle");
		check_length = true;
	} else {
		validate_length.className = 'text-danger';
		validate_length_icon.setAttribute("xlink:href", "img/icons.svg#x-circle");
		check_length = false;
	}
	
	// Validate match
	if (hasMatch(password_new, password_confirm)) {
		validate_match.className = 'text-success';
		validate_match_icon.setAttribute("xlink:href", "img/icons.svg#check-circle");
		check_match = true;
	} else {
		validate_match.className = 'text-danger';
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
	var minLength = 2
	
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