<?php
/**************************************************************************************************************

    NAME
        DbBufferedUpdate.php

    DESCRIPTION
        A class for buffering UPDATE statements.
 	Typical use is :
  
 		$updater	=  new BufferedUpdate ( 'table_name', $id_field, 100 ) ;
  
 		while  ( $condition )
 			$updater -> Add ( [ values ] ) ;
  
 		$updater -> Flush ( ) ;

    AUTHOR
        Christian Vigh, 06/2015.

    HISTORY
    [Version : 1.0]    [Date : 2015/06/09]     [Author : CV]
        Initial version.

    [Version : 1.1]		[Date : 2016/01/19]     [Author : CV]
	. Some rewriting due to the optimizations on the BufferedOperation class.

 **************************************************************************************************************/
require_once ( "DbBufferedOperation.php" ) ;


/*==============================================================================================================

    DbBufferedUpdate -
        A class for buffering UPDATE statements.
	Note that the same column name cannot be updated if it is used in the WHERE clause. This is a
	limitation of the class, not of mysql.

  ==============================================================================================================*/
class  DbBufferedUpdate		extends  DbBufferedOperation
   {
	// Fields used to identify the appropriate record in the WHERE clause
	protected 	$WhereFieldNames ;
	// Field names (not including WhereFieldNames)
	protected	$UpdateFieldNames ;

	protected	$InnerBufferSize		=  64 ;
	
	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        Constructor - Builds a BufferedInsert object
	
	    PROTOTYPE
	        $inserter	=  new BufferedInsert ( $table_name, $where_fields, $column_fields, $buffer_size = 100, 
								$database = null ) ;
	
	    DESCRIPTION
	        Builds a BufferedUpdate object.
	
	    PARAMETERS
	        $table_name (string) -
	                Name of the underlying table.
	  
	 	$where_fields (string or array of strings) -
	 		Field names to be used for locating a row in a WHERE clause.

		$column_fields (array of strings) -
			Names of the fields to be updated.
	  
	 	$buffer_size (integer) -
	 		Number of rows to be buffered before an INSERT statement is issued.
	  
	 	$database (Database) -
	 		Database object. If not specified, the global $Database object will be used.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  __construct ( $table_name, $where_fields, $field_names, $buffer_size = 100, $database )
	   {
		if  ( ! is_array ( $where_fields ) )
			$where_fields	=  [ $where_fields ] ;

		parent::__construct ( $table_name, array_merge ( $where_fields, $field_names ), $buffer_size, $database ) ;
		
		$this -> WhereFieldNames	=  $where_fields ;
		$this -> UpdateFieldNames	=  $field_names ;
		$this -> MultiQuery		=  true ;

		if  ( $this -> InnerBufferSize  >  $this -> BufferSize )
			$this -> InnerBufferSize	=  $this -> BufferSize ;
	    }
	
	
	/*--------------------------------------------------------------------------------------------------------------
	
	    getter and setter -
		Gives readonly access to the following properties :
		- WhereFieldNames
		- UpdateFieldNames

	 *-------------------------------------------------------------------------------------------------------------*/
	public function  __get ( $member )
	   {
		switch  ( $member )
		   {
			case	'WhereFieldNames'	:  return ( $this -> WhereFieldNames ) ;
			case	'UpdateFieldNames'	:  return ( $this -> UpdateFieldNames ) ;
			default :
				trigger_error ( "Undefined property '$member'." ) ;
		    }
	    }


	public function  __set  ( $member, $value )
	   {
		switch  ( $member )
		   {
			case	'WhereFieldNames'	:
			case	'UpdateFieldNames'	:
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
		$single_key		=  ( count ( $this -> WhereFieldNames )  ==  1 ) ;
		$super_query		=  '' ;
		$row_count		=  count ( $this -> Rows ) ;
		$not_first_inner_query	=  false ;

		// Global loop : build UPDATE queries for InnerBufferSize rows, and catenate them until
		// all the collected rows have been processed
		for  ( $i = 0 ; $i < $row_count ; $i += $this -> InnerBufferSize )
		   {
			if  ( $not_first_inner_query )
				$super_query		.=  ";\n" ;
			else
				$not_first_inner_query	 =  true ;

			// Build the SET clauses
			$not_first_field	=  false ;
			$escaped_key_rows	=  [] ;

			$query			=  "UPDATE {$this -> TableName} SET\n" ;

			foreach  ( $this -> UpdateFieldNames  as  $field_name )
			   {
				if  ( $not_first_field )
					$query			.=  ",\n" ;
				else
					$not_first_field	 =  true ;

				$query			.=  "$field_name = CASE\n" ;
				$not_first_id_field	 =  false ;

				// Loop through buffered rows 
				$index		=  0 ;
				$upper_index	=  min ( $i + $this -> InnerBufferSize, $row_count ) ;

				for ( $j = $i ; $j  <  $upper_index ; $j ++ )
				   {
					$row			=  $this -> Rows [$j] ;
					$not_first_id_field	=  false ;

					$query		.=  'WHEN (' ;
					$index ++ ;

					// Each field in the WhereFieldNames array will generate a "WHEN ... THEN" subclause within  "CASE ... END"
					foreach  ( $this -> WhereFieldNames  as  $id_name )
					   {
						if  ( $not_first_id_field ) 
							$query			.=  ' AND ' ;
						else
							$not_first_id_field	 =  true ;

						// Check that the current key name has been specified for this row
						if  ( isset ( $row [ 'keys' ] [ $id_name ] ) ) 
							$escaped_key	=  mysqli_escape_string ( $this -> Database, $row [ 'keys' ] [ $id_name ] ) ; 
						else
							throw new RuntimeException ( "DbBufferedUpdate : row #$index missing key column '$id_name'." ) ;

						// Add this key to the query 
						$escaped_key_rows [ $index - 1 ] [ $id_name ]	 =  $escaped_key ;
						$query						.=  "$id_name = '$escaped_key'" ;
					    }

					if  ( isset ( $row [ 'columns' ] [ $field_name ] ) ) 
						$value		=  "'" . mysqli_escape_string ( $this -> Database, $row [ 'columns' ] [ $field_name ] ) . "'" ;
					else if  ( isset ( $row [ 'computed-columns' ] [ $field_name ] ) ) 
						$value		=  $row [ 'computed-columns' ] [ $field_name ] ;
					else
						throw new RuntimeException ( "DbBufferedUpdate : row #$index missing key column '$field_name'." ) ;

					$query		.=  ") THEN $value\n" ;
				    }

				$query  .=  "END" ;
			    }

			// Second loop : build the WHERE clause. Two cases are distinguished :
			// - The key only uses one field. The WHERE clause will contain only one part :
			//	WHERE key IN ( list )
			// - The key covers several fields. The WHERE clause will contain several subparts :
			//	WHERE
			//		( key1 = kv11 AND key2 = kv12 AND ... keyn = kv1n ) OR
			//		( key1 = kv21 AND key2 = kv22 AND ... keyn = kv2n ) OR
			if  ( $single_key )
			   {
				reset ( $this -> WhereFieldNames ) ;
				$where_clause		=  current ( $this -> WhereFieldNames ) . ' IN (' ;
				$not_first_clause	=  false ;

				foreach  ( $escaped_key_rows  as  $key_row ) 
				   {
					if  ( $not_first_clause )
						$where_clause		.=  ',' ;
					else
						$not_first_clause	 =  true ;

					$where_clause	.=  current ( $key_row ) ;
				    }

				$where_clause	.=  ')' ;
			    }
			else
			   {
				$where_clause		=  '' ;
				$not_first_clause	=  false ;

				foreach  ( $escaped_key_rows  as  $key_row ) 
				   {
					if  ( $not_first_clause )
						$where_clause		.=  ' OR ' ;
					else
						$not_first_clause	 =  true ;

					$where_clause		.=  '(' ;
					$first_field		 =  true ;

					foreach ( $this -> WhereFieldNames  as  $key_field )
					   {
						if  ( ! $first_field )
							$where_clause	.=  ' AND ' ;
						else
							$first_field	 =  false ;

						$where_clause	.=  $key_field . "='" . $key_row [ $key_field ] . "'" ;
					    }

					$where_clause		.=  ')' ;
				    }
			    }

			// Final query
			$super_query	.=  "\n$query\nWHERE $where_clause" ;
		    }

		return ( $super_query ) ;
	    }
    }