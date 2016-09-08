<?php
	/***
		This example demonstrates the usage of the "arguments" attribute.
		It allows 1 up to 5 values to be specified for the -string_value parameter.

		An exception will be thrown if more than 5 values are specified.
	 ***/
	require_once ( dirname ( __FILE__ ) . '/../examples.inc.php' ) ;

	$definitions	=  <<<END
<command>
	<usage>
		This example demonstrates the usage of the "arguments" attribute.
		It allows 1 up to 5 values to be specified for the -string_value parameter.

		An exception will be thrown if more than 5 values are specified.
	</usage>

	<string name="string_value, sv" arguments="1..5">
		Help for the -string_value parameter.
	</string>
</command>
END;

	$cl		=  new CLParser ( $definitions ) ;
	echo "Values specified for the -string_value parameter : " ; print_r ( $cl -> string_value ) ;