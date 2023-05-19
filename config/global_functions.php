<?php
function displayTitle($title = null) {
	$output  = "<h1 class=\"h3 mb-3 font-weight-normal\">";
	$output .= $title;
	$output .= "</h1>";
	
	return $output;
}

function printArray($array) {
	echo ("<pre>");
	print_r ($array);
	echo ("</pre>");
}

function autoPluralise ($singular, $plural, $count = 1, $includeNum = false) {
	// returns the correct plural of a word/count combo
	// Usage:	$singular	= single version of the word (e.g. 'Dog')
	//       	$plural 	= plural version of the word (e.g. 'Dogs')
	//			$count		= the number you wish to work out the plural from
	//			$includeNum	= if you want to include the count number in the return string
	// Return:	the singular or plural word, based on the count
	// Example:	autoPluralise("Dog", "Dogs", 3, true)  -  would return "3 Dogs"
	//			autoPluralise("Dog", "Dogs", 1, false)  -  would return "Dog"
	
	if ($includeNum == true) {
		return ($count == 1)? $count . " " . $singular : $count . " " . $plural;
	} else {
		return ($count == 1)? $singular : $plural;
	}
}

function sendMail($subject = "No Subject Specified", $recipient = NULL, $body = NULL) {
	$mail = new PHPMailer\PHPMailer\PHPMailer(true);
	
	try {
		//Server settings
		$mail->isSMTP();
		$mail->Host       = SMTP_SERVER;
		
		//Recipients
		$mail->setFrom(SMTP_SENDER_ADDRESS, 'St Edmund Hall');
		$mail->addAddress($recipient);     //Add a recipient
		//$mail->addAddress("andrew.breakspear@seh.ox.ac.uk");     //Add a recipient
		
		//Content
		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body    = $body;
		
		$mail->send();
	} catch (Exception $e) {
		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}
}

function tokenCreate($email = null, $token = null, $user = null) {
	global $db;
	
	$sql = "INSERT INTO tokens (ip, email, token) values ('" . $_SERVER['REMOTE_ADDR'] . "', '" . $email . "', '" . $token . "')";
	$db->query($sql);
	
	logCreate("token_create", $user . " generated new token for " . $email);
	
	return true;
}

function tokensGet($current = false) {
	global $db;
	
	$sql = "SELECT * FROM tokens ";
	if ($current == true) {
		$sql .= "WHERE date_used IS NULL ";
	}
	$sql .= "ORDER BY date_created DESC";
	$array = $db->query($sql)->fetchAll();
	
	return $array;
}

function tokenGet($token = null) {
	global $db;
	
	$sql = "SELECT * FROM tokens WHERE token = '" . $token . "' LIMIT 1";
	$array = $db->query($sql)->fetchArray();
	
	return $array;
}

function tokenCheck($token = null) {
	global $db;
	
	$sql = "SELECT * FROM tokens WHERE token = '" . $token . "' AND date_used IS NULL LIMIT 1";
	$array = $db->query($sql)->fetchArray();
	
	return $array;
}

function tokenUse($token = null) {
	global $db;
	
	$sql = "UPDATE tokens SET date_used = '" . date('Y-m-d H:i:s') . "' WHERE token = '" . $token . "' LIMIT 1";
	$array = $db->query($sql);
	
	logCreate("token_use", $token . " token used");
	
	return true;
}

function tokenRemoveOldUsed() {
	global $db;
	
	$removeFromDate = date('Y-m-d H:i:s', strtotime("7 days ago"));
	
	$sql = "DELETE FROM tokens WHERE date_used < '" . $removeFromDate . "'";
	$db->query($sql);
	
	return true;
}

function tokenRemoveOldUnused() {
	global $db;
	
	$removeFromDate = date('Y-m-d H:i:s', strtotime("2 days ago"));
	
	$sql = "DELETE FROM tokens WHERE date_used IS NULL AND date_created < '" . $removeFromDate . "'";
	$db->query($sql);
	
	return true;
}

function logsRemoveOld() {
	global $db;
	
	$removeFromDate = date('Y-m-d H:i:s', strtotime("12 months ago"));
	
	$sql = "DELETE FROM logs WHERE date_created < '" . $removeFromDate . "'";
	$db->query($sql);
	
	return true;
}

function logCreate($type, $event) {
	global $db;
	
	$sql = "INSERT INTO logs (ip, type, event) values ('" . $_SERVER['REMOTE_ADDR'] . "', '" . $type . "', '" . $event . "')";
	$db->query($sql);
	
	return true;
}

function logsGet() {
	global $db;
	
	$sql = "SELECT * FROM logs ORDER BY date_created DESC LIMIT 200";
	$array = $db->query($sql)->fetchAll();
	
	return $array;
}

function createDateRangeArray($startDate, $endDate) {
	$dates = array();
	$current = strtotime($startDate);
	$date2 = strtotime($endDate);
	$stepVal = '+1 day';
	
	while($current <= $date2 ) {
		$dates[date('Y-m-d', $current)] = 0;
		$current = strtotime($stepVal, $current);
	}
	
	return $dates;
}

function totalReset($days = 30) {
	global $db;
	
	$date = date('Y-m-d', strtotime($days . " days ago"));
	
	$sql  = "SELECT count(*) AS total FROM logs ";
	$sql .= "WHERE (type = 'token_use' OR type = 'password_reset') ";
	$sql .= "AND DATE(date_created) > '" . $date . "'";
	
	$array = $db->query($sql)->fetchArray();
	
	return $array['total'];
}
?>