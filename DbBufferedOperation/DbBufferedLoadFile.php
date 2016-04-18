<?php
/**************************************************************************************************************

    NAME
        DbBufferedLoadFile.phpclass

    DESCRIPTION
        A class for buffering LOAD DATA INFILE statements.

    AUTHOR
        Christian Vigh, 07/2015.

    HISTORY
    [Version : 1.0]	[Date : 2015/07/28]     [Author : CV]
        Initial version.

    [Version : 1.1]		[Date : 2016/01/19]     [Author : CV]
	. Some rewriting due to the optimizations on the BufferedOperation class.

 **************************************************************************************************************/
require_once ( "DbBufferedOperation.php" ) ;


/*==============================================================================================================

    DbBufferedLoadFile class -
        A class for buffering LOAD DATA INFILE statements.

  ==============================================================================================================*/
class  DbBufferedLoadFile		extends  DbBufferedOperation
   {
	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        Constructor - Builds a BufferedLoadFile object
	
	    PROTOTYPE
	        $inserter	=  new BufferedLoadFile ( $table_name, $field_names, $buffer_size = 4096, 
								$database = null ) ;
	
	    DESCRIPTION
	        Builds a BufferedLoadFile object.
	
	    PARAMETERS
	        $table_name (string) -
	                Name of the underlying table.
	  
	 	$field_names (array of strings) -
	 		Field names.
	  
	 	$buffer_size (integer) -
	 		Number of rows to be buffered before a LOAD DATA INFILE statement is issued.
	  
	 	$database (Database) -
	 		Database object. If not specified, the global $Database object will be used.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  __construct ( $table_name, $field_names, $buffer_size = 4096, $database = null )
	   {
		parent::__construct ( $table_name, $field_names, $buffer_size, $database ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        BuildQuery - Builds the final query.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	private		$CsvFile		=  false ;

	public function  BuildQuery ( )
	   {
		$csvfile	=  str_replace ( '\\', '/', tempnam ( sys_get_temp_dir ( ), "mys" ) . ".csv" ) ;
		$fp		=  fopen ( $csvfile, "w" ) ;
		$value		=  '' ;

		// Collect rows and build csv file contents
		$index		=  0 ;

		foreach  ( $this -> Rows  as  $row )
		   {
			$first_field	=  true ;
			$index ++ ;
   
			foreach  ( $this -> FieldNames  as  $field_name )
			   {
				if  ( ! $first_field )
					$value		.=  ';' ;
				else
					$first_field	 =  false ;

				// Check that the current field name has been specified for this row
				if  ( isset ( $row [ 'columns' ] [ $field_name ] ) )
					$value		.=  '"' . mysqli_escape_string ( $this -> Database, $row [ 'columns' ] [ $field_name ] ) . '"' ;
				else
					throw new RuntimeException ( "DbBufferedLoadFile : row #$index missing column '$field_name'." ) ;
			    }
			
			$value	.=  "\n" ;
		    }

		fwrite ( $fp, $value ) ;
		fclose ( $fp ) ;

		// Build the query
		$query		 =  "
					LOAD DATA LOCAL INFILE '$csvfile'
					INTO TABLE {$this -> TableName}
					FIELDS TERMINATED BY ';' 
						OPTIONALLY ENCLOSED BY '\"'
						ESCAPED BY '\"'
					LINES  TERMINATED BY '\\n'
				    " ;
		$query		.=  '(' . implode ( ',', $this -> FieldNames ) . ')' ;

		// Remember the generated csv file for later cleaning
		$this -> CsvFile =  $csvfile ;

		return ( $query ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        Cleanup - Cleans up the temp file created by BuildQuery().
	
	 *-------------------------------------------------------------------------------------------------------------*/
	protected function  Cleanup ( )
	   {
		if  ( $this -> CsvFile )
		   {
			@unlink ( $this -> CsvFile ) ;
			$this -> CsvFile	=  false ;
		    }
	    }
    }