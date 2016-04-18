<?php
	require ( '../Sources/PdfLayer.php' ) ;

	$my_access_key		=  "YOUR_ACCESS_KEY" ;
	$my_secret_key		=  "YOUR_SECRET_KEY" ;		// Or null, if you defined no secret key in your dashboard
	$use_https		=  false ;

	$pdf			=  new PdfLayer ( $my_access_key, $my_secret_key, $use_https ) ;
	$pdf -> AcceptLanguage	=  'fr' ;
	$pdf_data		=  $pdf -> ConvertPage ( "http://www.google.com" ) ;
	file_put_contents ( 'google.pdf', $pdf_data ) ;