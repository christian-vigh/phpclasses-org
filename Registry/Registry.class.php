<?php
// Determine if we run under Windows or Unix
if  ( ! defined ( 'IS_WINDOWS' ) )
   {
	if  ( ! strncasecmp ( php_uname ( 's' ), 'windows', 7 ) )
	    {
 		define ( 'IS_WINDOWS'		,  1 ) ;
 		define ( 'IS_UNIX'		,  0 ) ;
	     }
	 else
	    {
 		define ( 'IS_WINDOWS'		,  0 ) ;
 		define ( 'IS_UNIX'		,  1 ) ;
	     }
    }


/**
 * 
 * A class that allows access to the Windows registry.
 *
 * This class can use either the WMI or the WShell API. More functionalities are available when
 * using WMI calls.
 * Registry keys can be enumerated, created, deleted, as well as registry values.
 * After each registry access method call, the GetLastError() method can be called to retrieve
 * the last Windows error code.
 *
 * @Package	Windows
 * @Category	Registry
 * @version	1.0
 * @depends	PHP COM extension
 * @author	Christian Vigh (christian.vigh@orange.fr)
 * 
 */
class  Registry
   {
	// Root keys
	const	HKEY_CLASSES_ROOT		=  0x80000000 ;
	const	HKEY_CURRENT_USER		=  0x80000001 ;
	const	HKEY_LOCAL_MACHINE		=  0x80000002 ;
	const	HKEY_USERS			=  0x80000003 ;
	const	HKEY_PERFORMANCE_DATA		=  0x80000004 ;
	const	HKEY_PERFORMANCE_TEXT		=  0x80000050 ;
	const	HKEY_PERFORMANCE_NLSTEXT	=  0x80000060 ;
	const	HKEY_CURRENT_CONFIG		=  0x80000005 ;
	const	HKEY_DYN_DATA			=  0x80000006 ;
	
	// Root key aliases
	const	HKCR				=  0x80000000 ;
	const	HKCU				=  0x80000001 ;
	const	HKLM				=  0x80000002 ;
	const	HKCC				=  0x80000005 ;
      
	// Value types
	const	REG_NONE			=  0 ;
	const	REG_SZ				=  1 ;
	const	REG_EXPAND_SZ			=  2 ;
	const	REG_BINARY			=  3 ;
	const	REG_DWORD			=  4 ;
	const	REG_DWORD_LITTLE_ENDIAN		=  4 ;
	const	REG_DWORD_BIG_ENDIAN		=  5 ;
	const	REG_LINK			=  6 ;
	const	REG_MULTI_SZ			=  7 ;
	const	REG_RESOURCE_LIST		=  8 ;
	const	REG_FULL_RESOURCE_DESCRIPTOR	=  9 ;
	const	REG_RESOURCE_REQUIREMENTS_LIST	=  10 ;
	const	REG_QWORD			=  11 ;
	const	REG_QWORD_LITTLE_ENDIAN		=  11 ;
      
	// Value options - Not used
	const	REG_OPTION_RESERVED		=  0x0000 ;
	const	REG_OPTION_NON_VOLATILE		=  0x0000 ;
	const	REG_OPTION_VOLATILE		=  0x0001 ;
	const	REG_OPTION_CREATE_LINK		=  0x0002 ;
	const	REG_OPTION_BACKUP_RESTORE	=  0x0004 ;
	const	REG_OPTION_OPEN_LINK		=  0x0008 ;
	const	REG_LEGAL_OPTION		=  0x000F ;		//  REG_OPTION_RESERVED | REG_OPTION_NON_VOLATILE | REG_OPTION_CREATE_LINK | 
									//  REG_OPTION_BACKUP_RESTORE | REG_OPTION_OPEN_LINK
      
	// Creation options - Not used
	const	REG_CREATED_NEW_KEY		=  1 ;
	const	REG_OPENED_EXISTING_KEY		=  2 ;
      
	// Registry restore options - Not used
	const	REG_WHOLE_HIVE_VOLATILE		=  0x0001 ;
	const	REG_REFRESH_HIVE		=  0x0002 ;
	const	REG_NO_LAZY_FLUSH		=  0x0004 ;
	const	REG_FORCE_RESTORE		=  0x0008 ;
      
	// General constants
	const	MAX_KEY_LENGTH			=  514 ;		//  Max key name length
	const	MAX_VALUE_LENGTH		=  32768 ;		//  Max key value length
	
	// Standard registry access object (WMI or else)
	const	REGISTRY_STANDARD_OBJECT	=  "winmgmts:{impersonationLevel=impersonate}!//{Computer}/root/default:StdRegProv" ;
	
	// Registry special node for 64 bits platforms
	const	SPECIAL_64BITS_NODE		=  "Wow6432Node" ;
	
	
	// Os architecture
	protected static	$OsArchitecture			=  null ;
	// Class constants - They will be useful to map a HK constant string (like "HKEY_CURRENT_USER" or "HKCU")
	// to its corresponding value
	protected static	$ClassConstants	;

	// Connection parameters
	public			$Computer ;			// Computer name ; defaults to "." (= "localhost")
	public			$User ;
	public			$Password ;
	// WMI instance
	protected		$WmiInstance ;
	// WShell instance - In some cases, we need to retrieve a registry key value without knowing its type in advance.
	// The StdRegProv WMI provider does not allow us to do that : it does not provide an API to retrieve a value
	// whatever its type, but rather provides us with the GetxxxValue() functions, which require knowing the value
	// type before retrieving it.
	// This is why we use the WScript.Shell object, which has a GetValue() function that will work in this case.
	protected static	$WShellInstance			=  null ;
	// Last error status returned by the Windows API
	protected		$LastError			=  0 ;
	
	
	/*==============================================================================================================
	
	    NAME
	        Constructor
	
	    PROTOTYPE
	        $reg = new Registry ( $computer = ".", $user = null, $password = null ) ;
	
	    DESCRIPTION
	        Creates a Registry object, which gives access to the whole registry.
	
	    PARAMETERS
	        $computer (string) -
	                Computer where to connect to the registry. Current computer can be specified as "." or "localhost".
	 
	 	$user (string) -
	 		User to connect with to the registry. Not required when computer is local host.
	  
	 	$password (string) -
	 		User password.
	
	  ==============================================================================================================*/
	public function  __construct  ( $computer  =  ".", $user = null, $password = null )
	   {
		$this -> Computer	=  $computer ;
		
		// Get class constants ; this is useful for the GetValue() method, which takes a root key and key path
		// that must be mapped to a string like "\\rootkey\keypath"
		$reflector		=  new  \ReflectionClass ( __CLASS__ ) ;
		self::$ClassConstants	=  $reflector -> getConstants ( ) ;
		
		// Get OS architecture
		if  ( self::$OsArchitecture  ===  null )
			self::$OsArchitecture	=  self::GetOsArchitecture ( ) ;
		
		// Check if we need to connect to a local or remote registry
		if   ( $computer  !==  "."  &&  $computer  !==  "localhost" )
		   {
			$locator		=  new \COM ( "WbemScripting.SWbemLocator" ) ;
			$this -> WmiInstance    =  $locator -> ConnectServer ( $computer, "root/default:StdRegProv", $user, $password ) ;
		    }
		else
			$this -> WmiInstance	=  new \COM ( 
				str_ireplace ( "{Computer}", $computer, self::REGISTRY_STANDARD_OBJECT ) ) ;
	    }
	

	/*==============================================================================================================
	
	    NAME
	        ConvertFromVariant - Converts variant to PHP data.
	
	    PROTOTYPE
	        $phpdata	=  $registry -> ConvertFromVariant ( $variant ) ;
	
	    DESCRIPTION
	        Variant data returned by the StdRegProv provider may need adaptation to be mapped to PHP data. This is
	 	the purpose of this function.
	
	    PARAMETERS
	        $variant (variant) -
	                Variant whose data is to be mapped to PHP data.
	
	    RETURN VALUE
	        The mapped PHP data.
	
	    NOTES
	        This function is able to handle arrays.
	
	  ==============================================================================================================*/
	protected function  ConvertFromVariant ( $variant ) 
	   {
		$variant_type	=  variant_get_type ( $variant ) ;		// Get variant type
		$is_array	=  ( $variant_type  &  VT_ARRAY ) ;		// Check if array
		$is_ref		=  ( $variant_type  &  VT_BYREF ) ;		// Check if reference (not used)
		$variant_type  &=  ~( VT_ARRAY | VT_BYREF ) ;			// Keep only basic type flags
		$items		=  array ( ) ;					// Return value
		
		// If variant is an array, get all array elements into a PHP array
		if  ( $is_array )
		   {
			foreach  ( $variant  as  $variant_item )
				$items []	=  $variant_item ;
		    }
		else
			$items []	=  $variant ;
		
		$item_count	=  count ( $items ) ;
		
		// Loop through array items (item count will be 1 if supplied variant is not an array)
		for  ( $i = 0 ; $i  <  $item_count ; $i ++ )
		   {
			$item	=  $items [$i] ;
			
			// Handle scalar types
			switch  ( $variant_type )
			   {
				case	VT_NULL :
					$items [$i]	=  null ;
					break ;
				
				case	VT_EMPTY :
					$items [$i]	=  false ;
					break ;
			
				case    VT_UI1 :	case	VT_UI2 :	case	VT_UI4 :	case	VT_UINT :
				case    VT_I1  :	case	VT_I2  :	case	VT_I4  :	case	VT_INT  :
					$items [$i]	=  ( integer ) $item ;
					break ;
				
				case	VT_R4 :
					$items [$i]	=  ( float ) $item ;
					break ;
				
				case	VT_R8 :
					$items [$i]	=  ( double ) $item ;
					break ;
					
				case	VT_BOOL :
					$items [$i]	=  ( boolean ) $item ;
					break ;
					
				case	VT_BSTR :
					$items [$i]	=  ( string )  $item ;
					break ;
					
				case    VT_VARIANT :
					if  ( $is_array )
						break ;
					else
						/* Intentionally fall through the default: case */ ;
				
				default :
					warning ( "Unexpected variant type $variant_type." ) ;
					$items [$i]	=  false ;
			    }
		    }
		
		return ( ( $is_array ) ?  $items : $items [0] ) ;
	    }
	
	
	/*==============================================================================================================
	
	    NAME
		GenericGetValue - Generic registry value retrieval method.
	  
	    PROTOTYPE
	        $value	=  $registry -> GenericGetValue ( $root, $keypath, $value_name, $method ) ;
	
	    DESCRIPTION
	        This method is the central point for all other methods such as GetBinaryValue, GetDWORDValue, etc.
	
	    PARAMETERS
	        $root (integer) -
	                Identifier of the registry root.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be retrieved.
	  
	 	$value_name (string) -
	 		Name of the value to be retrieved.
	  
	 	$method (string) -
	 		StdRegProv method to be called.
	
	    RETURN VALUE
	        The desired value, as a variant.
	  
	    NOTES
	 	The key path can contain any reference that will be processed by the NormalizeKey() method.
	
	  ==============================================================================================================*/
	protected function  GenericGetValue ( $root, $keypath, $value_name, $method )
	   {
		$keypath	=  $this -> NormalizeKey ( $keypath ) ;

		$output	=  new \VARIANT ( ) ;
		$status = $this -> WmiInstance -> $method ( $root, $keypath, $value_name, $output ) ;
		$this -> SetLastError ( $status ) ;

		return ( $this -> ConvertFromVariant ( $output ) ) ;
	     }

	
	/*==============================================================================================================
	
	    NAME
		GenericSetValue - Generic registry value creation/update method.
	  
	    PROTOTYPE
	        $value	=  $registry -> GenericSetValue ( $root, $keypath, $value_name, $value, $method ) ;
	
	    DESCRIPTION
	        This method is the central point for all other methods such as SetBinaryValue, SetDWORDValue, etc.
	
	    PARAMETERS
	        $root (integer) -
	                Identifier of the registry root.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be created/updated.
	  
	 	$value_name (string) -
	 		Name of the value to be created/updated.
	  
	 	$value (mixed) -
	 		Contents of the value to be created/updated.
	  
	 	$method (string) -
	 		StdRegProv method to be called.
	
	    RETURN VALUE
	        A boolean value indicating the outcome of the function call. When false, the GetLastError() method can
		be called to retrieve the Windows error code.
	  
	    NOTES
	 	The key path can contain any reference that will be processed by the NormalizeKey() method.
	
	  ==============================================================================================================*/
	protected function  GenericSetValue ( $root, $keypath, $value_name, $value, $method )
	   {
		$keypath	=  $this -> NormalizeKey ( $keypath ) ;

		$status = $this -> WmiInstance -> $method ( $root, $keypath, $value_name, $value ) ;
		$this -> SetLastError ( $status ) ;
		
		return  ( ( $status ) ?  true : false ) ;
	     }


	/*==============================================================================================================
	
	    NAME
	        GetHKConstant - Returns the name corresponding to an HKxxx constant.
	
	    PROTOTYPE
	        $name	=  $registry -> GetHKConstant ( $value ) ;
	
	    DESCRIPTION
	        Returns the constant name corresponding to the specified value.
	
	    PARAMETERS
	        $value (integer) -
	                Constant value whose name is to be retrieved.
	
	    RETURN VALUE
	        One of the HKxxx constant names defined in this class, or false if the specified value does not 
		correspond to any of the HKxxx constants.
	
	  ==============================================================================================================*/
	public function  GetHKConstant ( $constant_value )
	   {
		foreach  ( self::$ClassConstants  as  $name => $value )
		   {
			if  ( substr ( $name, 0, 2 )  ==  "HK"  &&  $value  ==  $constant_value )
				return ( $name ) ;
		    }
		
		return ( false ) ;
	    }
	
	
	/*==============================================================================================================
	
	    NAME
	        GetOsArchitecture - Returns the OS architecture.
	
	    PROTOTYPE
	        $bits	=  $registry -> GetOsArchitecture ( ) ;
	
	    DESCRIPTION
	        Returns the OS architecture, ie the supported word length in bits, as an integer.
	
	    RETURN VALUE
	        Either the integer value 32 or 64, for a 32-bit or a 64-bit OS.
	
	  ==============================================================================================================*/
	public static function  GetOsArchitecture ( )
	   {
		$wmi	=  new  \COM ( 'winmgmts:{impersonationLevel=impersonate}//./root/cimv2' ) ;		
		
		$result_set		=  $wmi -> ExecQuery ( 'SELECT OSArchitecture FROM Win32_OperatingSystem' ) ;
		
		if  ( ! $result_set -> Count ( ) )
			return ( false ) ;
		
		$result		=  $result_set -> ItemIndex ( 0 ) ;
		$size		=  preg_replace ( '/[^\d]+/', '', $result -> OSArchitecture ) ;
		
		return (  ( integer ) $size ) ;
	    }
	

	/*==============================================================================================================
	
	    GetWShellInstance -
	        Initially called once to get an instance of the WScript.Shell object.
	
	  ==============================================================================================================*/
	protected static function  GetWShellInstance ( )
	   {
		if  ( self::$WShellInstance  ===  null )
			self::$WShellInstance	=  new  \COM ( "WScript.Shell" ) ;
	    }
	
	
	/*==============================================================================================================
	
	    NAME
	        NormalizeKey - Performs substitutions inside a registry key path.
	
	    PROTOTYPE
	        $key	=  $registry -> NormalizeKey ( $key ) ;
	
	    DESCRIPTION
	        This function takes a key and replaces references of the form : {name} with the subtituted value of
	 	"name".
	  
	 	"name" can be one of the following values :
	 	- OsArchitecture :
	 		Replaced with the special key "Wow6432Node" (or Registry::SPECIAL_64BITS_NODE) on 64-bits
	 		systems.
	
	    PARAMETERS
	        $key (string) -
	                String to be normalized.
	
	    RETURN VALUE
	        The normalized input key, after all substitutions have taken place.
	
	    NOTES
	        - Forward slashes in the input key are replaced by backward slashes
		- Duplicate forward/backward slashes are replaced by one backslash
	
	  ==============================================================================================================*/
	protected function  NormalizeKey ( $key, $remove_dup_separators = true )
	   {
		$key	=  trim 
			     ( 
				str_replace 
				   (
					array ( '{OsArchitecture}'	 , "/"  ),
					array ( self::SPECIAL_64BITS_NODE, "\\" ),
					$key 
				    ) 
			       ) ;
		
		if  ( $remove_dup_separators )
			$key	=  str_replace ( "\\\\", "\\", $key ) ;		
		
		$length	=  strlen ( $key ) ;
		
		if  ( $length  &&  $key [ $length - 1 ]  ==  "\\" )
			$key	=  substr ( $key, 0, $length - 1 ) ;
				
		return (  $key ) ;
	    }
	
	
	/*==============================================================================================================
	
	    SetLastError -
	        Sets the error result of the last called registry function.
	
	  ==============================================================================================================*/
	protected function  SetLastError ( $err )
	   {
		$this -> LastError	=  $err ;
	    }
	

	/*==============================================================================================================
	
	    NAME
	        CreateKey - Creates a registry key.
	
	    PROTOTYPE
	        $status		=  $registry -> CreateKey ( $root, $keypath ) ;
	
	    DESCRIPTION
	        Creates a registry key.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key to be created.
	  
	    RETURN VALUE
	        True if the function is successful, false otherwise. In this case, the GetLastError() method can be used
		to retrieve the Windows error code.
	
	  ==============================================================================================================*/
	public function  CreateKey  ( $root, $keypath )
	   {
		$keypath	=  self::NormalizeKey ( $keypath ) ;
		$status		=  $this -> WmiInstance -> CreateKey ( $root, $keypath ) ;
		$this -> SetLastError ( $status ) ;
		
		return ( ( $status ) ?  false : true ) ;
	    }
	
	
	/*==============================================================================================================
	
	    NAME
	        DeleteKey - Deletes a registry key.
	
	    PROTOTYPE
	        $status		=  $registry -> DeleteKey ( $root, $keypath ) ;
	
	    DESCRIPTION
	        Deletes a registry key.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key to be deleted. The key, all its subkeys and values will be also removed.
	  
	    RETURN VALUE
	        True if the function is successful, false otherwise. In this case, the GetLastError() method can be used
		to retrieve the Windows error code.
	
	  ==============================================================================================================*/
	public function  DeleteKey  ( $root, $keypath )
	   {
		$keypath	=  self::NormalizeKey ( $keypath ) ;
		$status		=  $this -> WmiInstance -> DeleteKey ( $root, $keypath ) ;
		$this -> SetLastError ( $status ) ;
		
		return ( ( $status ) ?  false : true ) ;
	    }
	
	
	/*==============================================================================================================
	
	    NAME
	        DeleteValue - Deletes a registry value.
	
	    PROTOTYPE
	        $status		=  $registry -> DeleteValue ( $root, $keypath, $value ) ;
	
	    DESCRIPTION
	        Deletes a registry value.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be deleted.
	  
	 	$value (string) -
	 		Value to be deleted.
	  
	    RETURN VALUE
	        True if the function is successful, false otherwise. In this case, the GetLastError() method can be used
		to retrieve the Windows error code.
	
	  ==============================================================================================================*/
	public function  DeleteValue  ( $root, $keypath, $value_name )
	   {
		$keypath	=  self::NormalizeKey ( $keypath ) ;
		$status		=  $this -> WmiInstance -> DeleteValue ( $root, $keypath, $value_name ) ;
		$this -> SetLastError ( $status ) ;
		
		return ( ( $status ) ?  false : true ) ;
	    }
	
	
	/*==============================================================================================================
	
	    NAME
	        EnumKeys - Enumerates keys.
	
	    PROTOTYPE
	        $status		=  $registry -> EnumKeys ( $root, $keypath ) ;
	
	    DESCRIPTION
	        Enumerates keys located under the specified key path.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key to be enumerated.
	  
	    RETURN VALUE
	        Array of subkey names if the function is successful, false otherwise. In this case, the GetLastError() 
	 	method can be used to retrieve the Windows error code.
	
	  ==============================================================================================================*/
	public function  EnumKeys  ( $root, $keypath = "" )
	   {
		$keypath	=  self::NormalizeKey ( $keypath ) ;
		$keys		=  new  \VARIANT ( ) ;
		$status		=  $this -> WmiInstance -> EnumKey ( $root, $keypath, $keys ) ;
		$this -> SetLastError ( $status ) ;
		
		if  ( $status )
			return ( false ) ;
		
		if  ( variant_get_type ( $keys )  ==  VT_NULL )
			return ( array ( ) ) ;
		else
		   {
			$result		=  array ( ) ;
			
			foreach  ( $keys  as  $key )
				$result []	=  $key ;
			
			return ( $result ) ;
		    }
	    }

	
	/*==============================================================================================================
	
	    NAME
	        EnumValues - Enumerates key values.
	
	    PROTOTYPE
	        $status		=  $registry -> EnumValues ( $root, $keypath, $value_names_only = true ) ;
	
	    DESCRIPTION
	        Enumerates key values located under the specified key path.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key to be enumerated.
	  
	 	$value_names_only (boolean) -
	 		When true, only the value names are returned.
	 		When false, an associative array is returned whose keys are value names and whose values are
	 		value types (REG_xxx constants).
	  
	    RETURN VALUE
	        Array of value names or value names/types if the function is successful, false otherwise. In this case, 
	 	the GetLastError() method can be used to retrieve the Windows error code.
	
	  ==============================================================================================================*/
	public function  EnumValues  ( $root, $keypath = "", $value_names_only = true )
	   {
		$keypath	=  self::NormalizeKey ( $keypath ) ;
		$values		=  new  \VARIANT ( ) ;
		$value_types	=  new  \VARIANT ( ) ;
		$status		=  $this -> WmiInstance -> EnumValues ( $root, $keypath, $values, $value_types ) ;
		$this -> SetLastError ( $status ) ;
		
		if  ( variant_get_type ( $values )  ==  VT_NULL )
			return ( array ( ) ) ;
		else
		   {
			$found_values		=  array ( ) ;
			$found_types		=  array ( ) ;
			
			foreach  ( $values  as  $value )
				$found_values []	=  $value ;
			
			if  ( $value_names_only )
				$result		=  $found_values ;
			else
			   {
				foreach  ( $value_types  as  $value_type )
					$found_types []		=  $value_type ;
			
				$result		=  array_combine ( $found_values, $found_types ) ;
			    }
			
			return ( $result ) ;
		    }
	    }

	
	/*==============================================================================================================
	
	    NAME
	        EnumValuesEx - Enumerates key values.
	
	    PROTOTYPE
	        $values		=  $registry -> EnumValuesEx ( $root, $keypath ) ;
	
	    DESCRIPTION
	        Enumerates key values located under the specified key path. The difference with the EnumValues() method
		is that an array of RegistryValue objects is returned.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key to be enumerated.
	  
	    RETURN VALUE
		An array whose keys are the registry value names and values are RegistryValue objects.
	
	  ==============================================================================================================*/
	public function  EnumValuesEx  ( $root, $keypath = "" )
	   {
		$values		=  $this -> EnumValues ( $root, $keypath, false ) ;
		$result		=  [] ;
		
		foreach  ( $values  as  $name => $type )
			$result [ $name ]	=  new  RegistryValue ( $this, $root, $keypath, $name, $type ) ;
		
		ksort ( $result ) ;		// It's nicer when sorted...
		
		return ( $result ) ;
	    }

	
	/*==============================================================================================================
	
	    GetLastError -
	        Retrieves the error code returned by the last called registry function.
	
	  ==============================================================================================================*/
	public function  GetLastError ( )
	   { return ( $this -> LastError ) ; }
	
	
	/*==============================================================================================================
	
	    NAME
	        GetBinaryValue - Retrieves a registry binary value.
	
	    PROTOTYPE
	        $status		=  $registry -> GetBinaryValue ( $root, $keypath, $value ) ;
	
	    DESCRIPTION
	        Retrieves a registry binary value.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be retrieved.
	  
	 	$value (string) -
	 		Value to be retrieved.
	  
	    RETURN VALUE
	        The binary value, as a binary string.
	
	  ==============================================================================================================*/
	public function  GetBinaryValue ( $root, $keypath, $value_name )
	   { 
		// Registry binary values are returned as a Variant array
		$array		=  $this -> GenericGetValue ( $root, $keypath, $value_name, "GetBinaryValue" ) ;
		$result		=  "" ;

		foreach  ( $array  as  $item )
			$result .=  sprintf ( "%02X", $item ) ;
		
		return  ( $result ) ;
	    }

	
	/*==============================================================================================================
	
	    NAME
	        GetDWORDValue - Retrieves a registry DWORD value.
	
	    PROTOTYPE
	        $status		=  $registry -> GetDWORDValue ( $root, $keypath, $value ) ;
	
	    DESCRIPTION
	        Retrieves a registry binary value.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be retrieved.
	  
	 	$value (string) -
	 		Value to be retrieved.
	  
	    RETURN VALUE
	        The DWORD value, as a 32-bit integer.
	
	  ==============================================================================================================*/
	public function  GetDWORDValue ( $root, $keypath, $value_name )
	   { 
		$result		=  $this -> GenericGetValue ( $root, $keypath, $value_name, "GetDWORDValue" ) ;
		
		return  ( ( int ) $result ) ;
	    }

	
	/*==============================================================================================================
	
	    NAME
	        GetExpandedStringValue - Retrieves a registry string value.
	
	    PROTOTYPE
	        $status		=  $registry -> GetExpandedStringValue ( $root, $keypath, $value ) ;
	
	    DESCRIPTION
	        Retrieves a registry string value, after expanding any reference to environment variables.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be retrieved.
	  
	 	$value (string) -
	 		Value to be retrieved.
	  
	    RETURN VALUE
	        The expanded string value.
	
	  ==============================================================================================================*/
	public function  GetExpandedStringValue ( $root, $keypath, $value_name )
	   { 
		$result		=  $this -> GenericGetValue ( $root, $keypath, $value_name, "GetExpandedStringValue" ) ;
		
		return  ( ( string ) $result ) ;
	    }
	
	
	
	/*==============================================================================================================
	
	    NAME
	        GetMultiStringValue - Retrieves a registry string value.
	
	    PROTOTYPE
	        $status		=  $registry -> GetMultiStringValue ( $root, $keypath, $value ) ;
	
	    DESCRIPTION
	        Retrieves a registry multi-string value as an array of strings.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be retrieved.
	  
	 	$value (string) -
	 		Value to be retrieved.
	  
	    RETURN VALUE
	        The multi-string value, as an array of strings.
	
	  ==============================================================================================================*/
	public function  GetMultiStringValue ( $root, $keypath, $value_name )
	   { 
		$result		=  $this -> GenericGetValue ( $root, $keypath, $value_name, "GetMultiStringValue" ) ;
		
		return  ( $result ) ;
	    }

	
	/*==============================================================================================================
	
	    NAME
	        GetQWORDStringValue - Retrieves a registry QWORD value.
	
	    PROTOTYPE
	        $status		=  $registry -> GetQWORDValue ( $root, $keypath, $value ) ;
	
	    DESCRIPTION
	        Retrieves a registry QWORD value.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be retrieved.
	  
	 	$value (string) -
	 		Value to be retrieved.
	  
	    RETURN VALUE
	        The QWORD value.
	
	  ==============================================================================================================*/
	public function  GetQWORDValue ( $root, $keypath, $value_name )
	   { 
		$result		=  $this -> GenericGetValue ( $root, $keypath, $value_name, "GetQWORDValue" ) ;
		
		// QWORDs are returned as variant BSTRs
		if  ( PHP_INT_SIZE  ==  4 )
			return  ( $result ) ;
		else
			return  ( ( integer ) $result ) ;
	    }
	
	
	/*==============================================================================================================
	
	    NAME
	        GetStringValue - Retrieves a registry string value.
	
	    PROTOTYPE
	        $status		=  $registry -> GetStringValue ( $root, $keypath, $value ) ;
	
	    DESCRIPTION
	        Retrieves a registry string value.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be retrieved.
	  
	 	$value (string) -
	 		Value to be retrieved.
	  
	    RETURN VALUE
	        The string value.
	
	  ==============================================================================================================*/
	public function  GetStringValue ( $root, $keypath, $value_name )
	   { 
		$result		=  $this -> GenericGetValue ( $root, $keypath, $value_name, "GetStringValue" ) ;
		
		return  ( ( string ) $result ) ;
	    }
	
	
	/*==============================================================================================================
	
	    NAME
	        GetValue - Retrieves a registry value.
	
	    PROTOTYPE
	        $status		=  $registry -> GetValue ( $root, $keypath, $value ) ;
	
	    DESCRIPTION
	        Retrieves a registry value, whatever its type.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be retrieved.
	  
	 	$value (string) -
	 		Value to be retrieved.
	  
	    RETURN VALUE
	        Depends on the underlying type of the specified value.
	  
	   NOTES
	 	This function uses the WScript.Shell API rather than the StdRegProv WMI provider.
	
	  ==============================================================================================================*/
	public function  GetValue ( $root, $keypath, $value_name )
	   {
		$this -> GetWShellInstance ( ) ;
		
		$keypath	=  self::NormalizeKey ( $keypath ) ;
		$hkroot		=  self::GetHKConstant ( $root ) ;
		$key		=  "$hkroot\\$keypath\\$value_name" ;
		$result		=  new \VARIANT() ;
		
		$result		=  self::$WShellInstance -> RegRead ( $key ) ;
		$this -> SetLastError ( 0 ) ;
		
		return ( $result ) ;
	    }

	
	/*==============================================================================================================
	
	    NAME
	        SetBinaryValue - Defines a registry binary value.
	
	    PROTOTYPE
	        $status		=  $registry -> SetBinaryValue ( $root, $keypath, $value_name, $value ) ;
	
	    DESCRIPTION
	        Creates or updates a registry binary value.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be created or updated.
	  
	 	$value_name (string) -
	 		Value to create or update.
	 
	 	$value (string or array) -
	 		Either a binary string or an array of byte values.
	  
	    RETURN VALUE
	        True if the function is successful, false otherwise. In this case, the GetLastError() method can be used
		to retrieve the Windows error code.
	
	  ==============================================================================================================*/
	public function  SetBinaryValue ( $root, $keypath, $value_name, $value )
	   { 
		if  ( is_array ( $value ) )
			$new_value	=  $value ;
		else
		   {
			$new_value	=  array ( ) ;
			$length		=  strlen ( $value ) ;
			
			for  ( $i = 0 ; $i < $length ; $i ++ )
				$new_value []	=  ord ( $value [$i] ) ;
		    }
		
		$status		=  $this -> GenericSetValue ( $root, $keypath, $value_name, $new_value, "SetBinaryValue" ) ;
		
		return  ( ( $status ) ?  false : true ) ;
	    }

	
	/*==============================================================================================================
	
	    NAME
	        SetBinaryValueFromHexString - Defines a registry binary value.
	
	    PROTOTYPE
	        $status		=  $registry -> SetBinaryValueFromHexString ( $root, $keypath, $value_name, $value ) ;
	
	    DESCRIPTION
	        Creates or updates a registry binary value.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be created or updated.
	  
	 	$value_name (string) -
	 		Value to create or update.
	 
	 	$value (string or array) -
	 		A string containing hexadecimal Ascii digits.
	  
	    RETURN VALUE
	        True if the function is successful, false otherwise. In this case, the GetLastError() method can be used
		to retrieve the Windows error code.
	
	  ==============================================================================================================*/
	public function  SetBinaryValueFromHexString ( $root, $keypath, $value_name, $value )
	   { 
		$new_value	=  array ( ) ;
		$length		=  strlen ( $value ) ;
			
		for  ( $i = 0 ; $i  <  ( $length & ~1 ) ; $i += 2 )
			$new_value []	=  ( hexdec ( $value [$i] ) << 4 ) | hexdec ( $value [$i+1] ) ;
		
		if  ( $length & 1 )
			$new_value []	=  hexdec ( $value [ $length - 1 ] ) << 4 ;

		$status		=  $this -> GenericSetValue ( $root, $keypath, $value_name, $new_value, "SetBinaryValue" ) ;
		
		return  ( ( $status ) ?  false : true ) ;
	    }

	
	/*==============================================================================================================
	
	    NAME
	        SetDWORDValue - Defines a registry DWORD value.
	
	    PROTOTYPE
	        $status		=  $registry -> SetDWORDValue ( $root, $keypath, $value_name, $value ) ;
	
	    DESCRIPTION
	        Creates or updates a registry DWORD value.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be created or updated.
	  
	 	$value_name (string) -
	 		Value to create or update.
	 
	 	$value (string or array) -
	 		DWORD value.
	  
	    RETURN VALUE
	        True if the function is successful, false otherwise. In this case, the GetLastError() method can be used
		to retrieve the Windows error code.
	
	  ==============================================================================================================*/
	public function  SetDWORDValue ( $root, $keypath, $value_name, $value )
	   { 
		$status		=  $this -> GenericSetValue ( $root, $keypath, $value_name, $value, "SetDWORDValue" ) ;
		
		return  ( ( $status ) ?  false : true ) ;
	    }

	
	/*==============================================================================================================
	
	    NAME
	        SetExpandedStringValue - Defines a registry expanded string value.
	
	    PROTOTYPE
	        $status		=  $registry -> SetExpandedStringValue ( $root, $keypath, $value_name, $value ) ;
	
	    DESCRIPTION
	        Creates or updates a registry expanded string value.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be created or updated.
	  
	 	$value_name (string) -
	 		Value to create or update.
	 
	 	$value (string or array) -
	 		String value.
	  
	    RETURN VALUE
	        True if the function is successful, false otherwise. In this case, the GetLastError() method can be used
		to retrieve the Windows error code.
	
	  ==============================================================================================================*/
	public function  SetExpandedStringValue ( $root, $keypath, $value_name, $value )
	   { 
		$value		=  ( string ) $value ;
		$status		=  $this -> GenericSetValue ( $root, $keypath, $value_name, $value, "SetExpandedStringValue" ) ;
		
		return  ( ( $status ) ?  false : true ) ;
	    }

	
	/*==============================================================================================================
	
	    NAME
	        SetMultiStringValue - Defines a registry multi string value.
	
	    PROTOTYPE
	        $status		=  $registry -> SetMultiStringValue ( $root, $keypath, $value_name, $value ) ;
	
	    DESCRIPTION
	        Creates or updates a registry multi string value.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be created or updated.
	  
	 	$value_name (string) -
	 		Value to create or update.
	 
	 	$value (string or array) -
	 		Array of strings. If specified as a single string, it will be automatically converted to a
			1-element array.
	  
	    RETURN VALUE
	        True if the function is successful, false otherwise. In this case, the GetLastError() method can be used
		to retrieve the Windows error code.
	
	  ==============================================================================================================*/
	public function  SetMultiStringValue ( $root, $keypath, $value_name, $values )
	   { 
		if  ( ! is_array ( $values ) )
			$values	=  array ( $values ) ;
		
		$new_values	=  array ( ) ;
		
		foreach  ( $values  as  $value )
			$new_values []	=  ( string ) $value ;
		
		$status		=  $this -> GenericSetValue ( $root, $keypath, $value_name, $new_values, "SetMultiStringValue" ) ;
		
		return  ( ( $status ) ?  false : true ) ;
	    }

	
	/*==============================================================================================================
	
	    NAME
	        SetQWORDValue - Defines a registry QWORD string value.
	
	    PROTOTYPE
	        $status		=  $registry -> SetQWORDValue ( $root, $keypath, $value_name, $value ) ;
	
	    DESCRIPTION
	        Creates or updates a registry QWORD string value.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be created or updated.
	  
	 	$value_name (string) -
	 		Value to create or update.
	 
	 	$value (string or double or integer) -
	 		Either a integer value, a double (which will be converted to integer) or a binary string.
	  
	    RETURN VALUE
	        True if the function is successful, false otherwise. In this case, the GetLastError() method can be used
		to retrieve the Windows error code.
	
	  ==============================================================================================================*/
	public function  SetQWORDValue ( $root, $keypath, $value_name, $value )
	   { 
		if  ( is_float ( $value ) )
			$new_value	=  sprintf ( "%.0f", $value ) ;
		else if  ( is_integer ( $value ) )
			$new_value	=  sprintf ( "%d", $value ) ;
		else
			$new_value	=  $value ;
		
		$status		=  $this -> GenericSetValue ( $root, $keypath, $value_name, $new_value, "SetQWORDValue" ) ;
		
		return  ( ( $status ) ?  false : true ) ;
	    }

	
	/*==============================================================================================================
	
	    NAME
	        SetStringValue - Defines a registry string value.
	
	    PROTOTYPE
	        $status		=  $registry -> SetStringValue ( $root, $keypath, $value_name, $value ) ;
	
	    DESCRIPTION
	        Creates or updates a registry string value.
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be created or updated.
	  
	 	$value_name (string) -
	 		Value to create or update.
	 
	 	$value (string) -
	 		String value.
	  
	    RETURN VALUE
	        True if the function is successful, false otherwise. In this case, the GetLastError() method can be used
		to retrieve the Windows error code.
	
	  ==============================================================================================================*/
	public function  SetStringValue ( $root, $keypath, $value_name, $value )
	   { 
		$value		=  ( string ) $value ;
		$status		=  $this -> GenericSetValue ( $root, $keypath, $value_name, $value, "SetStringValue" ) ;
		
		return  ( ( $status ) ?  false : true ) ;
	    }
	
	
	/*==============================================================================================================
	
	    NAME
	        SetValue - Sets a registry value.
	
	    PROTOTYPE
	        $status		=  $registry -> SetValue ( $root, $keypath, $value_name, $value, $value_type ) ;
	
	    DESCRIPTION
	        Sets a registry value. The value type can be one of the following :
		- REG_SZ
		- REG_EXPAND_SZ
		- REG_DWORD 
		- REG_BINARY (note that values larger than one DWORD are not supported by the WShell API)
	
	    PARAMETERS
	        $root (integer) -
	                Handle of the root key or one of the predefined constants HKEY_CURRENT_USER, HK_LOCAL_MACHINE,
			etc.
	  
	 	$keypath (string) -
	 		Path to the key containing the value to be retrieved.
	  
	 	$value_name (string) -
	 		Value name.
	  
	 	$value (any) -
	 		Value to be written.
	  
	 	$value_type (integer) -
	 		Registry value type.
	  
	   NOTES
	 	This function uses the WScript.Shell API rather than the StdRegProv WMI provider.
	
	  ==============================================================================================================*/
	public function  SetValue ( $root, $keypath, $value_name, $value, $value_type )
	   {
		switch  ( $value_type ) 
		   {
			case	self::REG_SZ :
				$type_name	=  'REG_SZ' ;
				break ;

			case	self::REG_EXPAND_SZ :
				$type_name	=  'REG_EXPAND_SZ' ;
				break ;

			case	self::REG_BINARY :
				$type_name	=  'REG_BINARY' ;
				break ;

			case	self::REG_DWORD :
				$type_name	=  'REG_DWORD' ;
				break ;
				
			default :
				error ( new \Thrak\System\InvalidArgumentException ( "Only values of type REG_SZ, REG_EXPAND_SZ, REG_BINARY and REG_DWORD are supported." ) ) ;
		    }
		
		$this -> GetWShellInstance ( ) ;
		
		$keypath	=  self::NormalizeKey ( $keypath ) ;
		$hkroot		=  self::GetHKConstant ( $root ) ;
		$key		=  "$hkroot\\$keypath\\$value_name" ;
		$result		=  new \VARIANT() ;
		
		$result		=  self::$WShellInstance -> RegWrite ( $key, $value, $type_name ) ;
		$this -> SetLastError ( 0 ) ;
		
		return ( $result ) ;
	    }

	
    }


