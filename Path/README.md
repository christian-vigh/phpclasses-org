# INTRODUCTION #

The **Path** class contains static functions for handling paths and, more generally, for relieving you from the burden of handling platform-specifics issues related to paths, such as the ones you ay find when switching from Windows/Unix platforms. 

# FEATURES #

Using the **Path** class functions, you can :

- Get the real path of a file, even if the file does not exists (which the builtin PHP function *realpath()* is unable to do)
- Walk through a directory, applying selection criterias in the same spirit as the Unix *find* command
- Get the full path of an executable file, provided it resides in one of the directories listed by your PATH environment variable. On Windows platforms, it also takes into account your PATHEXT environment variable to search for the appropriate binaries when no executable file extension has been specified.
- Match a wildcard against a filename, much like the Msdos-style
- Get an *ls*-style output for file permissions
- And many more utility functions, related to handling filename extensions

# REFERENCE #

## AppendDS ##

	$newpath 	=  Path::AppendDS  ( $path ) ;

Appends a directory separator to the specified path, if not present.

This function takes into account the difference in directory separator between the Windows and Unix platforms.

## ContainsDirectory ##

	$status = Path::ContainsDirectory ( $path, $directory ) ;

Checks if *$path* contains the specified directory.

This function takes into account the case-sensitiveness of the host platform for filenames.  

## ContainsExtension ##

	$status 	=  Path::ContainsExtension ( $path, $extension ) ;

Checks if the specified path contains the specified extension.

The *$extension* parameter can be either a single file extension, or an array of file extensions. File extensions must not include a dot.

This function takes into account the case-sensitiveness of the host platform for filenames.  

## Equals ##

	$status 	=  Path::Equals ( $path1, $path2 ) ;

Checks if two paths are equal. Both path can be either relative or absolute.

This function takes into account the case-sensitiveness of the host platform for filenames.  

## Extension, Filename, Dirname, Basename ##

	$extension 		=  Path::Extension ( $path ) ;
	$filename 		=  Path::Extension ( $path ) ;
	$dirname 		=  Path::Extension ( $path ) ;
	$basename 		=  Path::Extension ( $path ) ;
 
These functions provide an alternative to the PHP *pathinfo()* builtin function.

The reason for their existence is that the **PATHINFO\_FILENAME** constant became available only with PHP 5.2.0.

## FileModeToString ##

	$mode_string 	=  Path::FileModeToString ( $mode ) ;

Returns a string corresponding to the specified file mode (*$mode*), in the style supplied by the Unix *ls -l* command.

## Find ##

	$result 	=  Find ( $path, $name = null, $find_options = FIND_OPTIONS_DEFAULT, $options = null, $callback = null ) ;

Implements a Unix *find*-like utility for finding files in a heirarchy.

### $path parameter ###

Starting path for the directory tree traversal.

### $name parameter ###

File pattern(s) to be searched.

If the pattern starts with the string '*re:*', then it will be considered as a regular expression enclosed with the character immediately after 're' ; otherwise, the normal Unix file pattern matching rules will be applied.

### $find_options parameter ###

A combination of the following flags that influence how the search is performed and how the results are returned :

- *FIND\_OPTIONS\_FILES\_ONLY* : Only filenames will be returned.

- *FIND\_OPTIONS\_STAT\_INFO* : Returns stat information about the file.

- *FIND\_OPTIONS\_EXTENDED\_STAT\_INFO* :	Returns extended stat information.

Additional constant exists :

- *FIND\_OPTIONS\_DEFAULT* :Defaults to FIND\_OPTIONS\_FILES\_ONLY | FIND\_OPTIONS\_STAT\_INFO | FIND\_OPTIONS\_EXTENDED\_STAT\_INFO.

- *FIND\_OPTIONS\_ALL* : Defaults to all options.
 
### $options parameter ###

Boolean expression representing the file selection criterias. This can be any valid PHP expression, specifying additional constructs referencing file information fields.

File information fields are enclosed with square brackets, and the values they are compared with must be enclosed with quotes. The following, for example, will search for file greater than 500kb and whose modification
time is greater than '2011/12/01' :

	"[size]  >  '500kb'  &&  [mtime]  >  '2011/12/01'"

Other boolean expressions can be mixed as well.

The available file information fields are the following :

