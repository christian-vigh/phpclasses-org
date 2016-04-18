<?php

	/***
		This example script performs the following :
		1) Take the log file data/example.log, which contains well-formatted entries such as :
			2016-01-01 13:20:01 httptracking[11776] Processing buffered http requests...
			2016-01-01 13:20:01 httptracking[11776] 0 http requests processed
			2016-01-01 13:25:02 httptracking[11908] Processing buffered http requests...
			2016-01-01 13:25:02 httptracking[11908] 2 http requests processed
			2016-01-01 13:30:01 httptracking[12043] Processing buffered http requests...
			2016-01-01 13:30:01 httptracking[12043] 0 http requests processed
		   The various fields of this log file, which resembles Apache or ssh auth logs, are :
		   - A timestamp
		   - A process name ("httptracking")
		   - A process id, within square brackets
		   - A message
		   The variable-length parts of this table are :
		   - the process name
		   - the message part
		2.1) Create a first table, httptracking_1, which will hold the various parts of the log file 
		2.2) Create a second table, httptracking_2, where the "process" and "message" fields have
		     been replaced with an id in a string store table
		3) Compare the results in size and number of records
	 ***/
	require ( 'DbStringStore.php' ) ;

	// Customize here the access parameters to your local database
	define ( MYSQL_HOST		, 'localhost' ) ;
	define ( MYSQL_USER		, 'root' ) ;
	define ( MYSQL_PASSWORD		, '' ) ;
	define ( MYSQL_DATABASE		, 'phpclasses' ) ;
	define ( LOGFILE 		, 'data/example.log' ) ;

	// String store entry types - one for the process name, one for the message part
	define ( STRING_STORE_PROCESS	, 0 ) ;
	define ( STRING_STORE_MESSAGE	, 1 ) ;

	// Connect to your local database
	$dblink		=  mysqli_connect ( MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD ) ;

	// Uncomment this if you want to create a brand new database for running this test
	/***
	$query		=  "CREATE DATABASE " . MYSQL_DATABASE . " DEFAULT CHARSET latin1" ;
	mysqli_query ( $dblink, $query ) ;
	 ***/

	// Select our test database
	mysqli_select_db ( $dblink, MYSQL_DATABASE ) ;

	// Create the version with inline variable-length fields
	create_standard_version ( $dblink, LOGFILE ) ;

	// Create the version with a string store
	create_string_store_version ( $dblink, LOGFILE, 'httptracking_string_store' ) ;


	/********************************************************************************
	 * 
	 *  Helper functions.
	 * 
	 ********************************************************************************/

	// Create the version with variable-length data stored in the same table
	function  create_standard_version ( $dblink, $logfile )
	   {
		// Recreate the httptracking_1 table if it already exists
		mysqli_query ( $dblink, "DROP TABLE IF EXISTS httptracking_1" ) ;

		$query	=  "
				CREATE TABLE httptracking_1
				   (
						id 		BIGINT UNSIGNED 	NOT NULL AUTO_INCREMENT,
						timestamp 	DATETIME 		NOT NULL,
						process 	VARCHAR(32) 		NOT NULL DEFAULT '',
						process_id 	INT 			NOT NULL DEFAULT 0,
						message 	VARCHAR(1024) 		NOT NULL DEFAULT '',

						PRIMARY KEY 	( id ),
						KEY 		( timestamp )
				    ) ENGINE = MyISAM ;
			   " ;
		mysqli_query ( $dblink, $query ) ;

		// Read the logfile, split each record parts and insert a new row in the table
		$fp	=  fopen ( $logfile, "r" ) ;

		while  ( ( $line = fgets ( $fp ) )  !==  false )
		   {
			list ( $timestamp, $process, $pid, $message )	=  get_log_parts ( $line ) ;
			$process	=  mysqli_escape_string ( $dblink, $process ) ;
			$message	=  mysqli_escape_string ( $dblink, $message ) ;
			$query		=  "
						INSERT INTO httptracking_1 
						SET
							timestamp	=  '$timestamp',
							process		=  '$process',
							process_id	=  $pid,
							message		=  '$message'
					   " ;
			mysqli_query ( $dblink, $query ) ;
		    }

		fclose ( $fp ) ;
	    }



	// Create the version with variable-length data stored in the same table
	function  create_string_store_version ( $dblink, $logfile, $store_name )
	   {
		// Recreate the httptracking_2 table if it already exists
		mysqli_query ( $dblink, "DROP TABLE IF EXISTS httptracking_2" ) ;

		$query	=  "
				CREATE TABLE httptracking_2
				   (
						id 		BIGINT UNSIGNED 	NOT NULL AUTO_INCREMENT,
						timestamp 	DATETIME 		NOT NULL,
						process_ssid 	BIGINT UNSIGNED		NOT NULL DEFAULT 0,
						process_id 	INT 			NOT NULL DEFAULT 0,
						message_ssid	BIGINT UNSIGNED		NOT NULL DEFAULT 0,

						PRIMARY KEY 	( id ),
						KEY 		( timestamp )
				    ) ENGINE = MyISAM ;
			   " ;
		mysqli_query ( $dblink, $query ) ;

		// Create the string store (or instanciate it if it already exists)
		// Keep the default size of 1024 characters and don't index the string value part
		$store		=  new DbStringStore ( $dblink, $store_name ) ;

		// Read the logfile, split each record parts and insert a new row in the table
		$fp	=  fopen ( $logfile, "r" ) ;

		while  ( ( $line = fgets ( $fp ) )  !==  false )
		   {
			list ( $timestamp, $process, $pid, $message )	=  get_log_parts ( $line ) ;
			$process_id	=  $store -> Insert ( STRING_STORE_PROCESS, $process ) ;
			$message_id	=  $store -> Insert ( STRING_STORE_MESSAGE, $message ) ;
			$query		=  "
						INSERT INTO httptracking_2
						SET
							timestamp	=  '$timestamp',
							process_ssid	=  $process_id,
							process_id	=  $pid,
							message_ssid	=  $message_id
					   " ;
			mysqli_query ( $dblink, $query ) ;
		    }

		fclose ( $fp ) ;
	    }

	// Get parts from one log entry, ie : timestamp, process name, process id (within square brackets) and message
	function  get_log_parts ( $line ) 
	   {
		$line		=  trim ( $line ) ;
		$timestamp	=  substr ( $line, 0, 19 ) ;
		$remainder	=  substr ( $line, 20 ) ;
		$re		=  '/
					(?P<process> [^\[]+)
					\[
					(?P<pid> [^\]]+)
					\]
					\s*
					(?P<message> .*)
				    /imsx' ;
		preg_match ( $re, $remainder, $match ) ;

		return ( [ $timestamp, $match [ 'process' ], $match [ 'pid' ], $match [ 'message' ] ] ) ;
	    }