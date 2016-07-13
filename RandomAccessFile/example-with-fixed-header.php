<?php
	/***
		This example opens a file (random-with-fixed-header.dat) having the following contents :

			This is a fixed-length header!Record01Record02Record03

		The string "This is a fixed-length header!" is the header
		It contains 3 records, "Record01" through "Record03", of length 8.

		The script then displays :
		- The header contents
		- The number of records
		- Each individual record
	 ***/
	require ( "RandomAccessFile.phpclass" ) ;

	if  ( php_sapi_name ( )  !=  'cli' )
		echo "<pre>" ;

	$random_file	=  "random-with-fixed-header.dat" ;

	// Open the random access file of record size 8
	$rf = new RandomAccessFile ( $random_file, 8 ) ;

	// Set the header size 
	// Note that we could have specified this value as the 5th parameter of the constructor
	// but we did not want to specify values for the $cache_size and $filler parameter, since
	// we're simply opening the file in read-only mode
	$rf -> HeaderSize	=  30 ;		
					
	// Open the file in read-only mode
	$rf -> Open ( true ) ;

	// Display data
	echo "HEADER DATA  : [{$rf -> Header}]\n" ;
	echo "RECORD COUNT : " . count ( $rf ) . "\n" ;
	
	for  ( $i = 0 ; $i  <  count ( $rf ) ; $i ++ )
		echo "RECORD #" . ( $i + 1 ) . " : [{$rf [$i]}]\n" ;
