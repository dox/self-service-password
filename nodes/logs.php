<style>
.form-signin {
  max-width: 100%;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


<?php
session_start();

if (isset($_POST['username']) && isset($_POST['password']) && $_POST['username'] == "breakspear") {
	logsRemoveOld();
	tokenRemoveOldUsed();
	tokenRemoveOldUnused();
	
	$cleanUsername = htmlspecialchars($_POST['username']);
	$cleanPassword = htmlspecialchars($_POST['password']);
	
	$user = $ldap_connection->query()
	->where('samaccountname', '=', $cleanUsername)
	->firstOrFail();
	
	// Create a new LDAP connection:
	if ($ldap_connection->auth()->attempt($user['distinguishedname'][0], $cleanPassword)) {
		$_SESSION['logged_in'] = true;
	} else {
		$_SESSION['logged_in'] = false;
		// Invalid credentials.
		return $message;
	}
}


if ($_SESSION['logged_in'] == true) {
	$days = "14";
	
	$token_create = $password_reset = $token_use = $other_log = createDateRangeArray(date('Y-m-d', strtotime($days . ' days ago')), date('Y-m-d'));
	
	foreach (logsGet() AS $log) {
		$date = date('Y-m-d', strtotime($log['date_created']));
		
		if ($log['type'] == "token_create") {
			if (array_key_exists($date, $token_create)) {
				$token_create[$date] = $token_create[$date] + 1;
			}
		} elseif ($log['type'] == "password_reset") {
			if (array_key_exists($date, $password_reset)) {
				$password_reset[$date] = $password_reset[$date] + 1;
			}
		} elseif ($log['type'] == "token_use") {
			if (array_key_exists($date, $token_use)) {
				$token_use[$date] = $token_use[$date] + 1;
			}
		} else {
			if (array_key_exists($date, $other_log)) {
				$other_log[$date] = $other_log[$date] + 1;
			}
		}
	}
	
	$token_create = array_reverse($token_create);
	$password_reset = array_reverse($password_reset);
	$token_use = array_reverse($token_use);
	$other_log = array_reverse($other_log);
	
	
	
	echo "<p>" . totalReset($days) . " passwords reset in the last " . $days . " days</p>";
?>
<div id="chart-logs"></div>

<table class="table table-sm">
  <thead>
	<tr>
	  <th scope="col">Created</th>
	  <th scope="col">Used</th>
	  <th scope="col">Token</th>
	  <th scope="col">IP</th>
	  <th scope="col">Email</th>
	</tr>
  </thead>
  <tbody>
	  <?php
	  foreach (tokensGet() AS $token) {
		  echo "<tr>";
		  
		  echo "<th scope=\"row\">" . $token['date_created'] . "</th>";
		  echo "<th scope=\"row\">" . $token['date_used'] . "</th>";

		  if (!empty($token['date_used'])) {
			  echo "<td><span class=\"badge rounded-pill text-bg-secondary\">" . "...." . substr($token['token'], -5) . "</span></td>";
		  } else {
			  echo "<td><span class=\"badge rounded-pill text-bg-success\">" . "...." . substr($token['token'], -5) . "</span></td>";
		  }
		  
		  echo "<td>" . $token['ip'] . "</td>";
		  echo "<td>" . $token['email'] . "</td>";
		  
		  echo "</tr>";
	  }
	  ?>
  </tbody>
</table>

<hr />

<table class="table table-sm table-striped">
  <thead>
    <tr>
      <th scope="col">Date</th>
      <th scope="col">Type</th>
	  <th scope="col">IP</th>
	  <th scope="col">Description</th>
    </tr>
  </thead>
  <tbody>
	  <?php
	  foreach (logsGet() AS $log) {
		  echo "<tr>";
		  echo "<th scope=\"row\">" . $log['date_created'] . "</th>";
		  echo "<td>" . $log['type'] . "</td>";
		  echo "<td>" . $log['ip'] . "</td>";
		  echo "<td colspan=\"3\" class=\"text-truncate\">" . $log['event'] . "</td>";
		  echo "</tr>";
	  }
	  ?>
  </tbody>
</table>


<script>
var options = {
	series: [{
			name: "Tokens Created",
			data: [<?php echo implode(",", array_reverse($token_create)); ?>]
		},{
			name: "Tokens Used",
			data: [<?php echo implode(",", array_reverse($token_use)); ?>]
		},{
			name: "Passwords Reset",
			data: [<?php echo implode(",", array_reverse($password_reset)); ?>]
		},{
			name: "Other",
			data: [<?php echo implode(",", array_reverse($other_log)); ?>]
		}],
	chart: {
		id: 'chart-logs',
		type: 'area',
		stacked: true,
		height: 300,
		toolbar: {
			show: false
		}
	},
	dataLabels: {
		enabled: false
	},
	xaxis: {
		categories: ['<?php echo implode("','", array_reverse(array_keys($token_create))); ?>']
	},
	yaxis: {
	  labels: {
		formatter: function (value) {
		  return value.toFixed(0);
		}
	  },
	},
};

var chartMonthly = new ApexCharts(document.querySelector("#chart-logs"), options);
chartMonthly.render();
</script>

<?php
} else {
?>
<form action="#" method="post">
	<div class="mb-3">
		<label for="username" class="form-label">Username</label>
		<input type="text" class="form-control" id="username" name="username" placeholder="Username" required autofocus autocomplete="off" aria-describedby="usernameHelp">
	</div>
	<div class="mb-3">
		<label for="password_old" class="form-label">Password</label>
		<input type="password" class="form-control" id="password" name="password" placeholder="Old Password" required autocomplete="off">
	</div>
	
	<div class="mb-3">
		<button class="btn btn-lg btn-primary w-100" id="password_submit" type="submit">Login</button>
	</div>
</form>
<?php
}
?>