- 'atime' or 'access-time' : Last access time. It can be any value accepted by the strtotime() function.
- 'blksize' or 'block-size' : File system block size.
- 'ctime' or 'creation-time' : Creation time. It can be any value accepted by the *strtotime()* 	function.
- 'depth' : 	Current entry nesting level.
- 'dev' or 'device' : Block device id.
- 'fmode' or 'file-mode' : File mode string, like it can be displayed by the "ls -l" command. Example : "-rw-rw-rw-".
- 'gid' or 'group-id' : 	Group id.
- 'ino' or 'inode' : Inode number.
- 'mode' : File mode. You can use any of the S\_xxx constants to apply a mask on this value.
- 'mtime' or 'modification-time' : Last modification time. It can be any value accepted by the *strtotime()* function.
- 'nlink' or 'link-count' : Number of files linked to this file.
- 'rdev' or 'raw-device' : Raw device id.
- 'size' : Entry size in bytes.
- 'type' : Entry type : 'file', 'directory', 'socket', 'block', 'character', 'fifo' or 'link'.
- 'uid' or 'user-id' : User id.

The following constants evaluate to true if the corresponding file mode bit is set :

- S\_IFSOCK	: True if entry is a socket
- S\_IFLNK	: True if entry is a symbolic link
- S\_IFREG	: True if entry is a regular file
- S\_IFBLK	: True if entry is a block device
- S\_IFCHR	: True if entry is a character device
- S\_IFDIR	: True if entry is a directory
- S\_IFIFO	: True if entry if a FIFO entry.
- S\_ISGID	: True if the set-group-id bit is set.
- S\_ISVTX	: True if the sticky bit is set.
- S\_IRUSR	: True if the read bit permission is set for the owner.
- S\_IWUSR	: Same, for write permission.
- S\_IXUSR	: Same, for execute permission.
- S\_IRGRP, S\_IWGRP, S\_IXGRP : Same permissions, for group users
- S\_IROTH, S\_IWOTH, S\_IXOTH :  Same permissions, for other users.

The following example will select all entries whose size is greater than 300kb, and that have the READ and EXECUTE permissions for the owner user :

		"[size] > '300kb'  &&  S_IRUSR  &&  S_IXUSR"

### $callback parameter ###

Specifies a callback function that will be used to select a found file or not.

The callback function must have the following signature :

	boolean  callback ( $fullpath, $filename, $stat_info )

Where :

- *$fullpath* is the fullpath of the currently processed file (including the filename part)
- *$filename* is the currently processed filename
- *$stat\_info* is the stat information as it can be returned when the FIND\_OPTIONS\_STAT\_INFO and FIND\_OPTIONS\_EXTENDED\_STAT\_INFO flags are specified.

### Return value ###

When the *FIND\_OPTION\_FILES\_ONLY* flag has been specified, returns an array containing the filenames that have been found.

Otherwise, the return value will be an associative array whose keys are the filenames and values are informations such as stat info and extended stat info. Each item in the returned array have the following entries :

- *FIND\_OPTIONS\_STAT\_INFO flag specified :
	- 'atime' (Unix time) : 	Last access time.
	- 'blksize' (integer) : 	Block size (-1 on Windows systems).
	- 'blocks' (integer) : Number of file system blocks occupied by this file.
	- 'ctime' (Unix time) : 	Creation time.
	- 'depth' (integer) : Current nesting level within the filesystem.
	- 'dev' (integer) : 	Block device id.
	- 'fmode' (string) : String representing the file access modes, as can be showed by the "ls -l" command.
	- 'gid' (integer) : 	Group ID (0 on Windows systems).
	- 'ino' (integer) : 	Inode number (0 on Windows systems).
	- 'mode' (integer) : File access mode.
	- 'mtime' (Unix time) : 	Last modification time.
	- 'nlink' (integer) : Number of links to this file (always 1 on Windows systems).
	- 'rdev' (integer) : Raw device id.
	- 'size' (integer) : Entry size, in bytes.
	- 'type' : Entry type. Can be :
		- 'file' : Regular file.
		- 'directory' : Directory.
		- 'socket' : System socket.
		- 'link' : Symbolic link.
		- 'block' : 	Block device.
		- 'character' : 	Character device.
		- 'fifo' : FIFO file.
	- 'uid' (integer) : 	Owner id (always 0 on Windows systems).

When the *FIND\_OPTIONS\_EXTENDED\_STAT\_INFO flag specified, the stat information will contain an extra entry, 'modes', which will contain one boolean entry for each *S\_xx* file mode constant defined :

