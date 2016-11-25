# INTRODUCTION #

The **PhpUtilities** class contains a set of static methods which focus on expression evaluation, PHP parsing and some shell-related features.

You will find below a complete reference about each method. There is also an *examples* directory where you will find a detailed example for most of the functions described here.

# DEPENDENCIES #

This package depends on the following package : [http://www.phpclasses.org/package/9336-PHP-Match-MSDOS-UNIX-patterns-with-regular-expressions.html](http://www.phpclasses.org/package/9336-PHP-Match-MSDOS-UNIX-patterns-with-regular-expressions.html "http://www.phpclasses.org/package/9336-PHP-Match-MSDOS-UNIX-patterns-with-regular-expressions.html").

A copy of the source file has been provided here for your convenience, but it may not reflect the latest version.

# REFERENCE #

## EvaluateExpression ##

	$status		=  PhpUtilities::EvaluateExpression ( $expr, &$result, &$error = null ) ;

Evaluates an expression, but guarantees that no message will be output if the expression is incorrect.

This function uses the *eval()* builtin function.

If the expression is correct, the expression result will be put in the *$result* parameter and the return value will be *true*. If the expression is incorrect or if its evaluation generated a notice or error message, the return value will be *false* and the *$error* variable will receive the exact error message.

The parameters are the following :

- **$expr** *(string)* : 

A PHP expression to be evaluated. It can include any PHP code allowed inside an expression and does not need to be terminated with a semicolon. A **return** keyword will be prepended to the expression you supplied so that you will be able to retrieve the result, and a semicolon will be appended if the supplied expression does not end with a semicolon. Thus, if you supplied the following expression :

	17 * 8

the final expression passed to the *eval()* function will be :

	return 17 * 8;

Note that this automatic process of appending a semicolon if the supplied expression does not end with it may alter some error messages in case of a syntax error ; consider the following (incorrect) expression :

	17/

The *eval()* function will issue the following error :

	Parse error: syntax error, unexpected end of file
	 
while you will get the following error with the **EvaluateExpression** method :

	Parse error: syntax error, unexpected ';'

This is due to the fact that **EvaluateExpression** has remodeled your initial expression in the following way before passing it to the *eval()* function :

	return 17/;

- **result** *(string)* : A variable that will receive the result of the expression evaluation. Beware : this variable will remain unmodified if an error occurred. For this reason, care must be taken when you perform successive calls to **EvaluateExpression** ; the following code :

		$status 	=  PhpUtilities::EvaluateExpression ( '3*2', $result, $error ) ;
		$status 	=  PhpUtilities::EvaluateExpression ( 'INCORRECT_CONSTANT/', $result, $error ) ;

will leave the variable *$result* to the value **6** even after the second invocation to **EvaluateExpression** ; you should always check the return value of the function to avoid such situations.

- **error** *(string)* : Variable that will receive the error message if an error occurred during evaluation. This variable is systematically set to the *null* value if the expression was correct.


## EvaluateTags ##

		$result = PhpUtilities::EvaluateTags ( $value, $prepend ) ;

Evaluates a string and replaces all the PHP opening/closing tags with the output of the corresponding code inside. In some sense, it does the following :

	ob_start ( ) ;
	include ( 'example.ini' ) ;
	$contents 	=  ob_get_clean ( ) ;
	
The main differences between the traditional PHP way and the **EvaluateTags* method are :

- PHP only operates on files. **EvaluateTags** operates on strings.
- Only the variables visible in the scope where you put the calls to the ob\_start/include/ob\_get\_contents functions will be accessible from the code to be evaluated. If you put this code in a function, your global variables will not be visible unless you declare them with the **global** keyword. **EvaluateTags** ensures that all your global variables are accessible.
- PHP eats up any line break after a closing tag (**?&gt;**), thus affecting your document structure. **EvaluateTags** does not. Consider the following example file, which is a .INI file :

	[Settings]
	HOME 	=  <?= getenv ( 'HOME' ?>
	File 	=  example.txt

This will produce the following, using the PHP way (assuming that your $HOME variable is set to */users/myself*) :

	[Setting]
	HOME 	=  /users/myselfFile	=  example.txt

The parameters are the following :

- **value** *(string)* : 

Contents to be evaluated. The following PHP opening/closing tags are recognized :
	
	<?php ... ?>
	<?= ... ?>
	<? ... ?>

The short open tag (*&lt;? ... ?&gt;*) will be processed only if the *short\_open\_tags* directive of your *php.ini* file is set to *on*. 


## ExpandShellParameters ##

	public static function  ExpandShellParameters ( $string, $values = null ) ;

Expands any shell-like parameter reference in the supplied string with its corresponding value in the *$values* array.

Parameter references specified in *$string* can have the following forms :

- **$0, $1, $2, ... $n** : 

Each occurrence in the input string will be replaced with its corresponding value in the *$values* array : *$0* will be replaced with element 0, *$1* with element 1, and so on.

**$0** traditionally contains the program path. 

- **$\*** :

Will be substituted with all parameter values in the *$values* array, separated by a space.

- **$x-y** :

Will be substituted with parameters *x* to *y*, which are optional ; the form "*$x-*" means "all parameters starting from *x* up to the last one, while the form "*$-y" means : "all parameters up to *y*".  

- *$$* :

Will be substituted with the last value in the *$values* array.

The function parameters are the following :

- **$string** *(string)* : value containing parameter references to be processed.
- **$value** *(array of strings)* : array of values which will be used as subtitutions to parameter references in the *$string* parameter. If null, the **$argv** array will be used

The following example prints the string "Hello world" :

	echo PhpUtilities::ExpandShellParameters ( "$0 $1", array ( 'Hello', 'world' ) ) ;

## GetPHPTokens ##

	public static function  GetPHPTokens ( $input, $flags = PHP_TOKENS_DEFAULT ) ;

This function overrides some limitations of the *token\_get\_all()* builtin function and adds a few extra features :

- Unlike *token\_get\_all()*, all tokens are returned as associative arrays ; *token\_get\_all()* returns simple strings instead of non-associative arrays describing the token for strings such as "<", ">", ":", etc.
- The supplied input string does not need to have PHP opening/closing tags (*&lt;?php* and *?&gt;*)
- Whitespaces can be removed in the return value
- You can optionally make the function recognize constructs such as **##** and **#** as *stringification* operators, such as the C-preprocessor does.

The function returns an array of associative arrays which have the following keys :
- **id** : token id ; can be any one of the predefined PHP token ids (*T\_\**, such as *T\_WHITESPACE* for example), or one of the following integer constants :

	- XT\_LESS\_THAN : the "<" character
	- XT\_GREATER\_THAN : ">" 
	- XT\_QUESTION\_MARK : "?"
	- XT\_LEFT\_PARENT : "("
	- XT\_RIGHT\_PARENT : ")"
	- XT\_COMMA : ","
	- XT\_AMPERSAND : "&"
	- XT\_TILDE : "~"
	- XT\_SHARP : "#". Normally returned as T\_COMMENT by the token_get_all() function 
	- XT\_DOUBLE\_QUOTE : double quote character
	- XT\_SINGLE\_QUOTE : single quote character
	- XT\_LEFT\_BRACE : "{"
	- XT\_LEFT\_BRACKET : "["
	- XT\_DASH : "-"
	- XT\_VERTICAL\_BAR : "|"
	- XT\_UNDERLINE : "_"
	- XT\_BACKSLASH : "\\"
	- XT\_CARET : "^"
	- XT\_AT\_SIGN : "@"
	- XT\_RIGHT\_BRACKET : "]" 
	- XT\_EQUAL\_SIGN : "="
	- XT\_PLUS : "+"
	- XT\_RIGHT\_BRACE : "}"
	- XT\_PERCENT\_SIGN : "%"
	- XT\_STAR : "*"
	- XT\_BANG : "!"
	- XT\_COLON : ":"
	- XT\_SLASH : "/"
	- XT\_SEMICOLON : ":"
	- XT\_DOT : "."
	- XT\_BACKQUOTE : "`" 
	- XT\_DOLLAR : "$"
	- XT\_CATENATE : "##". This is a catenation operator used by some preprocessor, and will only be recognized if the PHP_TOKENS_PREPROCESSOR flag has been specified in the $flags parameter.
	- XT\_EOF : Marks the end of the input
	- XT\_UNKNOWN : Unknown token found. Such a value is returned when the *token\_get\_all()* function returns a string, and that string has not been recognized (this should never happen).

- **name** : the *T\_* or *XT\_* constant name, as a string.
- **value** : the token value.
- **line** : line number where the token has been found.
 

Parameters are the following :

- **$input** *(string)* : PHP code to be tokenized.
- **$flags** *(integer) : A combination of the following flags :
	- *PHP\_TOKENS\_ADD\_PHP\_TAGS* : Adds the PHP opening and closing tags to the code before tokenizing.
	- *PHP\_TOKENS\_ADD\_EOF* : Adds an **XT\_EOF** token at the end of the output.
	- *PHP\_TOKENS\_REMOVE\_SPACES* : Removes space tokens from the output (this will accelerate your own token processing if you are not interested in handling spaces between other tokens).
	- *PHP\_TOKENS\_PREPROCESSOR* : when this flag is set, the "#" sign will be returned as *XT\_SHARP*, and "##" as *XT\_CATENATE*. If not specified, both tokens will be returned as *T\_COMMENT*.
	- *PHP\_TOKENS\_ALL* : Enables all the above flags.
	- *PHP\_TOKENS\_DEFAULT* : Enables the *PHP\_TOKENS\_ADD\_PHP\_TAGS* and *PHP\_TOKENS\_ADD\_EOF* flags.

## GetPHPTokenName ##

	public static function  GetPHPTokenName ( $value )

This function is similar to the builtin *token\_name()* function, but also takes into account the *XT\_\** constants defined by this package.

## ParseCallback ##

	public static function  ParseCallback ( $value )

Parses a callback specification string. The *$value* parameter can hold one of the following constructs :

- A function name
- A class/method specification of the form *class::method*.
- An object/method specification of the form *object -&gt; method'*. 

The function returns a callback value, either as an array contain a class name (as a string) or object and a method name, or as a string which represents a function name.
