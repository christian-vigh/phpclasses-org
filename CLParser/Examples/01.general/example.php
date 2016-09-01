#!/usr/bin/php
<?php
/**************************************************************************************************************

	The following example accepts the following arguments and displays their values :
	- A flag named 'boolean_flag', which also has the 'bf' alias
	- An optional string parameter, named 'string_parameter' (or 'sp') with a default value of 
	  "this is the default value".
	- An unlimiter number of strings or filenames.

	Since no "name=" attribute is specified on the <command> tag of the parameter definitions, it will be
	set to the name of the current script, without its extension.

	Note also that the usage text contains some PHP code, which displays the current date and time.

	You can invoke the script using the reserved parameters -help, -usage or -topics ; but you can also
	specify the special parameters beginning with "--".

 **************************************************************************************************************/

require_once ( dirname ( __FILE__ ) . '/../examples.inc.php' ) ;

$definitions	=  <<<END
	<command allow-files="true" min-files="0">

		<usage>
			This is an example script which allows an unlimited number of filenames to be specified, 
			a flag named -boolean_flag, and a string parameter named -string_parameter.
			The current date is : <?= date ( 'Y-m-d H:i:s' ) ; ?>.
		</usage>

		<flag name="boolean_flag, bf">
			A boolean flag.
		</flag>

		<double name="double_value, dv">
			A double (float) value.
		</double>

		<string name="string_parameter, sp" default="This is the default value">
			A string parameter.
		</string>
			
	</command>
END;

// Instantiate a command-line parser object
$CL	=  new CLParser ( $definitions ) ;

// Get the specified parameters
$bf	=  $CL -> boolean_flag ;		// $CL -> bf will also work
$dv	=  $CL -> double_value ;
$sp	=  $CL -> string_parameter ;
$files  =  $CL -> Files ;

output ( "Value of -boolean_flag              : $bf" ) ;
output ( "Value of -string_parameter          : $sp" ) ;
output ( "Value of -double_parameter          : $dv" ) ;
output ( "Files specified on the command line : " . implode ( ', ', $files ) ) ;