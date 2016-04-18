<?php
	// Modify your own mysql connection parameters here
	$mysql_host	=  'localhost' ;
	$mysql_user	=  'root' ;
	$mysql_password =  '' ;
	$mysql_database =  'test' ;

	// Include the variable store class
	include ( 'DbVariableStore.class.php' ) ;

	// Helper functions
	function  toboolean ( $value )
	   { return ( ( $value ) ?  '   yes' : '    no' ) ; } ;

	function  toverdict ( $value )
	   { return ( ( $value ) ?  '    ok' : 'failed' ) ; } ;

	function  formatted_printr ( $value )
	   {
		if  ( is_array ( $value ) ) 
			return ( str_replace ( [ "\r", "\n" ], [ '', "\n\t\t" ], print_r ( $value, true ) ) ) ;
		else
			return ( $value ) ;
	    }

	// Variables specific for running the tests either in Apache or CLI mode
	if  ( php_sapi_name ( )  == 'cli' )
	   {
		$nl	=  "\n" ;
		$tab	=  "\t" ;
	    }
	else
	   {
		$nl	=  "<br/>" ;
		$tab	=  "&nbsp;&nbsp;&nbsp;&nbsp;" ;
	    }



	// Step 1 : Create a variable store named 'testvariables'
	echo ( "****** Step 1 : Create the variable store :$nl" ) ;

	$connection	=  mysqli_connect ( $mysql_host, $mysql_user, $mysql_password, $mysql_database ) ;
	mysqli_query ( $connection, "DROP TABLE IF EXISTS testvariables" ) ;

	$store = new DbVariableStore ( 'testvariables', $connection ) ;
	echo ( $tab . toverdict ( $store ) ) ;
	echo ( $nl ) ;

	// Step 2 : Test variable definition/retrieval,with all the possible conversion cases.
	// Loop through the following definitions array to :
	// 2.1 - Create a variable (given by the 'name' element)  with a value of 'value' and a type of 'type'
	//	 The result should always be 'ok', except if the variable already exists, which should not be the 
	//	 case in this run
	// 2.2 - Check if the variable is defined (true of false). The result should always be 'ok'.
	// 2.3 - Retrieve its value and compare it with the 'expected' value. For example, an initial value of
	//	 " 3.14159" for a TYPE_INTEGER variable will be stored as "3".
	//	 The result should always be 'ok'.
	// 2.4 - Undefine it and display the status. The result should always be 'ok'.
	// 2.5 - Check if it still defined. The result should always be 'no', since the variable has been deleted.
	$test_definitions	= 
	   [
		   [
			'name'		=>  'string.null',
			'value'		=>  null,
			'type'		=>  DbVariableStore::TYPE_STRING,
			'expected'	=>  ''
		    ],
		   [
			'name'		=>  'string.value',
			'value'		=>  'Hello World',
			'type'		=>  DbVariableStore::TYPE_STRING,
			'expected'	=>  'Hello World'
		    ],
		   [
			'name'		=>  'integer.value',
			'value'		=>  1,
			'type'		=>  DbVariableStore::TYPE_INTEGER,
			'expected'	=>  1
		    ],
		   [
			'name'		=>  'integer.strvalue',
			'value'		=>  " 1",
			'type'		=>  DbVariableStore::TYPE_INTEGER,
			'expected'	=>  1
		    ],
		   [
			'name'		=>  'integer.dblvalue',
			'value'		=>  " 3.14159",
			'type'		=>  DbVariableStore::TYPE_INTEGER,
			'expected'	=>  3
		    ],
		   [
			'name'		=>  'integer.truevalue',
			'value'		=>  true,
			'type'		=>  DbVariableStore::TYPE_INTEGER,
			'expected'	=>  1
		    ],
		   [
			'name'		=>  'integer.falsevalue',
			'value'		=>  false,
			'type'		=>  DbVariableStore::TYPE_INTEGER,
			'expected'	=>  0
		    ],
		   [
			'name'		=>  'dblvalue',
			'value'		=>  " 3.14159",
			'type'		=>  DbVariableStore::TYPE_DOUBLE,
			'expected'	=>  3.14159
		    ],
		   [
			'name'		=>  'boolean.truevalue',
			'value'		=>  "1",
			'type'		=>  DbVariableStore::TYPE_BOOLEAN,
			'expected'	=>  true
		    ],
		   [
			'name'		=>  'boolean.falsevalue',
			'value'		=>  "0",
			'type'		=>  DbVariableStore::TYPE_BOOLEAN,
			'expected'	=>  false
		    ],
		   [
			'name'		=>  'datetime.1',
			'value'		=>  "2014/01/01 13:40:00",
			'type'		=>  DbVariableStore::TYPE_DATETIME,
			'expected'	=>  "2014/01/01 13:40:00"
		    ],
		   [
			'name'		=>  'datetime.2',
			'value'		=>  "now",
			'type'		=>  DbVariableStore::TYPE_DATETIME,
			'expected'	=>  strtotime ( "now" )			// Failure is ok here when checking the expected string
		    ],
		   [							
			'name'		=>  'datetime.3',
			'value'		=>  0,
			'type'		=>  DbVariableStore::TYPE_DATETIME,
			'expected'	=>  "1970/01/01 00:00:00"		// Failure is ok here if different GMT offset
		    ],
		   [
			'name'		=>  'date.1',
			'value'		=>  "2014/01/01",
			'type'		=>  DbVariableStore::TYPE_DATE,
			'expected'	=>  "2014/01/01"
		    ],
		   [
			'name'		=>  'time.1',
			'value'		=>  "17:40:17",
			'type'		=>  DbVariableStore::TYPE_TIME,
			'expected'	=>  "17:40:17"
		    ],
		   [
			'name'		=>  'timestamp',
			'value'		=>  "1970/01/01 00:00:00",
			'type'		=>  DbVariableStore::TYPE_TIMESTAMP,
			'expected'	=>  0					// Comparison will fail since the original timestamp is a date string
		    ],
		   [
			'name'		=>  'array',				// This test will generate a notice : "array to string conversion"
			'value'		=>  [ 1, 2 ],				// when displaying the results
			'type'		=>  DbVariableStore::TYPE_SERIALIZED,
			'expected'	=>  [ 1, 2 ]
		    ],
	    ] ;

	echo ( "****** Step 2 : Variable create/check/retrieve/undefine/check :$nl" ) ;
	echo ( "$tab" . sprintf ( "%-20s", 'Variable' ) . "  Created?  IsDefined?  IsExpected?  Deleted?  StillDefined?$nl" ) ;
	echo ( "$tab--------------------------------------------------------------------------------$nl" ) ;

	foreach  ( $test_definitions  as  $def )
	   {
		$creation_status	=  $store -> Define ( $def [ 'name' ], $def [ 'value' ], $def [ 'type' ] ) ;
		$is_defined		=  $store -> IsDefined ( $def [ 'name' ] ) ;
		$value			=  $store -> ValueOf ( $def [ 'name' ] ) ;
		$is_expected		=  ( $value  ==  $def [ 'expected' ] ) ;
		$deletion_status	=  $store -> Undefine ( $def [ 'name' ] ) ;
		$is_still_defined	=  $store -> IsDefined ( $def [ 'name' ] ) ;

		echo ( "$tab" . sprintf ( "%-20s", $def [ 'name' ] ) . "  " .
				"  " . toverdict ( $creation_status ) . "  " .
				"    " . toboolean ( $is_defined ) . "  " .
				"     " . toverdict ( $is_expected ) . "  " .
				"  " . toverdict ( $deletion_status ) . "  " .
				"       " . toboolean ( $is_still_defined ) .
				$nl ) ;

		$dvalue = formatted_printr ( $def [ 'value' ] ) ;
		$rvalue = formatted_printr ( $value ) ;
		echo ( "$tab$tab Defined value   : [$dvalue]$nl" ) ;
		echo ( "$tab$tab Retrieved value : [$rvalue]$nl" ) ;
	    }

	echo ( $nl ) ;


	// Now just recreate the variables that have been created then undefined during step 2),
	// just to have a data set for further testing
	foreach  ( $test_definitions  as  $def )
		$store -> Define ( $def [ 'name' ], $def [ 'value' ], $def [ 'type' ] ) ;

	// Step 3 -
	//	Display variable count and variable list
	echo ( "****** Step 3 : Display variable count and variable list :$nl" ) ;
	echo ( "{$tab}Number of variables defined in step 2) : " . count ( $store ) . $nl ) ;
	echo ( "{$tab}Variable list                          : " . implode ( ', ', $store -> GetNames ( ) ) . $nl ) ;
	echo ( $nl ) ;

	// Step 4 : display the list of defined variables together with their value.
	// You will see a warning because the variable named 'array' is... an array
	echo ( "****** Step 4 : Display variable names and values :$nl" ) ;

	foreach  ( $store  as  $name => $value )
	   {
		$value	=  formatted_printr ( $value ) ;
		echo ( $tab . sprintf ( "%-20s", $name ) . " : $value$nl" ) ;
	    }
  
	echo ( $nl ) ;

	// Step 5 -
	//	Loop through variables by their integer index.
	echo ( "****** Step 5 : Loop through variables using their integer index :$nl" ) ;
	$count	=  count ( $store ) ;
	$vnames =  $store -> GetNames ( ) ;

	for (  $i = 0 ; $i  <  $count ; $i ++ )
	   {
		$value	=  formatted_printr ( $store [$i] ) ;
		echo ( $tab . sprintf ( "%-20s", $vnames [$i] ) . " : $value$nl" ) ;
	    }
     
	echo ( $nl ) ;


	// Step 6 -
	//	Loop through variables by their variable name.
	echo ( "****** Step 6 : Loop through variables using their name :$nl" ) ;

	foreach  ( $vnames  as  $vname )
	   {
		$value	=  formatted_printr ( $store [ $vname ] ) ;
		echo ( $tab . sprintf ( "%-20s", $vname ) . " : $value$nl" ) ;
	    }
     
	echo ( $nl ) ;

	// Step 7 :
	//	Create the variable named 'zz' accessing it as a property
	echo ( "****** Step 7 : Create a variable named 'zz' by accessing it as a property :$nl" ) ;
	$store [ 'zz' ]		=  'the zz value' ;
	$vnames =  $store -> GetNames ( ) ;

	foreach  ( $vnames  as  $vname )
	   {
		$value	=  formatted_printr ( $store [ $vname ] ) ;
		echo ( $tab . sprintf ( "%-20s", $vname ) . " : $value$nl" ) ;
	    }