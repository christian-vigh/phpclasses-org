<?php
	/***
		This example defines a command line accepting one parameter, -string_value, having the 
		"multiple" attribute set to true, so that you can specify it multiple times.
	 ***/
	require_once ( dirname ( __FILE__ ) . '/../examples.inc.php' ) ;

	$definitions	=  <<<END
<command>
	<usage>
		This example defines a command line accepting one parameter, -string_value, having the 
		"multiple" attribute set to true, so that you can specify it multiple times.

		Example usage :

		$ php multiple.php -sv string1 -sv string2
	</usage>

	<string name="string_value, sv" multiple="true" default="*** no value specified ***">
		Help for the string_value parameter.
	</string>
</command>
END;

	$cl		=  new CLParser ( $definitions ) ;
	echo "Value(s) of the -string_value parameter  : " ; print_r ( $cl -> string_value ) ; echo "\n" ;
