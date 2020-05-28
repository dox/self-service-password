<?php
#==============================================================================
# POST parameters
#==============================================================================
# Initiate vars
$result = "";
$login = $presetLogin;
$token = "";
$tokenid = "";
$newpassword = "";
$confirmpassword = "";
$ldap = "";
$userdn = "";
if (!isset($pwd_forbidden_chars)) { $pwd_forbidden_chars=""; }
$mail = "";
$extended_error_msg = "";

if (isset($_REQUEST["token"]) and $_REQUEST["token"]) { $token = strval($_REQUEST["token"]); }
 else { $result = "Token is required"; }

#==============================================================================
# Get token
#==============================================================================
if ( $result === "" ) {

    # Open session with the token
    if ( $crypt_tokens ) {
        $tokenid = decrypt($token, $keyphrase);
    } else {
        $tokenid = $token;
    }

    ini_set("session.use_cookies",0);
    ini_set("session.use_only_cookies",1);

    # Manage lifetime with sessions properties
    if (isset($token_lifetime)) {
        ini_set("session.gc_maxlifetime", $token_lifetime);
        ini_set("session.gc_probability",1);
        ini_set("session.gc_divisor",1);
    }

    session_id($tokenid);
    session_name("token");
    session_start();
    $login = $_SESSION['login'];

    if ( !$login ) {
        $result = "Token is not valid";
	error_log("Unable to open session $tokenid");
    } else {
        if (isset($token_lifetime)) {
            # Manage lifetime with session content
            $tokentime = $_SESSION['time'];
            if ( time() - $tokentime > $token_lifetime ) {
                $result = "Token is not valid";
                error_log("Token lifetime expired");
	    }
        }
    }

}

#==============================================================================
# Get passwords
#==============================================================================
if ( $result === "" ) {

    if (isset($_POST["confirmpassword"]) and $_POST["confirmpassword"]) {
	    $confirmpassword = $_POST["confirmpassword"];
	} else {
 		$result = "Please confirm your new password";
	}
    
    if (isset($_POST["newpassword"]) and $_POST["newpassword"]) {
	    $newpassword = $_POST["newpassword"];
    } else {
	    $result = "Your new password is required";
	   }
}

#==============================================================================
# Check reCAPTCHA
#==============================================================================
if ( $result === "" && $use_recaptcha ) {
    $result = check_recaptcha($recaptcha_privatekey, $recaptcha_request_method, $_POST['g-recaptcha-response'], $login);
}

#==============================================================================
# Find user
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
    }

    # Check objectClass to allow samba and shadow updates
    $ocValues = ldap_get_values($ldap, $entry, 'objectClass');
    if ( !in_array( 'sambaSamAccount', $ocValues ) and !in_array( 'sambaSAMAccount', $ocValues ) ) {
        $samba_mode = false;
    }
    if ( !in_array( 'shadowAccount', $ocValues ) ) {
        $shadow_options['update_shadowLastChange'] = false;
        $shadow_options['update_shadowExpire'] = false;
    }

    # Get user email for notification
    if ( $notify_on_change ) {
        $mailValues = ldap_get_values($ldap, $entry, $mail_attribute);
        if ( $mailValues["count"] > 0 ) {
            $mail = $mailValues[0];
        }
    }

}}}}

#==============================================================================
# Check and register new passord
#==============================================================================
# Match new and confirm password
if ( $result === "" ) {
    if ( $newpassword != $confirmpassword ) { $result="Passwords mismatch"; }
}

# Check password strength
if ( $result === "" ) {
    $result = check_password_strength( $newpassword, "", $pwd_policy_config, $login );
}

# Change password
if ($result === "") {
    $result = change_password($ldap, $userdn, $newpassword, $ad_mode, $ad_options, $samba_mode, $samba_options, $shadow_options, $hash, $hash_options, "", "");
    //send_mail($mailer, "andrew.breakspear@seh.ox.ac.uk", $mail_from, $mail_from_name, "Self Service password system used", $login." changed their password by token", $data);
    if ( $result === "passwordchanged" && isset($posthook) ) {
        $command = posthook_command($posthook, $login, $newpassword, null, $posthook_password_encodebase64);
        exec($command, $posthook_output, $posthook_return);
    }
    if ( $result !== "passwordchanged" ) {
        if ( $show_extended_error ) {
            ldap_get_option($ldap, 0x0032, $extended_error_msg);
        }
    }
}

# Delete token if all is ok
if ( $result === "passwordchanged" ) {
    $_SESSION = array();
    session_destroy();
}

#==============================================================================
# HTML
#==============================================================================
if ( in_array($result, $obscure_failure_messages) ) { $result = "badcredentials"; }
?>

<?php
if ($result == "passwordchanged") {
	$output  = "<div class=\"result alert alert-success\">";
	$output .= "<p><i class=\"fa fa-fw\" aria-hidden=\"true\"></i> You have reset your SEH password</a></p>";
	$output .= "</div>";
} else {
	$output  = "<div class=\"result alert alert-warning\">";
	$output .= "<p><i class=\"fa fa-fw\" aria-hidden=\"true\"></i> " . $result . "</p>";
	$output .= "</div>";
}

echo $output;
?>


<?php if ( $display_posthook_error and $posthook_return > 0 ) { ?>

<div class="result alert alert-warning">
<p><i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i> <?php echo $posthook_output[0]; ?></p>
</div>

<?php } ?>

<?php if ( $result !== "passwordchanged" ) { ?>



<?php if ( $result !== "Token is required" and $result !== "Token is not valid"  ) { ?>

<input type="hidden" name="token" value="<?php echo htmlentities($token) ?>" />
<input type="text" id="login" name="login" class="form-control" placeholder="Username" value="<?php echo htmlentities($login) ?>" required autofocus disabled>

<label for="newpassword" class="sr-only">New Password</label>
<input type="password" id="newpassword" name="newpassword" class="form-control" placeholder="New Password" required>

<label for="confirmpassword" class="sr-only">New Password (Confirm)</label>
<input type="password" id="confirmpassword" name="confirmpassword" class="form-control" placeholder="New Password (Confirm)" required>
	
<div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_publickey; ?>" data-theme="<?php echo $recaptcha_theme; ?>" data-type="<?php echo $recaptcha_type; ?>" data-size="<?php echo $recaptcha_size; ?>"></div>
<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=<?php echo $lang; ?>"></script>

<button class="btn btn-lg btn-primary btn-block" type="submit">Reset Password</button>

<?php } ?>

<?php } else {

    # Notify password change
    if ($mail and $notify_on_change) {
        $data = array( "login" => $login, "mail" => $mail, "password" => $newpassword);
        if ( !send_mail($mailer, $mail, $mail_from, $mail_from_name, $messages["changesubject"], $messages["changemessage"].$mail_signature, $data) ) {
            error_log("Error while sending change email to $mail (user $login)");
        }
    }

}
?>
