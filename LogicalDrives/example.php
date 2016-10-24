<?php
	/****************************************************************************************************

		This example demonstrates how to use the various methods of the LogicalDrives class.

	 ****************************************************************************************************/
	require ( 'LogicalDrives.phpclass' ) ;

	if  ( php_sapi_name ( )  !=  'cli' )
		echo ( "<pre>" ) ;

	$ld	=  new LogicalDrives ( ) ;

	// Show assigned drive letters, with their label
	// Note that the $ld object can be accessed as an array, providing the drive letter as an index
	// (the drive letter can be followed by an optional semicolon and is not case-sensitive)
	echo ( "Assigned drives      :\n" ) ;

	foreach  ( $ld -> GetAssignedDrives ( )  as  $drive_letter )
		echo ( "\t$drive_letter ({$ld [ $drive_letter ] -> VolumeName})\n" ) ;

	// Show unassigned drives 
	echo ( "Unassigned drives    : " . implode ( ', ', $ld -> GetUnassignedDrives ( ) ) . "\n" ) ;

	// Next available drive letter 
	echo ( "Next available drive : " . $ld -> GetNextAVailableDrive ( ) . "\n" ) ;