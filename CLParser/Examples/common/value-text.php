<?php
	/***
		This example demonstrates the usage of the "value-text" attribute.
		It redefines the text shown for the type of value of the -string_value parameter.

		Simply specify the -help reserved parameter when runnning this script.
	 ***/
	require_once ( dirname ( __FILE__ ) . '/../examples.inc.php' ) ;

	$definitions	=  <<<END
<command>
	<usage>
		Example script that demonstrates the usage of the "value-text" attribute.
	</usage>

	<string name="string_value, sv" value-text="english noun">
		Help for the -string_value parameter. Provide an English noun as the parameter value.
	</string>
</command>
END;

	$cl		=  new CLParser ( $definitions ) ;
