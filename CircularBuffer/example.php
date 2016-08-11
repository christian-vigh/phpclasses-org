<?php
	/***********************************************************************************************************

		The following example demonstrates the use of the CircularBuffer class.

		It first creates a circular buffer for handling 10 items, then adds 20 items to it.
		It finally displays the contents of the circular buffer, which contains only items #11 to #20 (the
		items #1 to #10 have been overridden).

	 ***********************************************************************************************************/
	require ( 'CircularBuffer.phpclass' ) ;

	if  ( php_sapi_name ( )  !=  'cli' )
		echo ( "<pre>" ) ;

	// Create a circular buffer of 10 elements
	$buffer		=  new CircularBuffer ( 10 ) ;

	// Fill it with 20 items
	for  ( $i = 0 ; $i  <  20 ; $i ++ )
		$buffer []	=  "Item #" . ( $i + 1 ) ;

	// Print the items contained in the circular buffer (this will show the string "Item #11" through "Item #20"
	echo "*** Contents of the circular buffer :\n" ;

	foreach  ( $buffer  as  $item ) 
		echo "\t$item\n" ;

	echo "*** Number of items in the circular buffer : " . count ( $buffer ) . "\n" ;
	echo "*** Contents of the 1st circular buffer item : {$buffer [0]}\n" ;