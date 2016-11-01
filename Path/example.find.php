<?php
// Demonstrates the use of the Path::Find()  method
require ( 'Path.phpclass' ) ;

$dirname	=  dirname ( __FILE__ ) ;

if  ( php_sapi_name ( )  !=  'cli' )
	echo '<pre>' ;

echo ( "List of files in the current directory :\n" ) ;
$result		=  Path ::Find ( $dirname ) ;
print_r ( $result ) ;

echo ( "\n\n" ) ;
echo ( "Details of files in the current directory :\n" ) ;
$result		=  Path ::Find ( $dirname, null, FIND_OPTIONS_ALL ) ;
print_r ( $result ) ;
