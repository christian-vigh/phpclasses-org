<?php
// Demonstrates the use of the Path::RealPath()  method
require ( 'Path.phpclass' ) ;

$dirname	=  dirname ( __FILE__ ) ;

if  ( php_sapi_name ( )  !=  'cli' )
	echo '<pre>' ;

echo ( "Fullpath of file example.php : " . Path::RealPath ( 'Path.phpclass', true ) ) ;