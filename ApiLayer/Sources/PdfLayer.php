<?php
/**************************************************************************************************************

    NAME
        PdfLayer.phpclass

    DESCRIPTION
        Encapsulates access to the apilayer pdf api.

	Using the PdfLayer object is pretty simple. Just instantiate an object, define the required 
	properties, and call one of the PdfLayer public methods ; the following example will capture a 
	screenshot from http://www.google.com and return the pdf contents in the $pdf
	variable (if an error occurs, an ApiLayerException exception will be thrown) :

		$pdf			=  new PdfLayer ( $my_access_key ) ;
		$pdf_data		=  $pdf -> ConvertPage ( "http://www.google.com" ) ;

	Various properties may be assigned ; they mimic their counterpart in the screenshot layer api but 
	aliases are also available ; they are listed below :

	- accept_lang or accept_language or AcceptLanguage (string) :
		Sends the specified Accept-Language http header. Useful for sites that accept several user 
		language translations.

	- author or Author (string) :
		Allows to set the document author, visible in the document properties when using Acrobat Reader.

	- auth_user or AuthUser or AuthenticationUser (string),
	  auth_password or AuthPassword or AuthenticationPassword (string) :
		User and password required to connect to the website before being to view the page html 
		contents.
		These scheme uses the basic http authentication mechanism.

	- creator or Creator or application or Application (string) :
		Allows to specify the name of the application that generated the pdf document. 
		This information is visible in the document properties dialog box when using Acrobat Reader. 

	- css_url or CssUrl (string) :
		Url of a style sheet that will be injected to style html contents.

	- custom_unit or CustomUnit (keyword) :
		Specifies the unit to be used for all other properties that express a quantity, such as margin_left, 
		margin_right, page_width, etc.

		The following units can be specified :

		- Millimeters, using the strings 'mm', 'millimeter', 'millimeters' or the class constant 
		  UNIT_MILLIMETERS
		- Inches, using the strings 'in', 'inch', 'inches' or the class constant UNIT_INCHES
		- Pixels, using the strings 'px', 'pixel', 'pixels' or the class constant UNIT_PIXELS
		- Points, using the strings 'pt', 'point', 'points' or the class constant UNIT_POINTS

	- delay or Delay (integer) :
		Specifies the maximum delay, in milliseconds, before the html contents can be considered to be 
		completely loaded.

	- document_html or DocumentHtml (string) :
		Specifies direct html contents to be converted to pdf.
		Either the document_url or document_html property needs to be specified.

		Note that this parameter cannot be specified as a GET parameter, but must be used as a POST parameter 
		instead. If this property is assigned, the PdfLayer class will automatically issue a POST request to 
		the Apilayer API.

	-  document_name or DocumentName (string) :
		By default, PDF documents generated by the pdflayer API are named pdflayer.pdf. Using the API's 
		document_name parameter you can specify a custom name for your final PDF document.

		This information is mainly used when downloading generated documents.

	- document_url or DocumentUrl or url or Url :
		Url of the web page to be converted into PDF format.
		Either the document_url or document_html property needs to be specified.

	- encryption or Encryption (keyword) :
		There are two encryption levels available for PDFs generated by the pdflayer API: 40-bit and 128-bit. 
		In order to activate encryption, set the API's encryption parameter to 40 or 128.

	- footer_align or FooterAlign (keyword) :
		Specifies alignment for footer text. This can take the values left, right or center.

	- footer_html or FooterHtml (string) :
		Specifies direct html contents to be used for the generated pdf page header.
		Note that this parameter cannot be specified as a GET parameter, but must be used as a POST parameter 
		instead. If this property is assigned, the PdfLayer class will automatically issue a POST request to 
		the Apilayer API.

	- footer_text or FooterText (string) :
		Specifies the footer text.

	- footer_spacing or FooterSpacing (integer) :
		Specifies the spacing between the bottom of the page and the start of footer contents.
		The units used are give by the custom_unit parameter, which defaults to "px" (pixels).

	- footer_url or FooterUrl (string) :
		Specifies an url which contains html contents to be inserted into the footer part of a page.

	- force or Force (boolean) :
		Set to "1" (or true) if you want the pdf contents to be regenerated.

	- forms or Forms (boolean) :
		If form data is present in the html contents, then a pdf form will be generated.

	- grayscale or GrayScale (boolean) :
		Converts color information to grayscale.

	-  header_align or HeaderAlign (keyword) :
		Specifies alignment for header text. This can take the values left, right or center.

	- header_html or HeaderHtml (string) :
		Specifies direct html contents to be used for the generated pdf page footer.
		Note that this parameter cannot be specified as a GET parameter, but must be used as a POST 
		parameter instead. If this property is assigned, the PdfLayer class will automatically issue 
		a POST request to the Apilayer API.

	- header_text or HeaderText (string) :
		Specifies the header text.

	- header_spacing or HeaderSpacing (integer) :
		Specifies the spacing between the bottom of the header and the start of page contents.
		The units used are give by the custom_unit parameter, which defaults to "px" (pixels).

	- header_url or HeaderUrl (string) :
		Specifies an url which contains html contents to be inserted into the header part of a page.

	- inline or Inline (boolean) :
		By default, accessing a pdflayer API request URL in a browser will trigger the download of the 
		generated PDF (attachment behaviour). 
		By setting the API's inline parameter to 1 the API will be requested to display the PDF in the 
		browser instead.

	- margin_bottom or MarginBottom (integer) :
	- margin_left or MarginLeft (integer) :
	- margin_right or MarginRight (integer) : 
	- margin_top or MarginTop (integer) :
		Specifies the bottom, left, right and top margins to be used for the document page contents.
		The units used are given by the custom_unit parameter, which is "px" (pixels) by default.

	- no_background or NoBackground (boolean) :
		Remove background images when converting html to pdf.

	- no_copy or NoCopy (boolean) :
		Clipboard copy will be disabled in the generated pdf file.

	- no_hyperlinks or NoHyperlinks (boolean) :
		Do not include hyperlinks in the captured html contents.

	- no_images or NoImages (boolean) :
		Do not include images in the captured html contents.

	- no_javascript or NoJavascript (boolean) :
		Do no process javascript when reading html contents.

	- no_modify or NoModify (boolean) :
		User will not be authorized to modify the pdf contents.

	- no_print or NoPrint (boolean) :
		If set to true, printing will be disabled in the generated pdf file.	

	- owner_password or OwnerPassword (string) :
		Password to be specified for being able to modify the pdf file contents.

	- page_height or PageHeight (integer) :
		Specifies a page height that overrides the default page size parameter.
		Note that the page_width parameter must also be specified in this case.
 
	- page_size or PageSize (keyword) :
		Allows to specify a page format that can be one of the following values :

		- A0 to A9
		- B0 to B9
		- CSE, Comm10E, Executive, Folio, Ledger, Legal, Letter or Tabloid

		The class constants PAGE_SIZE_* provide an alias for these keywords.

	- page_width or PageWidth (integer) :
		Specifies a page width that overrides the default page size parameter.
		Note that the *page_height* parameter must also be specified in this case.
 
	- secret_key or SecretKey (string) :
		If you have activated your secret key in the pdflayer api, then you will have to
		set the SecretPassword property to that key.

		The SecretKey readonly property will return your own secret key for the given url when used 
		as a query parameter ; this *secret_key* url parameter is the md5 hash of the requested url 
		catenated with your secret key.

	- subject or Subject (string) :
		Allows to set the document subject, visible in the document properties when using Acrobat Reader.

	- test or Test or sandbox or Sandbox (boolean) :
		Enables sandbox mode. This allows you for testing document conversion even if you ran out of 
		queries authorized by your current subscription plan. 

		Note that in this case the generated pdf documents will have the word "Sample" printed in red 
		in the page background.

	- text_encoding or TextEncoding (string) :
		Specifies the text encoding to be used when retrieving an url contents.
		The default is UTF-8.

	- title or Title (string) :
		Allows to set the document title, visible in the document properties when using Acrobat Reader.

	- ttl or Ttl or TTL (integer) :
		Specifies the maximum time in seconds where a generated pdf document will be kept in the cache. 
		This can range from 300 (5 minutes) to 2592000 (30 days).

	- user_password or UserPassword (string) :
		Password to be specified for being able to view the pdf file contents.

	- use_print_media or UsePrintMedia (boolean) :
		Takes into account @media css instructions when generating the pdf contents.

	- user_agent or UserAgent :
		Specifies a custom user agent string.
		Some standard user agent strings are available as class constants starting with USER_AGENT_*.

	- viewport or Viewport (viewport dimensions) :
		A viewport dimension for the generated captures, which has the form :

			widthxheight

		where 'width' and 'height' represent the dimension. The default is : 1440x900.

	- watermark_in_background or WatermarkInBackground (boolean) :
		By default, watermarks are placed in the foreground of the document. Set this parameter to true 
		to put the watermark behind the page contents.

	- watermark_offset_x or WatermakOffsetX (integer) :
	- watermark_offset_y or WatermakOffsetY (integer) : 
		Specifies the X and Y position of the upper-left corner of the watermark. The default is (0,0).

	- watermark_opacity or WatermarkOpacity (integer) :
		Specifies the watermark opacity (0..100). The default is 20 (20%).

	- zoom or Zoom (integer) :
		Specifies a zoom factor for the captured html contents. The value can range from 0 to 50, 
		0 meaning "no zoom".

		Note that after zooming, the converted html contents will be truncated if they do not fit 
		on the page.

    AUTHOR
        Christian Vigh, 02/2016.

    HISTORY
        [Version : 1.0]		[Date : 2016-02-14]     [Author : CV]
                Initial version.

 **************************************************************************************************************/
