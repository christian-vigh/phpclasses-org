<?php
	/***********************************************************************************************************
		
		The following example demonstrates how a variable store can be used together with the IniFile class
		to allow .INI files to reference variables.

		The example .INI file contains the following :

			[Variables]
			HOST		=  somehost
			DOMAIN		=  www.$(HOST).com
			FIRSTNAME	=  John
			LASTNAME	=  Smith

			[Settings]
			Hostname	=  $(DOMAIN)
			Username	=  $(FIRSTNAME) $(LASTNAME)

		We decided that the [Variables] section would be here to define variables, such as HOST, DOMAIN, etc.
		that could be used in every setting value defined in this .INI file. This is done by calling the
		IniFile::SetVariableStore() method (but we could have used any other source for that, such as an
		associative array of variable name/value pairs, an existing VariableStore object, your currently
		defined environment variables, or any combination of them).

	 ***********************************************************************************************************/
	require ( 'IniFile.class.php' ) ;

	if  ( php_sapi_name ( )  !=  'cli' )
		echo "<pre>" ;

	// Instantiate an IniFile object for file example.store.ini
	$inifile 	=  IniFile::LoadFromFile ( 'example.store.ini' ) ;

	// Say that all the settings defined in the [Variables] section are to be used for building a variable store
	$inifile -> SetVariableStore ( 'Variables' ) ;

	// Now call the GetKey() method to retrieve the values of the Hostname and Username keys in the [Settings] section
	// GetKey() will process any reference to existing variables with their value before returning the setting contents
	echo "Hostname = " . $inifile -> GetKey ( 'Settings', 'Hostname' ) . "\n" ;
	echo "Username = " . $inifile -> GetKey ( 'Settings', 'Username' ) . "\n" ;