- S\_IFSOCK	: True if entry is a socket
- S\_IFLNK	: True if entry is a symbolic link
- S\_IFREG	: True if entry is a regular file
- S\_IFBLK	: True if entry is a block device
- S\_IFCHR	: True if entry is a character device
- S\_IFDIR	: True if entry is a directory
- S\_IFIFO	: True if entry if a FIFO entry.
- S\_ISGID	: True if the set-group-id bit is set.
- S\_ISVTX	: True if the sticky bit is set.
- S\_IRUSR	: True if the read bit permission is set for the owner.
- S\_IWUSR	: Same, for write permission.
- S\_IXUSR	: Same, for execute permission/
- S\_IRGRP, S_IWGRP, S_IXGRP : Same permissions, for group users.
- S\_IROTH, S_IWOTH, S_IXOTH : Same permissions, for other users.

## GetTempDirectory ##

	$path 	=  Path::GetTempDirectory ( ) ;

Returns one of the following values (in the order below) :

- The contents of the "TMP" environment variable
- The contents of the "TEMP" environment variable
- The contents of the "TMPDIR" environment variable
- The value returned by the *sys_get_temp_dir ( )* PH builtin function
- 
The returned value is always an absolute path.

## HasExtension ##

	$status = HasExtension ( $path, $extension_list, &$found_extension = null, &$original_extension = null ) ;

Checks if a path has one of the extensions specified by *$extension\_list*.

The parameters are the following :

- *$path* (string) : Path to be checked.
- *$extension\_list* (string or array) : List of extensions to be checked against, or single extension specified as a scalar value. Extensions must be specified without a dot.
- &$found\_extension* (string) : 	Will be set on output to the matched extension.
- *&$original\_extension* (string) : Will be set on output to the original extension.

## IsAbolute ##

	$status 	=  Path::IsAbsolute ( $path )

Checks if the specified path is absolute. Returns *true* if the path is absolute, *false* otherwise.

On Unix systems, a path is absolute if it starts with the '/' character. On Windows systems, a path is absolute if its starts either with :

- A '/' or '\' character
- Or a drive letter, followed by a semicolon, then by a '/' or '\' character
 
This function takes into account the specificities of the Windows/Unix platforms.

## IsValidFilename ##

	$status = Path::IsValidFilename ( $filename, $is_dir = false ) ;

Checks if the specified filename is a valid filename. The type of validation depends on whether we are running on a Unix or Windows system.

The parameters are the following :

- *$filename* (string) : Filename to be checked.
- *$is\_dir* (boolean) : When true, the check will succeed even if the path ends with a directory separator or the traditional '.' and '..' items.

This function only checks that the specified path is sintactically correct.

Checking for a directory is only performed on a syntax basis ; no filesystem checking is performed.


## IsValidUnixFilename ##
	
	$status = Path::IsValidUnixFilename ( $filename, $is_dir = false ) ;

Checks if the specified path is a valid Unix filename. See the *IsValidFilename* function for additional information.

## IsValidWindowsFilename ##
	
	$status = Path::IsValidWindowsFilename ( $filename, $is_dir = false ) ;

Checks if the specified path is a valid Windows filename. See the *IsValidFilename* function for additional information.

## Matches ##

	$status 	=  Matches ( $file, $pattern, $case_sensitive = false ) ;

Matches a filename against a file mask. The pattern is in the style of Msdos or Unix file-matching patterns, and can contain the following special elements :

- * : Matches any sequence of characters, except a directory separator.
- ? : Matches any character, except a directory separator.
- [] : Matches a character set. For example : [a-z].

Directory separators act as stop-characters ; for example, *"file\*.txt"* will match *"file.001.txt"* but not *"file/001/file.txt"*.

## MkDir ##

	$status 	=  Path::MkDir ( $path, $mode = 0755, $recursive = false, $user = false, $group = false ) ;
	
Creates a directory using the specified access mode, user and group names.

The parameters are the following :

- *$path* (string) : Path to be created
- *$mode* (integer) :	Access mode
- *$recursive* (boolean) : When true, subdirectories are created as needed. When false, only the last directory will be created ; other directories in the path must already exist.
- *$user* (string) : When not null, created subdirectories will be owned by the specified user.
- *$group* (string) : When not null, created subdirectories will belong to the specified group.


## MkFile ##

	$status 	=  Path::MkFile ( $path, $mode = 0755, $recursive = false, $user = false, $group = false ) ;
	
Creates a file using the specified access mode, user and group names.

The parameters are the following :

- *$path* (string) : Path to be created
- *$mode* (integer) :	Access mode
- *$recursive* (boolean) : When true, subdirectories are created as needed. When false, only the last directory will be created ; other directories in the path must already exist.
- *$user* (string) : When not null, created subdirectories will be owned by the specified user.
- *$group* (string) : When not null, created subdirectories will belong to the specified group.

