<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<?php
session_start();

if (isset($_POST['username']) && isset($_POST['password']) && $_POST['username'] == "breakspear") {
	logsRemoveOld();
	
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
	
	$logsByDate = array();
	foreach (logsGet() AS $log) {
		$date = date('Y-m-d', strtotime($log['date_created']));
		
		$logsByDate[$date] = $logsByDate[$date] + 1;
	}
?>
<div id="chart-logs"></div>

<table class="table table-sm">
  <thead>
	<tr>
	  <th scope="col">Date</th>
	  <th scope="col">IP</th>
	  <th scope="col">Email</th>
	  <th scope="col">Token</th>
	</tr>
  </thead>
  <tbody>
	  <?php
	  foreach (tokensGet(true) AS $token) {
		  echo "<tr>";
		  echo "<th scope=\"row\">" . $token['date_created'] . "</th>";
		  echo "<td>" . $token['ip'] . "</td>";
		  echo "<td>" . $token['email'] . "</td>";
		  echo "<td>" . "...." . substr($token['token'], -5) . "</td>";
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
    </tr>
  </thead>
  <tbody>
	  <?php
	  foreach (logsGet() AS $log) {
		  echo "<tr>";
		  echo "<th scope=\"row\">" . $log['date_created'] . "</th>";
		  echo "<td>" . $log['type'] . "</td>";
		  echo "<td>" . $log['ip'] . "</td>";
		  echo "</tr>";
		  echo "<tr>";
		  echo "<td colspan=\"3\" class=\"text-truncate\">" . $log['event'] . "</td>";
		  echo "</tr>";
	  }
	  ?>
  </tbody>
</table>


<script>
var options = {
	series: [{
		name: "Logs By Day",
		data: [<?php echo implode(",", array_reverse($logsByDate)); ?>]
	}],
	chart: {
		id: 'chart-logs',
		type: 'bar',
		height: 300,
		toolbar: {
			tools: {
				zoomout: false,
				zoomin: false,
				pan: false
			}
		}
	},
	dataLabels: {
		enabled: false
	},
	xaxis: {
		categories: ['<?php echo implode("','", array_reverse(array_keys($logsByDate))); ?>']
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