# INTRODUCTION #

The **CLParser** (Command-Line Parser) package is yet-another command-line arguments parser, targeted for PHP scripts to be run on the command-line. 

If you are involved into developing multiple PHP scripts that are to be run as batch commands, command-line arguments parsing may become a real issue if you want this repetitive task to be reliable and easy to implement.

This package has been designed so that it will relieve you from the burden of parsing and verifying the values of command-line arguments, by implementing a vast amount of argument types and restrictions to be applied to the user-supplied values.

Don't feel yourself confused by the number of class files that make up this package ; you should never have to understand the few thousands of lines of code it represents to use it, since a real focus has been put on making its usage really simple.

# HOW DOES IT WORK ? #

Developing a batch PHP script using the **CLParser** package for parsing its command-line arguments is fairly straightforward ; it requires only a few steps that will quickly become familiar to you as your script development tasks will grow in quantity :

1. Include the file *CL.php* in your script ; it will in turn include everything needed to perform command-line parsing
2. Define your command-line syntax as an Xml string. These definitions will provide information such as parameter names, types, potential restrictions on their values, whether they are required or not, and so on.
3. Instantiate a **CLParser** object, providing the definitions coming from step 2).
4. Use your instance to retrieve individual parameter values.

By using the **CLParser** class, you will have access to features that are handled internally by the package, such as displaying command-line help, having access to a bunch of standard parameters, and much more.

# A SHORT EXAMPLE #

Consider the following example script (let's name it *example.php*), which has a single parameter named *string_value* (note that there is an alias to this parameter, called *sv*) :

	#!/usr/bin/php
	// Step 1 : include the main file, CL.php
	require ( 'path/to/CL.php' ) ;

	// Step 2 : Define the specifications of your command line
	$definitions 	=  <<<END
	<command>
		<usage>
			A script that displays the value of its -string_value argument.
		</usage>
	
		<string name="string_value, sv" default="default string value">
			A string value.
		</string>
	</command>				
	END;

	// Step 3 : Instantiate a CLParser object, providing the above definitions
	$cl 	=  new CLParser ( $definitions ) ;

	// Step 4 : Retrieve and use the supplied parameter values the way you like
	echo "The value of the string_value parameter is : " . $cl -> string_value . "\n" ;

This script can be found here : [examples/00.readme/example.md](examples/00.readme/example.md "examples/00.readme/example.md")

## HOW DID WE DEFINE THE COMMAND-LINE SYNTAX ? ##

You may have noticed that there is a *$definitions* variable, containing XML data. The XML format used for this is pretty simple :

- The root node is always **&lt;command&gt;**
- This root node contains several child items ; our example specifies the following :
	- **&lt;usage&gt;** node : specifies a more or less short text that gives a brief description of what your script is really doing
	- **&lt;string&gt;** node : defines a parameter of type *string*. Its name is *string\_value* and it has an alias named *sv*. You can specify as many parameter names or aliases as you like within a single parameter definition, as long as they are unique within your Xml definitions. Note that a default value has been specified for this parameter (*"default string value"*). Since this parameter definition is not marked as *required*, not supplying a value for this parameter on the command line will automatically assign the default value to your parameter, as if it had actually been specified when invoking the script.

Many other parameter types are available : booleans, integers, floats, emails, keywords, bitsets, ip addresses, etc. 

## HOW TO RUN THE SCRIPT  ? ##

One of the first things you could try now is invoking the script without any parameter :

	$ php example.php

Or, if you're running Unix, if file *example.php* has the execute permission and if the directory that contains it is in your PATH, simply type :

	$ example.php

which will output the default value of the *-string\_value* parameter :

	The value of the string_value parameter is : default string value

Now, to run your script specifying your own value for the *string\_value* parameter, specify it the way you would for most Unix commands, by prepending a minus sign to its name :

	$ example.php -string_value "Hello world"
	The value of the string_value parameter is : Hello world

Of course, you can use any alias defined together with this parameter :

	$ example.php -sv "Hello world, again"
	The value of the string_value parameter is : Hello world, again

**A note for Windows users :**
Unless you define a new executable file extension using the **ASSOC** and **FTYPE** commands, add it to your **PATHEXT** environment variable and rename the example scripts to use this extension, you won't be able to directly execute the example scripts. You will have to invoke each script through *PHP.EXE*, such as in the following example :

	C:\> php example.php -sv "Hello world, again"
 
## A FEW EXTRA FEATURES ##

Every script using the **CLParser** package provides a *-help* parameter that shows the general syntax of your command when specified on the command line, in a way that will remain standard accross all the scripts using this package :

	$ example.php -help
	Usage : example [-string_value string]
        A script that displays the value of its -string_value argument.

        [-string_value string] (-sv) :
                A string value.
                Default value : "default string value".
	 
You may have noticed that it uses the description you specified in the **&lt;usage&gt;** tag of your xml definitions.

The help text also includes a definition for each command-line parameter, showing the following information :

- The name of the parameter with its parameter type ("*-string\_value string*"). The text is enclosed within square brackets to mention that this parameter is optional.
- The list of parameter aliases, enclosed within parentheses (*-sv*).
- The help text you supplied for this parameter in your xml definitions ("*A string value*").
- The default value for this parameter, if any.

There is also a *-usage* parameter, that shows a more condensed description :

	$ example.php -help
	Usage : example [-string_value string]
        A script that displays the value of its -string_value argument.

A bunch of predefined command-line parameters are automatically made available to you ; they all start with a double dash, such as in the following example, which displays the elapsed time that the script took to run :

	$ example.php -sv "Hello world" --time
	The value of the string_value parameter is : Hello world

	*** Elapsed time : 14ms ***
	
(well, to tell the truth, this *--time* option is useless for Unix users since there is a *time* command available, but Windows users will appreciate).

For the sake of satisfying your own curiosity, you can run your example script by specifying the special *--help** parameter (note again the double dash) to get a list of the special parameters that are made available to you by the **CLParser** class :

		$ example.php --help | more
  
## A FEW EXTRA FEATURES II ##

Now that you have acquired a small idea of what the **CLParser** package can do for you, you will find below a more detailed listing of the package's features. None of them will be demonstrated in this README file (you will have to walk a little bit through the documentation located here : [Help/help.html](Help/help.html "Help/help.html") to achieve that), but this will at least give you a more precise overview of what it can do for you.

You can also have a look at the following example, which shows a very reduced subset of the package features : [examples/01.general/example.php](examples/01.general/example.php "examples/01.general/example.php").

Here is an overview of the **CLParser** package features : 

1) Named *vs* positional parameters : *named* parameters are always specified with a parameter name/value pair, such as :

		-string_value "Hello world"

