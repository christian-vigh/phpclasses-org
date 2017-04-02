# INTRODUCTION #

The **WShell** class, which is intended only for Windows platforms, is an encapsulation of the *WShell.Script* ActiveX object ([https://msdn.microsoft.com/en-us/subscriptions/2f38xsxe(v=vs.84).aspx](https://msdn.microsoft.com/en-us/subscriptions/2f38xsxe(v=vs.84).aspx "https://msdn.microsoft.com/en-us/subscriptions/2f38xsxe(v=vs.84).aspx"). It provides the following features :

- Run a command in synchronous or asynchronous mode
- Activate an already opened application to bring it the focus
- Programatically send keys to the active application, providing more features than the traditional *WShell.Script* **SendKeys** method. 
- perform operations such as inserting text, selecting menus and so on
- Perform basic operations on registry keys

# EXAMPLE #

Maybe the most basic example, residing in file *example.php*, would be the best way to start :

	require_once ( 'WShell.phpclass' ) ;

	$wshell		=  new  WShell ( ) ;

	// Launch NOTEPAD and let time for it for startup
	$wshell -> Exec ( "NOTEPAD.EXE" ) ;
	sleep ( 2 ) ;

	// NOTEPAD is launched : write the string "Hello world" in the document
	$wshell -> SendKeys ( "Hello world" ) ;

	// We will save this new file :
	// Type Alt+F, then DOWN key 3 times, press ENTER and type "example.txt" as the output filename.
	// After that, you just need to click on the "Save" button to save the file.
	// Note that we have to specify some delay between keystrokes (100ms in this example), because
	// a few operations might need a delay to operate (for example, opening the "Save as" dialog box).
	// Without this delay, a few characters may be missed from the filename "example.txt"
	$wshell -> SendKeys ( "%(F){DOWN 3}{ENTER}example.txt", 100 ) ;

The above example opens the NOTEPAD.EXE application with an empty document, writes the string "Hello world" into it, then brings the "Save" dialog box to you, pre-filling the output filename with "example.txt"

# CLASS REFERENCE #

## METHODS ##

### Constructor ###

	$wshell 	=  new WShell ( ) ;

Instantiates a new *WShell.Script* object.

### AppActivate ###

	$wshell -> AppActivate ( $app ) ;

Activates the specified running application and bring the focus to it.

**$app** can either be the process ID of the application, or the application title.

When determining which application to activate, the specified title is compared to the title string of each running application. If no exact match exists, any application whose title string begins with title is activated. If an application still cannot be found, any application whose title string ends with title is activated. 

If more than one instance of the application named by title exists, one instance will be arbitrarily activated.

### Exec ###

	$pid 	=  $wshell -> Exec ( $command ) ;

Executes the specified command asynchronously, and returns its process id.

### Registry access functions ###

	$value		=  wshell -> RegistryRead   ( $name ) ;
	$status		=  wshell -> RegistryWrite  ( $name, $value, $type ) ;
	$status		=  wshell -> RegistryDelete ( $name ) ;

These methods allows for simple access to the registry. As does the *WScript.Shell*, it only allows to read, write or delete registry values and does not process registry keys.

If you need to perform more complex operations on the registry, you may consider the [https://www.phpclasses.org/package/9348-PHP-Manage-the-values-of-keys-in-Windows-registry.html](https://www.phpclasses.org/package/9348-PHP-Manage-the-values-of-keys-in-Windows-registry.html "PHP Windows Registry Access") package.

All those three functions have a common parameter, *$name*, which is the name of the registry value to be read, written or deleted. If the name does not start with one of the standard registry roots ("\HKCU", "\HKLM", etc.) the "\HKCU" will be assumed.

When writing a new registry value, you can specify its type, which can be one of the following constants :

- *WShell::REG\_SZ* : String value.
- *WShell::REG\_DWORD* : Double-word value.
- *WShell::REG\_BINARY* : Binary value.
- *WShell::REG\_EXPAND\_SZ* : 	Expandable string.

### Run ###

	$status 	=  $wshell -> Run ( $command, $wait = false ) ;

Runs a command either synchronously (*$wait* = true) or synchronously (*$wait* = false). Returns the status of the execution.

When a command is run synchronously, don't expect to be able to use the **SendKeys** method just after calling **Run** : you will remain in the **Run** call until the application is terminated.

### SendKeys ###

   	WShell::SendKeys ( $sequence, $pause_between_keys = 0, $default_pause = 100 ) ;


Send keys to the application that currently owns the focus. It provides some additional features when compared to the *WShell.Script* **SendKeys()** method, and overcomes some quirks.

Backward compatibility has been preserved (ie, any string passed to the Windows API **SendKeys** method will work when passed to the **WShell::SendKeys**).

Enhancements over the Windows API SendKeys method are signalled by the "*[EXTENSION]*" string.
	  
- Simple characters, except those listed below, are sent as is : "abc" sends the   characters "a", "b" and "c"
- The following characters have special meanings, they are modifiers :
	- + : Simulates a keypress on the SHIFT key. For example, the string "+a" will send capital letter A.
	- ^ : CTRL key.
	- % : ALT key.
- The following characters also have special meanings :
	- ~ : Synonym for the {ENTER} key.
	- {} construct : 
		- Must be used to escape the following characters : +^%~(){}.
		- Can be used to specify a special key that have no Ascii equivalent ; the list of special keys is given below :
		
				BACKSPACE		{BACKSPACE},	{BS}, or	{BKSP}
				BREAK			{BREAK}
				CAPS LOCK		{CAPSLOCK} or [EXTENSION] {CAPS}
				DEL or DELETE	{DELETE} or	{DEL}
				DOWN ARROW		{DOWN}
				END				{END}
				ENTER			{ENTER} or ~
				ESC				{ESC} or [EXTENSION] {ESCAPE}
				HELP			{HELP}
				HOME			{HOME}
				INS or INSERT	{INSERT} or	{INS}
				LEFT ARROW		{LEFT}
				NUM LOCK		{NUMLOCK} or [EXTENSION] {NUM}
				PAGE DOWN		{PGDN} or [EXTENSION] {PAGEDOWN}
				PAGE UP			{PGUP} or [EXTENSION] {PAGEUP}
				PRINT SCREEN	{PRTSC}
				RIGHT ARROW		{RIGHT}
				SCROLL LOCK		{SCROLLLOCK}
				SPACE			[EXTENSION] {SPACE} or {SP}
				TAB				{TAB}
				UP ARROW		{UP}
				F1				{F1}
				F2				{F2}
				F3				{F3}
				F4				{F4}
				F5				{F5}
				F6				{F6}
				F7				{F7}
				F8				{F8}
				F9				{F9}
				F10				{F10}
				F11				{F11}
				F12				{F12}
				F13				{F13}
				F14				{F14}
				F15				{F15}
				F16				{F16}
				PAUSE			[EXTENSION] Introduces a pause whose duration is given by the $default_pause parameter :
									{PAUSE}
								An optional number of milliseconds can be given after the "PAUSE" keyword to override $default_pause value :
									{PAUSE 100}

  		- *[EXTENSION]* MS SendKeys() requires that the characters [] be escaped, although they do not have any special meaning. They can be specified as is with the **SendKeys()** method.
		- Can be used to specify a repeat count. For example, {a 10} will send 10 times the letter "a".
		- *[EXTENSION]* This notation can be used on special keys : {F1 10} will send 10 times the F1 key.
		- *[EXTENSION]* A space can be escaped such as in : {F1\ 10} or {F1\s10}, which will send the string "F1 10" without any preprocessing on the "F1" token.
		- *[EXTENSION]* Strings longer than 1 character can also be sent : 
				{abc 3}
		    will send the string "abcabcabc"
		- *[EXTENSION]* Strings to be sent as is but which also are a special key name can be prefixed with the backslash character : {\F10} will send the string "F10", not the F10 key code.
		- *[EXTENSION]* Although the *$pause\_between\_keys* parameter applies to key repetitions, a specific timing can also be specified after a semicolon : {F1 10:100}
		- *[EXTENSION]* Any modifier (+, ^ or %) before a {} construct will apply to all the characters within it. For example : +{abc} will send the key sequences SHIFT+A, SHIFT+B and SHIFT+C. This is the equivalent of the +(abc) construct.
	- () grouping construct :
		Allows for applying a key modifier to a group of letters :
			+(abc)
		will send the key sequences SHIFT+A, SHIFT+B and SHIFT+C.
	[EXTENSION] Special characters inside a "(...)" construct need not to be escaped.
	Note that backslashes are not interpreted in such a construct.
	- *[EXTENSION]* Any character, special or not, can be escaped with the backslash character.
	- *[EXTENSION]* PHP code can be specified using the construct traditional constructs
	- *[EXTENSION]* The backslash character can be used to escape the character following. "\r" and "\n" will be replaced with {ENTER}, "\t" with {TAB}, "\\" with "\". Any other character will be processed without any interpretation.
	

The parameters are the following : 	
	  
- **$sequence** *(string)* : Sequence of keys to be sent.
- **$pause\_between\_keys** *(integer)* : Number of milliseconds to wait before each keystroke.
- **	$default\_pause** *(integer)* : Default number of milliseconds to wait for each {PAUSE} construct.

The function returns the real key sequence sent to the Windows *SendKeys()* API. 

Note that the special notation *{PAUSE [x]}* will not be included in the returned value, because it is not directly sent to the *SendKeys()* API but rather interpreted as a pause to be introduced between two sequences of keys.