## NextUniqueFilename ##

	$filename	=  Path::NextUniqueFilename ( $file_specifier, $specifier = 's' ) ;

Generates a unique filename based on the specified *$file_specifier* parameter. The unicity of the filename is determined by a unique integer id, which will replace the *sprintf()*-like format specified in *$file_specifier*.

The following example :

	$filename 	=  Path::NextUniqueFilename ( 'file.%s.txt' ) ;

will return "file.1.txt" if the current directory (ie, the one for file "file.*.txt") does not contain any filename having this naming scheme.

If the directory already contains several files having this naming scheme, say "file.1.txt" through "file.99.txt", then the next call to NextUniqueFilename() will return "file.100.txt".

If not format specifier has been found in the $*file_specifier* string, then ".%s" will be appended to the supplied value ; so, the following call :

	$filename 	=  Path::NextUniqueFilename ( 'file.txt' ) ;

will return "file.txt.1" if no files matched the mask "file.txt.*".

The *$specifier* parameter allows you to specify a letter other than "s" for a format specifier.

Format specifiers can include any width options, such as for *sprintf()* ; the following example :

	$filename 	=  Path::NextUniqueFilename ( 'file.%04s.txt' ) ;

will return "file.0001.txt".
	

## PushDirectory ##

	Path::PushDirectory ( $directory = false ) ;

Pushes the specified directory onto the directory stack.

If the *$directory* parameter is not specified, the current working directory will be pushed onto the stack.

The *PushDirectory()* method does not change the current working directory ; it is the caller responsibility to perform a call to the PHP builtin *chdir()* function.

## PopDirectory ##

	$status		=  Path::PopDirectory ( ) ;

Pops the last pushed directory from the directory stack and set it to be the current working directory if the directory stack is not empty. 

When the directory stack is empty, *PopDirectory()* returns false.

## PrependExtension ##

	$path	=  Path::PrependExtension ( $path, $extension ) ;

Prepends an extension before the specified path extension. If the path does not contain any extension, it will simply be appended.

The parameters are the following :

- *$path* (string) : Path on which an extension is to be prepended.
- *$extension* (string) : Extension to be prepended. The extension does not need to include a leading dot.

## Quote ##

	$quoted		=  Path::Quote ( $path, $force = false ) ;
	
 
Put quotes around a filename if it contains spaces or quotes (internal quotes will be escaped). Quotes are put only if needed, unless the *$force* parameter is set to true.
	 
## RealPath ##

	$path = Path::RealPath ( $path, $use_cwd = false ) ;

This function is a true alternative to the builtin *realpath()* function ; it works even if the specified path exists.

If you use this function on a stream wrapper that just implements a way to reference a particular part of a filesystem, the **RealPath** function will check if the strea options contain an entry named *root* and will use that root to build the final path.

When the *$use\_cwd* parameter is true, the current working directory will be used for building the	absolute path.
	    		
When false, it will be the directory of the currently running script.

If specified as a string, it will be considered as the current working directory for relative paths.

**Note** : Don't try to get the absolute path of a relative path within a specified drive on Windows. Windows has a notion of per-drive current working directory ; however PHP does not provide any support for retrieving the current working directory for a specific drive.

## ReplaceExt ##

	$newfile = Path::ReplaceExtension ( $file, $newext ) ;

Replaces the extension of *$file* with *$newext*. If *$newext* is null or equal to ".", the file extension will be removed.

## ToCygwin ##

	$result 	=  Path::ToCygWin ( $path ) ;

Converts a Windows path to a Cygwin-compatible one ; for example :

	C:\Temp\sample.txt

will be converted to :

	/cygdrive/c/temp/sample.txt

On Unix platforms, the supplied path will be returned unchanged.

## ToUnix ##

	$result 	=  ToUnix ( $path, $strip_drive_letter = false ) ;

converts a windows-style path to unix ; backslashes are replaced by slashes,	and the drive letter (along with the following semicolon) is removed from the string if the *$strip\_drive\_letter* parameter is true.

## ToWindows ##

	$result 	=  ToWindows ( $path ) ;

Converts a Unix-style path to a Windows-style path.

## ToHost ##

	$result 	=  ToHost ( $path, $append_trailing_separator = false ) ;

Converts a path to the notation used by the current host (Unix or Windows).

## WhereIs ##

	$realpath	=  Path::WhereIs ( $command ) ;

Tries to locate the real path of a command, by using the **PATH** environment variable.

On Windows systems, the **PATHEXT** environment variable is also used to determine which extensions are for
executables, in case the supplied command name does not contain any extension.


