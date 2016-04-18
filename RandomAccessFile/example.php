<?php
	require ( "RandomAccessFile.phpclass" ) ;

	$random_file	=  "random.dat" ;

	// Initialize a file containing the numbers 0 through 100, written with 3 digits 
	// and terminated by a newline (each record will occupy 4 bytes)
	// This file will be used as our example case for testing random file access.
	$fp	=  fopen ( $random_file, "w" ) ;

	for  ( $i = 1 ; $i  <  100 ; $i ++ )
		fwrite ( $fp, sprintf ( "%03d", $i ) . "\n" ) ;

	fwrite ( $fp, "100" ) ;		// Note that the last record will be incomplete (no terminating newline)
	fclose ( $fp ) ;

	// Instantiate a random access file and open it in read/write mode.
	// "4" is the record size, "1024" the number of records to be cached, and "\n" the filler character to be
	// used when inserting empty records 
	$rf = new RandomAccessFile ( $random_file, 4, 1024, "\n" ) ;
	$rf -> Open ( ) ;

	// Show the number of records that this file holds (should be 100)
	echo ( "Count = " . count ( $rf ) . "\n" ) ;

	// Swap 10 records (3d parameter) from record #0 with record #10
	// The file should now have the following values (one per line) :
	// Records  0 to  9 : 011..020
	// Records 10 to 19 : 001..010
	// Records 20 to 99 : 021..099
	$rf -> Swap ( 0, 10, 10 ) ;

	// Now copy 20 records from record #0 to record #100 (which is past the end of file)
	// The new contents should have 20 more records, with values in the range 011..020 and 001..010
	$rf -> Copy ( 0, 100, 20 ) ;

	// Note that you can use the for() and foreach() constructs to loop through each record
	foreach  ( $rf  as  $entry )
		echo ("[" . trim ( $entry ) . "]\n") ;

	// There is also a small (and dumb) cache that store a few statistics
	echo ( "Hits : {$rf -> CacheHits}, misses = {$rf -> CacheMisses}\n" ) ;
