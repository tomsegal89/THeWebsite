<!- ********* plotFlowData.php ********* ->
<?php
	function plotFlowData($timeDuration,$timeUnit,$resolution,$manualScale1,$min1,$max1,
			$manualScale2,$min2,$max2,$width,$height,$chartId,$overview){
		include 'constants.php';
		
		$timeCorrection = $GLOBALS['timeCorrection'];

		# calculate the starting and ending timestamps starting from which we'd like to plot data
		$timeConversionDict = ["seconds"=>1, "minutes"=>60, "hours"=>60*60, "days"=>24*60*60,
							  "weeks"=>7*24*60*60];
		$timeInSeconds = $timeDuration*$timeConversionDict[$timeUnit];
		$startingTimeStamp = (time()-$timeInSeconds);
		# echo "starting time stamp ".$startingTimeStamp."<br>";
		# echo "now is ".time()."<br>";
		# prepare the variables that will store the data
		$timeStampsRisingEdges = array( array() , array() ); # the first array is for He and the second for the N2. Contains the timestamps where rising edges were detected.
		
		# obtain the data from the files
		$filePaths = [$GLOBALS['HeFlowTimeStampsFilePath'],$GLOBALS['N2FlowTimeStampsFilePath']];
		for($i=0;$i<2;$i++){
			$k = 0;
			$file = fopen($filePaths[$i],'r');
			$line = fgets($file); # skip the header
			# skip lines (as in, days) that don't contain any timestamps of relevance
			$line = explode('	',fgets($file));
			$dateTimeStamp = DateTime::createFromFormat("Y-m-d",$line[0])->getTimestamp();
			# echo "skipped:<br>";
			while($line !== FALSE && ($dateTimeStamp+60*60*24)<$startingTimeStamp){
				#echo "date time stamp+stuff ".($dateTimeStamp+60*60*24)." < starting time stamp ".$startingTimeStamp."<br>";
				# echo $line[0]."<br>";
				#echo "<br><br>";
				$dateTimeStamp = DateTime::createFromFormat("Y-m-d",$line[0])->getTimestamp();

				# echo $dateTimeStamp."<br>";
				$line = fgets($file);
				if($line !== FALSE){
					$line = explode('	',$line);
				}
			}
			# now we've reached the lines (the days) of interest. Keep reading timestamps from them until the end of the file.
			# echo "using:<br>";
			while ($line !== false) {
				# $line = fgetcsv($file,0,"\t");
				# echo $line[0]."<br>";
				#echo "<br><br>";
				for($j=1;$j<sizeof($line)-1;$j++){ #1 to skip the first value which is the date, -1 to skip the \n?
					if($line[$j]>$startingTimeStamp){
						$timeStampsRisingEdges[$i][$k] = $line[$j]; 
						$k = $k+1;
					}
				}
				$line = fgets($file);
				if($line !== FALSE){
					$line = explode('	',$line);
				}
			}
			fclose($file);	
		}
		
		# create the time values
		if(sizeof($timeStampsRisingEdges[0])==0){
			echo "no time stamps found for the requested time duration.<br>";
			exit(0);
		}
		$numberOfHeRisingEdges = sizeof($timeStampsRisingEdges[0]);
		$numberOfN2RisingEdges = sizeof($timeStampsRisingEdges[1]);
		# echo "number of He ".$numberOfHeRisingEdges." number of N2 ".$numberOfN2RisingEdges."<br>";
		# print_r($timeStampsRisingEdges);
		$firstHeRisingEdge = $timeStampsRisingEdges[0][0];
		$firstN2RisingEdge = $timeStampsRisingEdges[1][0];
		$lastHeRisingEdge = $timeStampsRisingEdges[0][$numberOfHeRisingEdges-1];
		$lastN2RisingEdge = $timeStampsRisingEdges[1][($numberOfN2RisingEdges-1)];
		# we will crop the horizontal axis to be between the latest of the first rising edges and the first of the last rising edges
		$firstRisingEdgeOfInterest = max($firstHeRisingEdge,$firstN2RisingEdge);
		$lastRisingEdgeOfInterest = min($lastHeRisingEdge,$lastN2RisingEdge);
		# echo "first He ".$firstHeRisingEdge." first N2 ".$firstN2RisingEdge."<br>";
		# echo "last He ".$lastHeRisingEdge." last N2 ".$lastN2RisingEdge."<br>";
		# echo "timestamps of first and last rising edges of interest ".$firstRisingEdgeOfInterest." and ".$lastRisingEdgeOfInterest."<br>";
		# first populate the timeStamps array
		$timeStamps = array();
		for($i=$firstRisingEdgeOfInterest;$i<$lastRisingEdgeOfInterest;$i=$i+$resolution){
			$timeStamps[] = $i+$timeCorrection; # arr[] = blah; adds blah to the end of arr
		}
		
		# calculate the flow values and populate the flowvalues matrix
		$flowValues = array( array() , array() ); # the first array is for He and the second for the N2. Contains the flow values in L/min
		# var_dump($timeStampsRisingEdges);
		for($i=0; $i<2; $i++){ # for each channel
			# calculate the flow value for each pair of rising edges found
			$numberOfRisingEdges = sizeof($timeStampsRisingEdges[$i]);
			for($j=0; $j<$numberOfRisingEdges-1; $j++){
				$risingEdge1 = $timeStampsRisingEdges[$i][$j]; # in seconds
				$risingEdge2 = $timeStampsRisingEdges[$i][$j+1]; # in seconds
				$timeDifference = $risingEdge2 - $risingEdge1; # in seconds
				# notice the if timeDifference==0 here. I saw a pair of identical timestamps for the N2. Why did that happen? Maybe because PyEnvDAQ was restarted? Maybe this is a quick fix
				if($timeDifference == 0 || $risingEdge2<$firstRisingEdgeOfInterest || $risingEdge1>$lastRisingEdgeOfInterest){
					# do nothing
				}
				else{
					if($timeDifference==0){
						echo $timeDifference." ".$risingEdge1." ".$risingEdge2."<br>";
					}
					$flowValuesUnique[$i][$j] = $GAS_COUNTER_CALIBRATION_FACTOR/($timeDifference/60); # in litres/(s/60) = litres/min
				}
			}
			# populate the flowvalues matrix
			for($j = $firstRisingEdgeOfInterest; $j < $lastRisingEdgeOfInterest; $j = $j + $resolution){
				# find the relevant flow value for this time period
				$k = 0;
				while ($j > $timeStampsRisingEdges[$i][$k+1]){
					$k = $k + 1;
				}
				# copy-paste this flow value many times to populate the flowValues array
				$flowValues[$i][] = $flowValuesUnique[$i][$k];
			}
		}
		
		# print_r($timeStamps);
		$numOfDataPoints = sizeof($timeStamps);
		
				
		# in case PyEnvDAQ is not running at the moment, display a gap in the data (from between the last data point's timestamp and the time stamp corresponding to now)
		# (only display a gap if theres a gap larger than $resolution, otherwise if for instance $resolutio=10s then we'll always have an extra point at the end which will make it look like theres always a flat section at the end for the last 10s)
		$nextTimeStep = end($timeStamps)+$resolution; # notice that $timeCorrection is already inside
		while($nextTimeStep + $resolution < time() + $timeCorrection){ # not really sure whether there should be a +$resolution there
			$timeStamps[] = $nextTimeStep;
			$nextTimeStep = end($timeStamps)+$resolution;
			$flowValues[0][] = end($flowValues[0]);
			$flowValues[1][] = end($flowValues[1]);
		}
		
		# find the time duration
		if (sizeof($timeStamps)>0){
			$timeStampsMin = min($timeStamps);
			$timeStampsMax = max($timeStamps);
			$timeDuration = $timeStampsMax - $timeStampsMin;
		}
		
		# turn the timestamps into the appropriate date format
		# (the larger the time span, the more detailed we want the format to be)
		$dateFormat='Y.m.d H:i';
		if ($timeDuration<$timeConversionDict["days"]){
			$dateFormat='H:i';
			$amChartsDateFormat='HH:mm'; # why does removing this line break the script? this variable is not in use
			$minDateJump = '15mm'; # display in minimal time jumps of 15 minutes # why does removing this line break the script? this variable is not in use
			if ($timeDuration<15*$timeConversionDict["minutes"]){
					$dateFormat = 'H:i:s';
			}
		}
		#$dateFormat = 'YYYY-MM-DD hh:mm:ss';
		for($i=0;$i<sizeof($timeStamps);$i++){
			$timeStamps[$i] = '\''.date($dateFormat,$timeStamps[$i]).'\'';
		}
		$HeFlowValues = $flowValues[0];
		# var_dump($HeFlowValues);
		# echo sizeof($HeFlowValues)." , ".$HeFlowValues[5]."<br>";
		# exit();
		# assemble the data into an array to be given as argument to the plotting function (but only if theres points to plot)
		if(sizeof($timeStamps)>0){

			$title = "He (red) and  N2 (blue) in L per min vs time (starting from ".date('Y.m.d H:i:s',$timeStampsMin).")";
			# $channel1Name = "He (red in L per min)";
			# $channel2Name = "N2 (blue in L per min) (2)";
			$channel1Name = "He";
			$channel2Name = "N2";			
			$plotData = '[';
			for($i=0;$i<sizeof($timeStamps);$i++){
				$plotData = $plotData."{'time': ".$timeStamps[$i].","
							 .$channel1Name.": ".$flowValues[0][$i].","
							 .$channel2Name.": ".$flowValues[1][$i]."}";		
				if($i<sizeof($timeStamps)-1){
					$plotData = $plotData.",";
				}								 
			}
			$plotData = $plotData."]";
		}
		
		/* only plot if there are points to plot */
		if(sizeof($timeStamps)>0){
			# define min and max if they were not defined manually
			if($manualScale1==0){
				$min1 = min($flowValues[0]);
				$max1 = max($flowValues[0]);
			}
			if($manualScale2==0){
				$min2 = min($flowValues[1]);
				$max2 = max($flowValues[1]);
			}
			
			/* plot (performed between the <HTML> tags) */
			?>

			<script>
			var chartData<?php echo $chartId;?> = <?php echo $plotData; ?>;

            AmCharts.ready(function () {
                var chart<?php echo $chartId;?> = new AmCharts.AmSerialChart();

                chart<?php echo $chartId;?>.dataProvider = chartData<?php echo $chartId;?>;
                chart<?php echo $chartId;?>.categoryField = "time";
				chart<?php echo $chartId;?>.angle = 30;
				chart<?php echo $chartId;?>.depth3D = 15;
				chart<?php echo $chartId;?>.backgroundColor = "#282828";
				chart<?php echo $chartId;?>.backgroundAlpha = 1;
				chart<?php echo $chartId;?>.creditsPosition = "bottom-right";
				chart<?php echo $chartId;?>.color = "#FFFFFF";
				
                // AXES
                // category
                var categoryAxis = chart<?php echo $chartId;?>.categoryAxis;
				categoryAxis.equalSpacing = "true";
                categoryAxis.dashLength = 20;
                categoryAxis.minorGridEnabled = true;
                categoryAxis.minorGridAlpha = 0.1;
				categoryAxis.autoGridCount= false;
				categoryAxis.gridCount = 4;
				categoryAxis.fontSize = 25;
				categoryAxis.fontColor = '#FF0000';
				categoryAxis.axisColor = '#FFFFFF';
				categoryAxis.fillAlpha = 0.1;
				categoryAxis.fillColor = '#aaaaFF';
				categoryAxis.equalSpacing = true;
				var guide1 = chart<?php echo $chartId;?>.guide;
				
				// first value axis (on the left)
				var valueAxis<?php echo $chartId;?>a = new AmCharts.ValueAxis();
				valueAxis<?php echo $chartId;?>a.position = "left";
				valueAxis<?php echo $chartId;?>a.axisColor = "#FF0000";
				valueAxis<?php echo $chartId;?>a.axisThickness = 5;
				valueAxis<?php echo $chartId;?>a.title='He (red, in L/min)';
				valueAxis<?php echo $chartId;?>a.titleFontSize = 20;
				valueAxis<?php echo $chartId;?>a.titleColor = "#FF0000";
				valueAxis<?php echo $chartId;?>a.color = "#FF0000";
				valueAxis<?php echo $chartId;?>a.fontSize = 25;
				valueAxis<?php echo $chartId;?>a.autoGridCount = false;
				valueAxis<?php echo $chartId;?>a.gridCount = 4;
				<?php if($manualScale1=='1'){?>valueAxis<?php echo $chartId;?>a.minimum = <?php echo $min1;?>;
				valueAxis<?php echo $chartId;?>a.maximum = <?php echo $max1;?>;<?php }?>
chart<?php echo $chartId;?>.addValueAxis(valueAxis<?php echo $chartId;?>a);
				
				// second value axis (on the right)
				var valueAxis<?php echo $chartId;?>b = new AmCharts.ValueAxis();
				valueAxis<?php echo $chartId;?>b.position = "right"; // this line makes the axis to appear on the right
				valueAxis<?php echo $chartId;?>b.axisColor = "#aaaaFF";
				
				valueAxis<?php echo $chartId;?>b.gridAlpha = 0;
				valueAxis<?php echo $chartId;?>b.axisThickness = 5;
				valueAxis<?php echo $chartId;?>b.title='N2 (blue, in L/min) (2)'
				valueAxis<?php echo $chartId;?>b.titleFontSize = 20;
				valueAxis<?php echo $chartId;?>b.titleColor = "#aaaaFF";
				valueAxis<?php echo $chartId;?>b.color = "#aaaaFF";
				valueAxis<?php echo $chartId;?>b.fontSize = 25;
				valueAxis<?php echo $chartId;?>b.autoGridCount = false;
				valueAxis<?php echo $chartId;?>b.gridCount = 4;
				<?php if($manualScale1=='1'){?>valueAxis<?php echo $chartId;?>b.minimum = <?php echo $min2;?>;
				valueAxis<?php echo $chartId;?>b.maximum = <?php echo $max2;?>;<?php }?>
chart<?php echo $chartId;?>.addValueAxis(valueAxis<?php echo $chartId;?>b);
				
				// GRAPHS
				// first graph
				var graph<?php echo $chartId;?>a = new AmCharts.AmGraph(AmCharts.themes.chalk);
				graph<?php echo $chartId;?>a.valueAxis = valueAxis<?php echo $chartId;?>a; // we have to indicate which value axis should be used
				graph<?php echo $chartId;?>a.valueField = "<?php echo $channel1Name; ?>";
				graph<?php echo $chartId;?>a.bullet = "round";
				graph<?php echo $chartId;?>a.hideBulletsCount = 20;
				graph<?php echo $chartId;?>a.lineColor = "#FF0000";
				graph<?php echo $chartId;?>a.lineThickness = 3;
				graph<?php echo $chartId;?>a.type = "smoothedLine";
				graph<?php echo $chartId;?>a.connect = false;
				chart<?php echo $chartId;?>.addGraph(graph<?php echo $chartId;?>a);

				// second graph
				var graph<?php echo $chartId;?>b = new AmCharts.AmGraph(AmCharts.themes.chalk);
				graph<?php echo $chartId;?>b.valueAxis = valueAxis<?php echo $chartId;?>b; // we have to indicate which value axis should be used
				graph<?php echo $chartId;?>b.valueField = "<?php echo $channel2Name; ?>";
				graph<?php echo $chartId;?>b.bullet = "square";
				graph<?php echo $chartId;?>b.hideBulletsCount = 20;
				graph<?php echo $chartId;?>b.lineColor = "#aaaaFF";
				graph<?php echo $chartId;?>b.lineThickness = 3;
				graph<?php echo $chartId;?>b.type = "smoothedLine";
				graph<?php echo $chartId;?>b.connect = false;
				chart<?php echo $chartId;?>.addGraph(graph<?php echo $chartId;?>b);
			   
			   <?php if($overview=='false'){
				   ?>
				// CURSOR
                var chartCursor<?php echo $chartId;?> = new AmCharts.ChartCursor();
                chartCursor<?php echo $chartId;?>.cursorAlpha = 1;
                chartCursor<?php echo $chartId;?>.cursorPosition = "mouse";
				chartCursor<?php echo $chartId;?>.color = "#FFFFFF";				
                chart<?php echo $chartId;?>.addChartCursor(chartCursor<?php echo $chartId;?>);

				// BALLOON
				var balloon = chart<?php echo $chartId;?>.balloon;
				balloon.color="#ffffff";
				balloon.fontSize=30;
				
                // SCROLLBAR
                var chartScrollbar<?php echo $chartId;?> = new AmCharts.ChartScrollbar();
				chartScrollbar<?php echo $chartId;?>.color = "#333333";
				chartScrollbar<?php echo $chartId;?>.backgroundAlpha = 0.2;
				
                chart<?php echo $chartId;?>.addChartScrollbar(chartScrollbar<?php echo $chartId;?>);
                <?php }?>
				

                // WRITE
                chart<?php echo $chartId;?>.write("chart<?php echo $chartId;?>div");
            });
			</script>
			
			
			<?php
			return 'ok';
		}
		else{
			return 'error';
		}
		#echo "(data taken from ".$filePath.")<br>";
	}
?>
