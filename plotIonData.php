<!- ********* plotIonData.php ********* ->
<?php
	include_once 'readIonFiles.php';
	
	// get the chosen month and day
	if (isset($_GET['month'])){
		$chosenMonth = $_GET['month'];
	}
	else{
		$chosenMonth = date('m');
	}
	
	if (isset($_GET['day'])){
		$chosenDay = $_GET['day'];
	}
	else{
		// if the chosen month is the current month, the chosen day should be today
		if($chosenMonth == date('m')){
			$chosenDay = date('d');
		}
		// otherwise it should be the last day of the month
		else{
			echo date('Y').'-'.$chosenMonth.'-'.date('d').'<br>';
			$myDate = strtotime('Y'.'-'.$chosenMonth.date('d'));
						echo $myDate."<br>";
			$chosenDay = date('t',$myDate);
			echo $chosenDay."<br>";
		}
	}
	
	// get the files to be plotted, assuming they were chosen by the user. If not, we will select the before-latest and latest files at a later point down the script.
	if (isset($_GET['previousFileName'])){
		$fileName1 = $_GET['previousFileName'];
	}
	else{
		$fileName1 = ""; // this is just there to avoid bugs, it is overwritten later on in the script
	}
	if (isset($_GET['fileName'])){
		$fileName2 = $_GET['fileName'];
	}
	else{
		$fileName2 = ""; // this is just there to avoid bugs, it is overwritten later on in the script
	}
		
		
	echo "<p>";
	// display the months
	$months = scandir($GLOBALS['ionDataFilesDirectoryPath']);
	echo "month ";
	for($i=2;$i<sizeof($months);$i++){
		$monthNumber = substr($months[$i],5);
		$linkStr = "index.php?plottingMode=ionData&month=".$monthNumber.'&autoRefresh=30';
		if($monthNumber == $chosenMonth){
			echo "<a href=".$linkStr."><b>".$monthNumber."</b></a> ";
		}
		else{
			echo "<a href=".$linkStr.">".$monthNumber."</a> ";
		}
	}
	
	echo "<br>";
	
	// display the days of the chosen month (default - the current month)
	$days = scandir($GLOBALS['ionDataFilesDirectoryPath'].'/month'.$chosenMonth); 
	echo "day ";
	for($i=2;$i<sizeof($days);$i++){
		$dayNumber = substr($days[$i],3);
		// only display a link to days during which measurements were done
		$dayDir = $GLOBALS['ionDataFilesDirectoryPath'].'/month'.$chosenMonth.'/day'.$dayNumber;
		$files = glob($dayDir."/*.THes",GLOB_BRACE); // load files to check if the folder isn't empty
		if(!empty($files)){
			$linkStr = "index.php?plottingMode=ionData&month=".$chosenMonth."&day=".$dayNumber.'&autoRefresh=30';
			if($dayNumber == $chosenDay){
				echo "<a href=".$linkStr."><b>".$dayNumber."</b></a> ";
			}
			else{
				echo "<a href=".$linkStr.">".$dayNumber."</a> ";
			}
		}
	}
	echo "<br><br>";
			
			
	// display the timestamps of the ion data files of the chosen day (default - today)
	$allFilesInChosenDayDir = scandir($GLOBALS['ionDataFilesDirectoryPath'].'/month'.$chosenMonth.'/day'.$chosenDay);  // get all files
	// filter the files to get only the .dat ones
	$magSweepNames = [];
	$cycSweepNames = [];
	$calNames = [];
	
	// sort the .dat files into the 3 types
	for($i=0;$i<sizeof($allFilesInChosenDayDir);$i++){
		$fileName = $allFilesInChosenDayDir[$i];
		$fileExtension = explode('.',$fileName)[1];
		if ($fileExtension == 'dat'){
			if(explode('_',$fileName)[0] == "Sweep"){
				$fileType = explode('_',$fileName)[0].'_'. explode('_',$fileName)[1];
			}
			else{
				$fileType = explode('_',$fileName)[0];
			}
			if($fileType == "Sweep_Magnetron"){
				$magSweepNames[] = $fileName;
			}
			if($fileType == "Sweep_Cyclotron"){
				$cycSweepNames[] = $fileName;
			}
			if($fileType == "Miscellaneous"){
				$calNames[] = $fileName;
			}
		}
	}
	
	// create the links list for the plotting of the data files of the 3 different types
	$totalNumberOfFiles = sizeof($magSweepNames) + sizeof($cycSweepNames) + sizeof($calNames);
	// the file names of the second/before-latest (1) and the first/latest (2) and files of each of the 3 types
	$magName1 = '';
	$magName2 = '';
	$cycName1 = '';
	$cycName2 = '';
	$calName1 = '';
	$calName2 = '';
	
	// the timeStamps of the second/before-latest (1) and the first/latest (2) and files of each of the 3 types
	$magTime1 = 0;
	$magTime2 = 0;
	$cycTime1 = 0;
	$cycTime2 = 0;
	$calTime1 = 0;
	$calTime2 = 0;
	
	// show the list of available data files - only do this if theres only data taken for today
	
	if($totalNumberOfFiles == 0){
		echo "<br> No data taken for this day.";
	}
	else{
		
		// show the list of magnetron sweeps
		if(sizeof($magSweepNames)>0){
			echo "Magnetron Sweeps</big>";
			for($i=0;$i<sizeof($magSweepNames);$i++){
				$magName1 = $magName2;
				$magTime1 = $magTime2;
				
				$magName2 = $magSweepNames[$i];
				$timeStamp = explode('.',$magName2)[0];
				$timeStamp = substr($timeStamp,sizeof($timeStamp)-7);
				$magTime2 = $timeStamp;
				$timeStamp = substr($timeStamp,0,-2); // remove the seconds portion (get a substring starting from the first (0th) char (including) up to the 2nd char from the end (not including)
				$timeStamps[] = $timeStamp;
				$linkStr = "index.php?plottingMode=ionData&month=".$chosenMonth."&day=".$chosenDay."&previousFileName=".$magName1."&fileName=".$magName2.'&autoRefresh=30';
				if ($magName2 === $fileName2){
					echo "<a href=".$linkStr."><b><big>".$timeStamp."</big></b></a> ";
				}else{
					echo "<a href=".$linkStr.">".$timeStamp."</a> ";
				}
			}
			echo "<br>";
		}

		// show the list of cyclotron sweeps
		if(sizeof($cycSweepNames)>0){
			echo "Cyclotron Sweeps<br>";
			for($i=0;$i<sizeof($cycSweepNames);$i++){
				$cycName1 = $cycName2;
				$cycTime1 = $cycTime2;
				
				$cycName2 = $cycSweepNames[$i];
				$timeStamp = explode('.',$cycName2)[0];
				$timeStamp = substr($timeStamp,sizeof($timeStamp)-7);
				$cycTime2 = $timeStamp;
				$timeStamp = substr($timeStamp,0,-2); // remove the seconds portion (get a substring starting from the first (0th) char (including) up to the 2nd char from the end (not including)
				$timeStamps[] = $timeStamp;
				$linkStr = "index.php?plottingMode=ionData&month=".$chosenMonth."&day=".$chosenDay."&previousFileName=".$cycName1."&fileName=".$cycName2.'&autoRefresh=30';
				if ($cycName2 === $fileName2){
					echo "<a href=".$linkStr."><b><big>".$timeStamp."</big></b></a> ";
				}else{
					echo "<a href=".$linkStr.">".$timeStamp."</a> ";
				}
			}
			echo "<br>";
		}
		
		// show the list of calibration measurements
		if(sizeof($calNames)>0){
			echo "Calibration Measurements<br>";
			for($i=0;$i<sizeof($calNames);$i++){
				$calName1 = $calName2;
				$calTime1 = $calTime2;
				
				$calName2 = $calNames[$i];
				$timeStamp = explode('.',$calName2)[0];
				$timeStamp = substr($timeStamp,sizeof($timeStamp)-7);
				$calTime2 = $timeStamp;
				$timeStamp = substr($timeStamp,0,-2); // remove the seconds portion (get a substring starting from the first (0th) char (including) up to the 2nd char from the end (not including)
				$timeStamps[] = $timeStamp;
				$linkStr = "index.php?plottingMode=ionData&month=".$chosenMonth."&day=".$chosenDay."&previousFileName=".$calName1."&fileName=".$calName2.'&autoRefresh=30';
				if ($calName2 === $fileName2){
					echo "<a href=".$linkStr."><b><big>".$timeStamp."</big></b></a> ";
				}else{
					echo "<a href=".$linkStr.">".$timeStamp."</a> ";
				}
			}
			echo "<br>";
		}		
		
		$latestName2 = "";
		// check which of the (potentially up to) 3 latest files (mag cyc and cal) is the latest. Then choose that file, as well as the latest one before it of the same type.
		if($magTime2>=$cycTime2 && $magTime2>=$calTime2){
			$latestName1 = $magName1;
			$latestName2 = $magName2;
		}
		if($cycTime2>$magTime2 && $cycTime2>$calTime2){
			$latestName1 = $cycName1;
			$latestName2 = $cycName2;
		}
		if($calTime2>$magTime2 && $calTime2>$cycTime2){
			$latestName1 = $calName1;
			$latestName2 = $calName2;
		}		
		// at this point in the script, $fileName1 and $fileName2 are defined as $_GET['previousFileName'] and $_GET['fileName'], if those variables are set, and as "" otherwise.

		// set the file to be plotted $fileName2 to be either the chosen file or the latest one in case no file was chosen.
		if (isset($_GET['fileName'])){
			$fileName1 = $_GET['previousFileName'];  # we don't check whether previousFileName is set because if fileName is set then so is previousFileName
			if($fileName1==""){ # if theres no previousFileName, as in if fileName is the first of its type for today, just select it twice, as in plot it twice on the same graph
				$fileName1 = $_GET['fileName'];
			}
			$fileName2 = $_GET['fileName'];
		}
		else{
			$fileName1 = $latestName1;
			$fileName2 = $latestName2;
		}
	
	
	
		// determine the files type
		if(explode('_',$fileName2)[0] == "Sweep"){
			$type = explode('_',$fileName2)[0].'_'. explode('_',$fileName2)[1];
		}
		else{
			$type = explode('_',$fileName2)[0];
		}
		
		// read the dat and THes files of the first data file (which might be the same as the second in certain cases)
		$filePath1 = $GLOBALS['ionDataFilesDirectoryPath'] . '/month'. $chosenMonth. '/day'.$chosenDay.'/'.$fileName1;
		list($x1,$y1,$ionType1,$pulseAmp1,$pulseDur1,$sweepAmp1,$sweepCenter1,$sweepRange1,$sweepTime1,$sweepDirection1,$sweepAtt1,$ringVoltage1,$ringCorrectionVoltage1,$driveAmplitude1_mV,$modulationAmplitude1_mV) = readIonFiles($filePath1,$fileName1,$type);
			
		// read the dat and THes files of the second data file
		$filePath2 = $GLOBALS['ionDataFilesDirectoryPath'] . '/month'. $chosenMonth. '/day'.$chosenDay.'/'.$fileName2;
		list($x2,$y2,$ionType2,$pulseAmp2,$pulseDur2,$sweepAmp2,$sweepCenter2,$sweepRange2,$sweepTime2,$sweepDirection2,$sweepAtt2,$ringVoltage2,$ringCorrectionVoltage2,$driveAmplitude2_mV,$modulationAmplitude2_mV) = readIonFiles($filePath2,$fileName2,$type);
	
		echo "<br>";
		
		 // if plotting sweeps
		if($type == "Sweep_Magnetron" || $type == "Sweep_Cyclotron"){
			// define axes for the plotting
			$horizontalAxisName = "frequency";
			$horizontalAxisUnit = "Hz";
			
			$plotBothGraphs = 1; // plot both graphs? 1 yes 0 no
			// use the variables of the upsweep file for plotting
			$sweepCenter = $sweepCenter2;
			$sweepRange = $sweepRange2;
			if($sweepDirection2=="up"){
				$x = $x2;
				}
				else{
					$x = array_reverse($x2);
					}
			
				
			// if the ranges or the centers aren't equal, plot file2 only (by plotting it twice)
			if( ($sweepRange1 != $sweepRange2) || ($sweepCenter1 != $sweepCenter2) ){
				$plotBothGraphs = 0;
				$x1 = $x2;
				$y1 = $y2;
				$sweepDirection1 = $sweepDirection2;
				$sweepCenter = $sweepCenter2;
				$sweepRange = $sweepRange2;
				
			}
			
			// // if the sweep directions are not equal, 
			// if($sweepDirection1!=$sweepDirection2){ // if the sweep directions are not equal
			// 		$y2 = array_reverse($y2); // flip y2
			// }

			// shift the horizontal axis
			for($i=0;$i<sizeof($x);$i++){
				$x[$i] = $x[$i] - $sweepCenter;
			}
			$verticalAxisName1 = $sweepDirection1."sweep - Lock Voltage in V (1)";
			$verticalAxisName2 = $sweepDirection2."sweep - Lock Voltage in V (2)";
			
			// calculate the intersection point
			$minDifference = 999;
			$intersectionFrequency = 0;
			for($i=0;$i<min(sizeof($y1),sizeof($y2));$i++){
				$difference = abs($y1[$i]-$y2[$i]);
				if($difference<$minDifference){
					$minDifference = $difference;
					$intersectionFrequency = $x[$i];
				}
			}
				
		}
			

		if($type == 'Miscellaneous'){
			$x = $x1; // we assume that for calibration measurements the same range is used for all of them
			$horizontalAxisName = "time";
			$horizontalAxisUnits = "s";
			$yAverage1 = round(array_sum($y1)/sizeof($y1),2);
			$verticalAxisName1 = "Calibration measurement (1)";
			$yAverage2 = round(array_sum($y2)/sizeof($y2),2);
			$verticalAxisName2 = "Calibration measurement (2)";
		}
		// prepare the data in the format that the plot function likes
		$numOfPoints = min(sizeof($y1),sizeof($y2));
		$myDataProvider = '[';
		for($i=1;$i<$numOfPoints;$i++){ // first sweep data point has an unexpected x value so it was removed
			$myDataProvider = $myDataProvider."{'".$horizontalAxisName."': ".$x[$i].","
						 ."'amp1': ".$y1[$i].","
						 ."'amp2': ".$y2[$i]."}";		
			if($i<sizeof($x)-1){
				$myDataProvider = $myDataProvider.",";
			}								 
		}
		$myDataProvider = $myDataProvider."]";

		$chartId = 1;
		echo "<br>";
		?>
		<script>
			var myDataProvider = <?php echo $myDataProvider; ?>;
			AmCharts.ready(function () {
                var myChart = new AmCharts.AmSerialChart();

                myChart.dataProvider = myDataProvider;
				myChart.categoryField = "<?php echo $horizontalAxisName;?>";
				myChart.angle = 30;
				myChart.depth3D = 15;
				myChart.backgroundColor = "#282828";
				myChart.backgroundAlpha = 1;
				myChart.creditsPosition = "bottom-right";
				myChart.color = "#FFFFFF";
				
                // AXES
                // category (horizontal axis?)
                var myCategoryAxis = myChart.categoryAxis;
                myCategoryAxis.dashLength = 20;
                myCategoryAxis.minorGridEnabled = true;
                myCategoryAxis.minorGridAlpha = 0.1;
				myCategoryAxis.autoGridCount= false;
				myCategoryAxis.gridCount = 1;
				myCategoryAxis.fontSize = 15;
				myCategoryAxis.fontColor = '#FF0000';
				myCategoryAxis.axisColor = '#FFFFFF';
				myCategoryAxis.fillAlpha = 0.1;
				myCategoryAxis.fillColor = '#aaaaFF';
				myCategoryAxis.equalSpacing = true;
				var myGuide = myChart.guide;
				
				// first value (vertical) axis (on the left)
				var myValueAxis1 = new AmCharts.ValueAxis();
				myValueAxis1.position = "left";
				myValueAxis1.axisColor = "#FF0000";
				myValueAxis1.axisThickness = 5;
				myValueAxis1.title="<?php echo $verticalAxisName1; ?>";
				myValueAxis1.titleFontSize = 20;
				myValueAxis1.titleColor = "#FF0000";
				myValueAxis1.color = "#FF0000";
				myValueAxis1.fontSize = 25;
				myValueAxis1.autoGridCount = false;
				myValueAxis1.gridCount = 4;
				myChart.addValueAxis(myValueAxis1);
				// second value (vertical) axis (on the right)
				var myValueAxis2 = new AmCharts.ValueAxis();
				myValueAxis2.position = "right"; // this line makes the axis to appear on the right
				myValueAxis2.axisColor = "#aaaaFF";
				myValueAxis2.gridAlpha = 0;
				myValueAxis2.axisThickness = 5;
				myValueAxis2.title="<?php echo $verticalAxisName2; ?>";
				myValueAxis2.titleFontSize = 20;
				myValueAxis2.titleColor = "#aaaaFF";
				myValueAxis2.color = "#aaaaFF";
				myValueAxis2.fontSize = 25;
				myValueAxis2.autoGridCount = false;
				myValueAxis2.gridCount = 4;
				myChart.addValueAxis(myValueAxis2);
				
				// GRAPHS
				// first graph
				var myGraph1 = new AmCharts.AmGraph(AmCharts.themes.chalk);
				myGraph1.valueAxis = myValueAxis1; // we have to indicate which value axis should be used
				myGraph1.valueField = "amp1";
				myGraph1.bullet = "round";
				myGraph1.hideBulletsCount = 20;
				myGraph1.lineColor = "#FF0000";
				myGraph1.lineThickness = 3;
				myGraph1.type = "smoothedLine";
				myGraph1.connect = false;
				myChart.addGraph(myGraph1);
				// second graph
				var myGraph2 = new AmCharts.AmGraph(AmCharts.themes.chalk);
				myGraph2.valueAxis = myValueAxis2; // we have to indicate which value axis should be used
				myGraph2.valueField = "amp2";
				myGraph2.bullet = "square";
				myGraph2.hideBulletsCount = 20;
				myGraph2.lineColor = "#aaaaFF";
				myGraph2.lineThickness = 3;
				myGraph2.type = "smoothedLine";
				myGraph2.connect = false;
				myChart.addGraph(myGraph2);
				
			   
				// CURSOR
                var myChartCursor = new AmCharts.ChartCursor();
                myChartCursor.cursorAlpha = 1;
                myChartCursor.cursorPosition = "mouse";
				myChartCursor.color = "#FFFFFF";				
                myChart.addChartCursor(myChartCursor);

				// BALLOON
				var myBalloon = myChart.balloon;
				myBalloon.color="#ffffff";
				myBalloon.fontSize=30;
				
                // SCROLLBAR
                var myChartScrollbar = new AmCharts.ChartScrollbar();
				myChartScrollbar.color = "#333333";
				myChartScrollbar.backgroundAlpha = 0.2;
				
                myChart.addChartScrollbar(myChartScrollbar);
				

                // WRITE
                myChart.write("chart1div");
            });
			</script>
		<?php
		}
		
		// print info about the files
		if($totalNumberOfFiles > 0){
			if($type=="Sweep_Magnetron" || $type=="Sweep_Cyclotron"){
				echo "(1) ion <b>".$ionType1."</b> center <b>".$sweepCenter1."Hz</b> range <b>".$sweepRange1."mHz</b> amp <b>".$sweepAmp1."mV</b> att <b>".$sweepAtt1." dB</b> time <b>".$sweepTime1."s</b> dir <b>".$sweepDirection1."</b> drive amp <b>".$driveAmplitude1_mV." mV</b> mod <b>".$modulationAmplitude1_mV." mV</b> ring <b>".$ringVoltage1."V</b> corr <b>".$ringCorrectionVoltage1."V</b><br>";
				echo "(2) ion <b>".$ionType2."</b> center <b>".$sweepCenter2."Hz</b> range <b>".$sweepRange2."mHz</b> amp <b>".$sweepAmp2."mV</b> att <b>".$sweepAtt2." dB</b> time <b>".$sweepTime2."s</b> dir <b>".$sweepDirection2."</b> drive amp <b>".$driveAmplitude2_mV." mV</b> mod <b>".$modulationAmplitude2_mV." mV</b> ring <b>".$ringVoltage2."V</b> corr <b>".$ringCorrectionVoltage2."V</b><br>";
				echo "intersection at ".($sweepCenter1+$intersectionFrequency)." Hz<br>";
				if( ($sweepRange1!=$sweepRange2) || ($sweepCenter1!=$sweepCenter2) ){
					echo "note: ranges and/or centers are not equal. Plotting the second (newer) graph only.<br>";
				}
			}
			if($type=="Miscellaneous"){
				echo "ion <b>".$ionType1."</b> pulse amp <b>".$pulseAmp1."mV</b> dur <b>".$pulseDur1."ms</b> drive amp <b>".$driveAmplitude1_mV." mV</b> mod <b>".$modulationAmplitude1_mV." mV</b> ring <b>".$ringVoltage1."V</b> corr <b>".$ringCorrectionVoltage1."V</b> avg <b>".$yAverage1."</b><br>";	
				echo "ion <b>".$ionType2."</b> pulse amp <b>".$pulseAmp2."mV</b> dur <b>".$pulseDur2."ms</b> drive amp <b>".$driveAmplitude2_mV." mV</b> mod <b>".$modulationAmplitude2_mV." mV</b>  ring <b>".$ringVoltage2."V</b> corr <b>".$ringCorrectionVoltage2."V</b> avg <b>".$yAverage2."</b><br>";
				echo 'difference = '.($yAverage2-$yAverage1).'<br>';
			}		
		}
		
				
		?>
		</p>
		<div id="chart1div" style="width:80%; height:50%;"></div>
		</td></tr></table>

