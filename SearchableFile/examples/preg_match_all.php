<?php
	include ( '../SearchableFile.phpclass' ) ;
	$file 		=  'verybigfile.rtf' ;
	$re 		=  '/\\\\pict/' ;

	$t1		=  microtime ( true ) ;
	
	$sf 		=  new SearchableFile ( ) ;
	$sf -> Open ( $file ) ;

	$offset 	=  0 ;
	$status1 	= $sf -> pcre_match_all ( $re, $matches1, PREG_OFFSET_CAPTURE, $offset ) ;
	$count1 	=  count ( $matches1 ) ;
	
	$t2 		=  microtime ( true ) ;
	
	$contents 	=  file_get_contents ( $file ) ;
	$offset 	=  0 ;
	$status2 	=  preg_match_all ( $re, $contents, $matches2, PREG_OFFSET_CAPTURE, $offset ) ;
	$count2 	=  count ( $matches1 ) ;

	$t3 		=  microtime ( true ) ;
	
	echo "Elapsed (SearchableFile) : " . round ( $t2 - $t1, 3 ) . " (count = $count1)\n" ;
	echo "Elapsed (preg_match_all) : " . round ( $t3 - $t2, 3 ) . " (count = $count2)\n" ;
