<?php
/**************************************************************************************************************

    NAME
        MailboxLayer.php

    DESCRIPTION
        Encapsulates access to the ApiLayer mailbox API.

	Using the MailboxLayer object is pretty simple. Just instantiate an object, define the required 
	properties, and call either the GetEmail or GetEmails method to retrieve information :

		$mail	=  new  MailboxLayer ( $my_access_key ) ;
		$info	=  $mail -> GetEmail ( 'john.doe@gmail.com' ) ;

	Various properties may be assigned ; they mimic their counterpart in the screenshot layer api but 
	aliases are also available ; they are listed below :

	- email or Email (string) :
		Email address whose information is to be retrieved (or the last email address whose
		information has been retrieved).

	- format or Format (0 or 1) :
		Set to 1 if the json contents returned by the api should be "pretty-printed" (for debugging
		purposes only).

	- smtp_check or SmtpCheck (0 or 1) :
		When set to 1, the smtp MX entry is checked.

	- catch_all or CatchAll (0 or 1) :
		When set to 1, possibly all smtp servers having an MX entry for this email address are 
		checked.

	- callback or Callback (string) :
		Allows to specify the name of a javascript function that will be called upon return.

    AUTHOR
        Christian Vigh, 02/2016.

    HISTORY
        [Version : 1.0]		[Date : 2016-02-12]     [Author : CV]
                Initial version.

 **************************************************************************************************************/
require ( dirname ( __FILE__ ) . '/ApiLayer.php' ) ;


/*==============================================================================================================

    class MailboxLayer -
        Encapsulates access to the ApiLayer mailbox API.

  ==============================================================================================================*/
class	MailboxLayer		extends		ApiLayer
   {
	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        Constructor
	
	    PROTOTYPE
	        $mail	=  new MailboxLayer ( $access_key, $use_https = false ) ;
	
	    DESCRIPTION
	        Creates a mailbox layer access object to retrieve information about email addresses.
	
	    PARAMETERS
	        $access_key (string) -
	                Access key, as provided on your apilayers.com dashboard.

		$use_https (boolean) -
			Indicates whether secure http should be used or not.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  __construct ( $access_key, $use_https = false )
	   {
		static	$parameters	=
		   [
			   [ 
				'name'			=>  'email', 
				'property'		=>  [ 'email', 'Email' ],
				'required'		=>  true
			    ], 
			   [ 
				'name'			=>  'format', 
				'property'		=>  [ 'format', 'Format' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [ 
				'name'			=>  'smtp_check', 
				'property'		=>  [ 'smtp_check', 'SmtpCheck' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [ 
				'name'			=>  'catch_all', 
				'property'		=>  [ 'catch_all', 'CatchAll' ],
				'type'			=>  self::APILAYER_PARAMETER_BOOLEAN
			    ], 
			   [
				'name'			=>  'callback',
				'property'		=>  [ 'callback', 'Callback' ]
			    ]
		    ] ;

		parent::__construct ( 'apilayer.net/api/check', $access_key, $use_https, $parameters ) ;
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
	        GetEmail - Retrieves information about an email address.
	
	    PROTOTYPE
	        $result -> GetEmail ( $email = null ) ;
	
	    DESCRIPTION
	        Retrieves information about an email address.
	
	    PARAMETERS
	        $email (string) -
	                Email whose information is to be retrieved. If not specified, the value of the Email property
			will be used.
	
	    RETURN VALUE
	        An object having the following properties :
			$email                           = (string[27]) "contact@wuthering-bytes.com"
			$did_you_mean                    = (string[0]) ""
			$user                            = (string[7]) "contact"
			$domain                          = (string[19]) "wuthering-bytes.com"
			$format_valid                    = (bool) true
			$mx_found                        = (bool) true
			$smtp_check                      = (bool) false
			$catch_all                       = (bool) false
			$role                            = (bool) true
			$disposable                      = (bool) false
			$free                            = (bool) false
			$score                           = (float) 0.64

		For a detailed description on the meaning of each property, have a look at the documentation here :

			https://mailboxlayer.com/documentation
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  GetEmail ( $email = null )
	   {
		if  ( $email  !==  null )
			$this -> Email	=  $email ;
		else
			$email		=  $this -> Email ;

		$this -> Execute ( ) ;

		return ( $this -> QueryResult -> Data ) ;
	    }


	/*--------------------------------------------------------------------------------------------------------------
	
	    NAME
	        GetEmails - Retrieve information for a list of email addresses.
	
	    PROTOTYPE
	        $result		=  $mail -> GetEmails ( $emails ) ;
	
	    DESCRIPTION
	        Retrieves information for a list of email addresses.
	
	    PARAMETERS
	        $emails (array of strings) -
	                Emails whose information are to be retrieved.
	
	    RETURN VALUE
	        Returns an array of email information, one for each email address specified in the $email parameter.
		The array is an associative array whoses keys are the email addresses.
	
	 *-------------------------------------------------------------------------------------------------------------*/
	public function  GetEmails ( $emails )
	   {
		$results	=  [] ;
		$errors		=  [] ;

		foreach  ( $emails  as  $email )
		   {
			try
			   {
				$results [ $email ]	=  $this -> GetEmail ( $email ) ;
			    }
			catch  ( ApiLayerException  $e )
			   {
				$errors []		=  ". " . str_replace ( "\n", "\n\t", $e -> getMessage ( ) ) ;
			    }
		    }

		// Throw an exception if one or more errors occured
		$error_count	=  count ( $errors ) ;

		if  ( $error_count )
			throw ( new ApiLayerException ( "$error_count error(s) have been encountered during email information retrieval :\n" . 
					implode ( "\n", $errors ) ) ) ;

		// The query results will hold the entire set of answers for this query
		$this -> QueryResult -> Data	=  $results ;

		// No error occurred : return the list of email information entries
		return ( $results ) ;
	    }
    }