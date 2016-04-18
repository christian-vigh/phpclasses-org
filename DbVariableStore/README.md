# INTRODUCTION #

We all wanted to be able to store and retrieve application parameters to and from a database table dedicated to this sole purpose.

If you're as lazy as I am, you probably won't want to multiply SQL queries throughout your code  but rather access your parameters as if they were grouped together in an array or object, just to keep your code clean and readable.

And if you're running in a multitasking environment, you'll probably want your parameters to be accurate each time you retrieve their value ; and that all the modifications you made become instantly available to all other processes.

This is what the **DbVariableStore** class is aimed at, although this is *yet-another-variable-store-table-access* class : to provide you with an easy way to create variable store tables, and to easily create, update, access or delete them ; and also provide the syntactic sugar that will make you forget that you are manipulating values that are stored in an underlying SQL table.

Note that this class uses **MYSQL**.


## A BASIC SAMPLE ##

Perhaps the most basic sample code would be the following : it creates a variable store, then defines the 'RunMode' variable to 'production' ; note that the variable is created as a string value, but you will discover later in the following sections that several other types are available :

	include ( 'DbVariableStore.class.php' ) ;

	// Initialization process :
	// 1) First of all, connect to your local database
	$connection 	=  mysqli_connect ( 'localhost', 'root', '', 'mydatabase' ) ;

	// 2) Then create you variable store (if it does not already exist) :
	$store 			=  new DbVariableStore ( 'myvariables', $connection ) ;

	// 3) Create the 'RunMode' variable if not defined :
	if  ( ! $store -> IsDefined ( 'RunMode' ) )
			$store -> Define ( 'RunMode', 'production' ) ;

Note that step 3) could also be written as :

	if  ( ! isset ( $store [ 'RunMode' ] ) )
		$store [ 'RunMode' ] 	=  'production' ;

Or even :

		$store -> RunMode 		=  'production' ;

You can even store complex objects :

		$store -> Define ( 'arrayvalue', [ 1, 2 ], DbVariableStore::TYPE_SERIALIZED ) ;
		print_r ( $store -> arrayvalue ) ;

which will display :

		Array
		(
		    [0] => 1
		    [1] => 2
		)
		

## CLASS VERSATILITY ##

the **DbVariableStore** has to be seen as some kind of dictionary holding key/value pairs (the keys being the variable names). As such, provisions has been made so that you can access or set variable values using several ways :

- As an object :
	
		$store -> RunMode 	= 'production' ;

- As an associative array :

		$store [ 'RunMode' ] 	=  'production' ;

- As an integer indexed array, providing you now the position of your variable within the table !

		$store [0] 			=  'production' ;

(in the above example, the variable having index 0 is supposed to be 'RunMode').

Of course, this last access method is provided here only for consistency and completeness ; but keep in mind that this may not be the fastest one (or even the most readable one).

 
# IMPLEMENTATION #

The variable store table has the following structure :

	CREATE TABLE IF NOT EXISTS **table_name**
	   (
		id				INTEGER UNSIGNED		NOT NULL AUTO_INCREMENT
												COMMENT 'Unique id for this entry',
		name			CHAR(**length**)		NOT NULL DEFAULT ''
												COMMENT 'Variable name',
		type			ENUM ( 'string', 'integer', 'double', 'boolean', 'datetime', 'date', 'time', 'timestamp', 'serialized' )
												NOT NULL DEFAULT 'string'
												COMMENT 'Variable type',
		value			LONGTEXT				NOT NULL
												COMMENT 'Variable value',
		creation_time	DATETIME				NOT NULL DEFAULT '0000-00-00 00:00:00'
												COMMENT 'Creation time',
		update_time	DATETIME					NOT NULL DEFAULT '0000-00-00 00:00:00'
												COMMENT 'Last update time',

		PRIMARY KEY	( id ),
		UNIQUE KEY	( name )
	   ) ENGINE = MyISAM CHARSET latin1 COMMENT '{$this -> Comment}' 

Where *\*\*table\_name\*\** and *\*\*length\*\** are parameters specified to the **DbVariableStore** class constructor.

The meaning of the various columns is described below :

- *id* : Auto-increment id of the variable. Each id is unique, as well as is each variable name. This is also the primary key of the table.
- *name* : Variable name, which must be unique.
- *type* : Variable type, stored as an enumeration.
- *value* : Variable value.
- *creation\_time**, **update\_time* : Variable creation and last update time.

