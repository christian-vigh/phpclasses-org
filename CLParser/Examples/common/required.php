<?php
	/***
		This example defines a command line accepting one REQUIRED parameter, -string_value.

		Note that you reserved parameters such as -help are processed first, so it's not necessary
		to supply required values for displaying command-line help ; thus, the following will not
		throw an exception but simply display command-line help :

			$ php required.php -help
	 ***/
	require_once ( dirname ( __FILE__ ) . '/../examples.inc.php' ) ;

	$definitions	=  <<<END
<command>
	<usage>
		Example script that demonstrates the usage of the "required" attribute.
		Run it without any parameter ; An exception will be thrown because the -string_value parameter
		is required :

			\$ php required.php

		Then with a value specified for the -string_value parameter :

			\$ php default.php -string_value "hello world"
	</usage>

	<string name="string_value, sv" required="true">
		Help for the string_value parameter.
	</string>
</command>
END;

	$cl		=  new CLParser ( $definitions ) ;
	echo "Value of the -string_value parameter  : {$cl -> string_value}\n" ;
