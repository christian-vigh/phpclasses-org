<?php
/**************************************************************************************************************

	Example script that prints out your current configuration of WAMP.

	Note that the WampManager class package has the following dependencies :

	- https://www.phpclasses.org/package/10004-PHP-Retrieve-the-letters-of-the-drives-on-Windows.html
	- https://www.phpclasses.org/package/10248-PHP-Encapsulates-the-WShell-Script-Windows-object.html
	- https://www.phpclasses.org/package/10001-PHP-Query-local-and-remote-Windows-systems-with-WMI.html
	- https://www.phpclasses.org/package/9413-PHP-Load-and-edit-configuration-INI-format-files.html
	- https://www.phpclasses.org/package/10018-PHP-Manage-file-and-folder-paths-in-Windows-and-Linux.html

	However, for your own convenience, the Src directory which contains the WampManager classes has a
	subdirectory called Dependencies, which contains all the required source files. Note however that they
	may not be the latest releases ! but the current versions are sufficient to make the package working.

 **************************************************************************************************************/

require ( 'Src/WampManager.phpclass' ) ;

if  ( php_sapi_name ( )  !=  'cli' )
	echo ( "<pre>" ) ;

// Create a WampManager instance - the installation directory will be searched first in the path pointed to by
// the WAMPDIR environment variable (if you defined it) then on all the non-removable drives present on your system.
$wamp	=  new WampManager ( ) ;

print_r ( $wamp ) ;