<!- ********* overview.php ********* ->
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
		<script src="amcharts/themes/chalk.js" type="text/javascript"></script>
	</head>
<meta http-equiv="refresh" content="60">
<?php
	# include the necessary files
	include 'constants.php';
	$_GET["autoRefresh"] = 300;
	include 'readPyenvFile.php';
	include 'plotEnvironmentalData.php';
	include 'plotFlowData.php';
	# define the plot parameters for all 4 plots
	$width = 40; # in %
	$height = 35; # in %
	# define the plot parameters for the first three plots (they're overwritten for the fourth downstairs)
	$resolution = '60';
	$timeDuration = '8';
	$timeUnit = 'hours';
	$overview = 'true';
?>
			<?php
			
				$channel1 = '1: p_Bore';
				$channel2 = '2: p_Reservoir';
				$chartId1 = '1';
				$manualScale1 = '0';
				$min1 = '0';
				$max1 = '0';
				$manualScale2 = '0';
				$min2 = '0';
				$max2 = '0';
				$plotResult1 = plotEnvironmentalData($timeDuration,$timeUnit,$channel1,$channel2,$resolution,$units[1],$units[2],
					$factors,$offsets,$names,$manualScale1,$min1,$max1,
					$manualScale2,$min2,$max2,$width,$height,$chartId1,$overview,$MEASUREMENT_PERIOD_s);
			
				$channel1 = '4: p_LN2';
				$channel2 = '8: Fluxgate_1';
                $chartId2 = '2';
				$manualScale1 = '0';
				$min1 = '0';
				$max1 = '0';
				$manualScale2 = '0';
				$min2 = '0';
				$max2 = '0';
                $plotResult2 = plotEnvironmentalData($timeDuration,$timeUnit,$channel1,$channel2,$resolution,$units[4],$units[8],
					$factors,$offsets,$names,$manualScale1,$min1,$max1,
					$manualScale2,$min2,$max2,$width,$height,$chartId2,$overview,$MEASUREMENT_PERIOD_s);
			
				$channel1 = '9: Valve_Bore';
				$channel2 = '10: Valve_Reservoir';
				$chartId3 = '3';
				$manualScale1 = '1';
				$min1 = '-10';
				$max1 = '10';
				$manualScale2 = '1';
				$min2 = '-10';
				$max2 = '10';
				$plotResult3 = plotEnvironmentalData($timeDuration,$timeUnit,$channel1,$channel2,$resolution,$units[9],$units[10],
					$factors,$offsets,$names,$manualScale1,$min1,$max1,
					$manualScale2,$min2,$max2,$width,$height,$chartId3,$overview,$MEASUREMENT_PERIOD_s);
			
				/*
				$channel1 = '17: LHe_Counter';
				$channel2 = '18: LN2_Counter';
				$chartId4 = '4';
				$resolution = '5';
				$timeDuration = '8';
				$timeUnit = 'hours';
				$manualScale1 = '1';
				$min1 = '0.5';
				$max1 = '1';
				$manualScale2 = '1';
				$min2 = '3.2';
				$max2 = '5';
				#$manualScale1 = 1;
				#$max1 = 1;
				#$manualScale2 = 1;
				#$max2 = 5;
				$plotResult4 = plot($timeDuration,$timeUnit,$channel1,$channel2,$resolution,$units[17],$units[18],
					$factors,$offsets,$names,$manualScale1,$min1,$max1,
					$manualScale2,$min2,$max2,$width,$height,$chartId4,$overview,$MEASUREMENT_PERIOD_s);
				*/
				$chartId4 = '4';
				$resolution = '60';
				$timeDuration = '8';
				$timeUnit = 'hours';
				$manualScale1 = '0';
				$min1 = '0.5';
				$max1 = '1';
				$manualScale2 = '0';
				$min2 = '3.2';
				$max2 = '5';
				$plotResult4 = plotFlowData($timeDuration,$timeUnit,$resolution,$manualScale1,$min1,$max1,
					$manualScale2,$min2,$max2,$width,$height,$chartId4,$overview,$MEASUREMENT_PERIOD_s);

					
				#if($plotResult1=='error' || $plotResult2=='error' || $plotResult3=='error' || $plotResult4=='error'){
				#	echo "Can't plot. (Probably because no data was recorded in the last ".$timeDuration." ".$timeUnit.")<br>";
				#}
			?>
			
<body bgcolor="#282828">

<div id="chart<?php echo $chartId1;?>div" style="top: 5%; left: 5%; width:<?php echo $width;?>%; height:<?php echo $height;?>%;"></div>
<div id="chart<?php echo $chartId2;?>div" style="top: 5%; left: 55%; width:<?php echo $width;?>%; height:<?php echo $height;?>%;"></div>
<br>
<div id="chart<?php echo $chartId3;?>div" style="top: 55%; left: 5%; width:<?php echo $width;?>%; height:<?php echo $height;?>%;"></div>
<br>
<div id="chart<?php echo $chartId4;?>div" style="top: 55%; left: 55%; width:<?php echo $width;?>%; height:<?php echo $height;?>%;"></div>
</body>
</html>

