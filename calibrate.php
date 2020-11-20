<?php
	
	function calibrate($data,$calibrationType,$factors,$offsets,$channelIndex,$resolution){
		include 'constants.php';
		$calibratedData = [];
		switch($calibrationType){
			case 'linear':
				for($i=0;$i<sizeof($data);$i++){
					$calibratedData[$i] = $data[$i]*$factors[$channelIndex]+$offsets[$channelIndex];
				}
				return array($calibratedData,0,sizeof($data));
			break;	
			case 'logarithmic':
				for($i=0;$i<sizeof($data);$i++){
					$calibratedData[$i] = $factors[$channelIndex]*pow(10,$data[$i]);
				}
				return array($calibratedData,0,sizeof($data));
			break;						
			case 'gas counter':
				# initialize the array which will be used for output
				for($i=0;$i<sizeof($data);$i++){
					$calibratedData[$i] = 0;
				}
				# record the times (indices) where the gas counter recorded a cubic foot
				$jumpTimes = [];
				$j = 0;
				for($i=1;$i<sizeof($data);$i++){
					if(($data[$i]-$data[$i-1])>$GAS_COUNTER_THRESHOLD){ # only detecting up-jumps
						$jumpTimes[$j] = $i;
						$j++;
					}
				}
				# if at least 2 jumps were found, calculate the average flow values for the different times.
				# 	the values are determined by the time (in minutes) between every 2 jumps.
				#	note that 0 is displayed for both the area before the first jump and the area after the last jump.
				if(sizeof($jumpTimes)>1){
					for($i=0;$i<sizeof($jumpTimes)-1;$i++){
						$jump1 = $jumpTimes[$i];
						$jump2 = $jumpTimes[$i+1];
						# amount of points between the two jumps = ($jump2-$jump1)
						# time that passed between the jumps in seconds = ($jump2-$jump1)*$MEASUREMENT_PERIOD_s*$resolution
						$elapsedTimeBetweenJumps = ($jump2-$jump1)*$MEASUREMENT_PERIOD_s*$resolution/60; # in minutes
						$gasPerMinute = $GAS_COUNTER_CALIBRATION_FACTOR/$elapsedTimeBetweenJumps;
						for($j=$jump1;$j<$jump2;$j++){
							$calibratedData[$j] = $gasPerMinute;
						}
					}
					#print_r($jumpTimes);
					# we return not only the calibrated data but also the first and last jump, because we'd like the plotter to crop-out the
					# 	data from before the first and from after the last jumps, because the values there are undefined.
					return array($calibratedData,$jumpTimes[0],$jumpTimes[sizeof($jumpTimes)-1]);
				}
				else{
					return array($calibratedData,0,sizeof($data)-1);
				}
			break;
				
		}
	}
	?>