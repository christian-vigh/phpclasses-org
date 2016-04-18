<?php
/**************************************************************************************************************

    NAME
        DbBufferedInsert.php

    DESCRIPTION
        A class for buffering INSERT statements.
 	Typical use is :
  
 		$inserter	=  new BufferedInsert ( 'table_name', [ fields ], 100 ) ;
  
 		while  ( $condition )
 			$inserter -> Add ( [ values ] ) ;
  
 		$inserter -> Flush ( ) ;

    AUTHOR
        Christian Vigh, 06/2015.

    HISTORY
    [Version : 1.0]		[Date : 2015/06/09]     [Author : CV]
        Initial version.

    [Version : 1.0.1]		[Date : 2015/07/27]     [Author : CV]
	. Adapted for the new BufferedOperation parent class.

    [Version : 1.1]		[Date : 2016/01/19]     [Author : CV]
	. Some rewriting due to the optimizations on the BufferedOperation class.

 **************************************************************************************************************/
require_once ( "DbBufferedOperation.php" ) ;

/*==============================================================================================================

    DbBufferedInsert -
        A class for buffering INSERT statements.
	This class is not designed to provide a universal solution for buffering rows before inserting them into
	a database, but rather to provide a straightforward way to perform this operation. So don't expect
	elaborate methods for that...

  ==============================================================================================================*/
class  DbBufferedInsert		extends  DbBufferedOperation
   {
	// Buffered insert flags
	const		INSERT_FLAGS_NONE		=  0 ;			// No particular option
	const		INSERT_FLAGS_IGNORE		=  0x0001 ;		// Ignore duplicate keys
	

	// Insert flags
	protected	$Flags ;
	
	
	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        Constructor - Builds a BufferedInsert object
	
	    PROTOTYPE
	        $inserter	=  new BufferedInsert ( $table_name, $field_names, $buffer_size = 100, 
							$flags = self::INSERT_FLAGS_NONE, $database = null ) ;
	
	    DESCRIPTION
	        Builds a BufferedInsert object.
	
	    PARAMETERS
	        $table_name (string) -
	                Name of the underlying table.
	  
	 	$field_names (array of strings) -
	 		Field names.
	  
	 	$buffer_size (integer) -
	 		Number of rows to be buffered before an INSERT statement is issued.
	  
	 	$flags (integer) -
	 		A combination of the following flags :
	 		- INSERT_FLAGS_IGNORE :
	 			Ignore duplicate keys (duplicate records will not be added).
	 		- INSERT_FLAGS_NONE :
	 			Default value. No specific insert option is to be used.
	  
	 	$database (mysqli link) -
	 		Database object. If not specified, the global $Database object will be used.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  __construct ( $table_name, $field_names, $buffer_size = 100, $database, $flags = self::INSERT_FLAGS_NONE )
	   {
		global		$Database ;
		
		
		parent::__construct ( $table_name, $field_names, $buffer_size, $database ) ;
		
		$this -> FieldNames	=  $field_names ;
		$this -> Flags		=  $flags ;
	    }
	

	/*--------------------------------------------------------------------------------------------------------------
	
	    getter and setter -
		Gives readonly access to the following properties :
		- Flags

	 *-------------------------------------------------------------------------------------------------------------*/
	public function  __get ( $member )
	   {
		switch  ( $member )
		   {
			case	'Flags'		:  return ( $this -> Flags ) ;
			default :
				return ( parent::__get ( $member ) ) ;
		    }
	    }


	public function  __set  ( $member, $value )
	   {
		switch  ( $member )
		   {
			case	'Flags'		:
				trigger_error ( "Property '$member' is read-only." ) ;
				break ;

			default :
				parent::__set ( $member, $value ) ;
		    }
	    }

	
	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        BuildQuery - Builds the final query.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	protected function  BuildQuery ( )
	   {
		/***
			Using the catenation operator instead of creating an array of row values then imploding it 
			when building the query string introduces a small performance gain of around 5% on large buffers.
		 ***/
		$row_data	=  '' ;
		$first_time	=  true ;

		// Loop through each row dat
		foreach ( $this -> Rows  as  $row )
		   {
			if  ( ! $first_time )
				$row_data	.=  ',' ;
			else
				$first_time	 =  false ;

			$row_data	.=  '(' ;
			$first_row_time  =  true ;
			$index		 =  0 ;

			// Rely on the field names specified to the constructor to build the current row insertion data
			foreach ( $this -> FieldNames  as  $field_name )
			   {
				$index ++ ;

				if  ( ! $first_row_time )
					$row_data	.=  ',' ;
				else
					$first_row_time		=  false ;

				// Check that the current field name has been specified for this row
				if  ( isset ( $row [ 'columns' ] [ $field_name ] ) )
					$value		=  "'" . mysqli_escape_string ( $this -> Database, $row [ 'columns' ] [ $field_name ] ) . "'" ;
				else if  ( isset ( $row [ 'computed-columns' ] [ $field_name ] ) )
					$value		=  $row [ 'computed-columns' ] [ $field_name ] ;
				else
					throw new RuntimeException ( "DbBufferedInsert : row #$index missing column '$field_name'." ) ;

				// Append next column value
				$row_data	.=  $value ;
			    }

			$row_data	.=  ')' ;
		    }

		// Final query - take the IGNORE flag into account
		$ignore_option	=  ( $this -> Flags  &  self::INSERT_FLAGS_IGNORE ) ?  "IGNORE" : "" ;
			
		$query	=  "INSERT $ignore_option INTO {$this -> TableName} ( " .
				implode ( ', ', $this -> FieldNames ) . ' ) VALUES ' . "\n" . $row_data ; 

		return ( $query ) ;
	    }
    }