<?php
/**************************************************************************************************************

    NAME
        ScreenshotLayer.php

    DESCRIPTION
        Implements access to the ApiLayers Screenshot API.

	Using the ScreenShotLayer object is pretty simple. Just instantiate an object, define the required 
	properties, and call one of the ScreenshotLayer public methods ; the following example will capture a 
	screenshot from http://www.google.com and return the image contents in the $capture
	variable (if an error occurs, an ApiLayerException exception will be thrown) :

		$screenshot		=  new ScreenshotLayer ( $my_access_key ) ;
		$capture		=  $screenshot -> CapturePage ( "http://www.google.com" ) ;

	Various properties may be assigned ; they mimic their counterpart in the screenshot layer api but 
	aliases are also available ; they are listed below :

	- accept_lang or accept_language or AcceptLanguage (string) :
		specify a custom Accept-Language HTTP header to send with your request. For example :
		'fr', 'en', 'en-US', etc.

	- AccessKey (string) :
		Specify the access key attributed by the Apilayers site and available in your dashboard.

	- css_url or CssUrl (string) :
		Attaches an URL containing a custom CSS stylesheet.

	- delay or Delay (integer) :
		Specifies a delay in seconds before a screenshot is captured.

	- export or ExportTo (string) :
		Exports snapshot via custom ftp path or using your AWS S3 user details.

	- force or Force (boolean of the form zero or one) :
		Set to "1" if you want the capture to be refreshed.
	
	- format or Format (keyword) :
		Specifies image format : "png" (default), "gif" or "jpg"/"jpe"/"jpeg".

	- fullpage or FullPage (boolean of the form zero or one) :
		Set to "1" if you want to capture the full height of the target website.

	- placeholder or PlaceHolder ("1" or url) :
		Attach an URL containing a custom placeholder image or set to "1" to use default placeholder.

	- secret_key or SecretKey (string) :
		If you have activated your secret key in the screenshotlayer api, then you will have to
		set the SecretPassword property to that key.
		The SecretKey readonly property will return your own secret key for the given url,
		which is the md5 hash of the requested url catenated with your secret key (set through
		the SecretPassword property).

	- ttl or Ttl (integer) :
		Defines the time (in seconds) your snapshot should be cached. The default is 2592000 (30 days).

	- UseHttps (boolean) :
		Set to true if you want access through secure http. The default is false (use regular
		http protocol).
		Note that no provision is currently made for supporting CA certificates.

	- url or Url (string) :
		Url of the web page to be captured.

	- user_agent or UserAgent (string) :
		Specifies a custom User-Agent HTTP header to send with your request.
		You can use one of the ScreenshotLayer::USER_AGENT_* strings for that, or use your own
		user agent string.

	- viewport or Viewport (string) :
		Specifies preferred viewport dimensions in pixels (default : 1440x900).

	- width or Width (integer) :
		Specifies preferred screenshot width.

	Note that the Capture property will contain image data after a successful call to CapturePage().
		
    AUTHOR
        Christian Vigh, 02/2016.

    HISTORY
        [Version : 1.0]		[Date : 2016-02-07]     [Author : CV]
                Initial version.

 **************************************************************************************************************/
require ( dirname ( __FILE__ ) . '/ApiLayer.php' ) ;


/*==============================================================================================================

    class ScreenshotLayer -
        Encapsulates access to the screenshot api from ApiLayers.

  ==============================================================================================================*/
