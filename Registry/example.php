<?php
/***
 *	This sample script :
 *	1) Enumerates all the keys under HKCU\Software and prints them
 *	2) Creates the HKCU\TestRegistry\Values key
 *	3) Creates/updates the following keys (using WMI API calls) :
 *		a) A binary value :
 *			1. using a string
 *			2. using a byte array
 *			3. using a hex string
 *		b) A dword value
 *		c) An expanded string value
 *		d) A multi-string value :
 *			1. With only one string
 *			2. With multiple strings
 *		e) A qword value
 *		f) A string value
 *	4) Creates/updates the following keys (using WShell Registry calls) :
 *		a) A string value
 *		b) An expanded string value
 *		c) A DWORD value
 *	5) Enumerates all the values in the HKCU\TestRegistry\Values key
 *	6) Enumerates all values, using RegistryValue objects
 *	7) Retrieves created key values
 *	8) Deletes a key
 *	9) Tries to delete the created subkey (HKCU\TestRegistry)
 *		
 *	The check_error() function defined in this script is called after each registry method invocation
 *	and prints an error message if the function call failed.
 *	
 ***/

include ( 'Registry.class.php' ) ;

if  ( php_sapi_name ( )  ==  'cli' )
    {
	$eol	=  PHP_EOL ;
	$tab	=  "\t" ;
     }
else
    {
	$eol	=  "<br/>" ;
	$tab	=  str_repeat ( "&nbsp;", 8 ) ;
     }


// check_error -
//	Checks that the last called registry method succeded.
function  check_error ( )
   {
	global		$registry, $tab, $eol ;

	$status		=  $registry -> GetLastError ( ) ;

	if  ( $status )
	   {
		echo ( "Last registry call failed, status = 0x" . sprintf ( "%08X", $status ) . $eol ) ;
		exit ;
	    }
    }

// 0) Create the registry object
$registry	=  new Registry ( ) ;

// 1) Enumerate all the keys under HKCU\Software and print them
$keys	=  $registry -> EnumKeys ( Registry::HKCU, 'Software' ) ;
check_error ( ) ;

echo ( "Registry keys for HKCU\\Software :\n" ) ;

foreach ( $keys  as  $key )
	echo ( "$tab$key$eol" ) ;

// 2) Create the HKCU\TestRegistry\Values key
$test_key	=  'TestRegistry/Values' ;		// Slashes are replaced by backslashes
$registry -> CreateKey ( Registry::HKCU, $test_key ) ;
check_error ( ) ;

// 3.a.1) Create the HKCU\TestRegistry\Values\BinaryValueFromString key
$registry -> SetBinaryValue ( Registry::HKCU, $test_key, 'BinaryValueFromString', 'ABCDEF' ) ;
check_error ( ) ;

// 3.a.2) Create the HKCU\TestRegistry\Values\BinaryValueFromrArray key (set to "ABC")
$registry -> SetBinaryValue ( Registry::HKCU, $test_key, 'BinaryValueFromArray', [ 65, 66, 67 ] ) ;
check_error ( ) ;

// 3.a.3) Create the HKCU\TestRegistry\Values\BinaryValueFromrHexString key (set to "0123456789ABCDEF")
$registry -> SetBinaryValueFromHexString ( Registry::HKCU, $test_key, 'BinaryValueFromHexString', "0123456789ABCDEF" ) ;
check_error ( ) ;

// 3.b) Create the HKCU\TestRegistry\Values\DWORDValue key (set to 0x01020304)
$registry -> SetDWORDValue ( Registry::HKCU, $test_key, 'DWORDValue', 0x01020304 ) ;
check_error ( ) ;

// 3.c) Create the HKCU\TestRegistry\Values\ExpandedStringValue key, referencing the %WINDIR% environment variable
$registry -> SetExpandedStringValue ( Registry::HKCU, $test_key, 'ExpandedStringValue', '%WINDIR%\Something' ) ;
check_error ( ) ;

// 3.d.1) Create the HKCU\TestRegistry\Values\MultiStringValueSingle key
$registry -> SetMultiStringValue ( Registry::HKCU, $test_key, 'MultiStringValueSingle', 'A sample string' ) ;
check_error ( ) ;

// 3.d.2) Create the HKCU\TestRegistry\Values\MultiStringValueMultiple key
$registry -> SetMultiStringValue ( Registry::HKCU, $test_key, 'MultiStringValueMultiple', 
		[ 'Sample 1', 'Sample 2', 'Sample 3' ] ) ;
check_error ( ) ;

// 3.e) Create the HKCU\TestRegistry\Values\QWORDValue key (one greater than PHP_INT_MAX, and the other equal to 1)
$registry -> SetQWORDValue ( Registry::HKCU, $test_key, 'QWORDBigValue', PHP_INT_MAX * 10 ) ;
check_error ( ) ;

$registry -> SetQWORDValue ( Registry::HKCU, $test_key, 'QWORDValue', 1 ) ;
check_error ( ) ;

// 3.f) Create the HKCU\TestRegistry\Values\StringValue key
$registry -> SetStringValue ( Registry::HKCU, $test_key, 'StringValue', 'this is a sample string value' ) ;
check_error ( ) ;

// 4.a) Create the HKCU\TestRegistry\Values\WShellStringValue key
$registry -> SetValue ( Registry::HKCU, $test_key, 'WShellStringValue', 'this is a sample WSHELL string value', Registry::REG_SZ ) ;
check_error ( ) ;

