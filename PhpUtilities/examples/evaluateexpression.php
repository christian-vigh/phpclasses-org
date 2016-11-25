<?php
	/****************************************************************************************************

		This example demonstrates the use of the EvaluateExpression() method, using different
		expressions, valid or not.

	 ****************************************************************************************************/
	
	 require_once ( '../PhpUtilities.phpclass' ) ;

	 if  ( php_sapi_name ( )  !=  'cli' )
		echo ( '<pre>' ) ;

	$expressions	=  array
	   (
		'17 * 4',			// no error
		'99*',				// generates a fatal error
		'4 * 32',			// no error
		'UNDEFINED_CONSTANT * 12'	// generates a notice message
	    ) ;

	echo "Evaluation results :\n" ;
	echo "------------------\n" ;
	
	foreach ( $expressions  as  $expression )
	   {
		echo sprintf ( "%-24s", $expression ) . " : " ;

		$status		=  PhpUtilities::EvaluateExpression ( $expression, $result, $error ) ;

		// Returned status is true : the expression evaluated correctly
		if  ( $status )
			echo $result ;
		// Status = false : the expression generated a fatal error (such as parsing error) or notice/error message
		// In this case, the error message is available in the supplied $error variable
		else
			echo "(error) $error" ;

		echo "\n" ;
	    }