Note that in the current version, the *creation\_time* and *update\_time* columns are updated when needed, but cannot be accessed using the class methods.


# DOCUMENTATION #

## OVERVIEW ##

Variable names are case-insensitive, can contain any kind of characters and must be unique within a variable store (the indexes of the variable store table do not allow for multiple variables with the same name).

Each variable has a *type*, which is type String by default (a string can hold unprocessed data of any length). 

Additional types are available, including one which allows you to store/retrieve serialized values without caring for conversion aspects. You can have a look at the **Variable Types** section for more explanation on variable types. 

## API ##

### Constructor ###

The class constructor has the following signature :

	$store = new DbVariableStore ( $table, $connection, $varname_size = 128, $comment = 'Variable store' ) ;

Where the arguments are the following :

- *table* : Name of the table that will hold the variable store. This table will be automatically created if it does not exist (see the **Create()** method).
- *connection* : A database connection resource, as can be returned by a function such as **mysqli_connect()**.
- *varname_size* : Maximum length for a variable name. For (small) performance reasons (at the expense of storage), the **name** field is defined as CHAR type, not VARCHAR.
- *comment* : Table comment set when creating the table.

Note that instanciations specifying an already existing variable store table will not recreate it. You have to manually execute the DROP TABLE SQL statement if you really want to recreate your variable store.

### Defining a variable ###

To define a variable, use the **Define()** method :

	$store -> Define ( $name, $value, $type = TYPE_STRING ) ;

This defines a variable *$name* and assigns the value *$value* to it. By default, all variable values are considered as strings, unless you specify a different type through the *$type* parameter. You can have a look at the **Variable Types** section for more information on the supported variable types.

If the variable already exists, its value will be updated, unless the type you specified differs from its underlying type stored in the database table ; in this case, an exception will be thrown.

This method returns true if a new variable has been created, or false if an existing variable value was updated.

Note that there are other ways to define/update variables :

- By setting them as an object property :

		$store -> RunMode 	=  'production' ;

- By setting them as an associative array item :

		$store [ 'RunMode' ] 	=  'production' ;

- By setting them as an integer-indexed array (provided that you know the index of your variable) :

		$store [0] 	=  'production' ;

Integer indexes start from zero. 

You cannot create new variables using the integer-indexed array syntax.

However you can create new variables using the object property and the associative array syntaxes ; the created values will be of type String (see the *Variable types* section for an explanation on variable types).

### Checking for variable existence ###

You can check for variable existence using the following method :

	$status 	=  $store -> IsDefined ( $name ) ;

This will return *true* if the variable *$name* exists, and *false* otherwise.

### Deleting a variable ###

Use the following method to delete a variable :

	$status 	=  $store -> Undefine ( $name ) ;

This will return *true* if the variable did already exist and was deleted, and *false* otherwise.

### Retrieving variable values ###

Retrieving a variable value can be done in several ways :

- By calling the **ValueOf()** method :

		$value 	=  $store -> ValueOf ( 'RunMode' ) ;

- By accessing it as an object property :

		$value 	=  $store -> RunMode ;

- By accessing it as an associative array :

		$value 	=  $store [ 'RunMode' ] ;

- By accessing it as an integer-indexed array (provided you know the index of your variable !) :

		$value 	=  $store [0] ;

Integer indexes start from zero.

### Retrieving variable names ####

Use the following method to retrieve the names of the variables defined in your variable store :

	$names 	=  $store -> GetNames ( $pattern = null ) ;

The result is an array holding the variable names defined in the variable store. Items are always sorted in ascending order.

If a pattern is specified, then variable names will be filtered against this value, which can be any expression supported by the SQL LIKE operator.

## VARIABLE TYPES ##

Variables can be assigned a type, which is defined by one of the **DbVariableStore::TYPE\_xxx** constants. Variable types allow for automatic on-the-fly conversions when storing/retrieving values.

All the variables you create using the object property or associative array access methods will have the **DbVariableStore::TYPE\_STRING** type, as in the following example :

	$store -> RunMode 		=  'production' ;
	$store [ 'RunMode' ]	=  'production' ;

