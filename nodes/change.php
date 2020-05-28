<?php
#==============================================================================
# POST parameters
#==============================================================================
# Initiate vars
$result = "";
$login = $presetLogin;
$confirmpassword = "";
$newpassword = "";
$oldpassword = "";
$ldap = "";
$userdn = "";
if (!isset($pwd_forbidden_chars)) { $pwd_forbidden_chars=""; }
$mail = "";
$extended_error_msg = "";

if (isset($_POST["confirmpassword"]) and $_POST["confirmpassword"]) { $confirmpassword = strval($_POST["confirmpassword"]); }
 else { $result = "Please confirm your new password"; }
if (isset($_POST["newpassword"]) and $_POST["newpassword"]) { $newpassword = strval($_POST["newpassword"]); }
 else { $result = "Your new password is required"; }
if (isset($_POST["oldpassword"]) and $_POST["oldpassword"]) { $oldpassword = strval($_POST["oldpassword"]); }
 else { $result = "Your old password is required"; }
if (isset($_REQUEST["login"]) and $_REQUEST["login"]) { $login = strval($_REQUEST["login"]); }
 else { $result = "Your username is required"; }
if (! isset($_REQUEST["login"]) and ! isset($_POST["confirmpassword"]) and ! isset($_POST["newpassword"]) and ! isset($_POST["oldpassword"]))
 { $result = "Change your password"; }

# Check the entered username for characters that our installation doesn't support
if ( $result === "" ) {
    $result = check_username_validity($login,$login_forbidden_chars);
}

# Match new and confirm password
if ( $newpassword != $confirmpassword ) { $result="Passwords mismatch"; }

#==============================================================================
# Check reCAPTCHA
#==============================================================================
if ( $result === "" && $use_recaptcha ) {
    $result = check_recaptcha($recaptcha_privatekey, $recaptcha_request_method, $_POST['g-recaptcha-response'], $login);
}

#==============================================================================
# Check old password
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
        error_log("LDAP - Search error $errno  (".ldap_error($ldap).")");
    } else {

    # Get user DN
    $entry = ldap_first_entry($ldap, $search);
    $userdn = ldap_get_dn($ldap, $entry);

    if( !$userdn ) {
        $result = "Username or password incorrect";
        error_log("LDAP - User $login not found");
    } else {

    # Get user email for notification
    if ( $notify_on_change ) {
        $mailValues = ldap_get_values($ldap, $entry, $mail_attribute);
        if ( $mailValues["count"] > 0 ) {
            $mail = $mailValues[0];
        }
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

    # Bind with old password
    $bind = ldap_bind($ldap, $userdn, $oldpassword);
    if ( !$bind ) {
        $result = "Username or password incorrect";
        $errno = ldap_errno($ldap);
        if ( $errno ) {
            error_log("LDAP - Bind user error $errno  (".ldap_error($ldap).")");
        }
        if ( ($errno == 49) && $ad_mode ) {
            if ( ldap_get_option($ldap, 0x0032, $extended_error) ) {
                error_log("LDAP - Bind user extended_error $extended_error  (".ldap_error($ldap).")");
                $extended_error = explode(', ', $extended_error);
                if ( strpos($extended_error[2], '773') or strpos($extended_error[0], 'NT_STATUS_PASSWORD_MUST_CHANGE') ) {
                    error_log("LDAP - Bind user password needs to be changed");
                    $result = "";
                }
                if ( ( strpos($extended_error[2], '532') or strpos($extended_error[0], 'NT_STATUS_ACCOUNT_EXPIRED') ) and $ad_options['change_expired_password'] ) {
                    error_log("LDAP - Bind user password is expired");
                    $result = "";
                }
                unset($extended_error);
            }
        }
    }
    if ( $result === "" )  {

        # Rebind as Manager if needed
        if ( $who_change_password == "manager" ) {
            $bind = ldap_bind($ldap, $ldap_binddn, $ldap_bindpw);
        }

    }}}}}

}

#==============================================================================
# Check password strength
#==============================================================================
if ( $result === "" ) {
    $result = check_password_strength( $newpassword, $oldpassword, $pwd_policy_config, $login );
}

