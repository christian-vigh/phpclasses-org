# INTRODUCTION #

## WHAT IS THE PURPOSE OF THIS CLASS ? ##

The IniFile class provides support for both reading, updating and writing back .ini files. 

## A SHORT REMINDER ON .INI FILES ##

The .ini file format is maybe one of the simplest and easiest to parse file formats, when there is a need to store (not too complicated) application settings. This has been the preferred choice for the PHP engine (which stores its settings in the *php.ini* file), as well as it was on Windows platforms before the invention of the Registry and DotNet application settings.

The format is really straightforward : parameters (which are called *keys* in this document) are grouped within *sections*, whose names are enclosed in square brackets. After the section name come parameter definitions, which are a list of key/value pairs separated by an equal sign ("=") belonging to that section. For example, the following defines a section named **Network**, containing two parameters, **Listen** and **Port** :

	[Network]
	Listen 		=  127.0.0.0
	Port 		=  9999

Parameter values are not typed ; there are just plain text that must be interpreted by the application. 

Parameters defined before the very first section name in the .ini file are put in a section whose name is the empty string.

## WHAT CAN THE INIFILE CLASS DO FOR YOU ? ##

The IniFile class can :

- Read .ini files into memory
- Allow .ini file setting values to reference variables defined in a variable store (see [http://www.phpclasses.org/package/10048-PHP-A-class-to-store-variable-names-and-values-and-pr.html](http://www.phpclasses.org/package/10048-PHP-A-class-to-store-variable-names-and-values-and-pr.html "http://www.phpclasses.org/package/10048-PHP-A-class-to-store-variable-names-and-values-and-pr.html")
- Retrieve the list of sections defined in the .ini file
- Retrieve the keys defined within a section
- Retrieve key values
- Define new sections
- Change existing key values
- Define new keys 
- Delete existing keys or sections
- Rename keys or sections
- Write back the modified file

## WHY YET-ANOTHER-INIFILE PROCESSOR ? ##

PHP provides reasonable support for reading .ini files using the **ini\_get()**, **ini\_set()**, **parse\_ini\_file()** and **parse\_ini\_string()** functions.

However it completely lacks of support for modifying them on the fly and writing them back. Of course, you could use xml files for that, or store your settings in a Mysql table. But the .ini file format comes naturally when you need to store simple settings and document them in a human-readable format, as this is the case for *php.ini*.

The other key feature is that the **IniFile** class preserve your comments and formatting when writing the contents back. You wouldn't want to see your comments garbled when you write back the contents of *php.ini*, isn't it ?    


# .INI FILE SYNTAX #

The IniFile class can process .ini files whose syntax is commonly defined on Windows platforms (and in the php.ini file...). It also provides support for extended notations which are described below. 

## BASIC SYNTAX ##
The basic syntax follows the Windows specifications :

- Entries are defined by key/value pairs separated by an equal sign ("=").
- Spaces are not significant between a key name and the equal sign
- Key/value pairs can be grouped in sections, whose names are enclosed in square brackets (eg, "[MySection]")
- Comments are introduced by a semicolon
- Key/value pairs found BEFORE the first section name are put in a section with an empty name

Section and key names are not case-sensitive.

## EXTENDED SYNTAX ##
The IniFile class accepts extended syntax with regards to the Windows implementation. The new syntactic items are described below. Note that this is a superset of the basic syntax, and cannot be disabled.

### COMMENTS ###
Comments can be either single-line or multiline :

- Single line comments are specified either by : a semicolon ";" (.ini file style), a sharp sign "#" (shell style) or a double-slash "//" (C++ style)
- Multiline comments are C-style : they start with the string "/\*" and end with "\*/". Note that nested multiline comments are authorized

### SPECIFYING A KEY AND A VALUE ###
Basically, a key/value pair is specified like this :

	[MySection]
    MySetting = setting value

The key name can include spaces :

	[MySection]
	My Setting = setting value

Note however that the key "MySetting" will be different from "My Setting".

Empty values can be specified in two ways :

	[MySection]
	MySetting =
	MySetting

If you specify the same key twice in the same section, the second value will override the first one ; thus, the value of MySetting in the following example :

	[MySection]
	MySetting 	=  setting value 1
	MySetting 	=  setting value 2

will be "setting value 2". Note however that if you programmatically modify the value of "MySetting", there is no guarantee on which occurrence of "MySetting" will be actually modified.

Finally, multiline values can be specified as here-documents by adding the "<<" string after the equal sign :

	[MySection]
	MySetting1 	= <<
	this is the
	multiline value
	of mysetting1
	END

A keyword can be specified after the string "<<" :

	[MySection]
	MySetting1 	= <<STOP
	this is the
	multiline value
	of mysetting1
	STOP

The "END" keyword is expected when no keyword is specified after the here-document string.

The "<<<" string (as for PHP) can also be specified instead of "<<".

Spaces around the here-document string are ignored.

Note however that the end keyword must start at the beginning of the line ; no leading spaces are allowed.

### EXAMPLE .INI FILE ###
The following example shows a .ini file using all the extended features of the IniFile class :

	/***
		This .ini file gives examples on the extended syntax supported by the IniFile class.
	
		/* This is a nested multiline comment */
	 ***/

	; A comment
	# Another comment
	// and yet another comment

	[Variables]
	Root 				=  /

	[Settings]
	Display 			=  1
	EmptyValue1			=
	EmptyValue2
	Key with spaces 	=  xxxx
	Heredoc1			=  <<<
		contents of
		heredoc1
	END
	Heredoc2 			=  <<STOP
		contents
		of
		heredoc2
	STOP

### DESIGN ISSUES ###
- Spaces before a key name, before and after the equal sign and after the value are not significant.
- Spaces around a section name (between the square brackets) are not signification ; so both *[   MySection   ]* and *[MySection]* refer to a section named "MySection".
- I have decided that absolutely anything AFTER the equal sign would be part of the key value ; for that reason, you cannot specify a single-line comment on the same line than a key/value pair. If you aim to put a comment after a key value, it will be considered as part of the value.
- Flexibility has been priviledged over performance ; so, if you plan to use this class on .ini files that contain thousands of lines, consider other alternatives instead.

# SAMPLE USAGE #

An example script will read the settings in the following file, *example.ini*, update the *LastUpdate* entry of the *[Results]* section then add the *Status* key :

	/***
			example.ini - 
				Contains the settings for the example.php file.
	 ***/

	# Network settings
	[Network]
	; On which address to listen to ?
	Listen 	=  127.0.0.0
	; Which port ?
	Port 	=  9999

	[Results]
	LastUpdate 	=  2015/01/01 17:40:00

Here is the *example.php* script :

	<?php
		require ( 'IniFile.class.php' ) ;

		// Instanciate an IniFile object for file example.ini
		$inifile 	=  IniFile::LoadFromFile ( 'example.ini' ) ;

		// Get the value of the Listen and Port parameters in the [Network] section
		// Note that you can specify a default value if the parameter is not defined
		$listen 	=  $inifile -> GetKey ( 'Network', 'Listen', '127.0.0.0' ) ;
		$port 		=  $inifile -> GetKey ( 'Network', 'Port' ) ;


		// ... do some processing 

		// Processing done : update the LastUpdate parameter of the [Results] section then
		// add the Status parameter
		$inifile -> SetKey ( 'Results', 'LastUpdate', date ( 'Y/m/d H:i:s' ) ) ;
		$inifile -> SetKey ( 'Results', 'Status', 0 ) ;

		// Write the results back
		$inifile -> Save ( ) ;

# API #

The following sections list the IniFile API by category.

## LOADING IN-MEMORY .INI FILES ##

You can create an empty .INI file object by using the new operator ; however, if you  have existing contents to be loaded, use one of these three static functions. It will create a IniFile object, load the contents you specified, and return a reference to the object :

	    	$inifile = IniFile::LoadFromArray  ( $array, $separator = '=' ) ;
	    	$inifile = IniFile::LoadFromFile   ( $file, $load_option = IniFile::LOAD_ANY, $separator = '=' ) ;
	    	$inifile = IniFile::LoadFromString ( $string, $separator = '=' ) ;

Each of these functions accept a parameter, *$separator*, which is the character to be used for separating key/value pairs.

The **LoadFromFile** method is used to load .ini file contents from the specified *$file*. The *$load_option* parameter can be one of the following :

- *IniFile::LOAD_ANY* : The specified file is loaded. It will be created if it does not exist
- *IniFile::LOAD_NEW* : The specified file will be created empty, and will be overridden if it already exists.
- *IniFile::LOAD_EXISTING* : The specified file will be loaded. An exception will be thrown if it does not exist.

The **LoadFromString** method is used to load .ini file contents directly from a string.
**LoadFromArray** can be used for loading .ini file contents from an array of lines.

## SAVING .INI FILES ##

The following method is used to save .ini contents :

	$inifile -> Save ( $forced = false, $file = null ) ;

The *$forced* parameter determines the conditions for saving. Normally, the .INI file is saved if and only if the dirty flag is set (the dirty flag is set when you create/update/delete keys or sections). You can override this behavior and perform a forced save whatever the initial value of the dirty flag is, by setting this parameter to true.

*$file* is optional if the IniFile instance was created using the **LoadFromFile()** method. However it will be required if :

- The instance was created using the **LoadFromFile()** method and you want to save it to a different path
- The instance was created using either the **new** operator, the **LoadFromString()** or **LoadFromArray()** methods, and the *File* property was not set by the caller. 

## MANIPULATING KEYS ##

### $value = $inifile -> GetKey ( $section, $key, $default = null ) ; ###

Returns a reference to the specified key *$key* in section *$section*.

If the key does not exist, the default value *$default* will be returned.

Because a reference is returned, you can directly modify the key value :

	$value 	=  $inifile -> GetKey ( 'Network', 'Port' ) ;
	(...)
	$value 	=  998 ;

There are a certain number of drawbacks to this approach that make it safer to use the **SetKey()** method :

- The *dirty* flag won't be set, since the IniFile class has no way to know that you modified something.
- If the key was not defined, the reference will point to the *$default* parameter, not to a real .ini file parameter value. So, again, the IniFile class has no way to know whether you modified the value or not.

###  $inifile -> SetKey ( $section, $key, $value, $comment\_before = null, $comment\_after = null ) ;   ###

Creates or updates the key *$key* in section *$section* with the specified *$value*.
You can tell the IniFile class to insert comments before and after the key definition by specifying the *$comment\_before* and *$comment\_after* parameters.

### $inifile -> RemoveKey ( $section, $key, $clear\_comment\_before = true ) ; ###

Removes the key *$key* from section *$section*. By default, comments that are present before the key definition are considered to document the definition itself and will be removed. If you want to override this behavior and keep the potential comments before the key definition, simply set the *$clear\_comment\_before* parameter to *true*.

Returns *true* if the key was defined and *false* otherwise.

### $status = $inifile -> RenameKey ( $section, $old, $new ) ; ###

Renames a key contained in the specified section, from *$old* to *$new*.

This function returns true if the operation was successful, or false if one of the following conditions occurred :

- The section specified by *$section* does not exist
- The key specified by *$old* does not exist
- The key specified by *$new* already exist

In this case, the key will not be renamed.

###  $inifile -> ClearKey ( $section, $key ) ; ###

Clears a key value. This is the equivalent of calling :

	$inifile -> SetKey ( $section, $key, "" ) ;

Returns true if the key exists in the specified section, false otherwise.

### $status = $inifile -> IsKeyDefined ( $section, $key ) ; ###

Returns *true* if the key *$key* is defined in section *$section*.

## MANIPULATING SECTIONS ##

### $sections = $inifile -> GetSections ( $regex = null ) ; ###

Returns the list of section names defined in the .ini file.

If a regular expression is specified for the *$regex* parameter, then the return value will be an associative array containing the following entries :

- *name* : Full section name
- *match* : The result of the regular expression match.

Don't specify any anchor or delimiter in the input string since the regular expression will be replaced by the following string :
	  
	 			/^ \s* $regex \s* $/imsx

Why such a feature ? suppose your .ini file contains a list of sections that *start* with a certain string :

	[Connection #1]
	Host 	=  www.somehost.com

	[Connection #2]
	Host 	=  www.anotherhost.com

	...

	[Connection #n]
	Host 	=  www.nthhost.com

You can retrieve their names like this :

	$sections 	=  $inifile -> GetSection ( 'Connection \s* # (?P<id> \d+)' ;

On output, each element of the *$sections* array will contain :

- A *name* entry (eg, "Connection #1")
- A *match* entry, resulting from the call to the **preg_match()** function, that will contain any named capture that you supplied (in the above example, the *'id'* element will contain the number right after the "#' sign).

The function returns an empty array if a pattern was specified but no section was found matching this pattern.

### $keys = $inifile -> GetKeys ( $section, $keys\_by\_reference = true, $regex = null ) ; ###

Gets the key names/values defined the specified section. Returns an associative array whose keys are the .ini key names and whose values are the .ini key values.

If the *$keys\_by\_reference* parameter is set to *true*, the returned values will be references to the actual ones, so that you can directly modify them without calling the **SetKey()** method.

An optional regular expression can be specified to filter out the key names. Unlike the **GetSections()** method, it must be an expression accepted by the **preg_match()** function and it does not modify the shape of the returned result.

### $status = $inifile -> IsSectionDefined ( $section ) ; ###

Returns true if the specified section is defined.


### $inifile -> RemoveSection ( $section, $clear\_comment\_before = true ) ; ###

Removes the specified section from the .ini file, together with its contents. This method is a little bit different from the **ClearSection()** method, since it also removes the section name from the .ini file.

If comments are present before the section definition, they are considered by default to belong to the section and will also be removed, unless the *$clear\_comment\_before* is set to false.

### $inifile -> RenameSection ( $old, $new ) ; ###

Renames the section *$old* to *$new*.

The function returns true if the operation was successful, and false if one of the following conditions occurs :

- The section specified by *$old* does not exist
- The section specified by *$new* already exists

In this case, the section will not be renamed.

### $status = $inifile -> ClearSection ( $section ) ; ###

Clears a section contents, without removing the section name from the .INI file.

Returns true if the section exists, false otherwise.

### $result = $inifile -> GetAllKeys ( ) ; ###

Returns all the sections and corresponding keys defined in the .INI file.

The function returns an associative array corresponding to the sections defined in the .INI file ; the value of each item is itself an associative array whoses keys are key names and whose values are references to the actual key value.

For example, given the following .INI file :

	;---------------------------------------------
	Global = 1

	[General]
	Save = true
	Upload = false
	;---------------------------------------------

the function will return :

	$result = array (
		"" => array ( 'Global' => 1 ),
		"General" => array ( 'Save' => true, 'Upload' => false )
    )

Since the key values are references to the actual value, you can directly modify a key's contents, as in the following example :

	$result [ 'General' ][ 'Save' ] = false ;

instead of calling :

	$inifile -> SetKey ( 'General', 'Save', false ) ;

Note however that in the first case, multiline values will not be correctly handled, so the direct modification of a value should only be used for single-line values.

If you don't want to bother with single- or multi-line issues, simply call the **SetKey()** method.


## MISC PROPERTIES & METHODS ##

### PROPERTIES ###

#### File property : ####

This property is used by the **Save()** method, when no filename is specified, as the output path for the .ini contents to be saved.

#### Separator property :####

This property determines the string used to separate key names from their values. The default is the equal sign ("=").

### METHODS ###

#### $contents = $inifile -> AsString ( ) ####

Returns the .ini file contents as a string.

The following are equivalent :

	$contents 	=  $inifile -> AsString ( ) ;
	$contents 	=  ( string ) $inifile ;

#### $status = $inifile -> IsDirty ( ) ####

Returns true if the in-memory .ini file contents have been modified, false otherwise.

Note that the IniFile class has no way to tell whether you modified key values using references or not. If you want to rely on the **IsDirty()** method, then use the **SetKey()** method to assign new values to keys.

### $inifile -> SetVariableStore ( [$section | $store | $array | $options]... ) ###

Defines a variable store for value expansion using the specified parameters (see the **VariableStore** class here : [http://www.phpclasses.org/package/10048-PHP-A-class-to-store-variable-names-and-values-and-pr.html](http://www.phpclasses.org/package/10048-PHP-A-class-to-store-variable-names-and-values-and-pr.html "http://www.phpclasses.org/package/10048-PHP-A-class-to-store-variable-names-and-values-and-pr.html")).

The parameter list is highly polymorphic and can contain any combination of the	following values, which are only named for convenience purpose :

- *$store* (VariableStore object) : 	A variable store object that contains variable definitions.

- *$section* (string) : 	The name of a section in this .ini file that contains variable definitions.

- *$array* (array or AssociativeArray) : An associative array of variable name/value pairs.

- *$options* (integer) : Options to use for the VariableStore object creation. The default value for 	this parameter is VariableStore::OPTION\_DEFAULT.

Variables specified in multiple *$section*, *$array* and *$store* parameters are merged into the final variable store object. Variables with the same name will be overridden.

Multiple *$options* parameters are merged.

If no arguments are specified, then any existing variable store will be cancelled.


## INI FILE STRUCTURE MANIPULATION ##

The following methods help you further manipulate in-memory .ini file contents.

### APPENDING DEFINITIONS ###

You can append .ini file definitions on an existing instance using the following methods :

	    	$status = $inifile -> AppendFromArray  ( $array ) ;
	    	$status = $inifile -> AppendFromFile   ( $file ) ;
	    	$status = $inifile -> AppendFromString ( $string ) ;

They work the same as their *LoadFromxxx()* counterparts, except that they operate on an existing object.

There are also methods for appending or inserting sections :

	    	$status = $inifile -> InsertSection ( $section, $section\_before, 
			    					$comment\_before = null,
			    					$comment\_after  = null ) ;

	    	$status = $inifile -> AppendSection ( $section, $comment\_before = null, $comment\_after = null ) ;

**InsertSection()** insert the section *$section* before *$section\_before*. **AppendSection()** inserts the section *$section* at the end of the .ini file.

## FORMATTING OPTIONS ##

When writing back your .ini file contents to a file, you may want some "pretty-printing" action, for example aligning key/value definitions on the same column ; the **SetAlignment()** method can be used for that. It accepts the following values :

- IniFile::ALIGN_NONE -

No alignment takes place. The .INI file will be written back as is.

- IniFile::ALIGN_SECTION -

Individual keys within a section will be aligned according to the longest key name in the section. For example :

	[Section1]
	V1=2
	Value10=3

	[Section2]
	V2=20
	LongValue10=30

will become :

	[Section1]
	V1      = 2
	Value10 = 3

	[Section2]
	V2          = 20
	LongValue10 = 30

- IniFile::ALIGN_FILE -

Alignement will be performed at the file-level ; the above example will give :

	[Section1]
	V1          = 2
	Value10     = 3

	[Section2]
	V2          = 20
	LongValue10 = 30

You can call the **AlignDefinitions()** method to realign in-memory .ini file contents before saving them. If no parameter is specified, the last one specified for the **SetAlignment()** method will be used.

You can retrieve the current alignment value with the **GetAlignment()** method.

Note that the **AsString()** and **Save()** methods automatically realign the definitions.
