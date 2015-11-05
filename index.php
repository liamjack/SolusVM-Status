<?php

function humanFileSize($size, $unit="") {
  if( (!$unit && $size >= 1<<30) || $unit == "GB")
    return number_format($size/(1<<30),2)." GB";
  if( (!$unit && $size >= 1<<20) || $unit == "MB")
    return number_format($size/(1<<20),2)." MB";
  if( (!$unit && $size >= 1<<10) || $unit == "KB")
    return number_format($size/(1<<10),2)." KB";
  return number_format($size)." bytes";
}

function post($action) {
	// Prepare the POST data

	// <configure> :

	$postfields["key"] = "YOUR API KEY";
	$postfields["hash"] = "YOUR API HASH";
	
	// This is the hostname of the master SolusVM server where you go to boot / reboot / configure / reinstall / get API keys for your VPS
	
	$masterurl = "https://master.vpshost.tld";
	
	// </configure>
	
	$postfields["action"] = $action;
	$postfields["status"] = "true";
	
	if($action == "info") {
		$postfields["hdd"] = "true";
		$postfields["mem"] = "true";
		$postfields["bw"] = "true";
	}

	// Prepare the POST request

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "{$masterurl}/api/client/command.php");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:  "));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	
	// Execute the request

	$data = curl_exec($ch);
	
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	if($code != 200) {
		$return['error'] = 1;
		
		if($code == 405) {
			$return['message'] = "Incorrect API credentials.";
			return $return;
		}
		
		$return['message'] = "Invalid status code.";
		
		return $return;
	}

	// Close the Curl handle

	curl_close($ch);
	
	if(!$data) {
		$return['error'] = 1;
		$return['message'] = "Error connecting to API.";
		
		return $return;
	}

	// Extract the data

	preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $match);

	$result = array();

	foreach ($match[1] as $x => $y) {
		$result[$y] = $match[2][$x];
	}
	
	if($result['status'] == "error") {
		$return['error'] = 1;
		$return['message'] = $result['statusmsg'];
		
		return $return;
	}

	$return = $result;
	$return['error'] = 0;
	
	return $return;
}

if(isset($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$action = "info";
}

switch($action)
{
	case 'info':
		$result = post("info");
		
		if($result['error'] == 0) {
			$return = $result;
			
			$return['hdd'] = explode(",", $return['hdd']);
			$return['mem'] = explode(",", $return['mem']);
			$return['bw'] = explode(",", $return['bw']);
		} else {
			$return = $result;
		}
		
		break;
	
	case 'reboot':
		$result = post("reboot");
		
		if($result['error'] == 0) {
			$return = $result;
			
			$return['message'] = "Server rebooting now.";
		} else {
			$return = $result;
		}
		
		break;
		
	case 'boot':
		$result = post("boot");
		
		if($result['error'] == 0) {
			$return = $result;
			
			$return['message'] = "Server booting now.";
		} else {
			$return = $result;
		}
		
		break;
			
	case 'shutdown':
		$result = post("shutdown");
		
		if($result['error'] == 0) {
			$return = $result;
			
			$return['message'] = "Server shutting down now.";
		} else {
			$return = $result;
		}
		
		break;	
	
	default:
		$return['error'] = 1;
		$return['message'] = "Invalid action specified.";
}

?>

<!doctype html>
<html>
<head>
	<title>SolusVM-Status</title>
	<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
	<div style="width: 90%; max-width: 800px; margin: 0 auto;">
		<h1>SolusVM-Status</h1>
		<hr/>
		
		<?php if($return['error'] == 1) { ?>
			<div class="alert alert-danger" role="alert"><?php echo $return['message']; ?></div>
			
			<a class="btn btn-default btn-block" href="?action=info" role="button">Return to status page</a>
		<?php } else { ?>
		
		<?php if($action == "info") { ?>
		
			<h3>Hostname</h3>
			<?php echo $return['hostname']; ?>
			
			<h3>Status</h3>
			<?php echo $return['vmstat']; ?>
			
			<h3>IP Address</h3>
			<?php echo $return['ipaddress']; ?>
		
			<h3>Hard Disk usage <small><?php echo humanFileSize($return['hdd'][1]); ?> used of <?php echo humanFileSize($return['hdd'][0]); ?></small></h3>
			<div class="progress">
				<div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $return['hdd'][3]; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $return['hdd'][3]; ?>%;">
					<?php echo $return['hdd'][3]; ?>%
				</div>
			</div>
		
			<h3>RAM usage <small><?php echo humanFileSize($return['mem'][1]); ?> used of <?php echo humanFileSize($return['mem'][0]); ?></small></h3>
			<div class="progress">
				<div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $return['mem'][3]; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $return['mem'][3]; ?>%;">
					<?php echo $return['mem'][3]; ?>%
				</div>
			</div>
		
			<h3>Bandwith usage <small><?php echo humanFileSize($return['bw'][1]); ?> used of <?php echo humanFileSize($return['bw'][0]); ?></small></h3>
			<div class="progress">
				<div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $return['bw'][3]; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $return['bw'][3]; ?>%;">
					<?php echo $return['bw'][3]; ?>%
				</div>
			</div>
			
			
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Actions</h3>
				</div>
				
				<div class="panel-body">
				<?php if($return['vmstat'] == "offline") { ?>
					<a class="btn btn-primary btn-block" href="?action=boot" role="button">Boot</a>
				<?php } else { ?>
					<a class="btn btn-primary btn-block" href="?action=shutdown" role="button">Shutdown</a>
					<a class="btn btn-primary btn-block" href="?action=reboot" role="button">Reboot</a>
					<a class="btn btn-default btn-block" href="?action=info" role="button">Reload the page</a>
				<?php } ?>
				</div>
			</div>
		
		<?php } ?>
		
		<?php if($action == "reboot" || $action == "boot" || $action == "shutdown") { ?>
		
			<?php if($return['error'] == 0) { ?>
			<div class="alert alert-success" role="alert"><?php echo $return['message']; ?></div>
			<?php } ?>
			
			<a class="btn btn-default btn-block" href="?action=info" role="button">Return to status page</a>
			
		<?php } ?>
		
		
		<?php } ?>
		<footer style="margin: 20px 0; text-align: center;">
			<a href="https://github.com/cuonic/SolusVM-Status" target="_blank">SolusVM-Status</a> by Cuonic
		</footer>
	</div>
</body>
</html>
