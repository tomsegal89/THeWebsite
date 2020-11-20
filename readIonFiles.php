<?php
	function readIonFiles($filePath,$fileName,$fileType){	
	
		include('constants.php');
		$downSamplingSweeps = $DOWN_SAMPLING_SWEEPS;
		$downSamplingCalibration = $DOWN_SAMPLING_CALIBRATION;
		// read the dat file contents
		$file = fopen($filePath,"r");
		$data = fgetcsv($file,1000," ");
		$linesRead = 1;
		if($fileType == "Sweep_Magnetron" || $fileType == "Sweep_Cyclotron"){
			$downSampling = $downSamplingSweeps;
		}
		if($fileType == "Miscellaneous"){
			$downSampling = $downSamplingCalibration;
		}
		$xSum = 0;
		$ySum = 0;
		while($data!= false){
			$xSum = $xSum + $data[0];
			$ySum = $ySum + $data[1];
			$data = fgetcsv($file,1000," ");
			$linesRead = $linesRead + 1;
			if($linesRead % $downSampling == 0){
				$x[] = $xSum/$downSampling;
				$y[] = $ySum/$downSampling;
				$xSum = 0;
				$ySum = 0;
			}
		}
		fclose($file);
			
		// read the accompanying THEs file and store the relevant information
		$THesFilePath = substr($filePath,0,-4).".THes";
		$THesFile = fopen($THesFilePath,'r');
		$line = fgets($THesFile);
		$version = explode(" ",fgets($THesFile))[1];
		while(explode("	",$line)[0]!="Ion_Loaded"){
			$line = fgets($THesFile);
		}
		$ionType = explode("	",$line)[1];
		
		while(explode("	",$line)[0]!="Freq_Mag_Hz"){
			$line = fgets($THesFile);
		}
		$freqMag = explode("	",$line)[1];
		$line = fgets($THesFile);
		$line = fgets($THesFile);
		$freqCyc = explode("	",$line)[1];
		if($fileType == "Sweep_Cyclotron"){
			$sweepCenter = $freqCyc;
		}
		else{
			$sweepCenter = $freqMag;
		}
		$line = fgets($THesFile);
		$line = fgets($THesFile);
		$pulseAmp = explode("	",fgets($THesFile))[1];
		$pulseDur = explode("	",fgets($THesFile))[1];
		while(explode("	",$line)[0]!="Sweep_Amp_mV"){
			$line = fgets($THesFile);
		}
		$sweepAmp = explode("	",$line)[1];
		$sweepRange = explode("	",fgets($THesFile))[1];
		$sweepTime = explode("	",fgets($THesFile))[1];
		$sweepDirection = explode("	",fgets($THesFile))[1][1]; # the [0] is to get the first char as in T or F. edited 11.2 the second [1] from [0] to [1].
		if($sweepDirection === "T"){
			$sweepDirection = "up";
		}
		else{
			$sweepDirection = "down";
		}
		while(explode("	",$line)[0]!="Attenuator_CH3_dB"){
			$line = fgets($THesFile);
		}
		$sweepAtt = explode("	",$line)[1];
		
		while(explode("	",$line)[0]!="NS_Ring_Complete_Voltage"){
			$line = fgets($THesFile);
		}
		$ringVoltage = substr($line,25);
		while(explode("	",$line)[0]!="NS_Correction_Complete_Voltage"){
			$line = fgets($THesFile);
		}
		$ringCorrectionVoltage = substr($line,32);
		
		while(explode(" 	 ",$line)[0]!="ScriptVariable_driveAmplitude_mV"){
			$line = fgets($THesFile);
		}
		$driveAmplitude_mV = substr($line,35);
		$line = fgets($THesFile);
		$modulationAmplitude_mV = substr($line,40);
		
		fclose($THesFile);
		
		return array($x,$y,$ionType,$pulseAmp,$pulseDur,$sweepAmp,$sweepCenter,$sweepRange,$sweepTime,$sweepDirection,$sweepAtt,$ringVoltage,$ringCorrectionVoltage,$driveAmplitude_mV,$modulationAmplitude_mV);
	}
?>