You have to use the **Define()** method to specify a type other than the default one, as in :

	// Since the variable type is integer, the stored value will be converted to an integer
	// so "3.14159" will be stored as "3" in the variable store.
	$store -> Define ( 'intvalue', 3.14159, TYPE_INTEGER ) ;

The following types are available :

- TYPE\_STRING 

The default type for all new values. Variable values of this type will be stored and retrieved as is, without any interpretation.

- TYPE\_INTEGER 

Integer value. Boolean and floating-point values will be converted to an integer ; any other kind of value will generate an exception.

- TYPE\_BOOLEAN

 A boolean value. It can be any of the following :

- True, non-zero value, non-empty string, the strings "Yes", "True", "On"
- False, zero-value, empty string, the strings "No", "False", "Off"

Any other kind of value will generate an exception.

- TYPE\_DOUBLE :

A floating-point, boolean or integer value, which will be converted to a floating-point value.

Any other kind of value will generate an exception.

- TYPE\_DATETIME :

The value will be stored using the following **date()** format : Y/m/d H:i:s.

The value supplied when defining the variable can either be a Unix timestamp or a string that can be understood by the **strtotime()** function.

Any other kind of value will generate an exception.

- TYPE\_DATE :

The value will be stored using the following **date()** format : Y/m/d.

The value supplied when defining the variable can either be a Unix timestamp or a string that can be understood by the **strtotime()** function.

Any other kind of value will generate an exception.

- TYPE\_TIME :

The value will be stored using the following **date()** format : H:i:s.

The value supplied when defining the variable can either be a Unix timestamp or a string that can be understood by the **strtotime()** function.

Any other kind of value will generate an exception.

- TYPE\_TIMESTAMP :

The value will be stored using as a Unix timestamp, which is the number of seconds elapsed since January 1st, 1970 at 0h.
The value supplied when defining the variable can either be a Unix timestamp or a string that can be understood by the **strtotime()** function.

Any other kind of value will generate an exception.

- TYPE\_SERIALIZED :

The supplied value will be serialized before storing it into the variable store, and unserialized when retrieving it. It can be used to hold any kind of complex structures such as objects or arrays.

## DERIVING ##

Derived classes may overload defined methods, especially the one that creates the underlying variable store table, **Create** ; below are the actual contents of the Create() method :

	public function  Create ( )
	   {
			$query	=  "
				CREATE TABLE IF NOT EXISTS {$this -> Name}
				   (
					id		INTEGER UNSIGNED	NOT NULL AUTO_INCREMENT
												COMMENT 'Unique id for this entry',
					name		CHAR({$this -> VariableNameSize})	NOT NULL DEFAULT ''
												COMMENT 'Variable name',
					type		ENUM ( 'string', 'integer', 'double', 'boolean', 'datetime', 'date', 'time', 'timestamp', 'serialized' )
												NOT NULL DEFAULT 'string'
												COMMENT 'Variable type',
					value		LONGTEXT				NOT NULL
												COMMENT 'Variable value',
					creation_time	DATETIME				NOT NULL DEFAULT '0000-00-00 00:00:00'
												COMMENT 'Creation time',
					update_time	DATETIME				NOT NULL DEFAULT '0000-00-00 00:00:00'
												COMMENT 'Last update time',

					PRIMARY KEY	( id ),
					UNIQUE KEY	( name )
				    ) ENGINE = MyISAM CHARSET latin1 COMMENT '{$this -> Comment}' 
			   " ;

			mysqli_query ( $this -> Connection, $query ) ;
	    }

## ADDING NEW DATA TYPES ##

Although the defined types should answer most of your needs, there may still be cases where you might want to implement your own one(s).

To do this, you must either modify the source code of the **DbVariableStore** class or inherit from it, then :

1. Add a **TYPE\_xxx** constant giving an integer value to your type
2. Override the **Create()** method to add your type name to the 'type' enumeration column. This has been a design choice to set the *type* column to an enumeration (from 'string' to 'serialized') rather than defining it as an integer value ; the advantage is that when browsing your variable store table, you will see readable names instead of integers corresponding to one of the **TYPE\_xxx** constants. The drawback is that you must establish a certain correspondance between the enum value (which starts with the value 'string', ie integer value 1) and the associated TYPE constant.
3. Modify the protected methods **FromDatabase()** and **ToDatabase()**, which convert a value taken from/written to the variable, to add a case for handling your new type within the **switch()** construct. 