#==============================================================================
# Change password
#==============================================================================
if ( $result === "" ) {
    $result = change_password($ldap, $userdn, $newpassword, $ad_mode, $ad_options, $samba_mode, $samba_options, $shadow_options, $hash, $hash_options, $who_change_password, $oldpassword);
    if ( $result === "passwordchanged" && isset($posthook) ) {
        $command = posthook_command($posthook, $login, $newpassword, $oldpassword, $posthook_password_encodebase64);
        exec($command, $posthook_output, $posthook_return);
    }
    if ( $result !== "passwordchanged" ) {
        if ( $show_extended_error ) {
            ldap_get_option($ldap, 0x0032, $extended_error_msg);
        }
    }
}

#==============================================================================
# HTML
#==============================================================================
if ( in_array($result, $obscure_failure_messages) ) { $result = "Username or password incorrect"; }
?>

<h1 class="h3 mb-3 font-weight-normal">Reset your SEH Password</h1>


<?php
if ($result == "passwordchanged") {
	$output  = "<div class=\"result alert alert-success\">";
	$output .= "<p>You have reset your SEH password</p>";
	$output .= "</div>";
} elseif ($result == "Username or password incorrect") {
	$output  = "<div class=\"result alert alert-warning\">";
	$output .= "<p>Username or password incorrect</p>";
	$output .= "</div>";
} elseif ($result == "badcaptcha") {
	$output  = "<div class=\"result alert alert-warning\">";
	$output .= "<p>Captcha not correct</p>";
	$output .= "</div>";
} else {
	$output = $result;
}

echo $output;
?>

<?php if ( $result !== "passwordchanged" ) { ?>





<small id="inputPasswordCurrent" class="form-text text-muted">If you don't know your current password, you can <a href="index.php?action=sendtoken">request a reset link here</a></small><br />

	<label for="login" class="sr-only">Username</label>
	<input type="text" id="login" name="login" class="form-control cssGroupTop" placeholder="Username" value="<?php echo htmlentities($login) ?>" required autofocus autocomplete="off">

	<label for="oldpassword" class="sr-only">Old Password</label>
	<input type="password" id="oldpassword" name="oldpassword" class="form-control cssGroupBottom" placeholder="Old Password" required>

	<br />

	<label for="newpassword" class="sr-only">New Password</label>
	<input type="password" id="newpassword" name="newpassword" class="form-control cssGroupTop" placeholder="New Password" required>

	<label for="confirmpassword" class="sr-only">New Password (Confirm)</label>
	<input type="password" id="confirmpassword" name="confirmpassword" class="form-control cssGroupBottom" placeholder="New Password (Confirm)" required>

	<div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_publickey; ?>" data-theme="<?php echo $recaptcha_theme; ?>" data-type="<?php echo $recaptcha_type; ?>" data-size="<?php echo $recaptcha_size; ?>"></div>
	<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=<?php echo $lang; ?>"></script>

	<br />

	<button class="btn btn-lg btn-primary btn-block" type="submit">Reset Password</button>

	<?php
if ($pwd_show_policy_pos === 'below') {
    show_policy($messages, $pwd_policy_config, $result);
}
?>

<?php } else {

    # Notify password change
    //send_mail($mailer, "andrew.breakspear@seh.ox.ac.uk", $mail_from, $mail_from_name, "Self Service password system used", $login." changed their password", $data);
    if ($mail and $notify_on_change) {
        $data = array( "login" => $login, "mail" => $mail, "password" => $newpassword);
        if ( !send_mail($mailer, $mail, $mail_from, $mail_from_name, "Password Reset Complete", "Your SEH password has been reset.  Please contact help@seh.ox.ac.uk immediately if this wasn't you.", $data) ) {
            error_log("Error while sending change email to $mail (user $login)");
        }
    }

    if (isset($messages['passwordchangedextramessage'])) {
        echo "<div class=\"result alert alert-" . get_criticity($result) . "\">";
        echo "<p><i class=\"fa fa-fw " . get_fa_class($result) . "\" aria-hidden=\"true\"></i> " . $messages['passwordchangedextramessage'] . "</p>";
        echo "</div>\n";
    }

}
?>
