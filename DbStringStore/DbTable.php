<?php
/**************************************************************************************************************

    NAME
        Table.phpclass

    DESCRIPTION
	A wrapper for encapsulating tables with a generic class.
 
    AUTHOR
        Christian Vigh, 06/2015.

    HISTORY
    [Version : 1.0]	[Date : 2015/06/08]     [Author : CV]
        Initial version.

    [Version : 1.0.1]	[Date : 2015/07/07]     [Author : CV]
	. Added the $comment parameter to the class constructor.

 **************************************************************************************************************/
// Used namespaces & objects
use	Thrak\System\Object ;
use     Thrak\IO\Path ;
use	Thrak\Types\String ;


/*==============================================================================================================

    Table class -
        Provides an abstraction layer for managing a database table.

  ==============================================================================================================*/
abstract class  DbTable
   {
	// Table name
	public		$Name ;
	// Associated database object
	public		$Database ;
	// Comment
	protected	$Comment ;
	
	
	/*==============================================================================================================
	
	    Constructor -
	        Creates a table object.
	
	  ==============================================================================================================*/
	public function  __construct ( $table_name, $comment = '', $database = null, $recreate = false )
	   {
		global		$Database ;
		
		
		$this -> Name		=  $table_name ;
		$this -> Database	=  ( $database ) ?  $database : $Database ;
		$this -> Comment	=  mysqli_escape_string ( $this -> Database, $comment ) ;
		$this -> EnsureExists ( $recreate ) ;
	    }
	
	
	/*==============================================================================================================
	
	    EnsureExists -
	        Ensures that the encapsulated table exists.
	
	  ==============================================================================================================*/
	protected function  EnsureExists ( $drop_before = false )
	   {
		if  ( $drop_before )
			$this -> Drop ( ) ;
		
		$this -> Create ( ) ;
	    }
	
	
	/*==============================================================================================================
	
	    Create -
	        Effectively creates the table. Must be implemented by derived classes.
	
	  ==============================================================================================================*/
	public abstract function  Create ( ) ;
	
	
	/*==============================================================================================================
	
	    Drop -
	        Deletes the encapsulated table.
	
	  ==============================================================================================================*/
	public function  Drop ( )
	   {
		mysqli_query ( $this -> Database, "DROP TABLE IF EXISTS {$this -> Name}" ) ;
	    }
	
	
	/*==============================================================================================================
	
	    Optimize -
	        Optimizes the encapsulated table.
	
	  ==============================================================================================================*/
	public function  Optimize ( )
	   {
		mysqli_query ( $this -> Database, "OPTIMIZE TABLE {$this -> Name}" ) ;
	    }
	    
	    
	/*==============================================================================================================
	
	    Truncate -
	        Truncates the encapsulated table. Returns the number of deleted rows.
	
	  ==============================================================================================================*/
	public function  Truncate ( )
	   {
		mysqli_query ( $this -> Database, "TRUNCATE TABLE {$this -> Name}" ) ;
	    }
    }