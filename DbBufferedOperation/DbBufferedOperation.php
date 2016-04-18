<?php
/**************************************************************************************************************

    NAME
        DbBufferedOperation.php

    DESCRIPTION
        Base class for buffered database table operations.

    AUTHOR
        Christian Vigh, 07/2015.

    HISTORY
    [Version : 1.0]	[Date : 2015/07/26]     [Author : CV]
        Initial version.

    [Version : 1.0.1]   [Date : 2015/07/28]     [Author : CV]
	. Added the ValidateFieldNames() method.

    [Version : 1.1]     [Date : 2016/01/19]     [Author : CV]
	. Rewrote class architecture to introduce optimizations.

 **************************************************************************************************************/


/*==============================================================================================================

    DbBufferedOperation -
        Base class for buffered database table operations.
	The idea is to issue one big SQL statement instead of several ones.

  ==============================================================================================================*/
abstract class  DbBufferedOperation
   {
	// Related database table
	protected		$TableName ;
	// Number of rows to be buffered before being flushed through an INSERT statement
	protected		$BufferSize ;
	// Database object
	protected		$Database ;
	// List of field names
	protected		$FieldNames ;
	// Buffered rows
	protected		$Rows		=  [] ;
	// To be set by derived classes if a mysqli_multi_query() should be used instead of mysql_query()
	protected		$MultiQuery	=  false ;
	// This is a little optimization that allows to gain a few cycles ; normally, count ( $this -> Rows ) could be
	// called whenever we need to check if a flush operation is needed. However, since the only operations performed
	// on the Rows array are appending a new item to it or setting it to an empty array, it is faster to manage a
	// counter
	private			$RowCount	=  0 ;


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        Constructor - Builds a BufferedOperation object
	
	    PROTOTYPE
	        $inserter	=  new BufferedOperation ( $table_name, $field_names, $buffer_size = 100, $database = null ) ;
	
	    DESCRIPTION
	        Builds a BufferedInsert object.
	
	    PARAMETERS
	        $table_name (string) -
	                Name of the underlying table.
	  
	 	$buffer_size (integer) -
	 		Number of rows to be buffered before an INSERT statement is issued.
	  
	 	$database (mysqli resource) -
	 		Database object. If not specified, the global $Database object will be used.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  __construct ( $table_name, $field_names, $buffer_size = 100, $database )
	   {
		$this -> TableName	=  $table_name ;
		$this -> FieldNames	=  $field_names ;
		$this -> BufferSize	=  $buffer_size ;
		$this -> Database	=  $database ;
	    }
	

	/*--------------------------------------------------------------------------------------------------------------
	
	    Destructor -
	        Flushes potentially buffered records.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  __destruct ( )
	   {
		$this -> Flush ( ) ;
	    }
	

	/*--------------------------------------------------------------------------------------------------------------
	
	    getter and setter -
		Gives readonly access to the following properties :
		- Table
		- BufferSize
		- Database
		- FieldNames
		- Rows

	 *-------------------------------------------------------------------------------------------------------------*/
	public function  __get ( $member )
	   {
		switch  ( $member )
		   {
			case	'Table'		:  return ( $this -> Table ) ;
			case	'BufferSize'	:  return ( $this -> BufferSize ) ;
			case	'Database'	:  return ( $this -> Database ) ;
			case	'FieldNames'	:  return ( $this -> FieldNames ) ;
			case	'Rows'		:  return ( $this -> Rows ) ;
			default :
				trigger_error ( "Undefined property '$member'." ) ;
		    }
	    }


	public function  __set  ( $member, $value )
	   {
		switch  ( $member )
		   {
			case	'Table'		:
			case	'BufferSize'	:
			case	'Database'	:
			case	'FieldNames'	:
			case	'Rows'		:
				trigger_error ( "Property '$member' is read-only." ) ;
				break ;

			default :
				trigger_error ( "Undefined property '$member'." ) ;
		    }
	    }

	
	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        Add - Adds a record to the buffer
	
	    PROTOTYPE
	        $object -> Add ( $values ) ;
	
	    DESCRIPTION
	        Adds a new record to the buffer. The buffer will be flushed (ie, an INSERT statement will be issued)
		if the current row count is greater than the $BufferSize property.
	
	    PARAMETERS
	        $values (array) -
	                Array of record values to be added to the buffer.

	    RETURN VALUE 
		Returns true if the buffer has been flushed (before adding the current row), false otherwise.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  Add ( $values ) 
	   {
		if  ( $this -> RowCount  >=  $this -> BufferSize )
		   {
			$this -> Flush ( ) ;
			$flush_status		=  true ;
		    }
		else 
			$flush_status		=  false ;

		$this -> Rows []	=  $values ;
		$this -> RowCount ++ ;

		return ( $flush_status ) ;
	    }
	

	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        Cleanup - Cleans any data after a flush has been performed.
	
	    PROTOTYPE
	        $buffer -> Cleanup ( ) ;
	
	    DESCRIPTION
	        Flushes the currently buffered records.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	protected function  Cleanup ( )
	   { }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        Flush - Flushes the currently buffered records.
	
	    PROTOTYPE
	        $buffer -> Flush ( ) ;
	
	    DESCRIPTION
	        Flushes the currently buffered records.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  Flush ( )
	   {
		if  ( $this -> RowCount )
		   {
			$query		=  $this -> BuildQuery ( ) ;

			if  ( $query )
			   {
				if  ( $this -> MultiQuery )
				   {
					mysqli_multi_query ( $this -> Database, $query ) ;
					 
					while  ( mysqli_next_result ( $this -> Database ) )
					   {
						$rs	=  mysqli_store_result ( $this -> Database ) ;

						if  ( $rs )
							mysqli_free_result ( $rs ) ;
					    }
				    }
				else
					mysqli_query ( $this -> Database, $query ) ;	

				$this -> Cleanup ( ) ;

				if  ( mysqli_errno ( $this -> Database ) )
					throw new RuntimeException ( "Query error : " . mysqli_error ( $this -> Database ) ) ;
			    }
			else
				$this -> Cleanup ( ) ;

			$this -> Rows		=  [] ;
			$this -> RowCount	=  0 ;

			return ( true ) ;
		    }
		else
			return ( false ) ;
	    }


	// Builds the final query
	abstract protected function  BuildQuery ( ) ;
    }