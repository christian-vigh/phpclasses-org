#!/usr/bin/php
<?php
	/**************************************************************************************************************

		Script described in the top-level README.md file.

	 **************************************************************************************************************/

	// Step 1 : include the main file, CL.php
	require ( '../examples.inc.php' ) ;

	// Step 2 : Define the specifications of your command line
	$definitions 	=  <<<END
<command>
	<usage>
		A script that displays the value of its -string_value argument.
	</usage>

	<string name="string_value, sv" default="default string value">
		A string value.
	</string>
</command>				
END;

	// Step 3 : Instantiate a CLParser object, providing the above definitions
	$cl 	=  new CLParser ( $definitions ) ;

	// Step 4 : Retrieve and use the supplied parameter values the way you like
	echo "The value of the string_value parameter is : " . $cl -> string_value . "\n" ;

