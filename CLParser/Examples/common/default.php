<?php
	/***
		This example defines a command line accepting one parameter, -string_value, with a default
		value of "hello world".

	 ***/
	require_once ( dirname ( __FILE__ ) . '/../examples.inc.php' ) ;

	$definitions	=  <<<END
<command>
	<usage>
		Example script that demonstrates the usage of the "default" attribute.
		Run it without any parameter ; the default value will be used :

			\$ php default.php

		Then with a value specified for the -string_value parameter :

			\$ php default.php -string_value user-specified
	</usage>

	<string name="string_value, sv" default="hello world">
		Help for the string_value parameter.
	</string>
</command>
END;

	$cl		=  new CLParser ( $definitions ) ;
	echo "Value of the -string_value parameter  : {$cl -> string_value}\n" ;
