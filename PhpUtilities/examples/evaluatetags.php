<?php
	/****************************************************************************************************

		This example demonstrates the use of the EvaluateTags() method to replace the contents of
		PHP open tags inside a string with the result of their output.
		We use sample data that looks like a .INI file.

	 ****************************************************************************************************/
	
	 require_once ( '../PhpUtilities.phpclass' ) ;

	 if  ( php_sapi_name ( )  !=  'cli' )
		echo ( '<pre>' ) ;

	$script_variable	=  'This is a script variable defined in evaluateexpression.ini' ;

	$before			=  file_get_contents( 'evaluateexpression.ini' ) ;

	echo "Contents before :\n" ;
	echo "---------------\n\n" ;
	echo $before ;

	$after			=  PhpUtilities::EvaluateTags ( $before ) ;
	echo "\n\n\n\nContents after :\n" ;
	echo "---------------\n\n" ;
	echo $after ;


	echo "\n\n\n\nContents generated using ob_xxx() functions :\n" ;
	echo "---------------\n\n" ;
	require ( 'evaluateexpression.ini' ) ;
	$ob_contents		 =  ob_get_clean ( ) ;

	echo "\n\n--------------------\n$ob_contents" ;