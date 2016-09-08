<?php
	/***
		This example demonstrates the usage of help topics.
	 ***/
	require_once ( dirname ( __FILE__ ) . '/../examples.inc.php' ) ;

	$definitions	=  <<<END
<command>
	<topic name="strings, string, str">
		General help for parameters belonging to the "strings" topic.
	</topic>

	<topic name="integers, integer, int">
		General help for parameters belonging to the "integers" topic.
	</topic>


	<string name="string_value, sv" topic="strings">
		Help for the -string_value parameter.
	</string>

	<integer name="integer_value1, iv1" topic="integers">
		Help for the -integer_value1 parameter.
	</integer>

	<integer name="integer_value2, iv2" topic="integers">
		Help for the -integer_value2 parameter.
	</integer>
</command>
END;

	$cl		=  new CLParser ( $definitions ) ;
	
