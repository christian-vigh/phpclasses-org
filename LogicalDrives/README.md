# INTRODUCTION #

The **LogicalDrives** class is a small utility class that works only on Windows platforms and allows you to query the logical disks that are declared on your system.

It relies on the Windows Management Instrumentation interface (WMI) to retrieve logical drive information.

# DOCUMENTATION #

Documentation about WMI can be found here : [https://msdn.microsoft.com/fr-fr/library/aa394582(v=vs.85).aspx](https://msdn.microsoft.com/fr-fr/library/aa394582(v=vs.85).aspx "https://msdn.microsoft.com/fr-fr/library/aa394582(v=vs.85).aspx").

You can also get some information about the kind of data held in a **LogicalDrive** object here : [https://msdn.microsoft.com/en-us/library/aa394173(v=vs.85).aspx](https://msdn.microsoft.com/en-us/library/aa394173(v=vs.85).aspx "https://msdn.microsoft.com/en-us/library/aa394173(v=vs.85).aspx") (an object of class *LogicalDrive* maps directly to the WMI class **Win32_LogicalDisk**).

Additionally, you will find a more generic WMI access implementation in PHP here : [http://www.phpclasses.org/package/10001-PHP-Provides-access-to-Windows-WMI.html](http://www.phpclasses.org/package/10001-PHP-Provides-access-to-Windows-WMI.html "http://www.phpclasses.org/package/10001-PHP-Provides-access-to-Windows-WMI.html")

# OVERVIEW #

Before retrieving drive information, you need to have an instance of the **LogicalDrives** class :

	include ( 'LogicalDrives.phpclass' ) ;

	$ld 	=  new LogicalDrives ( ) ;

The constructor takes no parameter.

You can then call one of the methods implemented by the **LogicalDrives** class, such as *GetAssignedDrives()*, which retrieves the list of currently assigned logical drives :

	$assigned_drives 	=  $ld -> GetAssignedDrives ( ) ;

the method will return an array of strings containing the drive letters that are currently assigned on your system, followed by a semicolon. You can go further and use them for indexing your **LogicalDrives** instance and get access to individual drive information, which will be presented as an object of class **LogicalDrive** ; the following example will display the volume name (or label) for each drive letter that has been assigned to your system :

	foreach  ( $assigned_drives  as  $letter )
	   {
			$drive 		=  $ld [ $letter ] 	; 	// You can access a LogicalDrives object as an array

			echo "Drive $letter ({$drive -> VolumeName})\n" ;
 	    }

# REFERENCE #

## LogicalDrives class ##

The **LogicalDrives** class is the main interface to access logical drive information. Since it implements the *ArrayAccess* interface, it can be accessed using either :

- an integer index ; integer indexes are limited to the range 0..25, for the drive letters 'A' through 'Z'
- a string index ; in this case, this is a drive letter optionally followed by a semicolon, such as in : "A", "B:", "C", "D:", etc.

The class also implements the *Iterator* interface, meaning that you can retrieve individual drive information using **foreach()** constructs.

### Constructor ###

The constructor takes no parameter and immediately retrieves information about all the logical disks that are defined on your system.

### GetAssignedDrives ###

	public function  GetAssignedDrives ( ) 

Returns an array of strings representing the drive letters that are currently assigned on your system.

Drive letters are systematically in uppercase and followed by a semicolon.

### GetNextAvailableDrive ###

	public function  GetNextAVailableDrive ( )

Returns the next available drive letter.

Drive letters are systematically in uppercase and followed by a semicolon.

### GetUnassignedDrives ###

	public function  GetUnassignedDrives ( )

Returns an array of strings representing the drive letters that are currently unassigned on your system (and therefore free for later use).

Drive letters are systematically in uppercase and followed by a semicolon.
  
## LogicalDrive class ##

The **LogicalDrive** class encapsulates a Wmi *WIN32\_LogicalDisk* object for a given logical drive and implements all the properties that can be found in such an object (see [https://msdn.microsoft.com/en-us/library/aa394173(v=vs.85).aspx](https://msdn.microsoft.com/en-us/library/aa394173(v=vs.85).aspx "https://msdn.microsoft.com/en-us/library/aa394173(v=vs.85).aspx") for more information about this Wmi class, and about all the constants that are defined in the *LogicalDrive* class).

You will find additional methods and constants that are described below :

### GetDriveType ###

	public function  GetDriveType ( )

Returns the drive type as a string ; it can be any of the following values, depending on the drive :

- "*Unknown*" : Unknown drive type
- "*No root directory*" : Drive is assigned, but does not contain any root directory (and maybe, no filesystem at all)
- "*Removable disk*" : Drive is a removable disk, such as a USB device
- "*Local disk*" : Local disk, physically hardwired to your system
- "*Network drive*" : Drive is mapped to a network resource.
- "*Compact disk*" : Drive is a compact disk reader and/or writer
- "*Ram disk*" : Drive is a ram disk
- "*Unknown drive type 0xab*" : Drive type unknown. "ab" gives the hex code of the drive type.

This method is designed only to provide a human-readable string describing the drive type. To programmatically determine the actual drive type, it is far better to use the *DriveType* property and compare it against one of the *LogicalDrive::DRIVE\_TYPE\_\** constants.

### GetNetworkName ###

	public function  GetNetworkName ( ) ;

Returns an associative array containing the following entries :

- '*fullname*' : the full network name (eg, "*\\\\myhost\\myresource*"1)
- '*host*' : the server name (eg, "*myhost*")
- '*resource*' : the resource name (eg, "*myresource*")

### Isxxx() classification functions ###

	$status 	=  $drive -> IsCompactDisk ( ) ;
	$status 	=  $drive -> IsLocalDisk ( ) ;
	$status 	=  $drive -> IsNetworkDrive ( ) ;
	$status 	=  $drive -> IsRamDisk ( ) ;
	$status 	=  $drive -> IsRemovableDisk ( ) ;

These functions check that the drive has the specified characteristic : compact disk, local to the system, network-mapped, ram disk or removable disk.

### NormalizeLetter ###

	public static function  NormalizeDriveLetter ( $letter )

This function is mainly used internally, but has been made public in case of... It returns a "normalized" drive letter corresponding to the supplied input.

The return value is always an uppercase letter followed by a semicolon.

### ArrayAccess and Iterator interfaces ###

The **LogicalDrive** class implements the *ArrayAccess* and *Iterator* interfaces, so that you can use *for()* or *foreach()* constructs to loop through the individual properties currently exposed by the underlying WMI object.

### Constants ###

All the constants described below are documented here : [https://msdn.microsoft.com/en-us/library/aa394173(v=vs.85).aspx](https://msdn.microsoft.com/en-us/library/aa394173(v=vs.85).aspx "https://msdn.microsoft.com/en-us/library/aa394173(v=vs.85).aspx")

- Drive access types (**Access** property) :
	- DRIVE\_ACCESS\_UNKNOWN
	- DRIVE\_ACCESS\_READ
	- DRIVE\_ACCESS\_WRITE
	- DRIVE\_ACCESS\_READ\_WRITE
	- DRIVE\_ACCESS\_WRITE\_ONCE
- Drive availabilities (**Availability** property) :
	- DRIVE\_AVAILABILITY\_OTHER
	- DRIVE\_AVAILABILITY\_UNKNOWN
	- DRIVE\_AVAILABILITY\_RUNNING
	- DRIVE\_AVAILABILITY\_WARNING
	- DRIVE\_AVAILABILITY\_TESTING
	- DRIVE\_AVAILABILITY\_NOT\_APPLICABLE
	- DRIVE\_AVAILABILITY\_POWER\_OFF
	- DRIVE\_AVAILABILITY\_OFFLINE
	- DRIVE\_AVAILABILITY\_OFF\_DUTY
	- DRIVE\_AVAILABILITY\_DEGRADED
	- DRIVE\_AVAILABILITY\_NOT\_INSTALLED
	- DRIVE\_AVAILABILITY\_INSTALL\_ERROR
	- DRIVE\_AVAILABILITY\_POWER\_SAVE\_UNKNOWN\_STATE
	- DRIVE\_AVAILABILITY\_POWER\_SAVE\_LOW\_POWER
	- DRIVE\_AVAILABILITY\_POWER\_SAVE\_STANDBY
	- DRIVE\_AVAILABILITY\_POWER\_CYCLE
	- DRIVE\_AVAILABILITY\_POWER\_SAVE\_WARNING
- Drive types (**DriveType** property) : 
	- DRIVE\_TYPE\_UNKNOWN
	- DRIVE\_TYPE\_NO\_ROOT\_DIRECTORY
	- DRIVE\_TYPE\_REMOVABLE\_DISK
	- DRIVE\_TYPE\_LOCAL\_DISK
	- DRIVE\_TYPE\_NETWORK\_DRIVE
	- DRIVE\_TYPE\_COMPACT\_DISK
	- DRIVE\_TYPE\_RAM\_DISK
- Media types (**MediaType** property) :
	- MEDIA\_TYPE\_UNKNOWN
	- MEDIA\_TYPE\_FLOPPY\_5\_1x2\_512bs
	- MEDIA\_TYPE\_FLOPPY\_5\_1x44\_512bs
	- MEDIA\_TYPE\_FLOPPY\_2x88\_512bs
	- MEDIA\_TYPE\_FLOPPY\_3\_20x8\_512bs
	- MEDIA\_TYPE\_FLOPPY\_3\_720\_512bs
	- MEDIA\_TYPE\_FLOPPY\_5\_360\_512bs
	- MEDIA\_TYPE\_FLOPPY\_5\_320\_512bs
	- MEDIA\_TYPE\_FLOPPY\_5\_320\_1024bs
	- MEDIA\_TYPE\_FLOPPY\_5\_180\_512bs
	- MEDIA\_TYPE\_FLOPPY\_5\_160\_512bs
	- MEDIA\_TYPE\_REMOVABLE\_MEDIA
	- MEDIA\_TYPE\_FIXED\_HARD\_DISK
	- MEDIA\_TYPE\_FLOPPY\_3\_120M\_512bs
	- MEDIA\_TYPE\_FLOPPY\_3\_640\_512bs
	- MEDIA\_TYPE\_FLOPPY\_5\_640\_512bs
	- MEDIA\_TYPE\_FLOPPY\_5\_7230\_512bs
	- MEDIA\_TYPE\_FLOPPY\_3\_1x2\_512bs
	- MEDIA\_TYPE\_FLOPPY\_3\_1x23\_1024bs
	- MEDIA\_TYPE\_FLOPPY\_5\_1x23\_1024bs
	- MEDIA\_TYPE\_FLOPPY\_3\_128M\_512bs
	- MEDIA\_TYPE\_FLOPPY\_3\_230M\_512bs
	- MEDIA\_TYPE\_FLOPPY\_8\_256\_128bs
- Power management capabilities (**PowerManagementCapabilities** property, which can be any combination of the flags listed below) :
	- PMC\_UNKNOWN
	- PMC\_NOT\_SUPPORTED
	- PMC\_DISABLED
	- PMC\_ENABLED
	- PMC\_AUTO\_POWER\_SAVING
	- PMC\_POWER\_STATE\_SETTABLE
	- PMC\_POWER\_CYCLING\_SUPPORTED
	- PMC\_TIMED\_POWER\_ON\_SUPPORTED
- Logical device state (**StatusInfo** property) :
 	- STATUS\_OTHER
	- STATUS\_UNKNOWN
	- STATUS\_ENABLED
	- STATUS\_DISABLED
	- STATUS\_NOT\_APPLICABLE

