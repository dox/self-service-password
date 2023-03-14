<?php
include_once("config/autoload.php");
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="Andrew Breakspear, St Edmund Hall University of Oxford">
	<meta name="generator" content="Panic Nova">
	<title><?php echo SITE_NAME; ?></title>

	<link rel="canonical" href="<?php echo SITE_URL; ?>">
	
	<!-- Bootstrap core CSS/JS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>

	
	<!-- Favicons -->
	<link rel="apple-touch-icon" href="img/favicons/apple-touch-icon.png" sizes="180x180">
	<link rel="icon" href="img/favicons/favicon-32x32.png" sizes="32x32" type="image/png">
	<link rel="icon" href="img/favicons/favicon-16x16.png" sizes="16x16" type="image/png">
	<link rel="manifest" href="img/favicons/manifest.json">
	<link rel="mask-icon" href="img/favicons/safari-pinned-tab.svg" color="#7952b3">
	<link rel="icon" href="img/favicons/favicon.ico">
	
	<meta name="theme-color" content="#7952b3">
	
	<!-- Custom styles for this template -->
	<link href="css/password.css" rel="stylesheet">
</head>
<body class="text-center">
	<main class="site-content">
		<h1><a href="<?php echo SITE_URL; ?>"><img class="mb-4" src="img/crest.png" alt="" width="80px" ></a></h1>
		<?php
		if (isset($_GET['node'])) {
			$node = "nodes/" . $_GET['node'] . ".php";
		} else {
			$node = "nodes/request_reset.php";
		}
		include_once($node);
		?>
		<p class="mt-5 mb-3 text-muted">&copy; <?php echo date('Y');?> <a href="https://github.com/dox/self-service-password" class="text-muted">Andrew Breakspear</a></p>
	</main>
	
	<script src="js/password.js"></script>
</body>
</html>

<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="https://help.seh.ox.ac.uk/assets/chat/chat.min.js"></script>
<script>
$(function() {
  new ZammadChat({
	title: 'Need IT Support?',
	background: '#6b7889',
	fontSize: '12px',
	chatId: 1
  });
});
</script>