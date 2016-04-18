# INTRODUCTION #

The DbStringStore class is used to store variable-length string values. It is useful when you have a table which should store several variable-length strings for which you know in advance that there will be several duplicates.

This is the case for example with a log file ; a typical log file could present information like this :

	2016-01-01 13:20:01 httptracking[11776] Processing buffered http requests...
	2016-01-01 13:20:01 httptracking[11776] 0 http requests processed
	2016-01-01 13:25:02 httptracking[11908] Processing buffered http requests...
	2016-01-01 13:25:02 httptracking[11908] 2 http requests processed
	2016-01-01 13:30:01 httptracking[12043] Processing buffered http requests...
	2016-01-01 13:30:01 httptracking[12043] 0 http requests processed

If you wanted to store this information into a table, you could try this first naive approach :

	CREATE TABLE httptracking
	   (
			id 			BIGINT UNSIGNED 		NOT NULL AUTO_INCREMENT,
			timestamp 	DATETIME 				NOT NULL,
			process 	VARCHAR(32) 			NOT NULL DEFAULT '',
			process_id 	INT 					NOT NULL DEFAULT 0,
			message 	VARCHAR(1024) 			NOT NULL DEFAULT '',

			PRIMARY KEY 	( id ),
			KEY 			( timestamp )
			-- and any other key you would like to perform specific searches
	    ) ENGINE = MyISAM ;

Or use a string store that have the following shape :

	CREATE TABLE my_string_store
	   (
  			id 			BIGINT UNSIGNED 	NOT NULL AUTO_INCREMENT,
  			type 		INT 				NOT NULL DEFAULT 0,
  			checksum 	INT UNSIGNED 		NOT NULL DEFAULT 0,
  			value 		VARCHAR(2048) 		NOT NULL DEFAULT '',

  			PRIMARY KEY ( id ),
  			KEY ( value(64) ),
  			KEY type ( type, checksum )
	    ) ENGINE=MyISAM ;

and reshape your main table so that it now looks like this :

	CREATE TABLE httptracking
	   (
			id 							BIGINT UNSIGNED 		NOT NULL AUTO_INCREMENT,
			timestamp 					DATETIME 				NOT NULL,
			process_string_store_id 	BIGINT UNSIGNED 		NOT NULL DEFAULT 0,
			process_id 					INT 					NOT NULL DEFAULT 0,
			message_string_store_id		BIGINT UNSIGNED 		NOT NULL DEFAULT 0,

			PRIMARY KEY 	( id ),
			KEY 			( timestamp )
			-- and any other key you would like to perform specific searches
	    ) ENGINE = MyISAM ;

Notice that your variable-length fields (*process* and *message* in the above example) have been replaced with the ids of string values in the string store.

Finally, a string store can help you, for performance reasons, to transform your main table containing variable-length columns into a fixed row length table using only pointers (ids) to string store values.

# WHAT DOES A STRING STORE HOLD ? #

The primary function of a string store is to hold an association of key/value pairs. Instead of storing variable-length string values in your main table, you store the ids of strings allocated into the string store.

To allow for fast information retrieval, a checksum value is associated with each string. This checksum is the base index of the table and is used whenever a string value needs to be retrieved.

You can also have an integer *type* field, which may be useful to you if you want to store apples and oranges in your string store. The main index is made of this type value and the checksum. If you don't care about storing different types of string, simply specify 0 for the *type* parameter of the **Insert()** method.

Finally, you can put an index on the string value when creating a string store. This may be useful if your string store contains several thousands or millions rows and you want to perform quick loose searches on the string value itself (for example, "SELECT ... WHERE value LIKE 'something%'")

# THE DbStringStore CLASS #

To use the DbStringStore class, simply include the DbStringStore.php file :

	require ( 'DbStringStore.php' ) ;

It itself includes the file DbTable.php, which is a (very very basic) base class for this one.

Once a string store object has been instanciated, you can use the **Insert()** method to insert/retrieve the id of a string store value.
 
## CONSTRUCTOR ##

The DbStringStore constructor is used to instanciate a string store object ; it can create or recreate the underlying database table :

	public function  __construct ( $database,
							       $table_name			= 'string_store', 
							       $comment				= '', 
							       $string_size			=  1024, 
							       $string_index_size	= 0, 
							       $recreate			= false )

The parameters are the following :

- **$database** : a link returned by the **mysqli_connect()** function
- **$table_name** : the name of the string store table in the database
- **$comment** : specify a comment to be used when creating the table
- **$string\_size** : the maximum size of a string value. Depending on the supplied size, the table that will be created will have either the VARCHAR, SMALLTEXT, MEDIUMTEXT or LONGTEXT type for its *value* field.
- **$string\_index\_size** : specifies the size of the index to be put on the *value* field. This should be used if you want to perform faster loose searches using the LIKE operator on your string store values. The default is 0, meaning that no index will be put on the *value* field, which will save index space and index updating time.
- **$recreate** : when true, forces the recreation of the specified string store table.

Note that the *$comment*, *$string\_size* and *$string\_index\_size* parameters are only used when the table is created. They are ignored when instanciating a DbStringStore object on an existing table.

## Create() method ##

The **Create()** method creates the string store table whose name has been specified to the class constructor. Nothing will happen if the table already exists.

## Drop() method ##

The **Drop()** method removes the string store table from the database.

## Insert() method ##

The **Insert()** method is the MAIN entry point when you want to create or retrieve string store values ; it has the following prototype :

	function Insert ( $type, $value ) ;

where :

- **$type** is the string store value type to be associated with your string value. Specify 0 if you do not want to have different string value types.
- **$value** is the string value to insert in the string store.

The method returns the unique id of the string store value, whether existing or newly created.

## Optimize() method ##

The **Optimize()** method optimizes the string store indexes.

## Truncate() method ##

The **Truncate()** method removes all rows from the string store table.

## UseCache property ##

The **$UseCache** property is a boolean value indicating whether string store values retrieved/created by the **Insert()** method should be cached into memory or not.

The default is false.

Caching avoids unnecessary mysql queries for values you already queried before ; it also caches new values you inserted. Note however that there is no checking on memory consumption implied by this feature. 