class	ScreenshotLayer		extends		ApiLayer
   {
	// Screenshot possible image formats
	const	FORMAT_PNG		=  'png' ;
	const	FORMAT_JPEG		=  'jpg' ;
	const	FORMAT_GIF		=  'gif' ;

	// Last retrieved screen capture
	public		$Capture	=  null ;


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        Constructor
	
	    PROTOTYPE
	        $screenshot	=  new ScreenshotLayer ( $access_key, $secret_key = false, $use_https = false ) ;
	
	    DESCRIPTION
	        Initializes a ScreenShot layer api object.
	
	    PARAMETERS
	        $access_key (string) -
	                Access key, as provided on your apilayers.com dashboard.

		$secret_key (string) -
			If you have defined a secret key in your screenshotlayer account, then you should specify it
			here or later set the SecretPassword property.

		$use_https (boolean) -
			Indicates whether secure http should be used or not.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  __construct ( $access_key, $secret_key = null, $use_https = false )
	   {
		$parameters	=
		   [
			   [ 
				'name'			=>  'url', 
				'property'		=>  [ 'url', 'Url' ],
				'required'		=>  true
			    ], 
			   [
				'name'			=>  'secret_key',
				'property'		=>  [ 'secret_key', 'SecretKey' ],
				'type'			=>  self::APILAYER_PARAMETER_FLAG_COMPUTED,
				'queryget'		=>  function ( $parameter ) 
				   { 
					$key	=  md5 ( $this -> Url . $this -> SecretKey ) ;

					return ( $key ) ;
				    }
			    ], 
			   [
				'name'			=>  'fullpage',
				'property'		=>  [ 'fullpage', 'FullPage' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'force',
				'property'		=>  [ 'force', 'Force' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'delay',
				'property'		=>  [ 'delay', 'Delay' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER,
				'range'			=>  [ 1, 20 ]
			    ], 
			   [
				'name'			=>  'ttl',
				'property'		=>  [ 'ttl', 'Ttl', 'TTL' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER,
				'range'			=>  [ 1, 2592000 ]
			    ], 
			   [
				'name'			=>  'width',
				'property'		=>  [ 'width', 'Width' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER
			    ], 
			   [
				'name'			=>  'accept_lang',
				'property'		=>  [ 'accept_lang', 'accept_language', 'AcceptLanguage' ]
			    ], 
			   [
				'name'			=>  'format',
				'property'		=>  [ 'format', 'Format' ],
				'type'			=>  self::APILAYER_PARAMETER_KEYWORD,
				'keywords'		=>  [ 'png', 'gif', 'jpg', 'jpeg' => 'jpg', 'jpe' => 'jpg' ]
			    ], 
			   [
				'name'			=>  'css_url',
				'property'		=>  [ 'css_url', 'CssUrl' ]
			    ], 
			   [
				'name'			=>  'placeholder',
				'property'		=>  [ 'placeholder', 'PlaceHolder' ]
			    ], 
			   [
				'name'			=>  'export',
				'property'		=>  [ 'export', 'ExportTo', 'FtpExportTo' ]
			    ], 
			   [
				'name'			=>  'user_agent',
				'property'		=>  [ 'user_agent', 'UserAgent' ]
			    ], 
			   [
				'name'			=>  'viewport',
				'property'		=>  [ 'viewport', 'Viewport' ],
				'type'			=>  self::APILAYER_PARAMETER_VIEWPORT
			    ]
		    ] ;

		parent::__construct ( 'api.screenshotlayer.com/api/capture', $access_key, $use_https, $parameters ) ;

		$this -> SecretKey	=  $secret_key ;
	    }


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
	        CapturePage - Captures a page
	
	    PROTOTYPE
	        $data	=  $screenshot -> CapturePage ( $url = false ) ;
	
	    DESCRIPTION
	        Captures a screenshot of the specified url.
	
	    PARAMETERS
	        $url (string) -
	                Web page to be captured. If not specified, the contents of the Url property will be used.
	
	    RETURN VALUE
	        Returns the binary data corresponding to the requested page.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  CapturePage ( $url = false )
	   {
		if  ( $this -> ExportTo )
			throw ( new ApiLayerException ( "You cannot call the CapturePage() method if you use the 'export' option" ) ) ;

		if  ( $url )
			$this -> Url	=  $url ;

		if  ( ! $this -> Url )
			throw ( new ApiLayerException ( "No url specified for capture" ) ) ;

		$result				=  $this -> Execute ( ) ;
		$this -> Capture		=  $result ;

		return ( $this -> Capture ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        CapturePages - Captures a set of pages.
	
	    PROTOTYPE
	        $result		=  $screenshot -> CapturePages ( $url_list, $output_directory, $prefix = 'capture.' ) ;
	
	    DESCRIPTION
	        Captures a set of pages given by the $url_list array.
	
	    PARAMETERS
	        $url_list (array of strings) -
	                List of urls to be captured.

		$output_directory (string) -
			Directory where the captures are to be put. This directory must exist.

		$prefix (string) -
			Prefix for the captured file names. A sequential index and the format extension ('png', 'gif' 
			or 'jpg') are added to the final filename.
			Thus, if the output directory is 'screenshots' and the prefix is 'capture.', the following
			files will be generated (for a format of type 'png') :

				screenshots/capture.1.png
				screenshots/capture.2.png
				...
	
	    RETURN VALUE
	        The returned value is an array of strings giving the generated filenames.
	
	    NOTES
	        An exception is thrown if one or more captures could not be achieved.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  CapturePages ( $url_list, $output_directory, $prefix = 'capture.' )
	   {
		// Check that the supplied output directory exists
		if  ( ! is_dir ( $output_directory ) )
			throw ( new ApiLayerException ( "Output directory \"$output_directory\" does not exist for batch page capture" ) ) ;

		$errors		=  [] ;
		$filenames	=  [] ;
		$index		=  0 ;
		$format		=  $this -> Format ;

		if  ( ! $format )
			$format		=  'png' ;

		// Loop through url list
		foreach  ( $url_list  as  $url )
		   {
			// Generate the appropriate capture filename, using a sequential index
			$index ++ ;
			$filename	=  "$output_directory/$prefix$index.$format" ;

			// Capture the screenshot
			try 
			   {
				$capture	=  $this -> CapturePage ( $url ) ;
				file_put_contents ( $filename, $capture ) ;
				$filenames []	=  $filename ;
			    }
			// In case of failure, collect the error
			catch  ( ApiLayerException  $e )
			   {
				$errors []	=  ". " . str_replace ( "\n", "\n\t", $e -> getMessage ( ) ) ;
			    }
		    }

		// Throw an exception if one or more errors occured
		$error_count	=  count ( $errors ) ;

		if  ( $error_count )
			throw ( new ApiLayerException ( "$error_count error(s) have been encountered during capture :\n" . implode ( "\n", $errors ) ) ) ;

		// Save the list of filenames as the query result
		$this -> QueryResult -> Data	=  $filenames ;

		// No error occurred : return the list of generated filenames
		return ( $filenames ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        DownloadPage - Downloads a screenshot-ed page.
	
	    PROTOTYPE
	        $screenshot -> DownloadPage ( $url = false, $filename = false ) ;
	
	    DESCRIPTION
	        Downloads a screenshot image.
	
	    PARAMETERS
	        $url (string) -
	                Url to capture. If not specified, the Url property will be used.

		$filename (string) -
			Default name of the downloaded file. If not specified, a filename will be built from the
			domain and path parts of the url.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  DownloadPage  ( $url = false, $filename = false )
	   {
		if  ( $this -> ExportTo )
			throw ( new ApiLayerException ( "A page cannot be downloaded if you use the 'export' option" ) ) ;

		if  ( $url )
			$this -> Url	=  $url ;
		else 
			$url		=  $this -> Url ;

		if  ( ! $this -> Url )
			throw ( new ApiLayerException ( "No url specified for capture" ) ) ;

		$result			=  $this -> Execute ( ) ;
		$size			=  strlen ( $result ) ;

		switch  ( strtolower ( $this -> format ) )
		   {
			case	'png'		:  $content_type	=  'image/png'  ; break ;
			case	'gif'		:  $content_type	=  'image/gif'  ; break ;
			case	'jpg'		:  $content_type	=  'image/jpeg' ; break ;
			default			:  $content_type	=  'binary' ;
		    }

		if  ( ! $filename )
		   {
			$parts		=  parse_url ( $url ) ;
			$filename	=  '' ;

			if  ( isset ( $parts [ 'host'] ) )
				$filename	.= $parts [ 'host' ] ;

			if  ( isset ( $parts [ 'path' ] ) )
				$filename	.=  str_replace ( '/', '_', $parts [ 'path' ] ) ;
		    }

		$this -> Capture	=  false ;
		$this -> QueryResult	=  $result ;

		@ob_clean ( ) ;
		header ( "Content-Type: $content_type" ) ;
		header ( "Content-Transfer-Encoding: Binary" ) ;
		header ( "Content-length: $size" ) ;
		header ( "Content-disposition: attachment; filename=\"$filename\""); 
		echo $result ;
		@ob_end_flush ( ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        ExportCapture - Exports a capture to a remote server.
	
	    PROTOTYPE
	        $filename	=  $screenshot -> ExportCapture ( $url = false, $export_url = false ) ;
	
	    DESCRIPTION
	        Captures a web page and exports it to an ftp or amazon S3 server.
	
	    PARAMETERS
	        $url (string) -
	                Url of the page to be captured. If this parameter is not specified, the $Url property will be
			used.

		$export_url (string) -
			Address, containing ftp/amazon credentials, where the capture is to be transferred.
			If this parameter is not specified, the $ExportTo property will be used.
	
	    RETURN VALUE
	        Returns the exported filename.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  ExportCapture ( $url = false, $export_url = false )
	   {
		if  ( $export_url )
		   {
			$this -> ExportTo	=  $export_url ;
		    }
		else
		   {
			if  ( ! $this -> ExportTo )
				throw ( new ApiLayerException ( "Cannot export capture, no export url specified." ) ) ;
		    }

		if  ( $url )
			$this -> Url	=  $url ;

		if  ( ! $this -> Url )
			throw ( new ApiLayerException ( "No url specified for capture" ) ) ;

		$this -> Capture	=  false ;
		$result			=  $this -> Execute ( ) ;
		$json_result		=  json_decode ( $result ) ;

		$this -> QueryResult -> Data	=  $json_result -> file_name ;

		return ( $this -> QueryResult -> Data ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        ExportCaptures - Exports a set of captures to a remote server.
	
	    PROTOTYPE
	        $filename	=  $screenshot -> ExportCapture ( $url_list, $export_url = false ) ;
	
	    DESCRIPTION
	        Captures a set of web pages and exports them to an ftp or amazon S3 server.
	
	    PARAMETERS
	        $url_list (array of strings) -
	                Url of the page to be captured.

		$export_url (string) -
			Address, containing ftp/amazon credentials, where the capture is to be transferred.
			If this parameter is not specified, the $ExportTo property will be used.
	
	    RETURN VALUE
	        Returns the exported filenames.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  ExportCaptures ( $url_list, $export_url )
	   {
		$errors		=  [] ;
		$filenames	=  [] ;
		$format		=  $this -> Format ;

		if  ( ! $format )
			$format		=  'png' ;

		if  ( $export_url )
		   {
			$this -> ExportTo	=  $export_url ;
		    }
		else
		   {
			if  ( ! $this -> ExportTo )
				throw ( new ApiLayerException ( "Cannot export capture, no export url specified." ) ) ;
		    }

		// Loop through url list
		foreach  ( $url_list  as  $url )
		   {
			// Capture the screenshot
			try 
			   {
				$result		=  $this -> ExportCapture ( $url, $export_url ) ;
				$filenames []	=  $result ;
			    }
			// In case of failure, collect the error
			catch  ( ApiLayerException  $e )
			   {
				$errors []	=  "\t. " . $e -> getMessage ( ) ;
			    }
		    }

		// Throw an exception if one or more errors occured
		$error_count	=  count ( $errors ) ;

		if  ( $error_count )
			throw ( new ApiLayerException ( "$error_count error(s) have been encountered during export :\n" . implode ( "\n", $errors ) ) ) ;

		// Save the filename list as the last query result
		$this -> QueryResult -> Data	=  $filenames ;

		// No error occurred : return the list of generated filenames
		return ( $filenames ) ;
	    }
    }