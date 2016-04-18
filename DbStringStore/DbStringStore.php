<?php
/**************************************************************************************************************

    NAME
        DbStringStore.phpclass

    DESCRIPTION
        Encapsulates a table whose purpose is to store long strings that may occur several times (for example,
	a user agent string).
	Purpose is to reduce the size of the stored data by eliminating duplicates.
 
    AUTHOR
        Christian Vigh, 06/2015.

    HISTORY
    [Version : 1.0]	[Date : 2015/06/08]     [Author : CV]
        Initial version.

    [Version : 1.0.1]	[Date : 2015/07/07]     [Author : CV]
	. Changed the md5 field to "checksum", which now holds a CRC32 value (index was bigger than data size
	  when using an md5 value and the number of collisions not big enough to justify the difference).
	. Added the $comment parameter to the class constructor

    [Version : 1.0.2]	[Date : 2015/07/23]     [Author : CV]
	. Added the UseCache property, which allows to cache read/written entries to avoid unnecessary database
	  access.

    [Version : 1.0.3]	[Date : 2015/07/24]     [Author : CV]
	. Changed the Insert() method to return 0 if the supplied string is empty.
	. Added the ResetCache() method

 **************************************************************************************************************/
require_once ( 'DbTable.php' ) ;


/*==============================================================================================================

    StringStore class -
        Implements a string storage, that can be searched through fulltext index.

  ==============================================================================================================*/
class  DbStringStore		extends  DbTable
   {
	// Size of the string value field
	protected 		$StringSize ;
	// When non-zero, an index of that length will be created on the string value
	protected		$StringIndexSize ;
	// When true, inserted values are cached
	public			$UseCache		=  false ;
	// Cache data, a two-dimensional array whose first dimension is the entry type, and second dimension is the string value
	protected		$CacheData		=  [] ;
	
	
	
	/*==============================================================================================================
	
	    Constructor -
	        Instanciates a StringStore object, and creates the underlying table if necessary.
	
	  ==============================================================================================================*/
	public function  __construct ( $database,
				       $table_name		= 'string_store', 
				       $comment			= '', 
				       $string_size		=  1024, 
				       $string_index_size	= 0, 
				       $recreate		= false )
	   {
		$this -> StringSize		=  $string_size ;
		$this -> StringIndexSize	=  $string_index_size ;

		parent::__construct ( $table_name, $comment, $database, $recreate ) ;
	    }
	

	/*==============================================================================================================
	
	    Create -
	        Creates the underlying table.
	
	  ==============================================================================================================*/
	public function  Create ( )
	   {
		$default	=  '' ;
		
		if  ( $this -> StringSize  <  16384 )		// Not really the maximum size of a VARCHAR, but we have to fix a limit
		   {
			$value_type	=  "VARCHAR( {$this -> StringSize} )" ;
			$default	=  "DEFAULT ''" ;
		    }
		else if  ( $this -> StringSize  <  65536 )
			$value_type	=  "TEXT" ;
		else if  ( $this -> StringSize  <  16 * 1024 * 1024 )
			$value_type	=  "MEDIUMTEXT" ;
		else
			$value_type	=  "LONGTEXT" ;
		
		if  ( $this -> StringIndexSize )
			$value_index	=  "KEY		( value( {$this -> StringIndexSize} ) )," ;
		else
			$value_index	=  '' ;
		
		$sql	=  <<<END
CREATE TABLE IF NOT EXISTS {$this -> Name}
   (
	id		BIGINT UNSIGNED		NOT NULL AUTO_INCREMENT
						COMMENT 'Unique id for this string entry',
	type		INT			NOT NULL DEFAULT 0
						COMMENT 'Value type ; can be used to differentiate between value groups',
	checksum	INT UNSIGNED		NOT NULL DEFAULT 0
						COMMENT 'CRC32 hash of the string value',
	
	value		$value_type 		NOT NULL $default
						COMMENT 'String value',
						
	PRIMARY KEY	( id ),
	$value_index
	KEY		( type, checksum )
    ) ENGINE = MyISAM  CHARSET latin1 COMMENT '{$this -> Comment}' ;
END;
		
		mysqli_query ( $this -> Database, $sql ) ;
	    }
	
	
	/*==============================================================================================================
	
	    Insert -
	        Inserts a string into the string store. $type can be used to differentiate between string collections.
	
	  ==============================================================================================================*/
	public function  Insert ( $type, $value )
	   {
		$value		=  trim ( $value ) ;

		// Ignore empty strings
		if  ( ! $value )
			return ( 0 ) ;

		$escaped_value	=  mysqli_escape_string ( $this -> Database, $value ) ;

		// Return the entry in the cache if present and the cache is activated
		if  ( $this -> UseCache  &&  isset ( $this -> CacheData [ $type ] )  &&  isset ( $this -> CacheData [ $type ] [ $value ] ) )
			return ( $this -> CacheData [ $type ] [ $value ] ) ;

		// Check if the entry is already defined in the string store
		$rs	=  mysqli_query 
		   (
			$this -> Database,
			"
				SELECT id 
				FROM {$this -> Name} 
				WHERE 
					type = $type AND 
					checksum = CRC32('$escaped_value') AND 
					value = '$escaped_value'
			" 
		    ) ;

		$row		=  mysqli_fetch_assoc ( $rs ) ;
		$id		=  $row [ 'id' ] ;
		
		// If not, create it
		if  ( ! $id )
		   {
			mysqli_query 
			   (
				$this -> Database,
				"
					INSERT INTO {$this -> Name}
					SET
						type		= $type,
						checksum	= CRC32('$escaped_value'),
						value		= '$escaped_value'
			         "
			    ) ;
			
			$id	=  mysqli_insert_id ( $this -> Database ) ;
		    }

		// If caching is activated, remember this entry
		if  ( $this -> UseCache )
			$this -> CacheData [ $type ] [ $value ] =  $id ;
		
		// All done, return
		return ( $id ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------

	    ResetCache -
		Resets cache data.
	 
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  ResetCache ( )
	   {
		$this -> CacheData	=  [] ;
	    }
    }