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
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

	
	<!-- Favicons -->
	<link rel="apple-touch-icon" href="img/favicons/apple-touch-icon.png" sizes="180x180">
	<link rel="icon" href="img/favicons/favicon-32x32.png" sizes="32x32" type="image/png">
	<link rel="icon" href="img/favicons/favicon-16x16.png" sizes="16x16" type="image/png">
	<link rel="mask-icon" href="img/favicons/safari-pinned-tab.svg" color="#7952b3">
	<link rel="icon" href="img/favicons/favicon.ico">
	
	<meta name="theme-color" content="#7952b3">
	
	<!-- Custom styles for this template -->
	<link href="css/password.css" rel="stylesheet">
	
	<style>
	  .bd-placeholder-img {
		font-size: 1.125rem;
		text-anchor: middle;
		-webkit-user-select: none;
		-moz-user-select: none;
		user-select: none;
	  }
	
	  @media (min-width: 768px) {
		.bd-placeholder-img-lg {
		  font-size: 3.5rem;
		}
	  }
	
	  .b-example-divider {
		width: 100%;
		height: 3rem;
		background-color: rgba(0, 0, 0, .1);
		border: solid rgba(0, 0, 0, .15);
		border-width: 1px 0;
		box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
	  }
	
	  .b-example-vr {
		flex-shrink: 0;
		width: 1.5rem;
		height: 100vh;
	  }
	
	  .bi {
		vertical-align: -.125em;
		fill: currentColor;
	  }
	
	  .nav-scroller {
		position: relative;
		z-index: 2;
		height: 2.75rem;
		overflow-y: hidden;
	  }
	
	  .nav-scroller .nav {
		display: flex;
		flex-wrap: nowrap;
		padding-bottom: 1rem;
		margin-top: -1px;
		overflow-x: auto;
		text-align: center;
		white-space: nowrap;
		-webkit-overflow-scrolling: touch;
	  }
	
	  .btn-bd-primary {
		--bd-violet-bg: #712cf9;
		--bd-violet-rgb: 112.520718, 44.062154, 249.437846;
	
		--bs-btn-font-weight: 600;
		--bs-btn-color: var(--bs-white);
		--bs-btn-bg: var(--bd-violet-bg);
		--bs-btn-border-color: var(--bd-violet-bg);
		--bs-btn-hover-color: var(--bs-white);
		--bs-btn-hover-bg: #6528e0;
		--bs-btn-hover-border-color: #6528e0;
		--bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
		--bs-btn-active-color: var(--bs-btn-hover-color);
		--bs-btn-active-bg: #5a23c8;
		--bs-btn-active-border-color: #5a23c8;
	  }
	
	  .bd-mode-toggle {
		z-index: 1500;
	  }
	
	  .bd-mode-toggle .dropdown-menu .active .bi {
		display: block !important;
	  }
	</style>

</head>
<body class="text-center bg-body-tertiary">
	<main class="form-signin w-100 m-auto">
		<h1><a href="<?php echo SITE_URL; ?>"><img class="mb-4" src="img/crest.png" alt="" width="80px" ></a></h1>
		<?php
		if (isset($_GET['node'])) {
			$node = "nodes/" . $_GET['node'] . ".php";
		} else {
			$node = "nodes/request_token.php";
		}
		include_once($node);
		?>
		<p class="mt-5 mb-3 text-muted">&copy; <?php echo date('Y');?> <a href="https://github.com/dox/self-service-password" class="text-muted">Andrew Breakspear</a></p>
	</main>
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


/*!
 * Color mode toggler for Bootstrap's docs (https://getbootstrap.com/)
 * Copyright 2011-2023 The Bootstrap Authors
 * Licensed under the Creative Commons Attribution 3.0 Unported License.
 */

(() => {
  'use strict'

  const getStoredTheme = () => localStorage.getItem('theme')
  const setStoredTheme = theme => localStorage.setItem('theme', theme)

  const getPreferredTheme = () => {
	const storedTheme = getStoredTheme()
	if (storedTheme) {
	  return storedTheme
	}

	return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  }

  const setTheme = theme => {
	if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
	  document.documentElement.setAttribute('data-bs-theme', 'dark')
	} else {
	  document.documentElement.setAttribute('data-bs-theme', theme)
	}
  }

  setTheme(getPreferredTheme())

  const showActiveTheme = (theme, focus = false) => {
	const themeSwitcher = document.querySelector('#bd-theme')

	if (!themeSwitcher) {
	  return
	}

	const themeSwitcherText = document.querySelector('#bd-theme-text')
	const activeThemeIcon = document.querySelector('.theme-icon-active use')
	const btnToActive = document.querySelector(`[data-bs-theme-value="${theme}"]`)
	const svgOfActiveBtn = btnToActive.querySelector('svg use').getAttribute('href')

	document.querySelectorAll('[data-bs-theme-value]').forEach(element => {
	  element.classList.remove('active')
	  element.setAttribute('aria-pressed', 'false')
	})

	btnToActive.classList.add('active')
	btnToActive.setAttribute('aria-pressed', 'true')
	activeThemeIcon.setAttribute('href', svgOfActiveBtn)
	const themeSwitcherLabel = `${themeSwitcherText.textContent} (${btnToActive.dataset.bsThemeValue})`
	themeSwitcher.setAttribute('aria-label', themeSwitcherLabel)

	if (focus) {
	  themeSwitcher.focus()
	}
  }

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
	const storedTheme = getStoredTheme()
	if (storedTheme !== 'light' && storedTheme !== 'dark') {
	  setTheme(getPreferredTheme())
	}
  })

  window.addEventListener('DOMContentLoaded', () => {
	showActiveTheme(getPreferredTheme())

	document.querySelectorAll('[data-bs-theme-value]')
	  .forEach(toggle => {
		toggle.addEventListener('click', () => {
		  const theme = toggle.getAttribute('data-bs-theme-value')
		  setStoredTheme(theme)
		  setTheme(theme)
		  showActiveTheme(theme, true)
		})
	  })
  })
})()

</script>