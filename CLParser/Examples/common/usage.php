<?php
	/***
		This example demonstrates the usage of the "usage" attribute.
		It redefines the usage string for the -string_value parameter.

		Simply specify the -help reserved parameter when runnning this script.
	 ***/
	require_once ( dirname ( __FILE__ ) . '/../examples.inc.php' ) ;

	$definitions	=  <<<END
<command>
	<usage>
		Example script that demonstrates the usage of the "usage" attribute.
	</usage>

	<string name="string_value, sv" usage="-string_value keyword">
		Help for the -string_value parameter.
	</string>
</command>
END;

	$cl		=  new CLParser ( $definitions ) ;