require ( dirname ( __FILE__ ) . '/ApiLayer.php' ) ;


/*==============================================================================================================

    class PdfLayer -
        Encapsulates access to the apilayer pdf api.

  ==============================================================================================================*/
class	PdfLayer		extends  ApiLayer
   {
	// Page sizes
	const	PAGE_SIZE_A0			=  'A0' ;
	const	PAGE_SIZE_A1			=  'A1' ;
	const	PAGE_SIZE_A2			=  'A2' ;
	const	PAGE_SIZE_A3			=  'A3' ;
	const	PAGE_SIZE_A4			=  'A4' ;
	const	PAGE_SIZE_A5			=  'A5' ;
	const	PAGE_SIZE_A6			=  'A6' ;
	const	PAGE_SIZE_A7			=  'A7' ;
	const	PAGE_SIZE_A8			=  'A8' ;
	const	PAGE_SIZE_A9			=  'A9' ;
	const	PAGE_SIZE_B0			=  'B0' ;
	const	PAGE_SIZE_B1			=  'B1' ;
	const	PAGE_SIZE_B2			=  'B2' ;
	const	PAGE_SIZE_B3			=  'B3' ;
	const	PAGE_SIZE_B4			=  'B4' ;
	const	PAGE_SIZE_B5			=  'B5' ;
	const	PAGE_SIZE_B6			=  'B6' ;
	const	PAGE_SIZE_B7			=  'B7' ;
	const	PAGE_SIZE_B8			=  'B8' ;
	const	PAGE_SIZE_B9			=  'B9' ;
	const	PAGE_SIZE_CSE			=  'CSE' ;
	const	PAGE_SIZE_COMM10E		=  'Comm10E' ;
	const	PAGE_SIZE_DLE			=  'DLE' ;
	const	PAGE_SIZE_EXECUTIVE		=  'Executive' ;
	const	PAGE_SIZE_FOLIO			=  'Folio' ;
	const	PAGE_SIZE_LEDGER		=  'Ledger' ;
	const	PAGE_SIZE_LEGAL			=  'Legal' ;
	const	PAGE_SIZE_LETTER		=  'Letter' ;
	const	PAGE_SIZE_TABLOID		=  'Tabloid' ;

	// Units
	const	UNIT_MILLIMETERS		=  'mm' ;
	const	UNIT_INCHES			=  'in' ;
	const	UNIT_PIXELS			=  'px' ;
	const	UNIT_POINTS			=  'pt' ;

	// Orientation
	const	ORIENTATION_PORTRAIT		=  'portrait' ;
	const	ORIENTATION_LANDSCAPE		=  'landscape' ;

	// Pdf contents of the last conversion operation
	public		$Pdf ;


	public function  __construct ( $access_key, $secret_key = null, $use_https = false )
	   {
		$parameters	=
		   [
			   [ 
				'name'			=>  'document_url', 
				'property'		=>  [ 'url', 'Url', 'document_url', 'DocumentUrl' ]
			    ], 
			   [
				'name'			=>  'secret_key',
				'property'		=>  [ 'secret_key', 'SecretKey' ],
				'type'			=>  self::APILAYER_PARAMETER_FLAG_COMPUTED,
				'queryget'		=>  function ( $parameter ) 
				   { 
					$key	=  md5 ( $this -> DocumentUrl . $this -> SecretKey ) ;

					return ( $key ) ;
				    }
			    ],
			   [
				'name'			=>  'test',
				'property'		=>  [ 'test', 'Test', 'sandbox', 'Sandbox' ]
			    ],
			   [
				'name'			=>  'title',
				'property'		=>  [ 'title', 'Title' ]
			    ],
			   [
				'name'			=>  'subject',
				'property'		=>  [ 'subject', 'Subject' ]
			    ],
			   [
				'name'			=>  'creator',
				'property'		=>  [ 'creator', 'Creator', 'application', 'Application' ]
			    ],
			   [
				'name'			=>  'author',
				'property'		=>  [ 'author', 'Author' ]
			    ],
			   [
				'name'			=>  'delay',
				'property'		=>  [ 'delay', 'Delay' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER,
				'range'			=>  [ 10, 20000 ]
			    ], 
			   [
				'name'			=>  'ttl',
				'property'		=>  [ 'ttl', 'Ttl', 'TTL' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER,
				'range'			=>  [ 300, 2592000 ]
			    ], 
			   [
				'name'			=>  'force',
				'property'		=>  [ 'force', 'Force' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'user_password',
				'property'		=>  [ 'user_password', 'UserPassword' ]
			    ], 
			   [
				'name'			=>  'owner_password',
				'property'		=>  [ 'owner_password', 'OwnerPassword' ]
			    ], 
			   [
				'name'			=>  'auth_user',
				'property'		=>  [ 'auth_user', 'AuthUser', 'AuthenticationUser' ]
			    ], 
			   [
				'name'			=>  'auth_password',
				'property'		=>  [ 'auth_password', 'AuthPassword', 'AuthenticationPassword' ]
			    ], 
			   [
				'name'			=>  'no_images',
				'property'		=>  [ 'no_images', 'NoImages' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'no_hyperlinks',
				'property'		=>  [ 'no_hyperlinks', 'NoHyperlinks' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'no_javascript',
				'property'		=>  [ 'no_javascript', 'NoJavascript' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'no_backgrounds',
				'property'		=>  [ 'no_backgrounds', 'NoBackgrounds' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'use_print_media',
				'property'		=>  [ 'use_print_media', 'UsePrintMedia' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'grayscale',
				'property'		=>  [ 'grayscale', 'GrayScale' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'low_quality',
				'property'		=>  [ 'low_quality', 'LowQuality' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'forms',
				'property'		=>  [ 'forms', 'Forms' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'no_print',
				'property'		=>  [ 'no_print', 'NoPrint' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'no_modify',
				'property'		=>  [ 'no_modify', 'NoModify' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'no_copy',
				'property'		=>  [ 'no_copy', 'NoCopy' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'inline',
				'property'		=>  [ 'inline', 'Inline' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'page_width',
				'property'		=>  [ 'page_width', 'PageWidth' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER
			    ], 
			   [
				'name'			=>  'margin_top',
				'property'		=>  [ 'margin_top', 'MarginTop' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER
			    ], 
			   [
				'name'			=>  'margin_bottom',
				'property'		=>  [ 'margin_bottom', 'MarginBottom' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER
			    ], 
			   [
				'name'			=>  'margin_left',
				'property'		=>  [ 'margin_left', 'MarginLeft' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER
			    ], 
			   [
				'name'			=>  'margin_right',
				'property'		=>  [ 'margin_right', 'MarginRight' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER
			    ], 
			   [
				'name'			=>  'page_height',
				'property'		=>  [ 'page_height', 'PageHeight' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER
			    ], 
			   [
				'name'			=>  'header_text',
				'property'		=>  [ 'header_text', 'HeaderText' ],
				'type'			=>  self::APILAYER_PARAMETER_FLAG_UTF8_ENCODE | self::APILAYER_PARAMETER_FLAG_HTML_ENTITIES
			    ], 
			   [
				'name'			=>  'header_spacing',
				'property'		=>  [ 'header_spacing', 'HeaderSpacing' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER
			    ], 
			   [
				'name'			=>  'header_url',
				'property'		=>  [ 'header_url', 'HeaderUrl' ]
			    ], 
			   [
				'name'			=>  'header_align',
				'property'		=>  [ 'header_align', 'HeaderAlign' ],
				'type'			=>  self::APILAYER_PARAMETER_KEYWORD,
				'keywords'		=>  [ 'left', 'center', 'right' ]
			    ], 
			   [
				'name'			=>  'footer_text',
				'property'		=>  [ 'footer_text', 'FooterText' ],
				'type'			=>  self::APILAYER_PARAMETER_FLAG_UTF8_ENCODE | self::APILAYER_PARAMETER_FLAG_HTML_ENTITIES
			    ], 
			   [
				'name'			=>  'footer_spacing',
				'property'		=>  [ 'footer_spacing', 'FooterSpacing' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER
			    ], 
			   [
				'name'			=>  'footer_url',
				'property'		=>  [ 'footer_url', 'FooterUrl' ]
			    ], 
			   [
				'name'			=>  'footer_align',
				'property'		=>  [ 'footer_align', 'FooterAlign' ],
				'type'			=>  self::APILAYER_PARAMETER_KEYWORD,
				'keywords'		=>  [ 'left', 'center', 'right' ]
			    ], 
			   [
				'name'			=>  'text_encoding',
				'property'		=>  [ 'text_encoding', 'TextEncoding' ]
			    ], 
			   [
				'name'			=>  'dpi',
				'property'		=>  [ 'dpi', 'Dpi', 'DPI' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER,
				'range'			=>  [ 10, 10000 ]
			    ], 
			   [
				'name'			=>  'zoom',
				'property'		=>  [ 'zoom', 'Zoom' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER,
				'range'			=>  [ 0, 50 ]
			    ], 
			   [
				'name'			=>  'document_name',
				'property'		=>  [ 'document_name', 'DocumentName' ]
			    ], 
			   [
				'name'			=>  'encryption',
				'property'		=>  [ 'encryption', 'Encryption' ],
				'type'			=>  self::APILAYER_PARAMETER_KEYWORD,
				'keywords'		=>  [ '40', '128' ]
			    ], 
			   [
				'name'			=>  'orientation',
				'property'		=>  [ 'orientation', 'Orientation' ],
				'type'			=>  self::APILAYER_PARAMETER_KEYWORD,
				'keywords'		=>  [ 'portrait', 'landscape' ]
			    ], 
			   [
				'name'			=>  'custom_unit',
				'property'		=>  [ 'custom_unit', 'CustomUnit', 'Unit', 'CustomUnits', 'Units' ],
				'type'			=>  self::APILAYER_PARAMETER_KEYWORD,
				'keywords'		=> 
				   [
					'mm', 
					'millimeters'		=>  'mm',
					'millimeter'		=>  'mm',
					'in',
					'inches'		=>  'in',
					'inch'			=>  'in',
					'px',
					'pixels'		=>  'px',
					'pixel'			=>  'px',
					'pt',
					'points'		=>  'pt',
					'point'			=>  'pt'
				    ]
			    ], 
			   [
				'name'			=>  'accept_lang',
				'property'		=>  [ 'accept_lang', 'accept_language', 'AcceptLanguage' ]
			    ], 
			   [
				'name'			=>  'user_agent',
				'property'		=>  [ 'user_agent', 'UserAgent' ]
			    ], 
			   [
				'name'			=>  'css_url',
				'property'		=>  [ 'css_url', 'CssUrl' ]
			    ], 
			   [
				'name'			=>  'page_size',
				'property'		=>  [ 'page_size', 'PageSize' ],
				'type'			=>  self::APILAYER_PARAMETER_KEYWORD,
				'keywords'		=>
				   [
					'A0', 'A1', 'A2', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'A9', 
					'B0', 'B1', 'B2', 'B3', 'B4', 'B5', 'B6', 'B7', 'B8', 'B9', 
					'CSE', 'Comm10E', 'Executive', 'Folio', 'Ledger', 'Legal', 'Letter', 'Tabloid'
				    ]
			    ],
			   [
				'name'			=>  'viewport',
				'property'		=>  [ 'viewport', 'Viewport' ],
				'type'			=>  self::APILAYER_PARAMETER_VIEWPORT
			    ],
			   [
				'name'			=>  'watermark_offset_x',
				'property'		=>  [ 'watermark_offset_x', 'WatermarkOffsetX' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER
			    ], 
			   [
				'name'			=>  'watermark_offset_y',
				'property'		=>  [ 'watermark_offset_y', 'WatermarkOffsetY' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER
			    ], 
			   [
				'name'			=>  'watermark_opacity',
				'property'		=>  [ 'watermark_opacity', 'WatermarkOpacity' ],
				'type'			=>  self::APILAYER_PARAMETER_INTEGER,
				'range'			=>  [ 0, 100 ]
			    ], 
			   [
				'name'			=>  'watermark_in_background',
				'property'		=>  [ 'watermark_in_background', 'WatermarkInBackground' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'watermark_url',
				'property'		=>  [ 'watermark_url', 'WatermarkUrl' ]
			    ],
			   [
				'name'			=>  'document_html',
				'property'		=>  [ 'document_html', 'DocumentHtml' ],
				'type'			=>  self::APILAYER_PARAMETER_FLAG_POST
			    ],
			   [
				'name'			=>  'header_html',
				'property'		=>  [ 'header_html', 'HeaderHtml' ],
				'type'			=>  self::APILAYER_PARAMETER_FLAG_POST
			    ],
			   [
				'name'			=>  'footer_html',
				'property'		=>  [ 'footer_html', 'FooterHtml' ],
				'type'			=>  self::APILAYER_PARAMETER_FLAG_POST
			    ]
		    ] ;

		parent::__construct ( 'api.screenshotlayer.com/api/convert', $access_key, $use_https, $parameters ) ;

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
	        ConvertPage - Converts a page
	
	    PROTOTYPE
	        $data	=  $pdf -> ConvertPage ( $url = false ) ;
	
	    DESCRIPTION
	        Converts html contents given by an url into a pdf file.
	
	    PARAMETERS
	        $url (string) -
	                Web page to be captured. If not specified, the contents of the Url property will be used.
	
	    RETURN VALUE
	        Returns the binary pdf data corresponding to the requested page.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  ConvertPage ( $url = false )
	   {
		if  ( $url )
			$this -> Url	=  $url ;

		if  ( ! $this -> Url )
			throw ( new ApiLayerException ( "No url specified for html-to-pdf conversion" ) ) ;

		$result				=  $this -> Execute ( ) ;
		$this -> Pdf			=  $result ;

		return ( $this -> Pdf ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        ConvertPages - Converts a set of html pages to pdf.
	
	    PROTOTYPE
	        $result		=  $screenshot -> ConvertPages ( $url_list, $output_directory, $prefix = 'capture.' ) ;
	
	    DESCRIPTION
	        Converts a set of html pages given by the $url_list array.
	
	    PARAMETERS
	        $url_list (array of strings) -
	                List of urls to be converted.

		$output_directory (string) -
			Directory where the captures are to be put. This directory must exist.

		$prefix (string) -
			Prefix for the captured file names. A sequential index and the format extension ('png', 'gif' 
			or 'jpg') are added to the final filename.
			Thus, if the output directory is 'pdf' and the prefix is 'capture.', the following
			files will be generated (for a format of type 'png') :

				pdf/capture.1.png
				pdf/capture.2.png
				...
	
	    RETURN VALUE
	        The returned value is an array of strings giving the generated filenames.
	
	    NOTES
	        An exception is thrown if one or more captures could not be achieved.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  ConvertPages ( $url_list, $output_directory, $prefix = 'capture.' )
	   {
		// Check that the supplied output directory exists
		if  ( ! is_dir ( $output_directory ) )
			throw ( new ApiLayerException ( "Output directory \"$output_directory\" does not exist for batch page capture" ) ) ;

		$errors		=  [] ;
		$filenames	=  [] ;
		$index		=  0 ;

		// Loop through url list
		foreach  ( $url_list  as  $url )
		   {
			// Generate the appropriate capture filename, using a sequential index
			$index ++ ;
			$filename	=  "$output_directory/$prefix$index.pdf" ;

			// Capture the screenshot
			try 
			   {
				$capture	=  $this -> ConvertPage ( $url ) ;
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
			throw ( new ApiLayerException ( "$error_count error(s) have been encountered during conversion :\n" . implode ( "\n", $errors ) ) ) ;

		// Save the list of filenames as the query result
		$this -> QueryResult -> Data	=  $filenames ;

		// No error occurred : return the list of generated filenames
		return ( $filenames ) ;
	    }



	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        ConvertHtml - Converts html to pdf
	
	    PROTOTYPE
	        $data	=  $pdf -> ConvertHtml ( $contents = false ) ;
	
	    DESCRIPTION
	        Converts html contents given by the specified parameter into a pdf file.
	
	    PARAMETERS
	        $contents (string) -
	                Html contents to be converted to pdf.
			If this parameter is not specified, the DocumentHtml property will be used.
	
	    RETURN VALUE
	        Returns the binary pdf data corresponding to the requested page.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  ConvertHtml ( $contents = false )
	   {
		if  ( $contents )
			$this -> DocumentHtml	=  $contents ;

		if  ( ! $this -> DocumentHtml )
			throw ( new ApiLayerException ( "No html contents specified for html-to-pdf conversion" ) ) ;

		$result				=  $this -> Execute ( ) ;
		$this -> Pdf			=  $result ;

		return ( $this -> Pdf ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        DownloadPage - Downloads a converted page.
	
	    PROTOTYPE
	        $screenshot -> DownloadPage ( $url = false, $filename = false ) ;
	
	    DESCRIPTION
	        Downloads a pdf file.
	
	    PARAMETERS
	        $url (string) -
	                Url to capture. If not specified, the Url property will be used.

		$filename (string) -
			Default name of the downloaded file. If not specified, a filename will be built from the
			domain and path parts of the url.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  DownloadPage  ( $url = false, $filename = false )
	   {
		if  ( $url )
			$this -> Url	=  $url ;
		else 
			$url		=  $this -> Url ;

		if  ( ! $this -> Url )
			throw ( new ApiLayerException ( "No url specified for capture" ) ) ;

		$result			=  $this -> Execute ( ) ;
		$size			=  strlen ( $result ) ;

		if  ( ! $filename )
		   {
			$parts		=  parse_url ( $url ) ;
			$filename	=  '' ;

			if  ( isset ( $parts [ 'host'] ) )
				$filename	.= $parts [ 'host' ] ;

			if  ( isset ( $parts [ 'path' ] ) )
				$filename	.=  str_replace ( '/', '_', $parts [ 'path' ] ) ;
		    }

		$this -> QueryResult	=  $result ;

		@ob_clean ( ) ;
		header ( "Content-Type: application/pdf" ) ;
		header ( "Content-Transfer-Encoding: Binary" ) ;
		header ( "Content-length: $size" ) ;
		header ( "Content-disposition: attachment; filename=\"$filename\""); 
		echo $result ;
		@ob_end_flush ( ) ;
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
	        OnBeforeQuery - Performs additional checkings before issuing an apilayer request.
	
	    DESCRIPTION
	        Performs the following checkings before issuing an apilayer request :
		- Either the document_url or document_html property must be set
	
	 *-------------------------------------------------------------------------------------------------------------*/
	protected function  OnBeforeQuery ( )
	   {
		if  ( ! $this -> DocumentUrl  &&  ! $this -> DocumentHtml )
			$this -> SetError ( 313, 'missing_document_source', 'No document source specified (document_url or document_html)' ) ;
	    }
    }