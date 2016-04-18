<?php

	/***
		To understand what happens here, I strongly suggest to consult the README.md file in this package !

		This example script performs the following :
		- Create one table, buffering_test, that we will be inserting/updating and loading data into
		- Time the insertion of MAX_ROWS rows using individual insert statements
		- Time the insertion of MAX_ROWS rows using a buffered insert object with a buffer size of MAX_INSERTS statements
		- Time the update of the rows created at the preceding step with individual UPDATE statements
		- Time the update of the rows created at the preceding step with a buffer size of MAX_UPDATES statements
		- Time the insertion of MAX_ROWS rows using a buffered load data object of MAX_INSERT rows

		Notes : 
		- your database user MUST have the FILE privilege in order to use LOAD DATA INFILE statements
		- since the queries built by the BufferedInsert and BufferedUpdate classes may be very large, depending on
		  the number of queries you wanted to buffer, you may have to increase the max_allowed_packet parameter in
		  your my.cnf (unix) or my.ini (windows) file.
	 ***/
	require ( 'DbBufferedInsert.php' ) ;
	require ( 'DbBufferedUpdate.php' ) ;
	require ( 'DbBufferedLoadFile.php' ) ;

	// Customize here the access parameters to your local database
	define ( MYSQL_HOST		, 'localhost' ) ;
	define ( MYSQL_USER		, 'root' ) ;
	define ( MYSQL_PASSWORD		, '' ) ;
	define ( MYSQL_DATABASE		, 'phpclasses' ) ;
	define ( LOGFILE 		, 'data/example.log' ) ;

	// String store entry types - one for the process name, one for the message part
	define ( STRING_STORE_PROCESS	, 0 ) ;
	define ( STRING_STORE_MESSAGE	, 1 ) ;

	// Constants related to the size of our benchmark
	define ( MAX_ROWS		, 50000 ) ;
	define ( MAX_INSERTS		, 8192 ) ;
	define ( MAX_UPDATES		, 8192 ) ;
	define ( MAX_LOAD_ROWS		, 50000 ) ;

	// Connect to your local database
	$dblink		=  mysqli_connect ( MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD ) ;
	$test_table	=  "buffering_test" ;

	// Uncomment this if you want to create a brand new database for running this test
	/***
	$query		=  "CREATE DATABASE " . MYSQL_DATABASE . " DEFAULT CHARSET latin1" ;
	mysqli_query ( $dblink, $query ) ;
	 ***/

	// Select our test database
	mysqli_select_db ( $dblink, MYSQL_DATABASE ) ;

	// Create the test table
	$query		=  "
				CREATE TABLE IF NOT EXISTS $test_table
				   (
					id		INT		NOT NULL AUTO_INCREMENT,
					date		DATETIME	NOT NULL DEFAULT '0000-00-00 00:00:00',
					intvalue	INT		NOT NULL DEFAULT 0,
					randvalue	INT		NOT NULL DEFAULT 0,
					strvalue1	CHAR(32)	NOT NULL DEFAULT '',
					strvalue2	VARCHAR(4096)	NOT NULL DEFAULT '',
					strvalue3	LONGTEXT	NOT NULL,

					PRIMARY KEY	( id ) 
				    ) ENGINE = MyISAM 
			   " ;
	mysqli_query ( $dblink, $query ) ;

	// Time insertion in seconds.milliseconds of MAX_ROWS rows using individual INSERT statements
	echo ( "Benchmarking buffered/unbuffered operations on " . MAX_ROWS . " rows :\n" ) ;

	time_function ( 'IndividualInserts', 
				'Using individual INSERT statements',
				$dblink, $test_table, MAX_ROWS ) ;

	time_function ( 'BufferedInserts', 
				'Using buffered INSERT statements (size = ' . MAX_INSERTS . ')',
				$dblink, $test_table, MAX_ROWS, MAX_INSERTS ) ;

	time_function ( 'IndividualUpdates', 
				'Using individual UPDATE statements',
				$dblink, $test_table, MAX_ROWS ) ;

	time_function ( 'BufferedUpdates', 
				'Using buffered UPDATE statements (size = ' . MAX_UPDATES . ')',
				$dblink, $test_table, MAX_ROWS, MAX_UPDATES ) ;

	time_function ( 'BufferedLoads', 
				'Using buffered LOAD DATA INFILE statements (size = ' . MAX_LOAD_ROWS . ')',
				$dblink, $test_table, MAX_ROWS, MAX_LOAD_ROWS ) ;

	/*** END OF SCRIPT - the rest of this file contains the benchmarking functions ***/

	// time_function -
	//	Times the execution of the specified function and display the results.
	function  time_function ( $funcname, $text, $dblink, $test_table, $max_rows, $buffer_size = null )
	   {
		echo ( "\t" . str_pad ( $text, 60 ) . ' : ' ) ;
		flush ( ) ;

		$timer_start		=  microtime ( true ) ;
		$funcname ( $dblink, $test_table, $max_rows, $buffer_size ) ;
		$timer_stop		=  microtime ( true ) ;
		$delta			=  round ( $timer_stop - $timer_start, 3 ) ;

		mysqli_query ( $dblink, "OPTIMIZE TABLE $test_table" ) ;
		mysqli_query ( $dblink, "FLUSH TABLES" ) ;

		echo ( $delta . "\n" ) ;
	    }


	// IndividualInserts -
	//	Insert $row_count rows into the specified table using individual INSERT statements.
	function  IndividualInserts ( $dblink, $table_name, $row_count )
	   {
		mysqli_query ( $dblink, "TRUNCATE TABLE $table_name" ) ;		// Make sure we start from a clean state

		for  ( $i = 1 ; $i <= $row_count ; $i ++ )
		   {
			$strvalue	=  sha1 ( microtime ( false ) ) ;		// Well, we have to fill columns with some data...
			$intvalue	=  mt_rand ( ) ;
			$query		=  "
						INSERT INTO $table_name
						SET
							randvalue	=  $intvalue,
							date		=  NOW(),
							intvalue	=  $i,
							strvalue1	=  '$strvalue',
							strvalue2	=  '$strvalue',
							strvalue3	=  '$strvalue'
					   " ;
			mysqli_query ( $dblink, $query ) ;
		    }
	    }

	// BufferedInserts -
	//	Insert $row_count rows into the specified table using buffered INSERT statements.
	function  BufferedInserts ( $dblink, $table_name, $row_count, $buffer_size )
	   {
		mysqli_query ( $dblink, "TRUNCATE TABLE $table_name" ) ;		// Make sure we start from a clean state
		$buffer		=  new DbBufferedInsert ( $table_name, [ 'date', 'intvalue', 'randvalue', 'strvalue1', 'strvalue2', 'strvalue3' ], $buffer_size, $dblink ) ;

		for  ( $i = 1 ; $i <= $row_count ; $i ++ )
		   {
			$strvalue	=  sha1 ( microtime ( true ) ) ;		// Well, we have to fill columns with some data...
			$intvalue	=  mt_rand ( ) ;
			$buffer -> Add 
			   ([ 
				'columns' =>
				   [
					'randvalue'	=> $intvalue, 
					'intvalue'	=> $i,
					'strvalue1'	=> $strvalue, 
					'strvalue2'	=> $strvalue, 
					'strvalue3'	=> $strvalue 
				    ],
				'computed-columns' =>
				   [
					'date'		=> 'NOW()', 
				    ]
			     ]) ;
		    }

		$buffer -> Flush ( ) ;
	    }

	// IndividualUpdates -
	//	Udpates $row_count rows into the specified table using individual UPDATE statements.
	//	The update consists of adding +1 to the intvalue column and an extra character to each string column.
	//	The id field is used for identifying the row.
	function  IndividualUpdates ( $dblink, $table_name, $row_count )
	   {
		for  ( $i = 1 ; $i <= $row_count ; $i ++ )
		   {
			$query		=  "
						UPDATE $table_name
						SET
							randvalue	=  randvalue + 1,
							strvalue1	=  'A$i',
							strvalue2	=  'B$i',
							strvalue3	=  'C$i'
						WHERE
							id = $i 
					   " ;
			mysqli_query ( $dblink, $query ) ;
		    }
	    }

	// BufferedUpdates -
	//	Updates $row_count rows into the specified table using buffered UPDATE statements.
	function  BufferedUpdates ( $dblink, $table_name, $row_count, $buffer_size )
	   {
		$buffer		=  new DbBufferedUpdate ( $table_name, [ 'id' ], [ 'intvalue', 'date', 'randvalue', 'strvalue1', 'strvalue2', 'strvalue3' ], $buffer_size, $dblink ) ;

		for  ( $i = 1 ; $i  <= $row_count ; $i ++ )
		   {
			$buffer -> Add
			   ([
				'keys'		=>  [ 'id' => $i ],
				'columns'	=>
				   [ 
					'intvalue'	=> $i,
					'randvalue'	=> $i + 10000000, 
					'strvalue1'	=> 'XXA' . $i, 
					'strvalue2'	=> 'ZZB' . $i, 
					'strvalue3'	=> 'ZZC' . $i 
				    ],
				'computed-columns' =>
				   [
					'date'		=> 'NOW()', 
				    ]
			     ]) ;
		    }

		$buffer -> Flush ( ) ;
	    }

	// BufferedLoads -
	//	Insert $row_count rows into the specified table using buffered LOAD DATA INFILE statements.
	function  BufferedLoads ( $dblink, $table_name, $row_count, $buffer_size )
	   {
		mysqli_query ( $dblink, "TRUNCATE TABLE $table_name" ) ;		// Make sure we start from a clean state
		$buffer		=  new DbBufferedLoadFile ( $table_name, [ 'intvalue', 'randvalue', 'strvalue1', 'strvalue2', 'strvalue3' ], $buffer_size, $dblink ) ;

		for  ( $i = 1 ; $i  <=  $row_count ; $i ++ )
		   {
			$strvalue	=  sha1 ( microtime ( true ) ) ;		// Well, we have to fill columns with some data...
			$intvalue	=  mt_rand ( ) ;
			$buffer -> Add 
			   ([ 
				'columns' =>
				   [
					'randvalue'	=> $intvalue, 
					'intvalue'	=> $i,
					'strvalue1'	=> $strvalue, 
					'strvalue2'	=> $strvalue, 
					'strvalue3'	=> $strvalue 
				    ]
			     ]) ;
		    }

		$buffer -> Flush ( ) ;
	    }
