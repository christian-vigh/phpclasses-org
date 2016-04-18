<?php
/**************************************************************************************************************

    NAME
        DbVariableStore.class.php

    DESCRIPTION
        Implements a variable store, which can be accessed either as an object or an associative array.
	Variable values can have a type, so that automatic conversions are performed when reading from or 
	writing to the table.

    AUTHOR
        Christian Vigh, 09/2015.

    HISTORY
    [Version : 1.0]    [Date : 2015/09/26]     [Author : CV]
        Initial version.

 **************************************************************************************************************/


/*==============================================================================================================

    DbVariableStore class -
        Implements a variable store.

==============================================================================================================*/
class   DbVariableStore		implements	\Countable, \Iterator, \ArrayAccess
    {
	// Variable types 
	const		TYPE_STRING		=  1 ;
	const		TYPE_INTEGER		=  2 ;
	const		TYPE_DOUBLE		=  3 ;
	const		TYPE_BOOLEAN		=  4 ;
	const		TYPE_DATETIME		=  5 ;
	const		TYPE_DATE		=  6 ;
	const		TYPE_TIME		=  7 ;
	const		TYPE_TIMESTAMP		=  8 ;
	const		TYPE_SERIALIZED		=  9 ;
	const		TYPE_UNKNOWN		=  0xFFFF ;

	// Variable type to human-readable type name
	protected static	$TypeNames	= 
	   [
		self::TYPE_STRING		=>  'string',
		self::TYPE_INTEGER		=>  'integer',
		self::TYPE_DOUBLE		=>  'double',
		self::TYPE_BOOLEAN		=>  'boolean',
		self::TYPE_DATETIME		=>  'datetime',
		self::TYPE_DATE			=>  'date',
		self::TYPE_TIME			=>  'time',
		self::TYPE_TIMESTAMP		=>  'timestamp',
		self::TYPE_SERIALIZED		=>  'serialized',
		self::TYPE_UNKNOWN		=>  '(*** UNKNOWN TYPE ***)'
	    ] ;

	// Connection to a Mysql server
	public		$Connection ;
	// Table name
	public		$Name ;
	// Table comment
	public		$Comment ;
	// Table comment and max variable name size
	public		$VariableNameSize ;


	/*--------------------------------------------------------------------------------------------------------------
	 
	    Constructor -
	        Instanciates a variable store object, and creates the underlying table if necessary.

	 *-------------------------------------------------------------------------------------------------------------*/
	public function  __construct ( $table, $connection, $varname_size = 128, $comment = 'Variable store' )
	   {
		$this -> Name			=  $table ;
		$this -> Comment		=  $comment ;
		$this -> VariableNameSize	=  $varname_size ;
		$this -> Connection		=  $connection ;

		$this -> Create ( ) ;
	    }



	/**************************************************************************************************************
	 **************************************************************************************************************
	 **************************************************************************************************************
	 ******                                                                                                  ******
	 ******                                                                                                  ******
	 ******                                 VARIABLE-RELATED METHODS                                         ******
	 ******                                                                                                  ******
	 ******                                                                                                  ******
	 **************************************************************************************************************
	 **************************************************************************************************************
	 **************************************************************************************************************/

	/*--------------------------------------------------------------------------------------------------------------
	 
	    NAME
	        Define - Defines a variable.
	 
	    PROTOTYPE
	        $status		=  $store -> Define ( $name, $value, $type = self::TYPE_STRING ) ;
	 
	    DESCRIPTION
	        Defines or updates if it already exists the specified variable.
	 
	    PARAMETERS
	        $name (string) -
	                Name of the variable to be created or updated.

		$value (string) -
			Value of the variable. If non-scalar, the value will be serialized before being written to the
			database.
	 
	    RETURN VALUE
	        Returns true if the variable has been created, false if it has been updated.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  Define ( $name, $value, $type = self::TYPE_STRING )
	   {
		$row		=  $this -> LoadRow ( $name, true ) ;	
		$escaped_value	=  mysqli_escape_string ( $this -> Connection, $this -> ToDatabase ( $name, $value, $type ) ) ;

		if  ( $row )
		   {
			if  ( $row [ 'type_id' ]  !=  $type )
				$this -> GenericError ( $name, $row [ 'type_id' ], "Database type mismatch with the specified one (" .
						$this -> GetTypeName ( $type ) . ').' ) ;

			$query	=  "
						UPDATE {$this -> Name}
						SET
							value		=  '$escaped_value',
							update_time	=  NOW()
						WHERE
							id = {$row [ 'id' ]}
				   " ;
			$status =  false ;
		    }
		else
		   {
			$escaped_name	=  mysqli_escape_string ( $this -> Connection, $name ) ;
			$query		=  "
						INSERT INTO {$this -> Name}
						SET
							name		=  '$escaped_name',
							type		=  $type,
							value		=  '$escaped_value',
							creation_time	=  NOW(),
							update_time	=  NOW()
					   " ;
			$status		=  true ;
		    }

		mysqli_query ( $this -> Connection, $query ) ;
		
		return ( $status ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	 
	    NAME
	        GetNames - Returns the names of defined variables.
	 
	    PROTOTYPE
	        $list	=  $store -> GetNames ( $pattern = null ) ;
	 
	    DESCRIPTION
	        Retrieves the list of defined variable names.
	 
	    PARAMETERS
	        $pattern (string) -
	                When specified, the list will be filtered using the specified pattern, which can include any
			special character recognized by the SQL LIKE operator.
	 
	    RETURN VALUE
	        An array of defined variable names. This array can be empty if no variable is defined or if the specified
		pattern does not match any variable name.
	 	 
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  GetNames ( $pattern = null )
	   {
		$query	=  "SELECT name FROM {$this -> Name} " ;

		if  ( $pattern )
			$query	.=  "WHERE name LIKE '" . mysqli_escape_string ( $this -> Connection, $pattern ) . "' " ;

		$query	.=  "ORDER BY name" ;

		$rs		=  mysqli_query ( $this -> Connection, $query ) ;
		$result		=  [] ;

		while  ( ( $row = mysqli_fetch_assoc ( $rs ) ) )
			$result []	=   $row [ 'name' ] ;

		return ( $result ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	 
	    NAME
	        IsDefined - Checks if a variable is defined.
	 
	    PROTOTYPE
	        $status		=  $store -> IsDefined ( $name ) ;
	 
	    DESCRIPTION
	        Checks if the variable $name is defined with the specified type ($type = one of the TYPE_xxx constants)
		or simply defined with any type.
	 
	    PARAMETERS
	        $name (string) -
	                Variable to be checked.

	    RETURN VALUE
	        True if the variable is defined, false otherwise.
	 
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  IsDefined ( $name )
	   {
		$row	=  $this -> LoadRow ( $name, true ) ;

		return ( ( $row ) ?  true : false ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	 
	    NAME
	        Undefine - Undefines a variable.
	 
	    PROTOTYPE
	        $status		=  $store -> Undefine ( $name ) ;
	 
	    DESCRIPTION
	        Undefines (deletes) the specified variable.
	 
	    PARAMETERS
	        $name (string) -
	                Name of the variable to be undefined.
	 
	    RETURN VALUE
	        True if the variable already existed, false otherwise.
	 
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  Undefine ( $name )
	   {
		$escaped_name	=  mysqli_escape_string ( $this -> Connection, $name ) ;
		$query		=  "DELETE FROM {$this -> Name} WHERE name = '$escaped_name'" ;

		mysqli_query ( $this -> Connection, $query ) ;

		return ( ( mysqli_affected_rows ( $this -> Connection ) ) ?  true : false ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	 
	    NAME
	        ValueOf - Returns a variable value.
	 
	    PROTOTYPE
	        $value	=  $store -> ValueOf ( $name ) ;
	 
	    DESCRIPTION
	        Retrieves the (converted) value of a database.
	 
	    PARAMETERS
	        $name (string) -
	                Name of the variable whose value is to be retrieved.
	 
	    RETURN VALUE
	        The value of the variable, or null if it does not exist.
		Note the return value of this method can also be null if the fetched value was a serialized null.
	 
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  ValueOf ( $name )
	   {
		$row		=  $this -> LoadRow ( $name, false ) ;
		$value		=  $this -> FromDatabase ( $name, $row [ 'value' ], $row [ 'type_id' ] ) ;

		return ( $value ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	 
	    Magic functions -
		Allow to get/set variable values using the object operator "->".
		Note : this is a somewhat lazy implementation, since it relies on the ArrayAccess interface.

	 *-------------------------------------------------------------------------------------------------------------*/
	public function  __get ( $member )
	   { return ( $this [ $member ] ) ; }


	public function  __set ( $member, $value )
	   {
		$this [ $member ]	=  $value ;
	    }


	/**************************************************************************************************************
	 **************************************************************************************************************
	 **************************************************************************************************************
	 ******                                                                                                  ******
	 ******                                                                                                  ******
	 ******                          SUPPORT FUNCTIONS THAT ARE PUBLICLY ACCESSIBLE                          ******
	 ******                                                                                                  ******
	 ******                                                                                                  ******
	 **************************************************************************************************************
	 **************************************************************************************************************
	 **************************************************************************************************************/

	/*--------------------------------------------------------------------------------------------------------------
	 
	    Create -
	        Creates the underlying table.

	 *-------------------------------------------------------------------------------------------------------------*/
	public function  Create ( )
	   {
		$query	=  "
				CREATE TABLE IF NOT EXISTS {$this -> Name}
				   (
					id		INTEGER UNSIGNED			NOT NULL AUTO_INCREMENT
												COMMENT 'Unique id for this entry',
					name		CHAR({$this -> VariableNameSize})	NOT NULL DEFAULT ''
												COMMENT 'Variable name',
					type		ENUM ( 'string', 'integer', 'double', 'boolean', 'datetime', 'date', 'time', 'timestamp', 'serialized' )
												NOT NULL DEFAULT 'string'
												COMMENT 'Variable type',
					value		LONGTEXT				NOT NULL
												COMMENT 'Variable value',
					creation_time	DATETIME				NOT NULL DEFAULT '0000-00-00 00:00:00'
												COMMENT 'Creation time',
					update_time	DATETIME				NOT NULL DEFAULT '0000-00-00 00:00:00'
												COMMENT 'Last update time',

					PRIMARY KEY	( id ),
					UNIQUE KEY	( name )
				    ) ENGINE = MyISAM CHARSET latin1 COMMENT '{$this -> Comment}' 
			   " ;

		mysqli_query ( $this -> Connection, $query ) ;
	    }


	/**************************************************************************************************************
	 **************************************************************************************************************
	 **************************************************************************************************************
	 ******                                                                                                  ******
	 ******                                                                                                  ******
	 ******                                  INTERFACES IMPLEMENTATIONS                                      ******
	 ******                                                                                                  ******
	 ******                                                                                                  ******
	 **************************************************************************************************************
	 **************************************************************************************************************
	 **************************************************************************************************************/

	 /*--------------------------------------------------------------------------------------------------------------
	  
		Countable interface.
  
	  *-------------------------------------------------------------------------------------------------------------*/

	public function  Count ( )
	   {
		$query	=  "SELECT COUNT(*) AS 'count' FROM {$this -> Name}" ;
		$rs	=  mysqli_query ( $this -> Connection, $query ) ;
		$result =  mysqli_fetch_assoc ( $rs ) ;

		return ( $result [ 'count' ] ) ;
	    }


	 /*--------------------------------------------------------------------------------------------------------------
	  
		Iterator interface.
  
	  *-------------------------------------------------------------------------------------------------------------*/

	private		$VariableNames ;
	private		$VariableIndex ;


	public function  rewind ( )
	   {
		$rs	=  mysqli_query ( $this -> Connection, "SELECT name FROM {$this -> Name} ORDER BY name" ) ;
		$result =  [] ;

		while  ( ( $row = mysqli_fetch_assoc ( $rs ) ) )
			$result []	=  $row [ 'name' ] ;

		$this -> VariableNames	=  $result ;
		$this -> VariableIndex	=  0 ;
	    }


	public function  valid ( )
	   {
		return ( isset ( $this -> VariableNames [ $this -> VariableIndex ] ) ) ;
	    }


	public function  key ( )
	   {
		return ( $this -> VariableNames [ $this -> VariableIndex ] ) ;
	    }


	public function  next ( )
	   {
		$this -> VariableIndex ++ ;
	    }


	public function  current ( )
	   {
		$name		=  $this -> VariableNames [ $this -> VariableIndex ] ;
		$escaped_name	=  mysqli_escape_string ( $this -> Connection, $name ) ;

		$rs		=  mysqli_query ( $this -> Connection, "SELECT type + 0 AS type_id, value FROM {$this -> Name} WHERE name = '$escaped_name'" ) ;
		$row		=  mysqli_fetch_assoc ( $rs ) ;

		return ( $this -> FromDatabase ( $name, $row [ 'value' ], $row [ 'type_id' ] ) ) ;
	    }


	 /*--------------------------------------------------------------------------------------------------------------
	  
		ArrayAccess interface.
		Allows access to a variable either by its name of by its index.
		This interface is provided for consistency and completeness when used with integer indexes, but it may 
		not be really performant...
  
	  *-------------------------------------------------------------------------------------------------------------*/

	// __offsetLoad -
	//	Loads a value either by its name or its integer position 
	private function  __offsetLoad ( $field, $offset )
	   {
		if  ( is_numeric ( $offset ) )
		   {
			$query			=  "SELECT $field FROM {$this -> Name} ORDER BY name LIMIT 1 OFFSET $offset" ;
		    }
		else
		   {
			$escaped_offset		=  mysqli_escape_string ( $this -> Connection, $offset ) ;
			$query			=  "SELECT $field FROM {$this -> Name} WHERE name = '$escaped_offset'" ;
		    }

		$rs		=  mysqli_query ( $this -> Connection, $query ) ;
		$result		=  mysqli_fetch_assoc ( $rs ) ;

		return ( $result ) ;
	    }


	// offsetExists -
	//	Checks that the specified offset exists. Invoked when the isset() function is called.
	public function  offsetExists ( $offset )
	   {
		$result		=  $this -> __offsetLoad ( 'id', $offset ) ;

		return ( ( $result [ 'id' ] ) ?  true : false ) ;
	    }


	// offsetGet -
	//	Returns the variable value corresponding to the specified name or integer index.
	//	Note that when the variable does not exist, the value NULL is returned, not FALSE.
	public function  offsetGet ( $offset )
	   {
		$result		=  $this -> __offsetLoad ( 'name, type + 0 AS type_id, value', $offset ) ;

		return ( ( $result  ===  false ) ?  null : $this -> FromDatabase ( $result [ 'name' ], $result [ 'value' ], $result [ 'type_id' ] ) ) ;
	    }


	// offsetUnset -
	//	Deletes a variable either by its name or its integer index. 
	//	Invoked when the unset() function is called.
	public function  offsetUnset ( $offset )
	   {
		$result		=  $this -> __offsetLoad ( 'id', $offset ) ;

		if  ( $result )
		   {
			mysqli_query ( $this -> Connection, "DELETE FROM {$this -> Name} WHERE id = {$result [ 'id' ]}" ) ;
		    }
	    }


	// offsetSet -
	//	Defines a variable or updates its value.
	//	If the variable exists, its original type will be preserved and the specified value converted to this
	//	type.
	//	If the variable does not exist, it will be created as a string.
	public function  offsetSet ( $offset, $value ) 
	   {
		$row	=  $this -> __offsetLoad ( 'name, type + 0 AS  type_id', $offset ) ;

		if  ( $row )
		   {
			$name	=  $row [ 'name' ] ;
			$type	=  $row [ 'type' ] ;
		    }
		else
		   {
			if  ( is_numeric ( $offset ) )
				$this -> GenericError ( "#$offset", self::TYPE_UNKNOWN, "Integer indexes can only be used for updating " .
						"an existing variable value, but cannot be used to create a new one." ) ;

			// Variable is undefined ; the one which will be created will be of type string
			$name	=  $offset ;
			$type	=  self::TYPE_STRING ;
		    }

		$this -> Define ( $name, $value, $type ) ;
	    }


	/**************************************************************************************************************
	 **************************************************************************************************************
	 **************************************************************************************************************
	 ******                                                                                                  ******
	 ******                                                                                                  ******
	 ******                                     PROTECTED FUNCTIONS                                          ******
	 ******                                                                                                  ******
	 ******                                                                                                  ******
	 **************************************************************************************************************
	 **************************************************************************************************************
	 **************************************************************************************************************/


	 /*--------------------------------------------------------------------------------------------------------------

	    LoadRow -
		Loads a row related to a variable.
		If $restricted is true, only the id, name and type fields will be loaded.
	  
	  *-------------------------------------------------------------------------------------------------------------*/
	protected function  LoadRow ( $name, $restricted = false )
	   {
		if  ( $restricted )
			$select		=  'id, name, type, ( type + 0 ) AS type_id' ;
		else
			$select		=  '*, ( type + 0 ) AS type_id' ;

		$escaped_name	=  mysqli_escape_string ( $this -> Connection, $name ) ;
		$query		=  "SELECT $select FROM {$this -> Name} WHERE name = '$escaped_name'" ;
		$rs		=  mysqli_query ( $this -> Connection, $query ) ;
		$row		=  mysqli_fetch_assoc ( $rs ) ;

		return ( $row ) ;
	    }


	 /*--------------------------------------------------------------------------------------------------------------

	    ToDatabase -
		Converts a value to the specified database format.
	  
	  *-------------------------------------------------------------------------------------------------------------*/
	protected function  ToDatabase ( $name, $value, $type )
	   {
		if  ( $type   !=  self::TYPE_SERIALIZED  &&  ! is_scalar ( $type ) )
			$this -> ConversionError ( $name, $type, "non-scalar value specified", 'from' ) ;

		switch ( $type )
		   {
			// String type -
			//	Convert null values to the empty string ; check that the supplied value is scalar.
			case	self::TYPE_STRING :
				if  ( ! $value )
					$value	=  '' ;

				return  ( $value ) ;

			// Integer type -
			//	Filters on integer or double values.
			case	self::TYPE_INTEGER :
				if  ( ! is_bool ( $value )  &&  ! is_numeric ( $value ) )
					$this -> ConversionError ( $name, $type, "non-numeric value specified", 'from' ) ;
				
				return ( ( string ) ( ( integer ) $value ) ) ;

			// Double type -
			//	Filters on integer or double values.
			case	self::TYPE_DOUBLE :
				if  ( ! is_bool ( $value )  &&  ! is_numeric ( $value ) )
					$this -> ConversionError ( $name, $type, "non-numeric value specified", 'from' ) ;
				
				return ( ( string ) ( ( double ) $value ) ) ;

			// Boolean type -
			//	Checks that the specified value expresses a real boolean value.
			case	self::TYPE_BOOLEAN :
				if  ( ( $result = self::BooleanValue ( $value ) )  ===  null )
					$this -> ConversionError ( $name, $type, "non-boolean value specified", 'from' ) ;

				return ( ( $result ) ?  "1" : "0" ) ;

			// Datetime -
			//	Can be specified either as a Unix timestamp or a date/time value that can be understood by the
			//	strtotime() function.
			//	The destination value is stored in the format : 'Y/m/d H:i:s'.
			case	self::TYPE_DATETIME :
				if  ( is_numeric ( $value ) )
					$result		=  $value ;
				else if  ( ( $result = strtotime ( $value ) )  ===  false )
					$this -> ConversionError ( $name, $type, "invalid date/time value specified", 'from' ) ;

				return ( date ( 'Y/m/d H:i:s', $result ) ) ;

			// Date -
			//	Can be specified either as a Unix timestamp or a date value that can be understood by the
			//	strtotime() function.
			//	The destination value is stored in the format : 'Y/m/d'.
			case	self::TYPE_DATE :
				if  ( is_numeric ( $value ) )
					$result		=  $value ;
				else if  ( ( $result = strtotime ( $value ) )  ===  false )
					$this -> ConversionError ( $name, $type, "invalid date value specified", 'from' ) ;

				return ( date ( 'Y/m/d', $result ) ) ;

			// Time -
			//	Can be specified either as a Unix timestamp or a time value that can be understood by the
			//	strtotime() function.
			//	The destination value is stored in the format : 'H:i:s'.
			case	self::TYPE_TIME :
				if  ( is_numeric ( $value ) )
					$result		=  $value ;
				else if  ( ( $result = strtotime ( $value ) )  ===  false )
					$this -> ConversionError ( $name, $type, "invalid time value specified", 'from' ) ;

				return ( date ( 'H:i:s', $result ) ) ;

			// Timestamp -
			//	Same as integer.
			case	self::TYPE_TIMESTAMP :
				if  ( is_numeric ( $value ) )
					$result		=  ( integer ) $value ;
				else if  ( ( $result = strtotime ( $value ) )  ===  false ) 
					$this -> ConversionError ( $name, $type, "non-numeric value specified", 'from' ) ;
				
				return ( ( string ) $result ) ;

			// Compound structure that needs to be serialized
			case	self::TYPE_SERIALIZED :
				return ( serialize ( $value ) ) ;

			// Other : error
			default :
				$this -> ConversionError ( $name, $type, "an invalid type was specified for conversion to database", 'to' ) ;
		   }
	    }


	 /*--------------------------------------------------------------------------------------------------------------

	    FromDatabase -
		Converts a value from the specified database format.
	  
	  *-------------------------------------------------------------------------------------------------------------*/
	protected function  FromDatabase ( $name, $value, $type )
	   {
		switch ( $type )
		   {
			// String type -
			//	Simply return the string as is.
			case	self::TYPE_STRING :
				return ( $value ) ;

			// Integer type -
			//	Simply check that the underlying table value is of numeric type.
			case	self::TYPE_INTEGER :
				if  ( ! is_numeric ( $value ) ) 
					$this -> ConversionError ( $name, $type, "non-numeric value \"$value\" flagged as integer", 'from' ) ;

				return ( ( integer ) $value ) ;

			// Double type -
			//	Simply check that the underlying table value is of numeric type.
			case	self::TYPE_DOUBLE :
				if  ( ! is_numeric ( $value ) ) 
					$this -> ConversionError ( $name, $type, "non-numeric value \"$value\" flagged as integer", 'from' ) ;

				return ( ( double ) $value ) ;

			// Boolean type -
			//	Checks that the underlying database value expresses a real boolean value.			
			case	self::TYPE_BOOLEAN :
				if  ( ( $result = self::BooleanValue ( $value ) )  ===  null )
					$this -> ConversionError ( $name, $type, "non-boolean value \"$value\" flagged as boolean", 'from' ) ;

				return ( ( $result ) ?  true : false ) ;

			// Datetime -
			//	Can be specified either as a Unix timestamp or a date/time value that can be understood by the
			//	strtotime() function.
			//	The returned value is a date in the format 'Y/m/d H:i:s'.
			case	self::TYPE_DATETIME :
				if  ( is_numeric ( $value ) )
					$result		=  $value ;
				else if  ( ( $result = strtotime ( $value ) )  ===  false )
					$this -> ConversionError ( $name, $type, "non-date/time value flagged as datetime", 'from' ) ;

				return ( date ( 'Y/m/d H:i:s', $result ) ) ;

			// Date -
			//	Can be specified either as a Unix timestamp or a date value that can be understood by the
			//	strtotime() function.
			//	The returned value is a date in the format 'Y/m/d'.
			case	self::TYPE_DATE :
				if  ( is_numeric ( $value ) )
					$result		=  $value ;
				else if  ( ( $result = strtotime ( $value ) )  ===  false )
					$this -> ConversionError ( $name, $type, "non-date value flagged as date", 'from' ) ;

				return ( date ( 'Y/m/d', $result ) ) ;

			// Time -
			//	Can be specified either as a Unix timestamp or a time value that can be understood by the
			//	strtotime() function.
			//	The returned value is a date in the format 'H:i:s'.
			case	self::TYPE_TIME :
				if  ( is_numeric ( $value ) )
					$result		=  $value ;
				else if  ( ( $result = strtotime ( $value ) )  ===  false )
					$this -> ConversionError ( $name, $type, "non-time value flagged as time", 'from' ) ;

				return ( date ( 'H:i:s', $result ) ) ;

			// Timestamp -
			//	Can be an integer timestamp or a datetime value.
			case	self::TYPE_TIMESTAMP :
				if  ( is_numeric ( $value ) ) 
					$result		=  ( integer ) $value ;
				else if  ( ( $result = strtotime ( $value ) )  ===  false )
					$this -> ConversionError ( $name, $type, "non-numeric value \"$value\" flagged as timestamp", 'from' ) ;

				return ( $result ) ;

			// Serialized value
			case	self::TYPE_SERIALIZED :
				if  ( ( $result = @unserialize ( $value ) )  ===  false )
					$this -> ConversionError ( $name, $type, "invalid serialized value \"$value\"", 'from' ) ;
				
				return ( $result ) ;

			// Other unhandled cases
			default :
				$this -> ConversionError ( $name, $type, "an invalid type was specified for conversion from database", 'from' ) ;
		   }
	   }


	 /*--------------------------------------------------------------------------------------------------------------

		Error functions are gathered here.
	  
	  *-------------------------------------------------------------------------------------------------------------*/
	protected function  GetTypeName ( $type )
	   {
		if  ( isset ( self::$TypeNames [ $type ] ) )
			return ( self::$TypeNames [ $type ] ) ;
		else
			return ( "(unknown type #$type)" ) ;
	    }


	protected function  ConversionError ( $variable, $type, $message, $direction )
	   {
		$typename	=  $this -> GetTypeName ( $type ) ;

		$errmsg		=  "Conversion $direction database failed because $message." ;

		$this -> GenericError ( $variable, $type, $message ) ;
	    }


	protected function  GenericError ( $variable, $type, $message )
	   {
		$typename	=  $this -> GetTypeName ( $type ) ;

		$errmsg		=  "Variable store \"{$this -> Name}\", $typename variable \"$variable\" : $message." ;

		throw ( new Exception ( $errmsg ) ) ;
	   }


	 /*--------------------------------------------------------------------------------------------------------------

		Check boolean value.
	  
	  *-------------------------------------------------------------------------------------------------------------*/
	private static  $BooleanValuesTable	=  array (
				""		=> false,
				"on"		=> true,
				"yes"		=> true,
				"true"		=> true,
				"checked"	=> true,
				"1"		=> true,
				"off"		=> false,
				"no"		=> false,
				"false"		=> false,
				"unchecked"	=> false,
				"0"		=> false
				) ;


	public static function  BooleanValue ( $value )
	   {
	   	// Trim any whitespace and convert to lowercase
	   	$value = trim ( strtolower ( $value ) ) ;

	   	// If the value is numeric, return either true (non-zero) or false (null value)
		if  ( is_numeric ( $value ) )
			return ( ( $value ) ? true : false ) ;

		// Other cases : loop through the boolean value keywords to retrieve the appropriate boolean constant
		foreach  ( self::$BooleanValuesTable  as  $name => $constant )
		   {
		   	if  ( ! strcmp ( $name, $value ) )
		   		return ( $constant ) ;
		    }

		// Otherwise return false : this means that we failed to interpret the value as a boolean constant
		return ( null ) ;
	    }

    }


