<?php
	/***
		This example script reads the example.ini file, updates some values, and writes back the
		results to example.out.ini.
		
		Comments and formatting are preserved ; by running the diff command on the input and output
		files, you should see the following :
		
		$ diff example.ini example.out.ini
		14c14,15
		< LastUpdate    =  2015/01/01 17:40:00
		---
		> LastUpdate    =  2015/10/01 14:16:07
		> Status = 0		
		
		The LastUpdate parameter was updated with the current date/time, and the Status parameter 
		was added.
		
	 ***/
	require ( 'IniFile.class.php' ) ;

	if  ( php_sapi_name ( )  !=  'cli' )
		echo "<pre>" ;

	// Instanciate an IniFile object for file example.ini
	$inifile 	=  IniFile::LoadFromFile ( 'example.ini' ) ;

	// Get the value of the Listen and Port parameters in the [Network] section
	// Note that you can specify a default value if the parameter is not defined
	$listen 	=  $inifile -> GetKey ( 'Network', 'Listen', '127.0.0.0' ) ;
	$port 		=  $inifile -> GetKey ( 'Network', 'Port' ) ;


	// ... do some processing 

	// Processing done : update the LastUpdate parameter of the [Results] section then
	// add the Status parameter
	$inifile -> SetKey ( 'Results', 'LastUpdate', date ( 'Y/m/d H:i:s' ) ) ;
	$inifile -> SetKey ( 'Results', 'Status', 0 ) ;

	// Write the results back ; the "example.out.ini" file is specified just to give you 
	// the possibility to compare the example.ini file contents before and after processing
	// You can simply write back "example.ini" by calling :
	//	$inifile -> Save ( ) ;
	// Note that in our case, you can specify either "true" or "false" for the $forced parameter
	// of the Save() method ; since we have called the SetKey() method to modify parameters, the
	// .ini file contents have been flagged as 'dirty' and will automatically be saved.
	$inifile -> Save ( true, 'example.out.ini' ) ;

	echo ( "example.ini file saved to example.out.ini, after changing the 'LastUpdate' and 'Status' settings of the [Results] section." ) ;
	echo ( "You can either edit this 'example.out.ini' file, or run the Unix diff command on both files to see the difference." ) ;