/*==============================================================================================================

    RegistryValue -
        Implements a registry value.
	This class is mainly used by the EnumValuesEx() method to encapsulate the queried registry value names 
	and provide a way for read/write access.

  ==============================================================================================================*/
class  RegistryValue
   {
	// Properties 
	protected	$Registry ;			// Registry object
	protected	$Root ;				// Root key (HKEY_xxx)
	protected	$Key ;				// Key path
	protected	$ValueName ;			// Value name
	protected	$ValueType ;			// Value type
	protected	$Value		=  null ;	// And value's value, thanks Microsoft for this vocabulary confusion
	
	
	/*==============================================================================================================
	
	    NAME
	        Constructor - Creates a registry value object.
	
	    PROTOTYPE
	        $registry_value		=  new  RegistryValue ( $registry, $root, $key, $value_name, $value_type ) ;
	
	    DESCRIPTION
	        Creates a RegistryValue object, that encapsulates read and write access to a single registry value.
	 	The value is not read until a first read access to the Value property is made.
	 	This class is mainly used by the Registry::EnumValuesEx() method which returns an array of RegistryValue
	 	objects, but could be used in a standalone way as well.
	
	    PARAMETERS
	        $registry (object) -
	                Instance of a Registry class.
	  
	 	$root (integer) -
	 		Handle of the root key (Registry::HKEY_xxx constants).
	  
	 	$key (string) -
	 		Path to the key that holds the value.
	  
	 	$value_name (string) -
	 		Value name.
	  
	 	$value_type (integer) -
	 		Value type (a Registry::REG_SZ, REG_DWORD, etc. constant).
	
	    NOTES
	        The specified value type is not checked against the real underlying value type. Due to the implementation
		of the StdRegProv provider, the only way to retrieve a value type would be to enumerate all the values
		stored in the specified key, locate the value in the returned array and return its type, which would be
		wasteful in terms of performance.
	
	  ==============================================================================================================*/
	public function  __construct ( $registry, $root, $key, $value_name, $value_type ) 
	   {
		$this -> Registry	=  $registry ;
		$this -> Root		=  $root ;
		$this -> Key		=  $key ;
		$this -> ValueName	=  $value_name ;
		$this -> ValueType	=  $value_type ;

		$this -> Sync ( ) ;
	    }
	

	/*==============================================================================================================
	
	    NAME
	        __get, __set - Getters and setters for the Name, Type and Value properties.
	
	    PROTOTYPE
	        $name	=  $registry_value -> Name ;
	        $type	=  $registry_value -> Type ;
	        $value	=  $registry_value -> Value ;
	
	 	$regisry_value -> Value		=  some_value ;
	 
	    DESCRIPTION
	        The __get magic function provides access to the following properties :
	  
	 	- Root :
	 		Root key.
	 	- Key :
	 		Registry key path.
	 	- Name :
	 		Registry value name.
	 	- Type :
	 		Registry value type.
	 	- Value :
	 		Registry value's value. This value is read into memory only upon the first access to this property.
	 		You can use the Sync() method with a boolean "true" argument to force the registry value to be
	 		re-read from the registry.
	  
	 	The __set magic function writes back the specified value to the registry.
	
	  ==============================================================================================================*/
	public function  __get ( $member )
	   {
		if  ( ! strcasecmp ( $member, 'Name' ) )
			return ( $this -> ValueName ) ;
		else if  ( ! strcasecmp ( $member, 'Root' ) ) 
			return ( $this -> Root ) ;
		else if  ( ! strcasecmp ( $member, 'Key' ) ) 
			return ( $this -> Key ) ;
		else if  ( ! strcasecmp ( $member, 'Type' ) ) 
			return ( $this -> ValueType ) ;
		else if  ( ! strcasecmp ( $member, 'Value' ) )
		   {
			$this -> Sync ( ) ;
			
			return ( $this -> Value ) ;
		    }
		else 
			error ( new \Thrak\System\BadPropertyException ( "Undefined property " . get_called_class ( ) . "::$member." ) ) ;
	    }
	    
	    
	public function  __set ( $member, $value )
	   {
		if  ( ! strcasecmp ( $member, 'Name' )  ||  ! strcasecmp ( $member, 'Type' )  ||  ! strcasecmp ( $member, 'Root' )  ||
					! strcasecmp ( $member, 'Root' ) )
			error ( new \Thrak\System\ReadOnlyPropertyException ( "The " . get_called_class ( ) . "::$member property is read-only." ) ) ;
		else if  ( ! strcasecmp ( $member, 'Value' ) )
		   {
			$this -> __write ( $value ) ;
		    }
		else 
			error ( new \Thrak\System\BadPropertyException ( "Undefined property " . get_called_class ( ) . "::$member." ) ) ;
	    }
	
	
	/*==============================================================================================================
	
	    NAME
	        Sync - Syncs a registry value with the actual data from the registry.
	
	    PROTOTYPE
	        $registry_value -> Sync ( $force = false ) ;
	
	    DESCRIPTION
	        The Sync() method ensures that the current registry value stored in memory is in sync with the one 
		stored in the registry.
		This can be used between two retrievals of the Value property when the registry contents are subject to
		change. The value is effectively read again in two cases :
		- When the Value property has never been retrieved
	 	- When the $force parameter is true
	
	    PARAMETERS
	        $force (boolean) -
	                When true, forces the value to be unconditionnally retrieved.
			When false, the value will be retrieved only if the Value property has never been accessed.
	 
	  ==============================================================================================================*/
	public function  Sync ( $force = false )
	   {
		if  ( $force  ||  $this -> Value  ===  null )
			$this -> __read ( ) ;
	    }
	
	
	/*==============================================================================================================
	
	    __read - 
	        Reads the value from the registry, taking its type into account.
	
	  ==============================================================================================================*/
	private function  __read ( )
	   {
		switch  ( $this -> ValueType ) 
		   {
			case	Registry::REG_SZ	:  $this -> Value = $this -> Registry -> GetStringValue		( $this -> Root, $this -> Key, $this -> ValueName ) ; break ;
			case	Registry::REG_BINARY	:  $this -> Value = $this -> Registry -> GetBinaryValue		( $this -> Root, $this -> Key, $this -> ValueName ) ; break ;
			case	Registry::REG_DWORD	:  $this -> Value = $this -> Registry -> GetDWordValue		( $this -> Root, $this -> Key, $this -> ValueName ) ; break ;
			case	Registry::REG_QWORD	:  $this -> Value = $this -> Registry -> GetQWordValue		( $this -> Root, $this -> Key, $this -> ValueName ) ; break ;
			case	Registry::REG_MULTI_SZ	:  $this -> Value = $this -> Registry -> GetMultiStringValue	( $this -> Root, $this -> Key, $this -> ValueName ) ; break ;
			case	Registry::REG_EXPAND_SZ	:  $this -> Value = $this -> Registry -> GetExpandedStringValue	( $this -> Root, $this -> Key, $this -> ValueName ) ; break ;
			default :
				error ( new \Thrak\System\UnsupportedOperationException ( "Unsupported value type {$this -> ValueType} for the \"{$this -> ValueName}\" registry value." ) ) ;
		    }
	    }
	
	
	/*==============================================================================================================
	
	    __write - 
	        Writes the value back to the registry, taking its type into account.
	
	  ==============================================================================================================*/
	private function  __write ( $value )
	   {
		switch  ( $this -> ValueType ) 
		   {
			case	Registry::REG_SZ	:  $this -> Registry -> SetStringValue		( $this -> Root, $this -> Key, $this -> ValueName, $value ) ; break ;
			case	Registry::REG_BINARY	:  $this -> Registry -> SetBinaryValue		( $this -> Root, $this -> Key, $this -> ValueName, $value ) ; break ;
			case	Registry::REG_DWORD	:  $this -> Registry -> SetDWordValue		( $this -> Root, $this -> Key, $this -> ValueName, $value ) ; break ;
			case	Registry::REG_QWORD	:  $this -> Registry -> SetQWordValue		( $this -> Root, $this -> Key, $this -> ValueName, $value ) ; break ;
			case	Registry::REG_MULTI_SZ	:  $this -> Registry -> SetMultiStringValue	( $this -> Root, $this -> Key, $this -> ValueName, $value ) ; break ;
			case	Registry::REG_EXPAND_SZ	:  $this -> Registry -> SetExpandedStringValue	( $this -> Root, $this -> Key, $this -> ValueName, $value ) ; break ;
			default :
				error ( new \Thrak\System\UnsupportedOperationException ( "Unsupported value type {$this -> ValueType} for the \"{$this -> ValueName}\" registry value." ) ) ;
		    }
		
		$this -> Value	=  $value ;
	    }
    }
