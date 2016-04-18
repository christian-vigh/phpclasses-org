<?php
	require ( '../Sources/ScreenshotLayer.php' ) ;

	$my_access_key		=  "YOUR_ACCESS_KEY" ;
	$my_secret_key		=  "YOUR_SECRET_KEY" ;		// Or null, if you defined no secret key in your dashboard
	$use_https		=  false ;

	$screenshot		=  new ScreenShotLayer ( $my_access_key, $my_secret_key, $use_https ) ;
	$screenshot -> Format	=  ScreenshotLayer::FORMAT_PNG ;
	$image			=  $screenshot -> CapturePage ( "http://www.google.com" ) ;
	file_put_contents ( 'google.png', $image ) ;