An exception to this rule is for *flag* or *boolean* parameters, which do not accept any value after them, eg :

		-boolean_flag_that_enables_some_switch
		+ignore_trailing_spaces

*positional* parameters do not have any name associated with them ; they simply are strings that will be collected together by the **CLParser** package. Maybe the best example to illustrate that is to take the well-known Unix command, **ls** : it has a bunch of *named* parameters and flags, such as *-a* or *-l*, followed by *positional* parameters, which represent the files or directories to be listed :

		$ ls -al /tmp /usr

The Unix convention states that positional parameters should be specified at the very end of the command line : specify options and flags first, then file names last.

The **CLParser** package keeps track of the positional parameters it finds while parsing the command line, thus allowing you to specify anything in any order ; suppose that the *ls* command was written in PHP using the **CLParser** package, then you could rewrite the above command this way :

	$ ls /tmp -a /usr -l

(note however that you cannot merge single-character parameters together ; if you specify "*-al*", then the CLParser package will expect a parameter whose name or alias is "*al*" and will not recognize that you intended to specify a parameter called "*a*" with a second one called "*l*").

2) Ability to specify a file containing command parameters instead of specifying them on the command line, using the "@" notation.

For example, suppose that you have a file *params.txt* which contains the following :

	-string_value "hello world"

Then, you can run our example script the following way :

	$ example @params.txt

which will be equivalent to :

	$ example -string_value "hello world"

You can even nest parameter files ! consider this new *params.txt* file :

	-string_value "hello world"
	@params2.txt

When scanning this file, the **CLParser** package will extract all the named and positional parameters from the first file, *params.txt*, then open file *params2.txt* to extract remaining parameters to build the final command line before parsing it.

3) A comprehensive set of command-line parameter types :

