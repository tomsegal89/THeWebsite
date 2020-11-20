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
		$chosenDay = date('d');
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
		$files = glob($dayDir."/*.THes",GLOB_BRACE); // we're only checking for THes files to make it easier, but it doesn't 	matter because if there are THes files then there area also dat files
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
		// check which of the (potentially up to) 3 before-latest files (mag cyc and cal) is the before-latest
		if($magTime1>=$cycTime1 && $magTime1>=$calTime1){
			$latestName1 = $magName1;
		}
		if($cycTime1>$magTime1 && $cycTime1>$calTime1){
			$latestName1 = $cycName1;
		}
		if($calTime1>$magTime1 && $calTime1>$cycTime1){
			$latestName1 = $calName1;
		}		
		
		// check which of the (potentially up to) 3 latest files (mag cyc and cal) is the latest
		if($magTime2>=$cycTime2 && $magTime2>=$calTime2){
			$latestName2 = $magName2;
		}
		if($cycTime2>$magTime2 && $cycTime2>$calTime2){
			$latestName2 = $cycName2;
		}
		if($calTime2>$magTime1 && $calTime2>$cycTime2){
			$latestName2 = $calName2;
		}		
		
		
		// set the first file to be plotted $fileName1 to be either the file before the chosen file or none in case the chosen file is the first of its type for today.
		if (isset($_GET['previousFileName'])){
			$fileName1 = $_GET['previousFileName'];
		}
		else{
			$fileName1 = $latestName1;
		}
	
	
		// set the file to be plotted $fileName to be either the chosen file or the latest one in case no file was chosen.
		if (isset($_GET['fileName'])){
			$fileName2 = $_GET['fileName'];
		}
		else{
			$fileName2 = $latestName2;
		}
		
		// determine the files type
		if(explode('_',$fileName2)[0] == "Sweep"){
			$type = explode('_',$fileName2)[0].'_'. explode('_',$fileName2)[1];
		}
		else{
			$type = explode('_',$fileName2)[0];
		}
		
		
		if(!$fileName1==''){ // if we have 2 files to plot
			// read the dat and THes files of the first data file
			$filePath1 = $GLOBALS['ionDataFilesDirectoryPath'] . '/month'. $chosenMonth. '/day'.$chosenDay.'/'.$fileName1;
			list($x1,$y1,$ionType1,$pulseAmp1,$pulseDur1,$sweepAmp1,$sweepCenter1,$sweepRange1,$sweepTime1,$sweepDirection1,$att1_1,$att2_1,$att3_1,$att4_1,$ringVoltage1,$ringCorrectionVoltage1) = readIonFiles($filePath1,$type,$fileName1);
			#if($sweepDirection1=='down'){ # no need - Marc already does this
			#	array_reverse($y1);
			#}
			
			// read the dat and THes files of the second data file
			$filePath2 = $GLOBALS['ionDataFilesDirectoryPath'] . '/month'. $chosenMonth. '/day'.$chosenDay.'/'.$fileName2;
			list($x2,$y2,$ionType2,$pulseAmp2,$pulseDur2,$sweepAmp2,$sweepCenter2,$sweepRange2,$sweepTime2,$sweepDirection2,$att1_2,$att2_2,$att3_2,$att4_2,$ringVoltage2,$ringCorrectionVoltage2) = readIonFiles($filePath2,$type,$fileName2);
			#if($sweepDirection2=='down'){ # no need - Marc already does this
			#	array_reverse($y2);
			#}
		}
		else{ // if we have one file to plot, plot the same file twice. This makes the code much shorter.
			// read the dat and THes files of the first data file
			$filePath1 = $GLOBALS['ionDataFilesDirectoryPath'] . '/month'. $chosenMonth. '/day'.$chosenDay.'/'.$fileName2;
			list($x1,$y1,$ionType1,$pulseAmp1,$pulseDur1,$sweepAmp1,$sweepCenter1,$sweepRange1,$sweepTime1,$sweepDirection1,$att1_1,$att2_1,$att3_1,$att4_1,$ringVoltage1,$ringCorrectionVoltage1) = readIonFiles($filePath2,$type,$fileName2);
			#if($sweepDirection1=='down'){ # no need - Marc already does this
			#	array_reverse($y1);
			#}
			
			// read the dat and THes files of the second data file
			$filePath2 = $GLOBALS['ionDataFilesDirectoryPath'] . '/month'. $chosenMonth. '/day'.$chosenDay.'/'.$fileName2;
			list($x2,$y2,$ionType2,$pulseAmp2,$pulseDur2,$sweepAmp2,$sweepCenter2,$sweepRange2,$sweepTime2,$sweepDirection2,$att1_2,$att2_2,$att3_2,$att4_2,$ringVoltage2,$ringCorrectionVoltage2) = readIonFiles($filePath2,$type,$fileName2);
			#if($sweepDirection2=='down'){ # no need - Marc already does this
			#	array_reverse($y2);
			#}
		}
	
		echo "<br>";
		
		if($type == "Sweep_Magnetron" || $type == "Sweep_Cyclotron"){ // if plotting sweeps
			// define axes for the plotting
			$horizontalAxisName = "frequency";
			$horizontalAxisUnit = "Hz";
			if($sweepRange1>$sweepRange2){ // if the range of file1 is larger than that of file2
				// use the range of file1 for plotting
				$x = $x1;
				$sweepCenter = $sweepCenter1;
				$factor = floor(sizeof($x)*$sweepRange2/$sweepRange1);
				// pad y2 with 0's on both sides in order to make it fit into x1 in the middle and not to one of the sides
				$y2New = [];
				for($i=0;$i<floor($factor/2);$i++){
					$y2New[$i] = 0;
				}
				for($i=0;$i<$factor;$i++){
					$j = $i*floor($sweepRange1/$sweepRange2);
					$y2New[floor($factor/2)+$i] = $y2[$j];
				}
				for($i=0;$i<floor($factor/2);$i++){
					$y2New[floor($factor/2) + $factor + $i] = 0;
				}
				$y2 = $y2New;
				
				// if the sweep directions are not equal, 
				if($sweepDirection1!=$sweepDirection2){ // if the sweep directions are not equal
					$y2 = array_reverse($y2); // flip y2 (because its using x1 which is inverted in relation to it)
				}
			}
			if($sweepRange2>$sweepRange1){ // if the range of file2 is larger than that of file1
				// use the range of file2 for plotting
				$x = $x2;
				$sweepCenter = $sweepCenter2;
				
				// pad y1 with 0's on both sides in order to make it fit into x2 in the middle and not to one of the sides
				$halfSizeDifference = floor(sizeof($x) - sizeof($y1));
				$y1New = [];
				for($i=0;$i<floor($halfSizeDifference);$i++){
					$y1New[$i] = 0;
				}
				for($i=0;$i<sizeof($y1);$i++){
					$y1New[floor($halfSizeDifference)+$i] = $y1[$i];
				}
				for($i=0;$i<floor($halfSizeDifference);$i++){
					$y1New[floor($halfSizeDifference) + sizeof($y1) + $i] = 0;
				}
				$y1 = $y1New;
				
				// if the sweep directions are not equal, 
				if($sweepDirection1!=$sweepDirection2){ // if the sweep directions are not equal
					$y1 = array_reverse($y1); // flip y1 (because its using x2 which is inverted in relation to it)
				}
			}
			if($sweepRange1 == $sweepRange2){ // if the range are equal
				// use the range of file2 for plotting
				$x = $x1;
				$sweepCenter = $sweepCenter1;
				
				// if the sweep directions are not equal, 
				if($sweepDirection1!=$sweepDirection2){ // if the sweep directions are not equal
					$y2 = array_reverse($y2); // flip y2 (because its using x1 which is inverted in relation to it)
				}
			}
			
			// shift the horizontal axis
			for($i=0;$i<sizeof($x);$i++){
				$x[$i] = round($x[$i] - $sweepCenter,4);
			}
			$verticalAxisName1 = $sweepDirection1."sweep - Lock Voltage in V (1)";
			$verticalAxisName2 = $sweepDirection2."sweep - Lock Voltage in V (2)";
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
		$myDataProvider = '[';
		for($i=0;$i<sizeof($x);$i++){
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
				myCategoryAxis.gridCount = 5;
				myCategoryAxis.fontSize = 25;
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
				echo "(1) ion <b>".$ionType1."</b> center <b>".$sweepCenter1."Hz</b> range <b>".$sweepRange1."mHz</b> amp <b>".$sweepAmp1."mV</b> att <b>".$att3_1."dB</b> time <b>".$sweepTime1."s</b> dir <b>".$sweepDirection1."</b> lock att <b>".$att1_1."dB</b> mod att <b>".$att2_1."dB</b> ring <b>".$ringVoltage1."V</b> corr <b>".$ringCorrectionVoltage1."V</b><br>";
				echo "(2) ion <b>".$ionType2."</b> center <b>".$sweepCenter2."Hz</b> range <b>".$sweepRange2."mHz</b> amp <b>".$sweepAmp2."mV</b> att <b>".$att3_2."dB</b> time <b>".$sweepTime2."s</b> dir <b>".$sweepDirection2."</b> lock att <b>".$att1_2."dB</b> mod att <b>".$att2_2."dB</b> ring <b>".$ringVoltage2."V</b> corr <b>".$ringCorrectionVoltage2."V</b><br>";
				if($sweepRange1!=$sweepRange2){
					echo "note: ranges are not equal. Padding the data with the smaller range.<br>";
				}
			}
			if($type=="Miscellaneous"){
				echo "ion <b>".$ionType1."</b> pulse amp <b>".$pulseAmp1."mV</b> dur <b>".$pulseDur1."ms</b> lock att <b>".$att1_1."dB</b> mod att <b>".$att2_1."dB</b> ring <b>".$ringVoltage1."V</b> corr <b>".$ringCorrectionVoltage1."V</b> avg <b>".$yAverage1."</b><br>";	
				echo "ion <b>".$ionType2."</b> pulse amp <b>".$pulseAmp2."mV</b> dur <b>".$pulseDur2."ms</b> lock att <b>".$att1_2."dB</b> mod att <b>".$att2_2."dB</b> ring <b>".$ringVoltage2."V</b> corr <b>".$ringCorrectionVoltage2."V</b> avg <b>".$yAverage2."</b><br>";
			}		
		}
		if(isset($type) && $type=="Miscellaneous" && !$fileName1==''){
			echo 'difference = '.($yAverage2-$yAverage1).'<br>';
		}
		
				
		?>
		</p>
		<div id="chart1div" style="width:80%; height:50%;"></div>
		</td></tr></table>

