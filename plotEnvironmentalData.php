<!- ********* plot.php ********* ->
<?php
	include_once 'calibrate.php';
	
	function plotEnvironmentalData($timeDuration,$timeUnit,$channel1,$channel2,$resolution,$channel1Units,$channel2Units,
			$factors,$offsets,$names,$manualScale1,$min1,$max1,
			$manualScale2,$min2,$max2,$width,$height,$chartId,$overview){

		$timeCorrection = $GLOBALS['timeCorrection'];
		
		# obtain the channel indices
		$channel1Index = explode(':',$channel1)[0];
		if($channel2!=0){
			$channel2Index = explode(':',$channel2)[0];
		}
		else{
			$channel2Index=0;
		}
		# calculate the starting and ending timestamps starting from which we'd like to plot data
		$timeConversionDict = ["seconds"=>1, "minutes"=>60, "hours"=>60*60, "days"=>24*60*60,
							  "weeks"=>7*24*60*60];
		$timeInSeconds = $timeDuration*$timeConversionDict[$timeUnit];
		$startingTimeStamp = (time()-$timeInSeconds);
		
		# prepare the variables that will store the data
		$timeStamps = [];
		$channel1Data = [];
		$channel2Data = [];
		
		# prepare additional variables
		#$numberOfChannels = sizeof($channel1Units);
		#$numOfLinesToSkip = $numberOfChannels+6; # messy line because $numberOfChannels is calculated in 'readPyEnvFile' and due to the use of a magic number (6)
		$k = 0;
		# obtain the list of pyEnv files names
		$fileNames = scandir($GLOBALS['envFilesDirectoryPath']); # get the list of all files in the folder
		$fileNames = array_slice($fileNames,2); # remove "." and "..", the first two elements of the array
		# obtain the list of pyEnv file paths sorted according to their creation dates and filtered according to our plot duration
		$filePaths = array();
		for($i=0;$i<sizeof($fileNames);$i++){
			$filePath = $GLOBALS['envFilesDirectoryPath'].'/'.$fileNames[$i]; # the first \ is there to escape the second \
			#echo "the file name is ".explode(".",$fileNames[$i])[0]."<br><br>";
			$fileCreationDate = explode(".",$fileNames[$i])[0];
			$fileCreationTimeStamp = strtotime($fileCreationDate);
			#echo "the creation date is ".$creationDate."<br><br>";
			# if the file's creation time is not too far away in the past, add it, but sorted according to its creation time
			if($fileCreationTimeStamp+60*60*24>time()-$timeInSeconds){
				# $filePaths[filectime($filePath)] = $filePath;
				$filePaths[$k] = $filePath;
				$k = $k+1;
			}
		}
		# print_r($filePaths);
		# echo "<br><br><br>";
		
		#ksort($filePaths); # sort the array according to keys. Isn't that what we did above? why is this needed? # no idea what this is here for, commented-out
		# $filePaths = array_reverse($filePaths,TRUE); # reverses the array so that the files will be sorted according to access date in descending order? # commented-out because they already were so this makes them reverse to what we want
		# print_r($filePaths);
		$k = 0;
		# print_r($filePaths);
		# echo "<br><br><br>";
		# exit(0);
		$linesToSkip = $GLOBALS['$LINES_TO_SKIP'];
		for($i=0;$i<sizeof($filePaths);$i++){
			$file = fopen($filePaths[$i],'r'); # open a pyEnv file
			# skip the header
			for($j=0;$j<$linesToSkip;$j++){
				$line = fgets($file);
			}
			# skip lines with timestamps smaller than the starting time stamp
			$line = fgetcsv($file,0,"\t");
			$timeStamp = $line[0];
			while($line !== false && $timeStamp<$startingTimeStamp){
				$line = fgetcsv($file,0,"\t");
				$timeStamp = $line[0];
			}
			$numOfLinesRead = 0;
			# read the data from the requested channels
			while ($line !== false) {
				if($numOfLinesRead % $resolution == 0){
					$timeStamps[$k] = $line[0]+$timeCorrection;
					$channel1Data[$k] = $line[$channel1Index];
					if($channel2!=0){
						$channel2Data[$k] = $line[$channel2Index];
					}
					$k = $k + 1;
				}
				$numOfLinesRead = $numOfLinesRead + 1;
				$line = fgetcsv($file,0,"\t");
				# echo $numOfLinesRead;
			}
			fclose($file);
		}
		
		
		# set the horizontal axis to stretch all the way from beginning to end of the data.
		# 	part of it might be cropped out during the calibration, in the case where we're
		# 	calibrating gas flow.
		$xStart1 = 0;
		$xStart2 = 0;
		$xEnd1 = sizeof($timeStamps);
		$xEnd2 = sizeof($timeStamps);
		
		# obtain the calibration types (needed not only for the calibration part but also for the gap-fixing part)
		$calibration1Type = 'linear';
		if (strpos($channel1Units,'log') !== false){
			$calibration1Type = 'logarithmic';
		}
		if (strpos($channel1Units,'L/min') !== false){
			$calibration1Type = 'gas counter';
		}
		$calibration2Type = 'linear';
		if (strpos($channel2Units,'log') !== false){
			$calibration2Type = 'logarithmic';
		}
		if (strpos($channel2Units,'L/min') !== false){
			$calibration2Type = 'gas counter';
		}		
		
		# calibrate the data if needed
		if ($channel1Units != 'arbitrary units'){
			$calibrationResults = calibrate($channel1Data,$calibration1Type,$factors,$offsets,$channel1Index,$resolution);
			$channel1Data = $calibrationResults[0];
			$xStart1 = $calibrationResults[1];
			$xEnd1 = $calibrationResults[2];
		}
		if ($channel2!=0 && $channel2Units != 'arbitrary units'){	
			$calibrationResults = calibrate($channel2Data,$calibration2Type,$factors,$offsets,$channel2Index,$resolution);
			$channel2Data = $calibrationResults[0];
			$xStart2 = $calibrationResults[1]; # in points (not in s)
			$xEnd2 = $calibrationResults[2]; # in points (not in s)
		}
		
		# in case we're plotting the LN2_Counter or the LHe_Counter channels and calibrating them,
		# 	the calibration leaves two useless areas, one in the beginning and one at the end,
		#	which we want to crop.
		$xStart = max($xStart1,$xStart2);
		# $xStart=0;
		$xEnd = min($xEnd1,$xEnd2);
		$filteredTimeStamps = [];
		$filteredchannel1Data = [];
		$filteredchannel2Data = [];
		$i = 0;
		for($j=$xStart;$j<$xEnd;$j++){
			$filteredTimeStamps[$i] = $timeStamps[$j];
			$filteredchannel1Data[$i] = $channel1Data[$j];
			if($channel2!=0){
				$filteredchannel2Data[$i] = $channel2Data[$j];
			}
			$i = $i + 1;
		}
		$timeStamps = $filteredTimeStamps;
		$channel1Data = $filteredchannel1Data;
		$channel2Data = $filteredchannel2Data;		
		
		
		# fill time-gaps (only those larger than the resolution)
		# 	if at least one of the channels is a gas flow channel don't fill time gaps as it will just give wrong flow readings.
		if($calibration1Type != 'gas counter' && $calibration2Type != 'gas counter'){
			$timeStampsWithoutGaps = [];
			$channel1DataWithoutGaps = [];
			if($channel2!=0){
				$channel2DataWithoutGaps = [];
			}
			$i = 0;
			for($j=0;$j<sizeof($timeStamps)-1;$j++){
				# copy-over the current data point
				$timeStampsWithoutGaps[$i] = $timeStamps[$j];
				$channel1DataWithoutGaps[$i] = $channel1Data[$j];
				if($channel2!=0){
					$channel2DataWithoutGaps[$i] = $channel2Data[$j];
				}
				$i = $i + 1;
				
				# look for a gap
				$gapSize = floor(($timeStamps[$j+1]-$timeStamps[$j]-$resolution)/$resolution); # calculate gap size
				# explanation for $gapSize:
				# assume $period=10s, $timeStamp[0]=1490019457, $timeStamp[1]=1490019467.
				# in that case theres a gap of $timeStamp[1]-$timeStamp[0] = 10s between the timestamps,
				# or $timeStamp[1]-$timeStamp[0]-$resolution = 0 points, as in theres no gap.
				# assume instead $timeStamp[1]=1490019482. Now the time gap is 25s, the point gap 1.5, and by flooring we get 1,
				# such that the new timeStamp sequence will be 1490019457 , 1490019467 , 1490019482 (still a gap of 15s between the
				# last two, and not 10s like we would have wanted, but at least this ensures that the gaps are smaller than 2*$resolution)

				if($gapSize>0){ # if detected gap fill it
					#echo "detected gap at timestamps ".$timeStamps[$j+1]." and ".$timeStamps[$j]." which correspond to j ".$j." i ".$i."<br>";
					for($k=0;$k<$gapSize;$k++){
						$timeStampsWithoutGaps[$i] = $timeStampsWithoutGaps[$i-1] + $resolution;
						$channel1DataWithoutGaps[$i] = $channel1DataWithoutGaps[$i-1];
						if($channel2!=0){
							$channel2DataWithoutGaps[$i] = $channel2DataWithoutGaps[$i-1];
						}
						$i = $i + 1;
					}
				}
			}
			
			# finished patching-up gaps - update the arrays
			$timeStamps = $timeStampsWithoutGaps;
			$channel1Data = $channel1DataWithoutGaps;
			if($channel2!=0){
				$channel2Data = $channel2DataWithoutGaps;
			}
			$numOfDataPoints = sizeof($timeStamps);
		}
		
		
		# in case PyEnvDAQ is not running at the moment, display a gap in the data (from between the last data point's timestamp and the time stamp corresponding to now)
		# (only display a gap if theres a gap larger than $resolution, otherwise if for instance $resolutio=10s then we'll always have an extra point at the end which will make it look like theres always a flat section at the end for the last 10s)
		$nextTimeStep = end($timeStamps)+$resolution; # notice that $timeCorrection is already inside
		while($nextTimeStep + $resolution < time() + $timeCorrection){ # not really sure whether there should be a +$resolution there
			$timeStamps[] = $nextTimeStep;
			$nextTimeStep = end($timeStamps)+$resolution;
			$channel1Data[] = end($channel1Data);
			if($channel2!=0){
				$channel2Data[] = end($channel2Data);
			}
		}
		
		
		# find the time duration
		if (sizeof($timeStamps)>0){
			$timeStampsMin = min($timeStamps);
			$timeStampsMax = max($timeStamps);
			$timeDuration = $timeStampsMax - $timeStampsMin;
		}
		
		# turn the timestamps into the appropriate date format
		# (the larger the time span, the more detailed we want the format to be)
		$dateFormat='d H:i';
		#$dateFormat='H:i';
		#$amChartsDateFormat='DD HH:mm';
		$amChartsDateFormat='DD HH:mm';
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
		$xStart = '\''.date($dateFormat,$xStart).'\'';
		$xEnd = '\''.date($dateFormat,$xEnd).'\'';
		
		
		# assemble the data into an array to be given as argument to the plotting function (but only if theres points to plot)
		if(sizeof($timeStamps)>0){
			if($channel2!=0){
				$title = $names[$channel1Index]." (in ".$channel1Units.", red) and ".$names[$channel2Index]." (in ".$channel2Units.", blue) vs time (starting from ".date('Y.m.d H:i:s',$timeStampsMin).")";
				$plotData = '[';
				#$plotData = "[['time (in s)','".$names[$channel1Index]." (in ".$channel1Units.")','".$names[$channel2Index]." (in ".$channel2Units.")']";
				for($i=0;$i<sizeof($timeStamps);$i++){
					$plotData = $plotData."{'time': ".$timeStamps[$i].","
								 ."'".$names[$channel1Index]."': ".$channel1Data[$i].","
								 ."'".$names[$channel2Index]." (2)': ".$channel2Data[$i]."}";		
					if($i<sizeof($timeStamps)-1){
						$plotData = $plotData.",";
					}								 
					#$plotData = $plotData.",";
					#$plotData = $plotData."[".$timeStamps[$i].",".$channel1Data[$i].",".$channel2Data[$i]."]";
				}
				$plotData = $plotData."]";
			}
			else{
				$title = $names[$channel1Index]." (in ".$channel1Units.") vs time (starting from ".date('Y.m.d H:i:s',$timeStampsMin).")";
				#$plotData = "[['time (in s)','".$names[$channel1Index]." (in ".$channel1Units.")']";
				$plotData = '[';
				for($i=0;$i<sizeof($timeStamps);$i++){
					#$plotData = $plotData.",";
					$plotData = $plotData."{'time': ".$timeStamps[$i].","
								."'".$names[$channel1]."': ".$channel1Data[$i]."}";
					if($i<sizeof($timeStamps)-1){
						$plotData = $plotData.",";
					}
					#$plotData = $plotData."[".$timeStamps[$i].",".$channel1Data[$i]."]";
				}
				$plotData = $plotData."]";
			
			}
		}
		# print_r($plotData);
		# echo "<br><br><br>";
		/* only plot if there are points to plot */
		if(sizeof($timeStamps)>0){
			# define min and max if they were not defined manually
			if($manualScale1==0){
				$min1 = min($channel1Data);
				$max1 = max($channel1Data);
			}
			if($channel2!=0 && $manualScale2==0){
				$min2 = min($channel2Data);
				$max2 = max($channel2Data);
			}
			
			/* plot (performed between the <HTML> tags) */
			?>

			<script>
			var chartData<?php echo $chartId;?> = <?php echo $plotData; ?>;

            AmCharts.ready(function () {
                var chart<?php echo $chartId;?> = new AmCharts.AmSerialChart();

                chart<?php echo $chartId;?>.dataProvider = chartData<?php echo $chartId;?>;
				//chart<?php echo $chartId;?>.dateFormat = "<?php echo $amChartsDateFormat;?>";
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
				categoryAxis.equalSpacing = "false";
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
				categoryAxis.maximum = <?php echo time(); ?>;
				var guide1 = chart<?php echo $chartId;?>.guide;
				
				// first value axis (on the left)
				var valueAxis<?php echo $chartId;?>a = new AmCharts.ValueAxis();
				valueAxis<?php echo $chartId;?>a.position = "left";
				valueAxis<?php echo $chartId;?>a.axisColor = "#FF0000";
				valueAxis<?php echo $chartId;?>a.axisThickness = 5;
				valueAxis<?php echo $chartId;?>a.title="<?php echo $names[$channel1Index]." (in ".$channel1Units.")";?>";
				valueAxis<?php echo $chartId;?>a.titleFontSize = 20;
				valueAxis<?php echo $chartId;?>a.titleColor = "#FF0000";
				valueAxis<?php echo $chartId;?>a.color = "#FF0000";
				valueAxis<?php echo $chartId;?>a.fontSize = 25;
				valueAxis<?php echo $chartId;?>a.autoGridCount = false;
				valueAxis<?php echo $chartId;?>a.gridCount = 4;
				<?php if($manualScale1=='1'){?>valueAxis<?php echo $chartId;?>a.minimum = <?php echo $min1;?>;
				valueAxis<?php echo $chartId;?>a.maximum = <?php echo $max1;?>;<?php }?>
				<?php if($calibration1Type == 'logarithmic'){?>
				valueAxis<?php echo $chartId;?>a.logarithmic = true;
				<?php }?>
				chart<?php echo $chartId;?>.addValueAxis(valueAxis<?php echo $chartId;?>a);
				<?php if($channel2!=0){
					?>
				// second value axis (on the right)
				var valueAxis<?php echo $chartId;?>b = new AmCharts.ValueAxis();
				valueAxis<?php echo $chartId;?>b.position = "right"; // this line makes the axis to appear on the right
				valueAxis<?php echo $chartId;?>b.axisColor = "#aaaaFF";
				
				valueAxis<?php echo $chartId;?>b.gridAlpha = 0;
				valueAxis<?php echo $chartId;?>b.axisThickness = 5;
				valueAxis<?php echo $chartId;?>b.title="<?php echo $names[$channel2Index]." (in ".$channel2Units.")";?>";
				valueAxis<?php echo $chartId;?>b.titleFontSize = 20;
				valueAxis<?php echo $chartId;?>b.titleColor = "#aaaaFF";
				valueAxis<?php echo $chartId;?>b.color = "#aaaaFF";
				valueAxis<?php echo $chartId;?>b.fontSize = 25;
				valueAxis<?php echo $chartId;?>b.autoGridCount = false;
				valueAxis<?php echo $chartId;?>b.gridCount = 4;
				<?php if($manualScale1=='1'){?>valueAxis<?php echo $chartId;?>b.minimum = <?php echo $min2;?>;
				valueAxis<?php echo $chartId;?>b.maximum = <?php echo $max2;?>;<?php }?>
				<?php if($calibration2Type == 'logarithmic'){?>
				valueAxis<?php echo $chartId;?>b.logarithmic = true;
				<?php }?>
				chart<?php echo $chartId;?>.addValueAxis(valueAxis<?php echo $chartId;?>b);
				<?php
				}
				else{
				}?>
				
				// GRAPHS
				// first graph
				var graph<?php echo $chartId;?>a = new AmCharts.AmGraph(AmCharts.themes.chalk);
				graph<?php echo $chartId;?>a.valueAxis = valueAxis<?php echo $chartId;?>a; // we have to indicate which value axis should be used
				graph<?php echo $chartId;?>a.valueField = "<?php echo $names[$channel1Index]; ?>";
				graph<?php echo $chartId;?>a.bullet = "round";
				graph<?php echo $chartId;?>a.hideBulletsCount = 20;
				graph<?php echo $chartId;?>a.lineColor = "#FF0000";
				graph<?php echo $chartId;?>a.lineThickness = 3;
				graph<?php echo $chartId;?>a.type = "smoothedLine";
				graph<?php echo $chartId;?>a.connect = false;
				chart<?php echo $chartId;?>.addGraph(graph<?php echo $chartId;?>a);
	
				<?php if($channel2!=0){
					?>
				// second graph
				var graph<?php echo $chartId;?>b = new AmCharts.AmGraph(AmCharts.themes.chalk);
				graph<?php echo $chartId;?>b.valueAxis = valueAxis<?php echo $chartId;?>b; // we have to indicate which value axis should be used
				graph<?php echo $chartId;?>b.valueField = "<?php echo $names[$channel2Index].' (2)'; ?>";
				graph<?php echo $chartId;?>b.bullet = "square";
				graph<?php echo $chartId;?>b.hideBulletsCount = 20;
				graph<?php echo $chartId;?>b.lineColor = "#aaaaFF";
				graph<?php echo $chartId;?>b.lineThickness = 3;
				graph<?php echo $chartId;?>b.type = "smoothedLine";
				graph<?php echo $chartId;?>b.connect = false;
				chart<?php echo $chartId;?>.addGraph(graph<?php echo $chartId;?>b);
				<?php } else {}?>
			   
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