- Strings or single characters
- Integral values (byte, shorts, integers, longs), both signed or unsigned, and floating-point values (float, double). Note that all numeric values can be specified as mathematical expressions, which will be evaluated by the **CLParser** package.
- Flags and boolean parameters
- Keywords
- Date, time and datetime, along with month names and day names 
- Email addresses
- Files, with the possibility of telling whether the file should be existing, or should be created
- Directory names
- File contents
- File mask
- File system tree contents
- Capacities
- Durations
- Urls
- Color names or values, using various color schemes such as RGB, HSL and so on
- IP addresses, masks and ip ranges.
- Domain names
- Mime types
- Lists
- Arrays (which can be viewed as lists)
- Url contents
- Country codes 
- Phone numbers
- Sets and bitsets
- Ranges
- Command lines ; an example of a potential usage for this parameter type can be found in the Unix *find* command, with its *-exec* parameter
- Drive names (Windows platforms only)
- Sort options

4) Ability to specify how many values can be accepted for single parameters, how many times parameters can be specified on the command line, and much more

5) Ability to preprocess or postprocess parameter values by specifying callbacks to the **CLParser** package

6) Automatic construction and formatting of help and usage text

7) Ability to embed PHP code in the XML command-line definitions, that will be interpreted at run time 

8) And much more !

# INSTALLATION #

Copy the *CL.php* file and *CL* directory into your preferred code base, include the file *CL.php* in your scripts and that's all !

# WHERE IT ALL COMES FROM #

Although this package tries to mimic the Unix-style of specifying command-line arguments, with providing greater reliability in parsing, greater versatility in the way to specify command-line arguments, and a greater ease of use when defining command-line specifications (to be compared to all the packages derived from the Unix *getopt* library), this package has one main, big source of inspiration : the **NOS/VE** proprietary operating system from the **Control Data** mainframe manufacturer (try [https://en.wikipedia.org/wiki/NOS/VE](https://en.wikipedia.org/wiki/NOS/VE "https://en.wikipedia.org/wiki/NOS/VE")).

The development of the 64-bits NOS/VE operating system started in 1974, on the Control Data **Cyber 170** series (and maybe older), which were mainframes using 60-bits words. The first versions appeared in 1984 and integrated features that were initially planned for the AT&T Multics operating system, such as DLLs (Windows) or shared libraries (Unix), which only appeared several years later on commercial systems.

It had a System Command Language (SCL, but most of you would call it a shell) that was greatly inspired from the Pascal and Ada languages. It offered exceptions, event handlers, strongly typed variables including enumerations and structures (records). It was not object-oriented, however.

A module of the NOS/VE operating system was called **CLM** (which stands for "*Command Line Module*"). It was natively available to script developers, and available as an API for OS developers that used the **Cybil** language (Cyber Implementation Language, a superset of the Pascal language that was used to develop the NOS/VE operating system, see [http://www.museumwaalsdorp.nl/computer/en/nosve.html#cybil](http://www.museumwaalsdorp.nl/computer/en/nosve.html#cybil "http://www.museumwaalsdorp.nl/computer/en/nosve.html#cybil")).

When I first discovered the Unix operating system in 1987, I was first impressed by its simplicity, its concepts of *everything-is-a-file* and much more. However, as a developer, I felt a little bit unsatisfied with the Unix habit of parsing program command-line arguments, having in mind what I had seen, used and relied on with the NOS/VE operating system.

In the late eighties, I made a first implementation of this command line parsing module using the C language, on a PC running MSDOS with 512Kb of RAM. Years went by, and when I really came to PHP development in 2008-2009, I knew that I would have to develop tenths or even hundreds of scripts, and that command-line parsing was definitely not a task I would agree to spend considerable time on, and that copy/paste operations to reproduce the same behaviors for validating arguments were definitely not a solution.

This is why I redeveloped this Command Line Module in PHP, trying to gather best of both worlds. Of course, my **CLParser** package will never implement all the features that could be found on the NOS/VE operating system, since the NOS/VE System Command Language was strongly typed, and my package had to cope with the untyped nature of Unix and Msdos shells. But, as I said, I tried to gather best of both worlds and bring some reliability and ease of use to script developers.

# FUTURE EVOLUTIONS #

I'm using this package since 2009 ; every PHP command-line script I ever developed since then - except a few exceptions - use this package. I'm constantly adding new features when I'm facing new needs, but I remain open to any new ideas, suggestions or feature requests from potential users.

Should you be in this case, please feel free to contact me by using the *Support* option, or directly at the following address :

	christian.vigh@wuthering-bytes.com



