<?php
	/* define constants */	
	$MEASUREMENT_PERIOD_s = '1'; # the reciprocal of the sampling rate. (in s) (must be updated in PyEnvDAQ as well)
	$DEFAULT_TIME_DURATION = '4';
	$DEFAULT_TIME_UNIT = 'hours';
	$DEFAULT_CHANNEL1INDEX = '1'; # p_Bore
	$DEFAULT_CHANNEL2INDEX = '2'; # p_Reservoir
	$DEFAULT_RESOLUTION = 10; # plot a point every 10s
	$DEFAULT_PLOTTING_MODE = 'environmentalData'; # there are only 2 options, the other is 'flowData'
	$GAS_COUNTER_THRESHOLD = 1; # jump size for both flow meters (He and N2), in Volts
	$GAS_COUNTER_CALIBRATION_FACTOR = 28.3168; # how much N2 and/or He was detected in-between two rising edges, in litres
	$GAS_COUNTER_AVERAGING_PERIOD = 3600; # we average over an hour
	
	$DOWN_SAMPLING_SWEEPS = 1000; // only plot every nth (for instance every 10th if set to 10) point
	$DOWN_SAMPLING_CALIBRATION = 1000; // only plot every nth (for instance every 10th if set to 10) point
	
	$GLOBALS['timeCorrection'] = 0; #2*3600; # This might need to be changed when the clock is shifted.
	
	$GLOBALS['directoryPath'] = '/nfs/d64/blm/tritium'; # for the online version
	#$GLOBALS['directoryPath'] = 'Z:'; # for the offline version
	
	# for both the online and the offline versions
	$GLOBALS['ionDataFilesDirectoryPath'] = $GLOBALS['directoryPath'].'/Data/2018';
	$GLOBALS['envFilesDirectoryPath'] = $GLOBALS['directoryPath'].'/PyEnvDAQ/data/';
	$GLOBALS['HeFlowTimeStampsFilePath'] = $GLOBALS['directoryPath'].'/PyEnvDAQ/HeFlowTimeStamps.txt';
	$GLOBALS['N2FlowTimeStampsFilePath'] = $GLOBALS['directoryPath'].'/PyEnvDAQ/N2FlowTimeStamps.txt';
	
	# old versions:
	#$GLOBALS['envFilesDirectoryPath'] = '/nfs/d64/blm/tritium/PyEnvDAQ/data'; # for the online version
	# $GLOBALS['envFilesDirectoryPath'] = 'Z:/PyEnvDAQ\\data'; # for the offline version
	
	$GLOBALS['$NUMBER_OF_CHANNELS'] = 26;
	$GLOBALS['$LINES_TO_SKIP'] = 7 + $GLOBALS['$NUMBER_OF_CHANNELS'];
?>