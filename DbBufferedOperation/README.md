# INTRODUCTION #

The *DbBufferedOperation* base class and its derived classes (*DbBufferedInsert*, *DbBufferedUpdate* and *DbBufferedLoadFile*) are designed to minimize the number of SQL queries needed to write or update tables in your database.

You will use a *DbBufferedInsert* object to buffer INSERT requests, *DbBufferedLoadFile* for buffered LOAD DATA statements, and *DbBufferedUpdate* for buffered UPDATE statements.

## WHY SHOULD I BUFFER DATABASE OPERATIONS ? ##

Sometimes, we have to perform database operations as fast as possible. Just write or update ten or a hundred records without taking too much time, because we are operating in some real-time interactive environment, and we don't want the user to notice that our website is trying a desperate attempt to crawl the stream upwards. Or just because we are running a background process that has to acquire many data from the network within a limited time.

The process is pretty simple :

1. Read the data from somewhere (the network, for example)
2. Write the data to the datase
3. Loop to step 1 until input data is exhausted

However, writing data to the database a row at a time is a time-consuming operation when you have many rows to process, especially if you are operating in a networked environment ; a better process would be :

0. Allocate a buffer of x rows for my buffered database operation
1. Read the data from somewhere (the network, for example)
2. Add the data (ie, the current row) to the buffer
3. Loop to step 1 until input data is exhausted
4. Flush the buffer by putting all the collected rows together to generate a single SQL statement

The DbBuffered\* classes help you walk through this process by :

- Initializing a row buffer of the size you desire for step 0
- Flushing the buffer contents (ie, executing a big SQL query) at step 4
- Flushing the buffer contents at step 2 each time the number of currently buffered rows reaches the limît of your buffer size 

This is the first level of optimization you can do before thinking about parallel stuff...

## WHAT IMPACT DOES IT HAVE ON MY CODE ? ##

Well, for sure, you will have to perform some rewriting, but it's not that complicated ; suppose your initial read/write loop for inserting new records is the following (the *$dblink* identifier is a connection resource to your preferred database, opened through the *mysqli\_connect* function) :

		while ( ( $row = get_some_data_from_somewhere ( ) )  !==  false )
		   {
				$query 	=  "
							INSERT INTO mytable
							SET
								field1 = {$row [ 'field1' ]},
								field2 = {$row [ 'field2' ]},
								-- set other fields of your interest here...
						   " ;

				mysqli_query ( $dblink, $query ) ;	
		    }

The effort of rewriting your code will not imply endless hours of development time ; in the case of insertions in the database, simply write :

		$buffer_size 	=  8192 ; 		// We will buffer that amount of rows before writing to the database
		$buffer 		=  new  DbBufferedInsert ( 'mytable', [ 'field1', 'field2' ], $buffer_size, $dblink ) ;  

		while ( ( $row = get_some_data_from_somewhere ( ) )  !==  false )
		   {
				$buffer -> Add
				   ([
						'columns' =>
						   [
								'field1' 	=> $row [ 'field1' ],
								'field2' 	=> $row [ 'field2' ]
								// set other fields of your interest here
						    ]
				     ]) ;
		    }  

		$buffer -> Flush ( ) ; 		// Make sure everything is written to the database

*(of course, the get\_some\_data\_from\_somewhere() function above does not exists ; this is just an example function that is meant to return some associative array containing row data)* 

In the above example, the constructor of the *DbBufferedInsert* class takes four parameters :

- The name of the table you want to insert rows into
- The names of the table columns concerned by the insertion
- The number of rows to be buffered before a *flush* occurs (ie, before issuing an INSERT statement)
- A link to a mysql database

The loop itselfs simply adds rows (together with their values) to the buffer. If the maximum buffer size has been reached then an implicit flush will occur (ie, a big INSERT SQL query will be issued).

You can read the following paragraphs in this README file :

