<?php
	/***
		This example opens a file (random-with-variable-header.dat) having the following contents :

			"!This is a variable-length headerRecord01Record02Record03"

		In this case, the header is a variable-size header (ie, its length may depend on your file structure).
		It has been decided in this example that the size of the header would be given by the first byte, an
		exclamation point, which is ascii value 33. 
		Hence the length of the header (including the first byte) is 33 and contains :

			"!This is a variable-length header"

		A callback function is specified in place of the $header_size parameter of the constructor, to allow the
		class to retrieve the correct length of the header.

		The file contains 3 records, "Record01" through "Record03", of length 8.

		The script then displays :
		- The header contents
		- The number of records
		- Each individual record

	 ***/
	require ( "RandomAccessFile.phpclass" ) ;

	if  ( php_sapi_name ( )  !=  'cli' )
		echo "<pre>" ;

	$random_file	=  "random-with-variable-header.dat" ;

	// Open the random access file of record size 8, specifying a closure function instead of an integer header size.
	// The closure function will read the first byte, which contains the actuel length of the header. This function
	// is called whenever the Open() method of the RandomAccessFile class is called.
	$rf = new RandomAccessFile 
	   ( 
		$random_file, 
		8,
		function  ( $fd )
		   {
			$ch	=  fread ( $fd, 1 ) ;	// Read the first byte of our sample file, which contains the header size.

			return ( ord ( $ch ) ) ;	// Return the size in bytes
		    }
	    ) ;

	// Open the file in read-only mode
	$rf -> Open ( true ) ;

	// Display data
	echo "HEADER DATA  : [{$rf -> Header}]\n" ;
	echo "HEADER SIZE  : {$rf -> HeaderSize}\n" ;
	echo "RECORD COUNT : " . count ( $rf ) . "\n" ;
	
	for  ( $i = 0 ; $i  <  count ( $rf ) ; $i ++ )
		echo "RECORD #" . ( $i + 1 ) . " : [{$rf [$i]}]\n" ;
