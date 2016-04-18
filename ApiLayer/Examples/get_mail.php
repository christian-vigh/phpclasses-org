<?php
	require ( '../Sources/MailboxLayer.php' ) ;

	$my_access_key		=  "YOUR_ACCESS_KEY" ;
	$use_https		=  false ;

	$mail			=  new MailboxLayer ( $my_access_key, $use_https ) ;
	$mail -> CatchAll	=  true ;
	$mail_data		=  $mail -> GetEmail ( "christian.vigh@orange.fr" ) ;
	print_r ( $mail_data ) ;