<?php
	/***
		This example demonstrates the usage of the "help-text" attribute.
		It defines two parameters : -string_value1, with a help-text attribute, and 
		-string_value2, whose help text is defined as tag contents.

		Simply specify the -help reserved parameter when runnning this script.
	 ***/
	require_once ( dirname ( __FILE__ ) . '/../examples.inc.php' ) ;

	$definitions	=  <<<END
<command>
	<usage>
		Example script that demonstrates the usage of the "help-text" attribute.
	</usage>

	<string name="string_value1, sv1" help-text="Help text for the -string_value1 parameter, specified with the help-text attribute">
		Help for the string_value parameter (will never be displayed because the "help-text" attribute has been specified).
	</string>

	<string name="string_value2, sv2">
		Help for the -string_value2 parameter.
	</string>
</command>
END;

	$cl		=  new CLParser ( $definitions ) ;
