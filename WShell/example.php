<?php
	/****************************************************************************************************

		A demonstration of the WShell class capabilities.

		NOTE : this example should be better run as a command-line script (in CLI mode).

		It opens the NOTEPAD.EXE application, writes the string "Hello world" and saves the result 
		to file "example.txt" (well, in order not to pollute your own drive, you will have to click
		yourself on the "Save" button).

	 ****************************************************************************************************/

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
	// a few operations might need a delay to operate (for example, opening the "Save as" dialog box.
	// Without this delay, a few characters may be missed from the filename "example.txt"
	$wshell -> SendKeys ( "%(F){DOWN 3}{ENTER}example.txt", 100 ) ;
