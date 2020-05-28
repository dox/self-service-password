<?php

# This page is called to send a reset token by mail

#==============================================================================
# POST parameters
#==============================================================================
# Initiate vars
$result = "";
$login = $presetLogin;
$mail = "";
$ldap = "";
$userdn = "";
$token = "";

if (isset($_REQUEST["login"]) and $_REQUEST["login"]) {
	$login = strval($_REQUEST["login"]);
} else {
	$result = "Your username is required";
}
if (! isset($_POST["mail"]) and ! isset($_REQUEST["login"])) {
	$result = "ready";
}

# Check the entered username for characters that our installation doesn't support
if ( $result === "" ) {
    $result = check_username_validity($login,$login_forbidden_chars);
}

#==============================================================================
# Check reCAPTCHA
#==============================================================================
if ( $result === "" && $use_recaptcha ) {
	$result = check_recaptcha($recaptcha_privatekey, $recaptcha_request_method, $_POST['g-recaptcha-response'], $login);
}

#==============================================================================
# Check mail
#==============================================================================
if ( $result === "" ) {
    # Connect to LDAP
    $ldap = ldap_connect($ldap_url);
    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
    if ( $ldap_starttls && !ldap_start_tls($ldap) ) {
        $result = "Cannot access LDAP directory";
        error_log("LDAP - Unable to use StartTLS");
    } else {

    # Bind
    if ( isset($ldap_binddn) && isset($ldap_bindpw) ) {
        $bind = ldap_bind($ldap, $ldap_binddn, $ldap_bindpw);
    } else {
        $bind = ldap_bind($ldap);
    }

    if ( !$bind ) {
        $result = "Cannot access LDAP directory";
        $errno = ldap_errno($ldap);
        if ( $errno ) {
            error_log("LDAP - Bind error $errno  (".ldap_error($ldap).")");
        }
    } else {

    # Search for user
    $ldap_filter = str_replace("{login}", $login, $ldap_filter);
    $search = ldap_search($ldap, $ldap_base, $ldap_filter);

    $errno = ldap_errno($ldap);
    if ( $errno ) {
        $result = "Cannot access LDAP directory";
        error_log("LDAP - Search error $errno (".ldap_error($ldap).")");
    } else {

    # Get user DN
    $entry = ldap_first_entry($ldap, $search);
    $userdn = ldap_get_dn($ldap, $entry);

    if( !$userdn ) {
        $result = "Username or password incorrect";
        error_log("LDAP - User $login not found");
    } else {

    # Compare mail values
    $mailValues = ldap_get_values($ldap, $entry, $mail_attribute);
    $nameValues = ldap_get_values($ldap, $entry, "givenName");
    unset($mailValues["count"]);
    $match = 0;

    if(count($mailValues) > 0) {
       $mailValue = $mailValues[0];
       if (strcasecmp($mail_attribute, "proxyAddresses") == 0) {
           $mailValue = str_ireplace("smtp:", "", $mailValue);
       }
       $mail = $mailValue;
       $name = $nameValues[0];
       $match = true;
    }

}}}}}

#==============================================================================
# Build and store token
#==============================================================================
if ( $result === "" ) {

    # Use PHP session to register token
    # We do not generate cookie
    ini_set("session.use_cookies",0);
    ini_set("session.use_only_cookies",1);

    session_name("token");
    session_start();
    $_SESSION['login'] = $login;
    $_SESSION['time']  = time();

    if ( $crypt_tokens ) {
        $token = encrypt(session_id(), $keyphrase);
    } else {
        $token = session_id();
    }

}

#==============================================================================
# Send token by mail
#==============================================================================
if ( $result === "" ) {

    if ( empty($reset_url) ) {

        # Build reset by token URL
        $method = "http";
        if ( !empty($_SERVER['HTTPS']) ) { $method .= "s"; }
        $server_name = $_SERVER['SERVER_NAME'];
        $server_port = $_SERVER['SERVER_PORT'];
        $script_name = $_SERVER['SCRIPT_NAME'];

        # Force server port if non standard port
        if (   ( $method === "http"  and $server_port != "80"  )
            or ( $method === "https" and $server_port != "443" )
        ) {
            $server_name .= ":".$server_port;
        }

        $reset_url = $method."://".$server_name.$script_name;
    }

    $reset_url .= "?action=resetbytoken&token=".urlencode($token);

    if ( !empty($reset_request_log) ) {
        error_log("Send reset URL " . ( $debug ? "$reset_url" : "HIDDEN") . "\n\n", 3, $reset_request_log);
    } else {
        error_log("Send reset URL " . ( $debug ? "$reset_url" : "HIDDEN"));
    }

    $data = array( "login" => $login, "mail" => $mail, "url" => $reset_url, "name" => $name ) ;

    $message = "Dear {name},\n\nPlease click on the link below to reset your SEH password:\n{url}\n\nIf you didn't request this password reset, please just ignore this email.";

    # Send message
    if ( send_mail($mailer, $mail, $mail_from, $mail_from_name, "SEH Password Link", $message, $data) ) {
        $result = "tokensent";
    } else {
        $result = "Error when sending confirmation email";
        error_log("Error while sending token to $mail (user $login)");
    }
}

#==============================================================================
# HTML
#==============================================================================
if ( in_array($result, $obscure_failure_messages) ) { $result = "Username or password incorrect"; }
?>

<h1 class="h3 mb-3 font-weight-normal">Request a reset link for your SEH Password</h1>

<?php
if ($result == "tokensent") {
	$output  = "<div class=\"result alert alert-success\">";
	$output .= "<p><i class=\"fa fa-fw\" aria-hidden=\"true\"></i> Link sent.  Please check your <a href=\"https://www.office.com\">Oxford email</a></p>";
	$output .= "</div>";
} else {
	$output = "";
}

echo $output;

if ( $result !== "tokensent" ) { ?>

	<small id="inputPasswordCurrent" class="form-text text-muted">Enter your username to reset your password. An email will be sent to your Oxford email  address. When you receive this email, click the link inside to complete the password reset.</a></small><br />

	<small id="inputPasswordCurrent" class="form-text text-muted">If you already know your current password, you can <a href="index.php?action=change">reset it to something new here</a></small><br />
	<label for="login" class="sr-only">Username</label>
	<input type="text" id="login" name="login" class="form-control" placeholder="Username" value="<?php echo htmlentities($login) ?>" required autofocus autocomplete="off">
	<br />

	<div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_publickey; ?>" data-theme="<?php echo $recaptcha_theme; ?>" data-type="<?php echo $recaptcha_type; ?>" data-size="<?php echo $recaptcha_size; ?>"></div>
	<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=<?php echo $lang; ?>"></script>

	<br />

	<button class="btn btn-lg btn-primary btn-block" type="submit">Reset Password</button>


<?php } ?>
