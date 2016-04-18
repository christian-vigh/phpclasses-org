<?php
/**************************************************************************************************************

    NAME
        ApiLayer.php

    DESCRIPTION
        A base class for using the ApiLayers services.

    AUTHOR
        Christian Vigh, 02/2016.

    HISTORY
        [Version : 1.0]		[Date : 2016-02-07]     [Author : CV]
                Initial version.

 **************************************************************************************************************/
class	ApiLayerException	extends		\RuntimeException		{ }


/*==============================================================================================================

    class ApiLayer -
        Abstract base class for ApiLayers access.

  ==============================================================================================================*/
abstract class  ApiLayer
   {
	// "Standard" user agent strings, for convenience purposes
	const		USER_AGENT_IE11			=  'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko' ;
	const		USER_AGENT_FIREFOX_WIN		=  'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:26.0) Gecko/20100101 Firefox/26.0' ;
	const		USER_AGENT_CHROME_WIN		=  'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.103 Safari/537.36' ;
	const		USER_AGENT_SAFARI_WIN		=  'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.57.2 (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2' ;
	const		USER_AGENT_OPERA_WIN		=  'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36 OPR/18.0.1284.68' ;


	/****************************************************************************************************************
	
	        Type constants for parameters coming from derived classes. Note that type enforcement is not very
		elaborated.
	
	 ***************************************************************************************************************/
	// Parameter can be any string - this is the default type
	const		APILAYER_PARAMETER_STRING		=  0x00000000 ;
	// Parameter is either a 0 or a 1
	const		APILAYER_PARAMETER_BOOLEAN		=  0x00000001 ;
	// Parameter is an integer value
	const		APILAYER_PARAMETER_INTEGER		=  0x00000002 ;
	// Parameter is a case-insensitive keyword that must belong to a predefined list
	const		APILAYER_PARAMETER_KEYWORD		=  0x00000003 ;
	// Parameter is a viewport dimensions (eg, 1400x900)
	const		APILAYER_PARAMETER_VIEWPORT		=  0x00000004 ;
	// The parameter value specified for a query url is different from the property value.
	// This is the case for example of the secret_key parameter : its property value is the secret key you
	// defined in your screenshotlayer dashboard. However, the value specified on the query url is actually
	// the md5 hash of the captured url + secret key strings.
	const		APILAYER_PARAMETER_FLAG_COMPUTED	=  0x10000000 ;
	// Parameters that require a POST request
	const		APILAYER_PARAMETER_FLAG_POST		=  0x20000000 ;
	// Options to be applied to string parameters
	const		APILAYER_PARAMETER_FLAG_UTF8_ENCODE	=  0x01000000 ;		// Convert string to utf8 (default : off)
	const		APILAYER_PARAMETER_FLAG_HTML_ENTITIES	=  0x02000000 ;		// Interpret html entities before utf8 encoding (default : off)
	const		APILAYER_PARAMETER_FLAG_NO_URL_ENCODE	=  0x04000000 ;		// Do not urlencode() this parameter

	// A mask to isolate flags from parameter type.
	const		APILAYER_PARAMETER_TYPE_MASK		=  0xFFFF ;

	// Required access key for all ApiLayer invocations
	public		$AccessKey ;
	// When true, https access will be used
	public		$UseHttps		=  false ;
	// Character set to be used when issuing queries to the Apilayer API
	public		$QueryCharset		=  false ;
	// Api url - must be set by derived classes
	protected	$ApiUrl ;
	// Extra Api parameters - must be defined by derived classes and passed to the class constructor.
	protected	$Parameters		=  [] ;
	// Last query result, with the following properties :
	// - $result -> Status (boolean) :
	// 	A boolean status indicating whether the last operation was successful or not.
	// 	The initial value (before the very first operation) is always true.
	// 	
	// - $result -> Error (ApiLayerResultError object) :
	// 	A structure providing error data. If the 'status' field is true, this structure will be set to
	// 	null, otherwise it will contain the following properties :
	// 	- Code (integer) :
	// 		An http error code.
	// 	- Type (string) :
	// 		Error type (usually a constant).
	// 	- Message (string) :
	// 		Error message.
	//
	// - $result -> Data (mixed) :
	//	Api result data, if the call succeded.
	public		$QueryResult ;
	// Last executed query
	public		$LastQuery ;
	// Http query data - http headers sent during the last query
	public		$HttpQueryData ;


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        Constructor
	
	    PROTOTYPE
	        parent::__construct ( $api_url, $access_key, $use_https = false, $parameters = [] ) ;
	
	    DESCRIPTION
	        Builds an ApiLayer object. This constructor must be called by all derived ApiLayer classes to correctly
		initialize the object.
	
	    PARAMETERS
	        $api_url (string) -
	                Base url for the ApiLayer API implemented by the derived class (for example, 
			api.screenshotlayer.com/api/capture). The url scheme (http://) can be omitted.

		$access_key (string) -
			Required access key. Specific to the registered user of the corresponding ApiLayer Api.

		$use_https (boolean) -
			Set this parameter to true to use the https protocol instead of http.
			Note that no provision is made to supply trusted certificates.

		$parameters (array of associative arrays) -
			Parameter definitions implemented by the derived classes.
			Each parameter is an associative array that can contain the following entries :

			- 'name' (string) :
				Parameter name, as it will appear in the final url.
			- 'property' (string or array of strings) :
				Property name(s) that will be accessible through the instanciated object. It can be either a string or
				an array of strings that defines property names and their aliases.
			- 'value' (string) :
				Initial value for this parameter. The default is null, which means that the parameter will not be included
				in the final Api layer url.
			- 'url-parameter' (boolean) :
				When true, the parameter value will be included in the final url. The default is true.
			- 'required' (boolean) :
				When true, the corresponding parameter is required. An exception will be thrown if its value has not been
				specified.
			- 'readonly' (boolean) :
				When true, the value cannot be set. This is used for already initialized entries, or for computed entries
				having a callback function.
			- 'get', 'set' (callback) :
				Callback function to be used for getting/setting the value. The function must have the following
				signature :
						string get_function ( $parmeter ) ;
						void   set_function ( &$parameter, $value ) ;
				where $parameter is the parameter definition and $value the parameter value.
			- 'queryget' (callback) -
				Callback function to be used hen building the url query string. This a
			- 'type' (one of the self::APILAYER_PARAMETER_* constants) :
				Parameter type. Can be any one of :
				- APILAYER_PARAMETER_STRING :
					Parameter value can be any string.
				- APILAYER_PARAMETER_BOOLEAN :
					Parameter value is a boolean expressed under the form of either 0 or 1.
				- APILAYER_INTEGER :
					Parameter value must be an integer.
				- APILAYER_KEYWORD :
					Parameter value is a case-insensitive keyword that must belong to a predefined list.
					In this case, the 'keywords' entry must contain the list of authorized keywords.
			- 'keywords' :
				When the parameter type is APILAYER_KEYWORD, the 'keywords' entry is required and must contain
				the list of authorized values. It can be either a list of strings or associative array entries ;
				the keys give the authorized input entries, while the values give the value returned when building the
				final url ; for example, the following entry :
					'keywords'		=>  [ 'png', 'gif', 'jpg', 'jpeg' => 'jpg', 'jpe' => 'jpg' ]
				will give :
					. 'png', if the input entry is 'png', whether expressed in lowercase or uppercase (PNG).
					. Same for 'gif'...
					. And 'jpg', if the input is 'jpg', 'jpeg' or 'jpe'
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  __construct ( $api_url, $access_key, $use_https = false, $parameters = [] )
	   {
		// Loop through derived class' parameters
		foreach  ( $parameters  as  $parameter )
		   {
			// The default parameter value is null, meaning that no value was specified
			if  ( ! isset ( $parameter [ 'value' ] ) )
				$parameter [ 'value' ]		=  null ;

			// All parameters are included in the final url, unless the 'url-parameter' entry is set to false.
			// The default is true
			if  ( ! isset ( $parameter [ 'url-parameter' ] ) )
				$parameter [ 'url-parameter' ]	=  true ;

			// Make sure the 'property' entry is an array of property name and aliases
			if  ( ! is_array ( $parameter [ 'property' ] ) )
				$parameter [ 'property' ]	= [ $parameter [ 'property' ] ] ;

			// Parameters are not required, by default
			if  ( ! isset ( $parameter [ 'required' ] ) )
				$parameter [ 'required' ]	=  false ;

			// The default type is string, if not specified
			if  ( ! isset ( $parameter [ 'type' ] ) )
				$parameter [ 'type' ]		 =  self::APILAYER_PARAMETER_STRING ;
			else if  ( ! $parameter [ 'type' ]  &  self::APILAYER_PARAMETER_TYPE_MASK )
				$parameter [ 'type' ]		|=  self::APILAYER_PARAMETER_STRING ;

			// All entries are read-write by default
			if  ( ! isset ( $parameter [ 'readonly' ] ) )
				$parameter [ 'readonly' ]	=  false ;

			// Make sure there is a callback entry
			if  ( ! isset ( $parameter [ 'get' ] ) )
				$parameter [ 'get' ]		=  false ;

			if  ( ! isset ( $parameter [ 'set' ] ) )
				$parameter [ 'set' ]		=  false ;

			if  ( ! isset ( $parameter [ 'queryget' ] ) )
				$parameter [ 'queryget' ]	=  false ;

			// Make sure that there is a 'keywords' entry for keyword-type parameters 
			$type	=  $parameter [ 'type' ]  &  self::APILAYER_PARAMETER_TYPE_MASK ;

			if  ( $type  ==  self::APILAYER_PARAMETER_KEYWORD )
			   {
				if  ( ! isset ( $parameter [ 'keywords' ] ) ) 
					error ( new ApiLayerException ( "Missing 'keywords' entry for parameter \"{$parameter[ 'name' ]}\"" ) ) ;

				// Replace it with an associative array using the lowercased keyword as the key
				// (but only for explicit associative keys)
				$new_keywords	=  [] ;

				foreach ( $parameter [ 'keywords' ]  as  $name => $value )
				   {
					if  ( is_numeric ( $name ) )
						$new_keywords [ strtolower ( $value ) ]		=  $value ;
					else
						$new_keywords [ strtolower ( $name ) ]		=  $value ;
				    }

				$parameter [ 'keywords' ]	=  $new_keywords ;
			    }
			// Integer values must have a 'range' entry
			else if  ( $type  ==  self::APILAYER_PARAMETER_INTEGER )
			   {
				if  ( ! isset ( $parameter [ 'range' ] ) )
					$parameter [ 'range' ]	=  [ PHP_INT_MIN, PHP_INT_MAX ] ;
				else if  ( ! is_array ( $parameter [ 'range' ] )  ||  count ( $parameter [ 'range' ] )  !=  2 )
					error ( new ApiLayerException ( "The 'range' entry for parameter \"{$parameter[ 'name' ]}\" must be an array containing 2 values" ) ) ;
				else if  ( $parameter [ 'range' ] [0]  > $parameter [ 'range' ] [1] )
					error ( new ApiLayerException ( "The first element of the 'range' entry for parameter \"{$parameter[ 'name' ]}\" must be less than its second element" ) ) ;
			    }

			$this -> Parameters []	=  $parameter ;
		    }

		// Remove any scheme from the supplied api url
		$this -> ApiUrl		=  preg_replace ( '-^[^:]+://-', '', $api_url ) ;

		$this -> AccessKey	=  $access_key ;
		$this -> UseHttps	=  $use_https ;
		$this -> SetResult ( ) ;

		// Check that no parameter names or property names are defined twice
		$parameter_count	=  count ( $this -> Parameters ) ;

		for  ( $i = 0 ; $i  <  $parameter_count ; $i ++ )
		   {
			for  ( $j = $i + 1 ; $j  <  $parameter_count ; $j ++ )
			   {
				if  ( ! isset ( $this -> Parameters [ 'name' ] ) )
					continue ;

				if  ( $this -> Parameters [$i] [ 'name' ]  &&  $this -> Parameters [$j] [ 'name' ]  &&
						$this -> Parameters [$i] [ 'name' ]  ==  $this -> Parameters [$j] [ 'name' ] )
					error ( new ApiLayerException ( "Url parameter \"{$this -> Parameters [$j] [ 'name' ]}\" has been defined more than once" ) ) ;

				foreach  ( $this -> Parameters [$j] [ 'property' ]  as  $property )
				   {
					if  ( in_array ( $property, $this -> Parameters [$i] [ 'property' ] ) )
						error ( new ApiLayerException ( "Property name \"$property\" has been defined for both parameters " .
								"\"{$this -> Parameters [$i] [ 'name' ]}\" and \"{$this -> Parameters [$i] [ 'name' ]}\"" ) ) ;
				    }
			    }
		    }
	    }


	/**************************************************************************************************************
	 **************************************************************************************************************
	 **************************************************************************************************************
	 ******                                                                                                  ******
	 ******                                                                                                  ******
	 ******                                         MAGIC FUNCTIONS                                          ******
	 ******                                                                                                  ******
	 ******                                                                                                  ******
	 **************************************************************************************************************
	 **************************************************************************************************************
	 **************************************************************************************************************/

	// __get -
	//	Retrieves a parameter value defined by one of its 'property' entries in the $Parameters array
	public function  __get ( $member )
	   {
		foreach  ( $this -> Parameters  as  $parameter )
		   {
			if  ( in_array ( $member, $parameter [ 'property' ] ) )
				return ( $this -> GetProperty ( $parameter ) ) ;
		    }

		error ( new \Thrak\System\UndefinedPropertyException ( $member ) ) ;
	    }


	// __set -
	//	Sets a parameter value defined by one of its 'property' entries in the $Parameters array
	public function  __set ( $member, $value )
	   {
		foreach  ( $this -> Parameters  as  &$parameter )
		   {
			if  ( in_array ( $member, $parameter [ 'property' ] ) )
			   {
				$this -> SetProperty ( $parameter, $value ) ;
				return ;
			    }
		    }

		error ( new \Thrak\System\UndefinedPropertyException ( $member ) ) ;
	    }


	// __tostring -
	//	Returns the final url used to query the api
	public function  __tostring ( )
	   { return ( $this -> GetQuery ( ) ) ; }


	/**************************************************************************************************************
	 **************************************************************************************************************
	 **************************************************************************************************************
	 ******                                                                                                  ******
	 ******                                                                                                  ******
	 ******                                         PUBLIC FUNCTIONS                                         ******
	 ******                                                                                                  ******
	 ******                                                                                                  ******
	 **************************************************************************************************************
	 **************************************************************************************************************
	 **************************************************************************************************************/

	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        GetQuery, GetQueryParameters, GetQueryUrl - Returns the query url
	
	    PROTOTYPE
	        $query	=  $apilayer -> GetQuery ( ) ;
		$params	=  $apilayer -> GetQueryParameters ( ) ;
		$url	=  $apilayer -> GetQueryUrl ( ) ;
	
	    DESCRIPTION
	        Returns the query url taking into account the query parameters defined by this class and its derived
		classes.
		GetQuery() returns the whole query that can be used in a GET request.
		GetQueryParameters() returns the parameter part of the query.
		GetQueryUrl() returns only the url part, without parameters.
	
	    NOTES
	        An ApiLayerException exception will be thrown if one of the required parameter(s) is missing.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  GetQuery ( )
	   {
		$scheme			=  ( $this -> UseHttps ) ?  'https' : 'http' ;
		$url			=  $scheme . '://' . $this -> ApiUrl . '?' . $this -> GetQueryParameters ( ) ;

		return ( $url ) ;
	    }


	public function  GetQueryUrl ( )
	   {
		$scheme			=  ( $this -> UseHttps ) ?  'https' : 'http' ;
		$url			=  $scheme . '://' . $this -> ApiUrl ;

		return ( $url ) ;
	    }


	public function  GetQueryParameters ( $post = false )
	   {
		$url_parameters		=  [ 'access_key=' . $this -> AccessKey ] ;

		// Loop through parameters
		foreach  ( $this -> Parameters  as  $parameter )
		   {
			// Parameters not tagged as "url parameter" will be ignored
			if  ( ! $parameter [ 'url-parameter' ] ) 
				continue ;

			// POST parameters requested : ignore non-POST ones
			if  ( $post  &&  ! ( $parameter [ 'type' ]  &  self::APILAYER_PARAMETER_FLAG_POST ) )
				continue ;

			// GET parameters requested : ignore POST parameters
			if  ( ! $post  &&  ( $parameter [ 'type' ]  &  self::APILAYER_PARAMETER_FLAG_POST ) )
				continue ;

			// Check if required parameters have a value (this is the case for example with the Access key)
			if  ( $parameter [ 'required' ] )
			   {
				if  ( $parameter [ 'value' ]  ===  null )
					error ( new ApiLayerException ( "Missing required parameter \"{$parameter [ 'name' ]}\"" ) ) ;
			    }

			// Ignore optional parameters without a value
			$value		=  $this -> GetProperty ( $parameter, true ) ;

			if  ( $value  ===  null )
				continue ;
			
			// Collect this parameter
			$url_parameters []	=   $parameter [ 'name' ] . '=' . $value ;
		    }

		return ( implode ( '&', $url_parameters ) ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        Execute - Executes an ApiLayer query.
	
	    PROTOTYPE
	        $result		=  $apilayer -> Execute ( $query = null ) ;
	
	    DESCRIPTION
	        Executes the specified query, or the query returned by the GetQuery() method if $query is empty.
	
	    PARAMETERS
	        $query (string) -
	                Query string to be executed. If not specified, the GetQuery() method will be used to form the
			final query.
	
	    RETURN VALUE
	        Returns the contents of the query execution.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  Execute ( $query = false )
	   {
		if  ( ! $query )
			$query	=  $this -> GetQuery ( ) ;

		$post	=  $this -> HasPostParameters ( ) ;

		// Save this query
		$this -> LastQuery	=  $query ;

		// Get query charset
		$charset		=  $this -> QueryCharset ;

		// Give derived classes a chance to perform pre-checks before issuing the query
		$this -> OnBeforeQuery ( ) ;

		// Curl is used here, because we may have to set options specific to https access
		$curl	=  curl_init ( ) ;

		if  ( $post )
		   {
			curl_setopt ( $curl, CURLOPT_URL, $query ) ;
			curl_setopt ( $curl, CURLOPT_POST, 1 ) ;

			$params		=  $this -> GetQueryParameters ( true ) ;
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $params ) ;

			if  ( $charset )
				curl_setopt ( $curl, CURLOPT_HTTPHEADER, [ "Content-type: multipart/form-data; charset=$charset" ] ) ;
		    }
		else
		   {
			curl_setopt ( $curl, CURLOPT_URL, $query ) ;

			if  ( $charset )
				curl_setopt ( $curl, CURLOPT_HTTPHEADER, [ "Content-type: text/html; charset=$charset" ] ) ;
		    }

		// Say that we want to retrieve query response
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true ) ;

		// Perform https requests without verifying originating CA authority
		if  ( $this -> UseHttps )
			curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false ) ;

		// We will save the http request contents, so enable the option
		curl_setopt ( $curl, CURLINFO_HEADER_OUT, true ) ;

		// Execute the request
		$result =  curl_exec ( $curl ) ;

		// Save the contents of the headers sent
		$this -> HttpQueryData	=  curl_getinfo ( $curl, CURLINFO_HEADER_OUT ) ;

		curl_close ( $curl ) ;

		// Perform checkings specific to the derived class
		$this -> CheckResult ( $result ) ;

		// Be consistent : give derived classes a chance to perform post-checks after the query has been issued
		$this -> OnAfterQuery ( ) ;

		// All done, return
		return ( $result ) ;
	    }


	/**************************************************************************************************************
	 **************************************************************************************************************
	 **************************************************************************************************************
	 ******                                                                                                  ******
	 ******                                                                                                  ******
	 ******                                       PROTECTED FUNCTIONS                                        ******
	 ******                                                                                                  ******
	 ******                                                                                                  ******
	 **************************************************************************************************************
	 **************************************************************************************************************
	 **************************************************************************************************************/

	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        CheckResult - Checks a query execution result.
	
	    PROTOTYPE
	        $apilayer -> CheckResult ( $result, $query ) ;
	
	    DESCRIPTION
	        Checks the execution result of the specified query. Throws an exception if an error occurred.
		This method can be overridden by derived classes to perform specific result checks, but the derived
		class CheckResult() method should call its parent one.
	
	    PARAMETERS
	        $result (string) -
	                Resulting contents returned by the execution of a query.

		$query (string) -
			Query url.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	protected function  CheckResult ( $result )
	   {
		if  ( empty ( $result ) )
		   {
			$class		=  basename ( get_called_class ( ) ) ;
			$this -> SetError ( -1, 'empty_response', 'Empty message', "Unexpected empty response received from $class" ) ;
		    }
		else 
		   {
			$json_result	=  json_decode ( $result ) ;

			if  ( isset ( $json_result -> success ) )
			   {
				$this -> SetResult ( $json_result ) ;
				$this -> QueryResult -> ThrowException ( ) ;
			    }
			else
			   {
				$this -> SetResult ( [ 'success' => true, 'error' => null, 'data' => $json_result ] ) ;
			    }
		    }
	    }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        SetResult - Assigns the result of the last executed query.
	
	    PROTOTYPE
	        $apilayer -> SetResult ( $result ) ;
	
	    DESCRIPTION
		Sets the QueryResult object for an api request. The accepted format can either be an stdClass object or 
		an array, or a string, since it can be called for a json_decode() object or an array that sets initial 
		values to 'success', or even with an http request result.
	
	    PARAMETERS
	        $result (null or json object or json string or array) -
	                A thing that will be used to set the QueryResult property, ensuring that there are three 
			"standard" fields : 'success', 'error' and 'data'.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	protected function  SetResult  ( $result = null )
	   {
		// No result provided means everything is ok
		if  ( $result  ===  null )
		   {
			$success	=  true ;
			$error		=  null ;
			$data		=  null ;
		    }
		// Results is an array
		else if  ( is_array ( $result ) ) 
		   {
			$success	=  $result [ 'success' ] ;
			$error		=  $result [ 'error' ] ;
			$data		=  $result [ 'data' ] ;
		    }
		// Result is either a json string or a json object (stdClass)
		else
		   {
			if  ( is_string ( $result ) ) 
				$json_result	=  json_decode ( $result ) ;
			else
				$json_result	=  $result ;

			// Either a json object was provided, or the supplied json string could be decoded successfully
			if  ( $json_result )
			   {
				$success	=  $json_result -> success ;
				$error		=  ( isset ( $json_result -> error ) ) ?  $json_result -> error : null ;
				$data		=  ( isset ( $json_result -> data  ) ) ?  $json_result -> data  : null ;
			    }
			// The supplied json string was invalid
			else
			   {
				$success	=  true ;
				$error		=  null ;
				$data		=  null ;
			    }
		    }

		// All done, build the query result object
		$this -> QueryResult	=  new ApiLayerResult ( $this, $success, $error, $data ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        SetError - Sets an error condition during an apilayer query.
	
	    PROTOTYPE
	        $apilayer -> SetError ( $code, $type, $info, $throw = true ) ;
	
	    DESCRIPTION
	        Sets an error condition during an apilayer query. This function is aimed at derived classes that need
		to report (and optionally throw) an error.
		The $code, $type and $info parameters are here to mimic as much as possible the errors that can be
		returned by the ApiLayers api.
	
	    PARAMETERS
	        $code (integer) -
	                Error code. 

		$type (string) -
			Error type (a string that represents a keywords).

		$info (string) -
			Error message.

		$throw (boolean) -
			When true, an exception will be thrown after setting the QueryResult property.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	protected function  SetError ( $code, $type, $info, $throw = true )
	   {
		$error		=  new \stdClass ( ) ;
		$error -> code	=  $code ;
		$error -> type	=  $type ;
		$error -> info	=  $info ;

		$this -> SetResult ( [ false, $error, null ] ) ;

		if  ( $throw )
			$this -> QueryResult -> ThrowException ( ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        GetProperty - Gets a property value.
	
	    PROTOTYPE
	        $value	= $apilayer -> GetProperty ( $parameter, $url_value = false ) ;
	
	    DESCRIPTION
	        Gets a property value (properties are defined through the $Parameters member).
		Derived classes can override this method to perform special processing before returning a parameter
		value.
	
	    PARAMETERS
	        $parameter (associative array) -
	                Parameter definition array.

		$url_value (boolean) -
			When true, the 'queryget' callback will be invoked to compute the parameter value for the
			url, if the parameter type has the APILAYER_PARAMETER_FLAG_COMPUTED flag set.
	
	    RETURN VALUE
	        The property value. 
	
	 *-------------------------------------------------------------------------------------------------------------*/
	protected function  GetProperty ( $entry, $url_value = false )
	   {
		if  ( $url_value  &&  ( $entry [ 'type' ]  &  self::APILAYER_PARAMETER_FLAG_COMPUTED )  &&  $entry [ 'queryget' ] ) 
		   {
			if  ( $entry [ 'value' ]  !==  null )
			   {
				$callback	=  $entry [ 'queryget' ] ;
				$value		=  $callback ( $entry ) ;
			    }
			else
				$value		=  null ;
		    }
		else if  ( $entry [ 'get' ] )
		   {
			if  ( $entry [ 'value' ]  !==  null )
			   {
				$callback	=  $entry [ 'get' ] ;
				$value		=  $callback ( $entry ) ;
			    }
			else
				$value		=  null ;
		    }
		else
			$value		=  $entry [ 'value' ] ;

		// Non-null string values may require additional character processing
		if  ( $value  !==  null )
		   {
			if  ( ( $entry [ 'type' ]  &  self::APILAYER_PARAMETER_TYPE_MASK )  ==  self::APILAYER_PARAMETER_STRING )
			   {
				if  ( $entry [ 'type' ]  &  self::APILAYER_PARAMETER_FLAG_UTF8_ENCODE )
					$value		=  utf8_encode ( $value ) ;

				if  ( $entry [ 'type' ]  &  self::APILAYER_PARAMETER_FLAG_HTML_ENTITIES )
					$value		=  html_entity_decode ( $value ) ;
			    }

			// Urlencode the value unless otherwise specified
			if  ( $url_value  &&  ! ( $entry [ 'type' ] & self::APILAYER_PARAMETER_FLAG_NO_URL_ENCODE ) )
				$value	=  urlencode ( $value ) ;
		    }

		return ( $value ) ; 
	    }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        SetProperty - Sets a property value.
	
	    PROTOTYPE
	        $apilayer -> SetProperty ( &$parameter, $value ) ;
	
	    DESCRIPTION
	        Sets the 'value' entry of the specified parameter to $value.
		Derived classes can override this method to perform special processing before setting a parameter
		value. 
	
	    PARAMETERS
	        $parameter (associative array) -
	                Parameter definition array.

		$value (string) -
			Value to be assigned to the specified parameter.
	
	    NOTES
	        The standard Setroperty() method checks that the parameter value is conformant to its type (ie, one of
		the APILAYER_PARAMETER_* constants). If a derived class implements this method for parameter-specific
		purpose, the parent method must called anyway for non-specific parameters.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	protected function  SetProperty ( &$entry, $value )
	   {
		if  ( $entry [ 'readonly' ] )
			error ( new ApiLayerException ( "The \"{$entry [ 'name' ]}\" parameter is read-only" ) ) ;

		if  ( $entry [ 'set' ] )
		   {
			$callback	=  $entry [ 'set' ] ;
			$callback ( $entry, $value ) ;

			return ;
		    }

		// Apply value checking depending on parameter type
		$type		=  $entry [ 'type' ]  &  self::APILAYER_PARAMETER_TYPE_MASK ;

		switch  ( $type ) 
		   {
			// Boolean parameters are either "0" or "1"
			case	self::APILAYER_PARAMETER_BOOLEAN :
				$entry [ 'value' ]	=  ( $value ) ?  "1" : "0" ;
				break ;

			// Integer type : check that the supplied value is an integer
			case	self::APILAYER_PARAMETER_INTEGER :
				if  ( ! is_numeric  ( $value )  ||  ( integer ) $value  !=  $value )
					error ( new ApiLayerException ( "Invalid integer value '$value' for the \"{$entry [ 'name' ]}\" parameter" ) ) ;

				if  ( $value  <  $entry [ 'range' ] [0]  ||  $value  >  $entry [ 'range' ] [1] )
					error ( new ApiLayerException ( "The integer value '$value' for the \"{$entry [ 'name' ]}\" parameter " .
							"must be in the range [{$entry [ 'range' ] [0]}..{$entry [ 'range' ] [1]}]" ) ) ;

				$entry [ 'value' ]	=  ( integer ) $value ;
				break ;

			// Keyword type : check that the supplied value is in the list of authorized keywords
			case	self::APILAYER_PARAMETER_KEYWORD :
				$lcvalue	=  strtolower ( $value ) ;

				if  ( ! isset ( $entry [ 'keywords' ] [ $lcvalue ] ) ) 
					error ( new ApiLayerException ( "Invalid keyword '$value' for the \"{$entry [ 'name' ]}\" parameter" ) ) ;

				$entry [ 'value' ]	=  $entry [ 'keywords' ] [ $lcvalue ] ;
				break ;

			// Viewport type : the value must be of the form "int1xint2"
			case	self::APILAYER_PARAMETER_VIEWPORT :
				if  ( preg_match ( '/ (?P<width> \d+) x (?P<height> \d+)/imsx', $value, $match ) )
					$entry [ 'value' ]	=  $value ;
				else
					error ( new ApiLayerException ( "Invalid viewport dimensions '$value' for the \"{$entry [ 'name' ]}\" parameter" ) ) ;
				break ;

			// For all other kind of values, no special processing is required
			default :
				$entry [ 'value' ]	=  $value ;
		    }
	    }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        HasPostParameters - Checks if there are any POST parameters for the current query.
	
	    PROTOTYPE
	        $status		=  $apilayer -> HasPostParameters ( ) ;
	
	    DESCRIPTION
	        Checks if there are any POST parameters for the current query. This method is used by the Execute()
		method to determine whether a POST or GET query should be issued.
	
	    RETURN VALUE
	        True if there is at least one parameter definition requiring that a POST request be issued, false
		otherwise.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	protected function  HasPostParameters ( )
	   {
		foreach  ( $this -> Parameters  as  $parameter )
		   {
			if  ( $parameter [ 'url-parameter' ]  &&  $parameter [ 'value' ]  !==  null  &&
					( $parameter [ 'type' ]  &&  self::APILAYER_PARAMETER_FLAG_POST ) )
				return ( true ) ;
		    }

		return ( false ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        OnBeforeQuery, OnAfterQuery - Perform pre- and post-query checks.
	
	    PROTOTYPE
	        $apilayer -> OnBeforeQuery ( ) ;
		$apilayer -> OnAfterQuery ( ) ;
	
	    DESCRIPTION
	        These two methods can be considered as "events" called before and after issuing a query. The derived
		classes can perform further checks (other than the default ones that handle parameter definitions) and
		throw an exception if needed.
		This is the case for example of the PdfLayer api, where either the document_url or document_html
		parameter is required. This case cannot be solved through simple declarative paraameter structures as 
		they are currently implemented, thus the need to be able to take the control before it's too late.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	protected function  OnBeforeQuery ( )
	   { }

	protected function  OnAfterQuery ( ) 
	   { }
    }


/*==============================================================================================================

    class ApiLayerResult -
        Implements the result of a call to the ApiLayer api.
	This class is intended to be instantiated from the ApiLayer class only.
	The constructor takes as parameter a json-decoded object.

  ==============================================================================================================*/
class  ApiLayerResult
   {
	public		$Parent ;			// Parent ApiLayer object
	public		$Success ;			// True if the api call succeeded
	public		$Error ;			// ApiLayerResultError object, or null if no error occurred
	public		$Data ;				// Data returned by the Apilayer call


	public function  __construct ( $parent, $success, $error, $data )
	   {
		$this -> Parent		=  $parent ;
		$this -> Success	=  $success ;
		$this -> Error		=  ( $success ) ?  null : new  ApiLayerResultError ( $parent, $error ) ;
		$this -> Data		=  $data ;
	    }


	// ThrowException -
	//	Throws an exception if the current api call has failed.
	public function  ThrowException ( $message = null )
	   {
		if  ( ! $this -> Success )
		   {
			if  ( ! $message )
			   {
				$class		=  basename ( get_class ( $this -> Parent ) ) ;
				$message	=  "Error in the $class API" ;
			    }

			throw ( new ApiLayerException ( $this -> Error -> GetMessage ( $message ) ) ) ;
		    }
	    }


	public static function  __dump_debuginfo ( )
	   { return ( [ 'hidden' => [ 'Parent' ] ] ) ; }
    }




/*==============================================================================================================

    class ApiLayerResultError -
        Implements an error object resulting from an ApiLayer call.
	This class is intended to be instantiated from the ApiLayer class only.
	The constructor takes as parameter a json-decoded object.

  ==============================================================================================================*/
class  ApiLayerResultError
   {
	public		$Parent ;				// Parent ApiLayer object
	public		$Code		=  0 ;			// Error code
	public		$Type		=  null ;		// Error type (a keyword)
	public		$Message	=  null ;		// Error message


	public function  __construct ( $parent, $error )
	   {
		if  ( $error )
		   {
			$this -> Code		=  $error -> code ;
			$this -> Type		=  $error  -> type ;
			$this -> Message	=  ( isset ( $error -> info ) ) ?  $error -> info : $error -> type ;
		    }

		$this -> Parent		=  $parent ;
	    }


	public function  GetMessage ( $message )
	   {
		$text	=  "$message :\n" .
				"Query   : {$this -> Parent -> LastQuery}\n" .
				"Message : {$this -> Message}\n" .
				"Code    : {$this -> Code}\n" .
				"Type    : {$this -> Type}" ;

		throw ( new ApiLayerException ( $text ) ) ;
	    }


	public static function  __dump_debuginfo ( )
	   { return ( [ 'hidden' => [ 'Parent' ] ] ) ; }
    }