<!- ********* index.php ********* ->
<link rel="stylesheet" href="styles.css">
<link rel="icon" href="icon.png" type="image/gif" sizes="20x20">
<!DOCTYPE html>
<html>
	<head>
		<title>
			THe Website
		</title>
		<script src="amcharts/amcharts.js" type="text/javascript"></script>
        <script src="amcharts/serial.js" type="text/javascript"></script>
		<script src="amcharts/themes/dark.js" type="text/javascript"></script>
		<?php include 'plotEnvironmentalData.php'; include 'plotFlowData.php'; ?>
	</head>
	<?php
		include 'constants.php';
		if(isset($_GET["autoRefresh"])){
			$autoRefresh = $_GET["autoRefresh"];
			if($autoRefresh!='Off'){
				echo '<meta http-equiv=\'refresh\' content=\''.$autoRefresh.'\'>';
			}
		}
	?>
	<body bgcolor="#282828">
<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	include 'readPyenvFile.php';	
	include 'links.php';
	include 'plottingForm.php';
	if(isset($_GET['plottingMode']) && $plottingMode == 'ionData'){
		include 'plotIonData.php';
	}
	/*
	if((isset($_POST['password']) && $_POST['password'] == 'THe') || (isset($_COOKIE['loggedIn']) && $_COOKIE['loggedIn']=='true')){
		if(!isset($_COOKIE['loggedIn'])){
			setcookie('loggedIn','true'); # remember that the user logged in so that they won't have to do so again
		}
		include 'readPyenvFile.php';	
		include 'links.php';
		include 'plottingForm.php';
		if(isset($_GET['plottingMode']) && $plottingMode == 'ionData'){
			include 'plotIonData.php';
		}
	}
	else{
		?>
		<form method="POST" action = "<?php $_PHP_SELF ?>">
			<p>Password: <input type='password' name='password'></input>
			<input type='submit' name='submit' value='submit'></input>
		</form>
		<?php
		if(isset($_POST['password'])){
			echo "<p> wrong password.<br>";
		}
	}
	*/
		
?>
		
	</body>
</html>
