<!- ********* readPyenvFile.php ********* ->
<?php
	# get the newest file in the folder
	$fileNames = scandir($GLOBALS['envFilesDirectoryPath']); # get the list of all files in the folder
	$filePath = $GLOBALS['envFilesDirectoryPath'].'/'.$fileNames[sizeof($fileNames)-1]; # get the latest pyEnv file
	$file = fopen($filePath,'r');
	# get the number of channels from the header
	$line = fgets($file); # for example: "Saving 20 NI channels and 4 MCS channels and 1 MKSFlow channel of data at 1 Sa/s since 05-11-2018 00:00:00."
	$line = substr($line,7); # for example: "20 NI channels and 4 MCS channels and 1 MKSFlow channel of data at 1 Sa/s since 05-11-2018 00:00:00."
	$numberOfNIChannels = (int) strpbrk($line, "0123456789"); # for example: 20
	$line = substr($line,19); # for example: "4 MCS channels and 1 MKSFlow channel of data at 1 Sa/s since 05-11-2018 00:00:00."
	$numberOfMCSChannels = (int) strpbrk($line, "0123456789"); # for example: 45
	$line = substr($line,19); # for example: "1 MKSFlow channel of data at 1 Sa/s since 05-11-2018 00:00:00."
	$numberOfEATChannels = (int) strpbrk($line, "0123456789"); # for example: 1
	$numberOfChannels = 1 + $numberOfNIChannels + $numberOfMCSChannels + $numberOfEATChannels; # + 1 for the time channel
	$line = fgets($file); # skip the empty line
	$line = fgets($file); # skip the NI card-related header line
	# read the information about the channels
	$linesRead = 0;
	# read the information about the channels
	while (($line = fgetcsv($file,0,"\t")) !== FALSE && $linesRead<$numberOfChannels) {
		if (strpos(implode($line),'MCS Box')=='True' || strpos(implode($line),'Ethernet')=='True'){ # skip this line
			$line = fgetcsv($file,0,"\t");
		}
		list($names[], $units[], $factors[], $offsets[], $min[], $max[], $WarningLow[], $warningHigh[], $numbers[]) = $line;
		$linesRead = $linesRead +1;
	}
	fclose($file);
	
?>