// 4.b) Create the HKCU\TestRegistry\Values\WShellExpandedStringValue key
$registry -> SetValue ( Registry::HKCU, $test_key, 'WShellExpandedStringValue', 'this is a sample WSHELL expanded string value, WINDIR = %WINDIR%', 
				Registry::REG_EXPAND_SZ ) ;
check_error ( ) ;

// 4.c) Create the HKCU\TestRegistry\Values\WShellDWORDValue key
$registry -> SetValue ( Registry::HKCU, $test_key, 'WShellDWORDValue', 0x01020304, 
				Registry::REG_DWORD ) ;
check_error ( ) ;

// 5) Enumerate all the values in the HKCU\TestRegistry\Values key with their type
$keys	=  $registry -> EnumValues ( Registry::HKCU, $test_key, false ) ;
check_error ( ) ;

echo ( "Created values :$eol" ) ;

foreach  ( $keys  as  $key => $type )
	echo ( "$tab$key ($type)$eol" ) ;

// 6) Enumerate all the values in the HKCU\TestRegistry\Values key, using registry value objects
$keys	=  $registry -> EnumValuesEx ( Registry::HKCU, $test_key ) ;
check_error ( ) ;

echo ( "Created RegistryValue objects :$eol" ) ;

foreach  ( $keys  as  $value )
	echo ( "$tab" . str_replace ( "$eol", "$eol$tab", print_r ( $value, true ) ) ) ;

// 7) Retrieve created key values - You must known the type of each key before doing that, or else use the GetValue() method,
//    which works only on REG_SZ, REG_EXPAND_SZ, REG_DWORD and REG_BINARY types.
echo ( "Created values :$eol" ) ;

echo ( "$tab$test_key/BinaryValueFromArray     : " . $registry -> GetBinaryValue ( Registry::HKCU, $test_key, 'BinaryValueFromArray' ) . $eol ) ;
echo ( "$tab$test_key/BinaryValueFromHexString : " . $registry -> GetBinaryValue ( Registry::HKCU, $test_key, 'BinaryValueFromHexString' ) . $eol ) ;
echo ( "$tab$test_key/BinaryValueFromString    : " . $registry -> GetBinaryValue ( Registry::HKCU, $test_key, 'BinaryValueFromString' ) . $eol ) ;
echo ( "$tab$test_key/DWORDValue               : 0x" . sprintf ( "%08X", $registry -> GetDWORDValue ( Registry::HKCU, $test_key, 'DWORDValue' ) ) . $eol ) ;
echo ( "$tab$test_key/ExpandedStringValue      : " . $registry -> GetExpandedStringValue ( Registry::HKCU, $test_key, 'ExpandedStringValue' ) . $eol ) ;
echo ( "$tab$test_key/BinaryValueFromHexString : " . $registry -> GetBinaryValue ( Registry::HKCU, $test_key, 'BinaryValueFromHexString' ) . $eol ) ;
echo ( "$tab$test_key/MultiStringValueMultiple : " . implode ( ', ', $registry -> GetMultiStringValue ( Registry::HKCU, $test_key, 'MultiStringValueMultiple' ) ) . $eol ) ;
echo ( "$tab$test_key/MultiStringValueSingle   : " . implode ( ', ', $registry -> GetMultiStringValue ( Registry::HKCU, $test_key, 'MultiStringValueSingle' ) ) . $eol ) ;
echo ( "$tab$test_key/MultiStringValueMultiple : " . implode ( ', ', $registry -> GetMultiStringValue ( Registry::HKCU, $test_key, 'MultiStringValueMultiple' ) ) . $eol ) ;
echo ( "$tab$test_key/QWORDBigValue            : " . $registry -> GetQWORDValue ( Registry::HKCU, $test_key, 'QWORDBigValue' ) . $eol ) ;
echo ( "$tab$test_key/QWORDValue               : " . $registry -> GetQWORDValue ( Registry::HKCU, $test_key, 'QWORDValue' ) . $eol ) ;
echo ( "$tab$test_key/StringValue              : " . $registry -> GetQWORDValue ( Registry::HKCU, $test_key, 'StringValue' ) . $eol ) ;
echo ( "$tab$test_key/WShellDWORDValue         : 0x" . sprintf ( "%08X", $registry -> GetValue ( Registry::HKCU, $test_key, 'WShellDWORDValue' ) ) . $eol ) ;

// Note that the GetValue() method for WShell does not seem to process variable expansion
echo ( "$tab$test_key/WShellExpandedStringValue: " . $registry -> GetValue ( Registry::HKCU, $test_key, 'WShellExpandedStringValue' ) . $eol ) ;
echo ( "$tab$test_key/WShellStringValue        : " . $registry -> GetValue ( Registry::HKCU, $test_key, 'WShellStringValue' ) . $eol ) ;

// 8) Delete a subkey (HKCU\TestRegistry\Values\WShellExpandedStringValue key)
$registry -> DeleteValue ( Registry::HKCU, $test_key, 'WShellExpandedStringValue' ) ;
check_error ( ) ;

// 9) Delete the test subkey - leads to an error, since a key can only be deleted if it has no subkeys - so you'll have to delete TestRegistry manually under HKCU
$registry -> DeleteKey ( Registry::HKCU, 'TestRegistry' ) ;
check_error ( ) ;