- **DbOperation classes API**, if you want a reference on the buffering classes
- **Benchmarking**, if you want a real-life example on why *DbOperation* classes operate faster on large data sets (well, not that large... we're not talking about Big Data here !)

## BUT WHAT IS BEHIND THAT "BUFFERING" STUFF ? ##

There is absolutely no magic in "buffering database operations". The basic idea is to buffer row data until it needs to be flushed, the flushing operation consisting of executing a global  SQL query that operates on these buffered rows instead of executing multiple queries on individual rows while they are being collected.

How does it work ? simply by rewriting individual queries in a way that a single query can insert/update multiple rows at the same time, in only one call to the **mysqli\_query()** function.

Just have a look to the following paragraphs that explain how SQL queries are rewritten for buffered operations...

### BUFFERED INSERTS ###

Suppose you have to insert several rows setting the *value1* and *value2* fields in some table :

		INSERT INTO mytable SET value1 = 10, value2 = 11 ;
		INSERT INTO mytable SET value1 = 20, value2 = 21 ;
		...
		INSERT INTO mytable SET value1 = 100, value2 = 101 ;

You will first instantiate a *DbBufferedInsert* object (in the example below, we want to buffer at most 1024 rows, and *$dblink* is an existing connection resource to a mysql database) :

		$buffer = new DbBufferedInsert ( 'mytable', [ 'value1', 'value2' ], 1024, $dblink ) ;

then you will collect your input data :
	
		while ( $row = get_some_data ( ) )
			$buffer -> Add ([ 'columns' => [ 'value1' => $row [ 'value1' ], 'value2' => $row [ 'value2' ] ]) ;

During the flush operation, a query will be executed, which will look like this :

		INSERT INTO mytable (value1, value2)
		   VALUES
				( 10, 11 ),
				( 20, 21 ),
				...
				( 100, 101 ) ;

### BUFFERED UPDATES ###

Trying to optimize an UPDATE query is a little bit trickier but is feasible ; lets consider an example set of queries :

		UPDATE mytable SET value1 = 10, value2 = 11 WHERE id = 1000 ;
		UPDATE mytable SET value1 = 20, value2 = 21 WHERE id = 1001 ;
		...
		UPDATE mytable SET value1 = 100, value2 = 101 WHERE id = 1100 ;

To buffer multiple updates, instanciate a *DbBufferedUpdate* object ; note that there is an extra parameter, which is the name(s) of the column(s) that should be used in the WHERE clause. This extra parameter can either be a string or or an array of strings (if you have multiple key columns in the WHERE clause of your UPDATE statement) :

		$buffer = new DbBufferedUpdate ( 'mytable', 'id', [ 'value1', 'value2' ], 32, $dblink ) ;

The read/write loop is slightly modified when compared to the buffered insert one, since the Add() method now requires an array containing two associative arrays :

- The first one gives the values for the keys of your UPDATE request ('id', in our example)
- The second one gives the column name/column value associations

Your read/write loop now looks like that :

		while ( $row = get_some_data ( ) )
			$buffer -> Add 
			   ([ 
					'keys' =>
						[ 'id' => $row [ 'id' ] ],
					'columns' => 
						[ 'value1' => $row [ 'value1' ], 'value2' => $row [ 'value2' ] ] 
			     ]) ;

(note the extra brackets enclosing the function arguments).

The flush operation will rewrite the query like this :

		UPDATE mytable
		SET
			value1 = CASE
				WHEN id = 1000 THEN 10
				WHEN id = 1001 THEN 20
				...
				WHEN id = 1100 THEN 100
			END,
			value2 = CASE
				WHEN id = 1000 THEN 11
				WHEN id = 1001 THEN 21
				...
				WHEN id = 1100 THEN 101
			END
		WHERE 
			id IN ( 1000, 1001, ..., 1100 ) ;

Some explanations may be needed :

- The WHERE clause selects only the records for which we have gathered the ids. Ok, so now we will select only the desired subset of our table, ie the rows that have an id field consisting of a value between 1000 and 1100
- Each column assignment is now a big CASE where each WHEN subclause assigns the value corresponding to the specified id of the specified column. So, for *id = 1000*, *value1* will be assigned the value 10, *value2* the value 11 and so on

You totally have the right to specify more than one id field when instanciating the *DbBufferedUpdate* object ; suppose you now want the following queries to be buffered :

		UPDATE mytable SET value1 = 10, value2 = 11 WHERE type = 'phone1' AND position = 1 ;
		UPDATE mytable SET value1 = 20, value2 = 21 WHERE type = 'phone2' AND position = 2 ;
		...
		UPDATE mytable SET value1 = 100, value2 = 101 WHERE type = 'phone100' AND position = 100 ;

Instantiating the *DbBufferedUpdate* class now looks like this :

		$buffer = new DbBufferedUpdate ( 'mytable', [ 'type', 'position' ], [ 'value1', 'value2' ], 32, $dblink ) ;

And your read/write loop looks like that :
 
		while ( $row = get_some_data ( ) )
			$buffer -> Add 
			   ([ 
					'keys' =>
						[ 'type' => $row [ 'type' ], 'position' => $row [ 'position' ] ],
					'columns' => 
						[ 'value1' => $row [ 'value1' ], 'value2' => $row [ 'value2' ] ]
			     ]) ;

The query built during the flush operation will even be trickier :

		UPDATE mytable
		SET
			value1 = CASE
				WHEN type = 'phone1' AND position = 1 THEN 10
				WHEN type = 'phone2' AND position = 2 THEN 20
				...
				WHEN type = 'phone100' AND position = 100 THEN 100
			END,
			value2 = CASE
				WHEN type = 'phone1' AND position = 1 THEN 11
				WHEN type = 'phone2' AND position = 1 THEN 21
				...
				WHEN type = 'phone100' AND position = 1 THEN 101
			END
		WHERE 
			( type = 'phone1' AND position = 1 ) OR
			( type = 'phone2' AND position = 2 ) OR
			...
			( type = 'phone100' AND position = 100 ) ;

Noticed how the WHERE clause was rewritten ? of course, it is strongly advised that your table has an index on the *type* and *position* columns, otherwise performances will be catastrophic !

### BUFFERED LOAD DATA INFILEs ###

Buffered load files should be used over buffered inserts when :

- You know that you will only have constant values to load into your table (ie, your input data does not make use of ny mysql function at all, such as NOW)
- Your input dataset has a significant amount of rows

And, anyway, Mysql LOAD DATA INFILE statement will always be faster than any INSERT query.
 
Using the same table and dataset as for buffered inserts, the code will not change significantly ; just instanciate a *DbBufferedLoadFile* object instead of a *DbBufferedInsert* one :

		$buffer = new DbBufferedLoadFile ( 'mytable', [ 'value1', 'value2' ], 1024, $dblink ) ;

		while ( $row = get_some_data ( ) )
			$buffer -> Add 
			    ([
					'columns' =>
						[ 'value1' => $row [ 'value1' ], 'value2' => $row [ 'value2' ] ]
				  ]) ;

The Add() method will keep the entries into memory and the flush operation will generate a temporary file ; the executed query will look like this :

		LOAD DATA LOCAL INFILE 'the_temporary_file'
		INTO TABLE mytable
		FIELDS TERMINATED BY ';' 
			OPTIONALLY ENCLOSED BY '"'
			ESCAPED BY '"'
		LINES  TERMINATED BY '\n'
		( value1, value2 ) ;

Ok, now let's have a look at the...

# DbBufferedOperation classes API #

The three buffering classes *DbBufferedInsert*, *DbBufferUpdate* and *DbBufferedLoadFile* all inherit from the abstract class *DbBufferedOperation*.

*DbBufferedOperation* is responsible for storing row data into a memory array that is extended by each call to the **Add()** method, which itself calls the **Flush()** method whenever the buffer is full.

Derived classes have the following responsibility :

- Provide a constructor with class-specific arguments
- Implement the abstract protected **BuildQuery()** method, which is called by **Flush()** to build the final insert/update/load query before Flush() executes it.

## The DbBufferedOperation class ##

The *DbBufferedOperation* class is the abstract base class for all other buffered operation classes. It provides most of the buffering mechanisms so that derived class mainly have to care about building the SQL query to be executed when a Flush operation is processed.

### function  \_\_construct (  $table_name, $columns, $buffer_size, $dblink ) ###

Initialize a DbBufferedOperation object. This constructor MUST be called by the derived classes.

The parameters are the following :

- *$table* : Name of the table concerned by the buffered operations.
- *columns* : Array of string holding the names of the columns to be operated on. Note that if you use both constant and computed columns when calling the **Add()** function, all names must be listed here.
- *$buffer_size* : Buffer size, ie the number of elements that will be kept into memory before the **Flush()** method is called.
- *$dblink* : A connection resource to a Mysql server. 
 
### function \_\_destruct ( ) ###

The destructor performs a last-chance call to the **Flush()** method when the object is being destroyed.

You should not rely on the destructor to perform a last *flush* ; always put a call to **Flush()** after the read/write data loop because, although destructors are called when an exception or an error condition is encountered, PHP fatal errors are never caught so the destructors will never be called in this case.

Fatal errors include for example a call to an undefined function, which is only detected at execution time.

### Add ( $values ) ###

Buffers the specified row data which is represented by the *values* parameter.

*values* is an associative array of associative arrays ; the top-level array keys can be the following :

- *'columns'* : An array of column name/value pairs that specify the new values for the columns to be updated. The values in this array are always escaped using the **mysqli\_escape\_string()** function before generating the query.
- *'computed-columns'* : An array of column name/value pairs that specify the new values for the columns to be updated. These values use expressions such as SQL functions such as NOW(), CONCAT(), etc. and are NEVER escaped.

Both entries are optional but at least one must be specified. Note that the columns needs not to be specified in the same order as they were specified to the constructor.

**Add()** returns *true* if adding the new record implied the **Flush()** method to be called because the buffer was full, or *false* otherwise.

### Flush ( ) ###

Unconditionnally flushes the buffer if rows are present in memory. This operation calls the abstract method **BuildQuery()** which must be implemented by derived classes.

### Properties ###

#### public $TableName ####
Gets the name of the table concerned with buffering, as specified to the class constructor.

#### public $BufferSize ####
Gets the number of rows to be buffered, as specified to the class constructor.

#### public $Database ####
Gets the connection link to the database, as specified to the class constructor.

#### public $FieldNames ####
Gets the array containing the field names concerned with insert/update operations, as specified to the class constructor.

#### public $Rows ####
Gets the data rows currently present in the buffer.

## DbBufferedInsert class ##

The *DbBufferedInsert* class provides the same functionalities as the *DbBufferedOperation* one. 

It implements its own protected **BuildQuery()** method.

### Properties ###

The following read-only properties are specific to the *DbBufferedInsert* class :

#### public $Flags ####

Insert flags. Can be any any of :

- *DbBufferedInsert::INSERT\_FLAGS\_NONE* : No specific flags.
- *DbBufferedInsert::INSERT\_FLAGS\_IGNORE* : Inserts will be performed with the IGNORE keyword (INSERT IGNORE) 

## DbBufferedUpdate class ##

The *DbBufferedUpdate* class provides the same functionalities as the *DbBufferedOperation* one. 

It implements its own protected **BuildQuery()** method.

However, since an update query needs to have columns specified in the WHERE clause to update only the appropriate rows, two methods have had slight modifications : the class *constructor* and the **Add()** method.

### function  \_\_construct (  $table_name, $where_fields, $columns, $buffer_size, $dblink ) ###

The constructor requires one additional parameters, *where_fields*, which is an array of column names to be used in the WHERE clause that will be generated by the **BuildQuery()** method.

### function  Add ( $values ) ###

The **Add()** method accepts an associative array of associative arrays, like its base class ; however, an additional entry, *keys*, will be needed to specify the column name/value pairs used in the WHERE clause.

The top-level associative array will thus contains the following keys :

- 'columns'
- 'computed-columns'
- 'keys' 

### Properties ###

The following read-only properties are specific to the *DbBufferedUpdate* class :

#### public $WhereFieldNames ####

List of column names that are used to select a row in the WHERE clause of an UPDATE query.

#### public $UpdateFieldNames ####

List of column names to be updated.

## DbBufferedLoadFile class ##

The *DbBufferedLoadFile* class provides the same functionalities as the *DbBufferedOperation* one. 

It implements its own protected **BuildQuery()** method.

### function  Add ( $values ) ###

The **Add()** method is identical to its parent class Add() method, but it ignores the *'computed-columns'* entry in the **$value** parameter (you cannot have computed values when loading data from a CSV file : it's just plain text).


# BENCHMARKING #

A small benchmarking script is available in file *benchmark.php*. 

Note that you should run it in CLI mode rather than as a web page because it may take more than the PHP default limit of 30 seconds for the maximum execution time of a script.

## OBJECTIVES ##

The benchmark objectives are to compare the performance of :

- Classical, individual INSERT statements *vs* using the *DbBufferedInsert* class 
- Classical, individual UPDATE statements *vs* using the *DbBufferedUpdate* class
- And, finally, comparing all of the above with the performance of the *DbBufferedLoadData* class.

All the insertion tests insert the same number of records into an empty table.

All the update tests update all the records of the table created by insertion tests. 

The benchmark is not meant to compare the performances of the same tests among different system configurations, but to compare the relative performance of each test on the same configuration.

## CONFIGURATION ##

All tests were run on a Dell Notebook, with a 1.8GHz dual-core Intel processor with 8Gb of ram, and running Windows 7.

The configuration is sufficient to ensure minimal OS swapping activity that could significantly affect the results from one run to another.

The version used for PHP is 5.6.16 and 5.6.17 for Mysql.

The benchmark was run in CLI mode.

## CONSTRAINTS ##

All the tests have been designed with the following considerations in mind :

- They must take at least 5 seconds or more to run, otherwise we could not distinguish the overhead implied by system activity from the real cpu time taken by the benchmark. In fact, the tests durations ranged from 7 to 15 seconds. To achieve that on my system, the number of records inserted by each test has been set to 50 000.
- There should be no more than a one second difference between two test runs. A greater difference would mean for example too much system swapping activity, which implies that the results would not be reliable (on idle Unix systems, the difference between two test runs should be minimal but, sorry, I'm using Windows for development so you have to cope with a lot of crap processes you cannot disable).
- The benchmark will be run at least 5 times, to ensure that the variation between the minimum and maximum execution times is not too high
- To ensure that the results remain consistent when the number of inserted records vary, the benchmark has been run with a dataset of ten times the initial dataset (that is, 500 000 records instead of 50 000).

## RESULTS ##

Since the benchmark has been run several times (5), the results show three quantities : min, max and average execution time, expressed in seconds and milliseconds. The delta column shows the difference between the min and max execution times, while the average takes into account the execution times (5 values), not the min/max. 

### BUFFERED INSERTS ###

The *DbBufferedInsert* class has been intantiated with a buffer size of 8192. Higher values do not show better performances :  

    On 50 000 rows :
                                   min      max    delta      avg
	Individual INSERTs          14.914   16.045    1.131   15.271
	Buffered INSERTS			 7.603    7.667    0.064    7.619

    %Gain : 50.11%

	Verification on 500 000 rows :
	Individual INSERTs         168.034
	Buffered INSERTs            77.806    

#### INTERPRETATION ####

We have a 50% increase in performance using buffered inserts over individual insert statements but that had to be expected :

- Individual inserts will call the **mysql\_query** function 50000 times : this mean 50000 network exchanges with the Mysql server, 50000 query parsing by the Mysql optimizer, and 50000 table locks before inserting one row.
- Buffered inserts with a buffer size of 8192 will call the **mysql\_query** function only 7 times ( ceil ( 50000 / 8192 ) ). 

The time taken by the **Add()** and **BuildQuery()** methods represent 25% of the total time each. This means that around 50% of the total execution time is taken by buffering rows into memory and generating SQL query text. Rewriting this class as a PHP extension written in C would give blazingly fast results !

Running the test on 500 000 rows showed consistent results for buffered inserts (there is a little overhead with regards to the 50 000-rows test that has to be accounted to system activity). 

#### CONCLUSION ####

If you :

- are really concerned with performance issues 
- have to generate inserts on-the-fly
- have several rows to insert (from, say, 10 to several thousands)
- don't want to bother with building INSERT queries with several value rows

then use the *DbBufferedInsert* class.

However, if you have a huge number of records to process, you might consider two alternatives :

1. Using the *DbBufferedLoadFile* class (but have a look at the conclusion of the **BUFFERED LOADFILES** paragraph below)
2. Directly generate yourself a .csv file and execute yourself a LOAD DATA INFILE sql statement

### BUFFERED UPDATES ###

Please note that the *DbBufferedUpdate* class was implemented just to provide one more handy class following the *DbBufferedOperation* logic ; however, as you will see, don't expect significant performance improvements. 

Note also that the performance results depend on the number of fields you are updating ; for example, adding a computed field (set to the return value of the NOW() function) added a little bit more than 0.5 seconds on the overall execution times. 

The *DbBufferedUpdate* class has been intantiated with a buffer size of 8192. Higher values do not show better performances :  

    On 50 000 rows :
                                   min      max    delta      avg
	Individual UPDATES          15.163   16.007    0.844   15.504
	Buffered UPDATES			14.309   14.448    0.139   14.379

    %Gain : 7.26%

	Verification on 500 000 rows :
	Individual UPDATES         172.867
	Buffered UPDATES           146.851    

#### INTERPRETATION ####

It's really difficult to optimize the execution time of multiple update requests on multiple rows with different values for each row. Although I tried different approaches, none of them succeeded to bring better results.

Remodeling multiple UPDATE queries into a single one, as described in the **BUFFERED UPDATES** paragraph can bring some benefits : I noticed a slight performance improvement when handling between around 30 and 60 rows at once (ie, grouping 30 to 60 UPDATE queries into a single one using CASE ... WHEN ... END constructs). Below 30, performance improvement diminishes ; above 60, Mysql tends to be confused and spend more time trying to handle the various CASEs.

A combined approach has been implemented : allowing big buffer size, generating consecutive statements of at most 64 rows and executing them using the **mysqli\_multi\_exec()** function.

However, the benefits of sending multiple reworked update queries are masked by the fact that each UPDATE statement returns a result set giving the number of affected rows ; so before running the next multiple query you must free all the results returned by the previous one. Otherwise you will get such an error from the **mysqli** PHP extension :

	Query error : Commands out of sync; you can't run this command now

Currently, I found no way in Mysql to discard the statistics returned by an UPDATE statement (which would be an equivalent of SQL Server statement : SET NOCOUNT ON).

As for inserts, running the test with 500 000 rows instead of 50 000 shows consistent results, although a small overhead is also present.

#### CONCLUSION ####

The *DbBufferedUpdate* class can bring some performance benefits.

Use it if :

- You are doing updates on a heavy number of rows
- You are not updating too much columns at once (updating a column means a WHEN clause in each CASE ... END statement associated to a column, for each buffered row)
- You have a low latency network

Don't use it if :

- It does not bother you to build yourself UPDATE statements
- You have a limited number of rows to update
- You have complex updates to perform (this includes for example update statements having multiple WHERE clauses)

### BUFFERED LOAD FILES ###

Please note that the *DbBufferedLoadFile* class was implemented just to provide one more handy class following the *DbBufferedOperation* logic ; however, as you will see, don't expect significant performance improvements. 

The *DbBufferedLoadFile* class has been intantiated with a buffer size of 50000 (ie, the size of our data set, so that the **Flush()** operation will be called only once) :

    On 50 000 rows :
                                   min      max    delta      avg
	Individual LOAD FILES        7.332    7.417    0.085    7.382  

	Verification on 500 000 rows :
	Individual LOAD FILES       75.404

#### INTERPRETATION ####

Again here, most of the time is spent in collecting row data into memory and writing it back to a temporary file when a flush operation is triggered, that will run a LOAD DATA INFILE sql statement.

#### CONCLUSION ####

Well, the *DbBufferedLoadFile** class may seem handy but it only has a few milliseconds performance improvement over the same data set processed by the *DbBufferedInsert* class.

So use it if :

- You want a uniform way of handling your data
- You want to perform insert operations with a fixed number of records

Don't use it if :

- Your data is already present in an existing csv file (the LOAD DATA INFILE statement only took 0.5 second with a csv file containing our 50000-rows data set !)
- Using the *DbBufferedLoadFile* did not solve your performance issues