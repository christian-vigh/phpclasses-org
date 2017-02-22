<?php
	/*************************************************************************************************

		This example demonstrates the use of the BitString and BitStringIterator classes on a
		simple example string, "ABCDEF", which will be considered as a string of bits.

	 *************************************************************************************************/

	require_once ( 'BitString.phpclass' ) ;

	if  ( php_sapi_name ( )  !=  'cli' )
		echo '<pre>' ;

	// getbits -
	//	This function simply outputs bits from the specified value, up to $count.
	function  getbits ( $value, $count )
	   {
		$result		=  '' ;

		for  ( $i = 0 ; $i  <  $count ; $i ++, $value >>= 1 )
			$result		.=  ( $value  &  1 ) ?  '1' : '0' ;

		return ( $result ) ;
	    }

	// Our example string
	$data	=  "ABCDEF" ;

	// Create a bit string from it
	$bs	=  new BitString ( $data ) ;

	// Try the ToHex() and ToBin() functions. Note that ToBin (0) will print the bits in exactly the same order
	// as they are stored (least significant bit first), while ToBin(1) will print them in the order they are 
	// printed by ToHex()
	echo "ToHex()                : "  ; print_r ( $bs -> ToHex ( ) )   ; echo "\n" ;
	echo "ToBin(1)               : " ; print_r ( $bs -> ToBin ( 1 ) ) ; echo "\n" ;
	echo "ToBin(0)               : " ; print_r ( $bs -> ToBin ( 0 ) ) ; echo "\n" ;

	// Use the ArrayAccess interface to retrieve and display each individual bit
	echo "Bit per bit (for)      : " ;

	for ( $i = 0 ; $i < count ( $bs ) ; $i ++ )
		echo $bs [$i] ;

	// Use the Iterator interface to display each individual bit
	echo "\nBit per bit (foreach)  : " ;

	foreach ( $bs  as  $bit )
		echo $bit ;

	// Now, use the BitString::GetBits() function to display bits by group of x bits, from 1 to 32.
	// Note that the getbits() function defined here will add trailing zeroes for bit counts that are
	// not a denominator of the machine's word size - this is not a bug, this inconvenience was aimed
	// at making this example simpler
	for  ( $bit_count = 1 ; $bit_count  <=  32 ; $bit_count ++ )
	   {
		echo sprintf ( "\nTaking by %2d bits      : ", $bit_count ) ;
		
		for  ( $i = 0 ; $i  <  count ( $bs ) ; $i += $bit_count )
		   {
			$value		=  $bs -> GetBits ( $i, $bit_count ) ;

			if  ( $value  ===  false )
				output ( "FALSE for offset $i, count $bit_count" ) ;

			echo getbits ( $value, $bit_count ) ;
		    }
	    }

	// Now use a BitStringIterator object : extract bits from the bit string by groups of
	// 3 components having 8 bits each. It should display the hexadecimal translation of
	// "ABCDEF" which is : "414243444546"
	echo "\nBitStringIterator(8,3) : " ;

	foreach  ( new BitStringIterator ( $bs, 8, 3 )  as  $values ) 
	   {
		foreach  ( $values  as  $value )
			echo ( sprintf ( "%02X", $value ) ) ;
	    }