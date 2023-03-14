<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(1);

include_once("config.php");

include_once("vendor/autoload.php");

include_once("database.php");
include_once("global_functions.php");

use LdapRecord\Container;
use LdapRecord\Connection;
use LdapRecord\Models\Entry;
use LdapRecord\Models\ActiveDirectory\User;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Create a new LDAP connection:
$ldap_connection = new Connection([
	'hosts' => LDAP_SERVERS,
	'port' => LDAP_PORT,
	'base_dn' => LDAP_BASE_DN,
	'username' => LDAP_BIND_DN,
	'password' => LDAP_BIND_PASSWORD,
	'use_tls' => LDAP_STARTTLS,
]);

// Add the LDAP connection into the container:
Container::addConnection($ldap_connection);

$db = new db(db_host, db_username, db_password, db_name);
?>