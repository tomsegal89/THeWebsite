<!- ********* plottingForm.php ********* ->
<?php
	if(!empty($_GET["plottingMode"])){
		$plottingMode = $_GET["plottingMode"];
	}
	else{
		$plottingMode = $DEFAULT_PLOTTING_MODE;
	}
	
	if($plottingMode == 'environmentalData'){
		echo "<h2>Plot environmental data:</h2>";
	}
	if($plottingMode == 'flowData'){
		echo "<h2>Plot flow data:</h2>
			<p>
				This uses a different plotting function which reads the flow data from a smaller file that contains just the timestamps of the rising edges and is thereby faster.<br>
				One should get identical results by plotting the flow data using the \"Plot Environmental Data\" option, it will just take longer.<br>
			<p>
			";
	}
	
	if($plottingMode == 'environmentalData' || $plottingMode == 'flowData'){
		?>
		<form method="GET" action = "<?php $_PHP_SELF ?>">
			<div>
				<table style="float: left">
					<tr>
						<td>
							plot the last
							<select name="timeDuration">
								<?php
									if(!empty($_GET['timeDuration'])){
										$timeDuration = $_GET['timeDuration'];
									}
									else{
										$timeDuration = $DEFAULT_TIME_DURATION;
									}
								?>
								<option value="1" <?php if ($timeDuration == '1'){echo "selected";}?>>1</option>
								<option value="2" <?php if ($timeDuration == '2'){echo "selected";}?>>2</option>
								<option value="4" <?php if ($timeDuration == '4'){echo "selected";}?>>4</option>
								<option value="8" <?php if ($timeDuration == '8'){echo "selected";}?>>8</option>
								<option value="16" <?php if ($timeDuration == '16'){echo "selected";}?>>16</option>
								<option value="32" <?php if ($timeDuration == '32'){echo "selected";}?>>32</option>
							</select>
							<select name="timeUnit">
								<?php
									if(!empty($_GET['timeUnit'])){
										$timeUnit = $_GET['timeUnit'];
									}
									else{
										$timeUnit = $DEFAULT_TIME_UNIT;
									}
								?>
								<option value="minutes" <?php if ($timeUnit == 'minutes'){echo "selected";} ?>>minutes</option>
								<option value="hours" <?php if ($timeUnit == 'hours'){echo "selected";} ?>>hours</option>
								<option value="days" <?php if ($timeUnit == 'days'){echo "selected";} ?>>days</option>
							</select>
							of 
							
						</td>
						<td>
							channel1 :
							<?php if($plottingMode == "environmentalData"){?>
								<select name="channel1">
									<?php
										if(!empty($_GET['channel1'])){
											$channel1Index = filter_var($_GET['channel1'],FILTER_SANITIZE_NUMBER_INT);
										}
										else{
											$channel1Index = $DEFAULT_CHANNEL1INDEX;
										}
										for($i=1;$i<$numberOfChannels;$i++){ # start from 1 to skip time
											echo "<option value=\"".$i."\""."";
											if ($channel1Index == $i){
												echo " selected";
											}
											echo ">".$i.": ".$names[$i]."</option>";
										}?>
								</select>
								<?php }
								if($plottingMode == "flowData"){
									echo "He";
								}?>
								
						</td>
						<td>
							and channel2 :
							<?php if($plottingMode == "environmentalData"){?>
								<select name="channel2">
									<?php
										if(isset($_GET['channel2'])){
											$channel2Index = $_GET['channel2'];
										}
										else{
											$channel2Index = $DEFAULT_CHANNEL2INDEX;
										}
										# and these are all of the other options
										for($i=0;$i<$numberOfChannels;$i++){ # start from 1 to skip time
											echo "<option value=\"".$i."\""."";
											if ($channel2Index == $i){
												echo " selected";
											}
											if($i!=0){
												echo ">".$i.": ".$names[$i]."</option>";
											}
											else{
												echo "></option>";
											}
										}
									?>
								</select>
								<?php }
								if($plottingMode == "flowData"){
									echo "N2";
								}?>
								
						</td>
						<td>
							<input type="submit" name="plot" value="plot"/>
							<input type="hidden" name="plotButtonClicked" value="1">
							<input type="hidden" name="plottingMode" value="<?php echo $plottingMode;?>">
						</td>
					</tr>
					<tr>
						<td> resolution
							<select name="resolution">
								<?php
									if(!empty($_GET['resolution'])){
										$resolution = $_GET['resolution'];
									}
									else{
										$resolution = $DEFAULT_RESOLUTION;
									}
								?>
								<option value="1" <?php if ($resolution == '1'){echo "selected";} ?>>1s</option>
								<option value="10" <?php if ($resolution == '10'){echo "selected";} ?>>10s</option>
								<option value="60" <?php if ($resolution == '60'){echo "selected";} ?>>1min</option>
								<option value="1800" <?php if ($resolution == '1800'){echo "selected";} ?>>30min</option>
							</select>
						</td>
						<td>
							<?php if($plottingMode == "environmentalData"){?>
								<label>
									<input type="hidden" name="uncalibrated1" value="0">
									<input type="checkbox" name="uncalibrated1" value="1" <?php if (!empty($_GET['uncalibrated1']) && $_GET['uncalibrated1'] == '1'){echo " checked=\"checked\"";} ?>> uncalibrated
								</label>
							<?php }?>
						</td>
						<td>
							<?php if($plottingMode == "environmentalData"){?>
								<label>
									<input type="hidden" name="uncalibrated2" value="0">
									<input type="checkbox" name="uncalibrated2" value="1" <?php if (!empty($_GET['uncalibrated2']) && $_GET['uncalibrated2'] == '1'){echo " checked=\"checked\"";} ?>> uncalibrated
								</label>
							<?php }?>
						</td>
						<td>
							<input type="hidden" name="autoRefresh" value="Off">
							<input type="checkbox" name="autoRefresh" value="10"  <?php if (!empty($_GET['autoRefresh']) && $_GET['autoRefresh'] != 'Off'){echo " checked=\"checked\"";} ?>>  auto refresh
						</td>
					</tr>
					<tr>
						<td>
						</td>
						<td>
							<label>
								<input type="hidden" name="manualScale1" value="0">
								<input type="checkbox" name="manualScale1" value="1" <?php if (!empty($_GET['manualScale1']) && $_GET['manualScale1'] == '1'){echo " checked=\"checked\"";} ?>> manual scale
							</label>
							<label>
								min <input type="text" name="min1" size="4" <?php if(isset($_GET['min1'])){echo "value='".$_GET['min1']."'";}?>>
								max <input type="text" name="max1" size="4" <?php if(isset($_GET['max1'])){echo "value='".$_GET['max1']."'";}?>>
							</label>
						</td>
						<td>
							<label>
								<input type="hidden" name="manualScale2" value="0">
								<input type="checkbox" name="manualScale2" value="1" <?php if (!empty($_GET['manualScale2']) && $_GET['manualScale2'] == '1'){echo " checked=\"checked\"";} ?>> manual scale
							</label>
							<label>
								min <input type="text" name="min2" size="4" <?php if(isset($_GET['min2'])){echo "value='".$_GET['min2']."'";}?>>
								max <input type="text" name="max2" size="4" <?php if(isset($_GET['max2'])){echo "value='".$_GET['max2']."'";}?>>
							</label>
						</td>
					</tr>		
				</table>
			</div>
			
		</form>
		<br><br>

		<?php 
			if(!empty($_GET["plotButtonClicked"])){ /* if submitted the form */
				# gather information from form and call plot.php
				$plottingMode = $_GET["plottingMode"];
				$timeDuration = $_GET["timeDuration"];
				$timeUnit = $_GET["timeUnit"];
				$resolution = $_GET["resolution"];
				if($plottingMode == "environmentalData"){
					$channel1 = $_GET["channel1"];
					$channel2 = $_GET["channel2"];
					$channel1Calibrated = 1-$_GET["uncalibrated1"];
					if($channel1Calibrated){
						$channel1Units = $units[$channel1Index];
					}
					else{
						$channel1Units = 'arbitrary units';
					}
					$channel2Calibrated = 1-$_GET["uncalibrated2"];
					if(!empty($channel2) && $channel2Calibrated){
						$channel2Units = $units[$channel2Index];
					}
					else{
						$channel2Units = 'arbitrary units';
					}
				}
				if(isset($_GET['manualScale1']) &&
				isset($_GET['min1']) && isset($_GET['max1']) && $_GET['min1']<$_GET['max1']){
					$manualScale1 = $_GET['manualScale1'];
					$min1 = $_GET['min1'];
					$max1 = $_GET['max1'];
				}
				else{
					$manualScale1=0;
					$min1 = '0';
					$max1 = '0';
				}
				if(!empty($_GET['manualScale2']) &&
				!empty($_GET['min2']) && !empty($_GET['max2']) && $_GET['min2']<$_GET['max2']){
					$manualScale2 = $_GET['manualScale2'];
					$min2 = $_GET['min2'];
					$max2 = $_GET['max2'];
				}
				else{
					$manualScale2=0;
					$min2 = '0';
					$max2 = '0';
				}
				if(!empty($_GET['autoRefresh'])){
					$autoRefresh = $_GET['autoRefresh'];
				}
				else{
					$autoRefresh = 'Off';
				}
				$width = 100; # in %
				$height = 40; # in %
				$chartId = '1';
				$overview = 'false';
				if($autoRefresh != 'Off'){
					echo "<p align='left'><br><br><h2>Result: (auto-refreshing)</h2>  <br><br><p>";
				}
				else{
					echo "<p align='left'><br><br><h2>Result:</h2><br><br><p>";
				}
				if($plottingMode == "environmentalData"){
					$plotResult = plotEnvironmentalData($timeDuration,$timeUnit,$channel1,$channel2,$resolution,$channel1Units,$channel2Units,
						$factors,$offsets,$names,$manualScale1,$min1,$max1,$manualScale2,$min2,$max2,$width,$height,$chartId,$overview);
				}
				if($plottingMode == "flowData"){
					$plotResult = plotFlowData($timeDuration,$timeUnit,$resolution,$manualScale1,$min1,$max1,$manualScale2,$min2,$max2,$width,$height,$chartId,$overview);
				}
					
				if($plotResult=='error'){
					echo "Can't plot. (Probably because no data was recorded in the last ".$timeDuration." ".$timeUnit.")<br>";
				}
				else{
					?>
					<div id="chart<?php echo $chartId;?>div" style="width:<?php echo $width;?>%; height:<?php echo $height;?>%;"></div>
				<?php }

			}
		?>
	<?php
	}
	
	?>