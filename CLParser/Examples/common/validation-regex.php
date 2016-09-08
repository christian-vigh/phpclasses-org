<?php
	/***
		This example demonstrates the usage of the "validation-regex" attribute.
		It defines two parameters :
		- string_value1, which accepts a value containing only lowercase letters
		- string_value2, which accepts a value starting with a letter, followed by any number
		  of letters and digits
	 ***/
	require_once ( dirname ( __FILE__ ) . '/../examples.inc.php' ) ;

	$definitions	=  <<<END
<command>
	<usage>
		This example demonstrates the usage of the "validation-regex" attribute.
		It defines two parameters :
		- string_value1, which accepts a value containing only lowercase letters
		- string_value2, which accepts a value starting with a letter, followed by any number
		  of letters and digits
	</usage>

	<string name="string_value1, sv1" validation-regex="[a-z]+">
		A parameter that accepts values containing lowercase letters only.
	</string>

	<string name="string_value2, sv2" validation-regex="/[a-z][a-z0-9]*/ix">
		A parameter that accepts values containing letters and digits, and starting with a letter.
	</string>
</command>
END;

	$cl		=  new CLParser ( $definitions ) ;
	echo "Value of -string_value1 : {$cl -> string_value1}\n" ;
	echo "Value of -string_value2 : {$cl -> string_value2}\n" ;
