<?php
	/***
		This example defines a command line accepting two parameters, -string_value and -integer_value.
		The -integer_value parameter is set as "hidden", so you won't see it when invoking the script with 
		the -help reserved parameter. It has a default value of 1.
		You will be able to see the -integer_value help text if you add the --hidden extra parameter with
		-help when running the script, eg :

			php hidden.php -help --hidden
	 ***/
	require_once ( dirname ( __FILE__ ) . '/../examples.inc.php' ) ;

	$definitions	=  <<<END
<command>
	<usage>
		Example script that demonstrates the usage of the "hidden" attribute.
		If you run :

			\$ php hidden.php -help

		you will only see one parameter, -string_value.
		If you add the --hidden special parameter, then you will also see the -interger_value parameter :

			\$ php hidden.php -help --hidden
	</usage>

	<string name="string_value, sv" >
		Help for the string_value parameter.
	</string>
	<integer name="integer_value, iv" hidden="true" default="1">
		Help for the hidden integer_value parameter.
	</integer>
</command>
END;

	$cl		=  new CLParser ( $definitions ) ;
	echo "Value of the -string_value parameter  : {$cl -> string_value}\n" ;
	echo "Value of the -integer_value parameter : {$cl -> integer_value}\n" ;
