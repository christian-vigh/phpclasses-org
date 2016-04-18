# INTRODUCTION #

The DbStringStore class is an utility database table wrapper class that is used to store strings of variable length. It has been tested using MySql but should work with other databases as well.

DbStringStore follows the Keep-It-Stupidly-Simple (KISS) principle ; don't expect here a major conceptual breakthrough in database design, but rather a simple and handy utility class aimed at providing an easy and performant way of storing strings to/retrieving strings from a database table.

# WHAT IS A STRING STORE ? #

A string store is a really simple and stupid table whose definition looks like this :

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

Each string store row holds a string **value**, identified by a unique **id**. But what could we further say about that ?

Every string value has an associated checksum, which is used to retrieve the string value ; instead of writing the following query to retrieve a value, which would force Mysql to use some index on the *value* field, if available :

		SELECT * FROM my_string_store WHERE value = 'hello world' ;

you will write :

		SELECT * FROM my_string_store WHERE checksum = CRC32( 'hello world' ) AND value = 'hello world'

What's the difference between the two queries above ? well, the first one uses the index on the string **value**, which has some constraints :

- When your table has millions of records, putting an index on a variable-length string column uses disk space. Of course, one could argue that disk space is not an argument nowadays ; however, consider that scanning a table with an index of 64 characters put on a variable-length value will require you to scan an index file of more than 6Gb for a 100-million records table.
- You cannot index the whole string value ; Mysql for example imposes a limit of 999 bytes for an index (this means 999 characters if you're not using UTF8 encoding, and 333 when using it because Mysql allocates 3 bytes per UTF8 character, whatever the UTF8 encoding you're using)

The second query uses the index put on the **checksum** column :

- It first locates ALL the rows that have the same CRC32 checksum than the string 'Hello world'
- Then it isolates the right row using the *value = 'hello world'* predicate in the WHERE clause

You will notice a significant performance improvement if you shape your database so that you can use checksums to locate your rows.

Of course, this method won't be of any use if you're doing a loose search, for example looking for rows that contain the string 'hello world' :

		SELECT * FROM my_string_store WHERE value LIKE '%hello world%' ;
	
You should keep in mind that Mysql NEVER USES ANY INDEX AT ALL when you use the **LIKE** operator with a string starting with '%', at least until version 5.7 ; however, given the example string store table above, it will use your index on the first 64 characters of the string value if you are looking for all the records that START WITH the string 'hello world', such as in :

		SELECT * FROM my_string_store WHERE value LIKE 'hello world%' ;

Worrying about the choice of the CRC32 algorithm to compute the checksum of a string and the potential collisions it could generate (ie, different string values having the same checksum) ? please have a look at the **WHY CHOOSING THE CRC32 algorithm** later in this article.

A note on the **type** field of the string store : it allows you to store apples and oranges in the same string store ; you could have for example a *type* field of 0 for storing english words, 1 for english sentences and so on... The **WHEN SHOULD I USE A STRING STORE** paragraph in this article shows some examples. If you do not plan to store strings of different categories within the same string store, simply use a value of zero.

In fact, the real query performed by the DbStringStore class to retrieve a value will be :

		SELECT * FROM my_string_store WHERE type = your_type AND checksum = CRC32( 'hello world' ) AND value = 'hello world'

# WHEN SHOULD I USE A STRING STORE ? #

You should use a string store when you know that the data you want to store will have several duplicates and/or when you want your main table to have rows of fixed length.

But maybe the best example I could give would comes from my own experience ; I currently own several VPS's at a hoster (OVH, not to name it) and I wanted to retrieve on a daily basis various log contents, such as apache logs, ssh auth logs and so on.

When you inspect such log files, you will rapidly notice that there are a certain number of elements in common ; for example, Apache logs can have hundreds of thousands of lines, but only exhibit a few tenths of different url requests, depending on your website size. This is a key choice for optimizing space and using a string store.

To further illustrate my reasoning, let's consider a log file of my own, which has the following kind of entries (don't worry about the real meaning of each entry, just focus on  the different parts it contains) :

	2016-01-01 13:20:01 httptracking[11776] Processing buffered http requests...
	2016-01-01 13:20:01 httptracking[11776] 0 http requests processed
	2016-01-01 13:25:02 httptracking[11908] Processing buffered http requests...
	2016-01-01 13:25:02 httptracking[11908] 2 http requests processed
	2016-01-01 13:30:01 httptracking[12043] Processing buffered http requests...
	2016-01-01 13:30:01 httptracking[12043] 0 http requests processed

A little bit like an Apache log, it contains the following information :

- A timestamp
- A process name (here, *httptracking*)
- A process id enclosed in square brackets
- A message : "*Processing buffered http requests...*" or "*x http requests processed*"

We could naively design a main table for storing these entries :

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

Note that we decided to stick to the field division described above. However we will have a lot of duplicate information :

- Process names (such as *httptracking*) are likely to be quite all the same
- log messages are likely to be redundant

This is why we will use a string store to store process names and log messages. Our *httptracking* table will now look like :

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
 
The *process* and *message* columns have become id columns in a string store (respectively, *process\_string\_store\_id* and *message\_string\_store\_id*). By convention, we will say that *process* entries will have a *type* of 0 in the string store, and *message* entries a type of 1. This is not really mandatory, unless you want want to retrieve information from the string store based on its type (for example, one of my string stores holds, among other information, ip addresses . Being able to retrieve their values in one simple query using the *type* field is very handy).

What are the impacts of these modifications ?

- Your table has now a fixed row length. When loading rows into memory, Mysql allocates a fixed area for each of them, even if it has variable-length columns such as VARCHAR or TEXT (the maximum size of a column is then used). If you have a VARCHAR(4096) field in your table, MySql will allocate 4096 bytes in memory for it when selecting your row (or 3 * 4096 if your field is encoded in UTF-8). This could lead to memory issues if your query selects too many rows.
- You will have to include JOIN clauses in your query if you want to retrieve the *process* and *message* values, such as in the following query, which selects the process names and log messages within a date range :

		SELECT
			httptracking. timestamp,
			process_store. value AS 'process',
			message_store. value AS 'message' 
		FROM httptracking
		LEFT JOIN my_string_store AS process_store ON
			httptracking. process_string_store_id = process_store. id
		LEFT JOIN my_string_store AS message_store ON
			httptracking. message_string_store_id =  message_store. id
		WHERE 
			timestamp BETWEEN '2015-01-01' AND '2015-12-31' ;
	
I agree, this is a little bit more complicated than directly selecting fields from the version 1 of the *httptracking* table, where the process name and message string are directly stored in the table, but :

- The perfomance gain of using a table with fixed-length rows will outperform the variable-length version, even if you are using JOINs
- The memory used by Mysql will be drastically diminished, because it will first scan the rows selected by the WHERE clause, then perform the joins. All rows will theorically occupy 36 bytes (the size of 3 BIGINTs, 1 DATETIME and 1 INT) instead of 8 + 8 + 32 + 4 + 1024 bytes = 1076 for the first version of the table, with the variable-length fields *process* and *message* (well, this is at least true for Mysql up to version 5.6).

As a conclusion, use a string store when :

- You know you will have a significant number of rows to store, with variable length information
- There are great chances that string values have several duplicates. Instead of storing several copies of the same string in a main table, you will store in the main table the id of a unique string in the string store.

# A SHORT EXAMPLE OF THE STRING STORE BENEFITS #

The *example.php* script stored with this package reads the *data/example.log* file (the one presented by the "WHEN SHOULD I USE A STRING STORE ?" paragraph above), and creates 3 tables using its contents :

- *httptracking_1*, where the process name and message part are included as VARCHAR columns in the table
- *httptracking_2*, with *httptracking_string_store*. Only the id of the process name and message part are stored into the *httptracking_2* table, while their actual values are put in the *httptracking_string_store* table.

The following query will show some table statistics :

	mysql> SHOW TABLE STATUS ;
	+---------------------------+--------+---------+------------+------+----------------+-------------+------------------+--------------+-----------+----------------+---------------------+---------------------+------------+-------------------+----------+----------------+---------+
	| Name                      | Engine | Version | Row_format | Rows | Avg_row_length | Data_length | Max_data_length  | Index_length | Data_free | Auto_increment | Create_time         | Update_time         | Check_time | Collation         | Checksum | Create_options | Comment |
	+---------------------------+--------+---------+------------+------+----------------+-------------+------------------+--------------+-----------+----------------+---------------------+---------------------+------------+-------------------+----------+----------------+---------+
	| httptracking_1            | MyISAM |      10 | Dynamic    | 8275 |             66 |      546520 |  281474976710655 |       217088 |         0 |           8276 | 2016-01-17 18:43:40 | 2016-01-17 18:43:48 | NULL       | latin1_swedish_ci |     NULL |                |         |
	| httptracking_2            | MyISAM |      10 | Fixed      | 8275 |             34 |      281350 | 9570149208162303 |       217088 |         0 |           8276 | 2016-01-17 18:43:45 | 2016-01-17 18:44:00 | NULL       | latin1_swedish_ci |     NULL |                |         |
	| httptracking_string_store | MyISAM |      10 | Dynamic    |   10 |             53 |         532 |  281474976710655 |         3072 |         0 |             11 | 2016-01-17 18:38:13 | 2016-01-17 18:43:54 | NULL       | latin1_swedish_ci |     NULL |                |         |
	+---------------------------+--------+---------+------------+------+----------------+-------------+------------------+--------------+-----------+----------------+---------------------+---------------------+------------+-------------------+----------+----------------+---------+
	3 rows in set (0.00 sec)

We can notice the following elements :

- the version of the *httptracking* table using string store ids is almost 50% smaller than its equivalent version using VARCHARs
- *httptracking\_2* row format is fixed ; its size is 34 bytes long (to be compared to 66 bytes for the average row length of *httptracking\_1*)
- Surprisingly, we have only 10 different values of process name and message part in our sample log file. You can imagine the I/O saving when a query returns all the records from *httptracking_2* with a join on string store values : mysql will have to perform only 10 index scans on the string store, instead of loading multiple duplicates of the same data as it would be the case on table *httptracking\_1*


# A REAL-LIFE EXAMPLE OF THE STRING STORE BENEFITS #

Well, the example in the above paragraph is ideal ; this may not be the case in real life and this is why I will give you some numbers taken from my personal experience. Remember I was collecting log file contents from my VPS'es on a daily basis ?  these log files are mainly related to apache access and error logs, ssh auth logs and mail logs (including a few other of mine).

- Every log file line has an entry in a table named *server\_logfile\_entries*. This is a fixed-row length table that has more than 3 million rows at the time of this writing.
- I'm storing the message part of the log lines (which occurs in general after the timestamp, the process name and process id enclosed in square brackets) in a table named *server\_logfile\_entries\_string\_store*. This table currently hold 1.8 million rows, meaning that 1.2 millions log lines have the same message part.
- During my import process, I'm extracting several types of information from the log message part ; these can include the url of a page that has been accessed, an ip address, a host name, an email address and so on. These multiple pieces of information are stored in a table name *server\_logfile\_data*, which has a fixed-row length since it relies on a string store to save the individual string items. The *server\_logfile\_data* table has 13 million rows...
- while the string store it uses has only 464 000 entries... So the variable length data is constrained and represents less than 10% of the whole data used to store information.

During the import process, I set the **UseCache** property of the *DbStringStore* object to true ; this ensures that once a string store value has been queried or inserted, it remains in memory for later access. Of course, this is a memory consuming method, so you will have to determine whether it fits your needs or not ; in my own case, the import process never ran out of memory even if it was retrieving tenths of thousands lines. But I have to admit that this import process is a cli php script which is authorized to have 1GB of memory...


# WHY CHOOSING THE CRC32 ALGORITHM ? #

To tell the truth, I first chosed an SHA1 key for the *checksum* field of a string store. Since an SHA1 is 32-characters long, it became rapidly evident that my string store, which held hundreds of thousands entries, was significantly smaller than its checksum index...

I then tried using a CRC32 field instead, which is only 4-bytes long. But before doing that, I was a little bit worrying about *collisions*, ie the percentage of different strings yielding the same CRC32 value.

So I did some...

## TESTING WITH THE CRC32 ALGORITHM ##

I did the following things :

- Take an example data set, such as this big list of English words : [data/words-english-big.dic.zip](data/words-english-big.dic.zip "data/words-english-big.dic.zip")
- Generate 100 millions of pseudo-sentences using between 3 and 11 words randomly taken from this dictionary. The function used to pick words was *mt_rand()* and a short query has proven that each generated sentence was unique.
- Create a table having two fields, the generated sentence and its CRC32 checksum
- Perform some statistics on the generated results

Here are the SQL statements that did that. The file *sentences.txt* contains the 100-million sentences (the program that generated this file is not supplied here, but I could provide you with a sample). The first step is to create the table :

		CREATE TABLE crc32_collision_test
		   ( 
				id 			INTEGER 			NOT NULL AUTO_INCREMENT,
				crc 		INTEGER UNSIGNED 	NOT NULL DEFAULT 0,
				text 		VARCHAR(4096)		NOT NULL DEFAULT '',
			
				PRIMARY KEY 	( id )
		    ) ENGINE = MyISAM CHARSET latin1 ;

Then load the sentences ; note that we will fill only one field in our table, the *text* field : the *crc* field, in fact, will be computed at the next step :

		LOAD DATA LOCAL INFILE 'C:/Temp/crc/sorted.txt'
			INTO TABLE crc32_collision_test
			FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '"' ESCAPED BY '"'
			LINES TERMINATED BY '\n'
			( text ) ;

Now compute the CRC32 field :

		UPDATE crc32_collision_test SET crc = CRC32(text) ;

Finally, add an index on the CRC32 field (loading the table with this index present would have had a great performance impact on row insertions) :

		ALTER TABLE crc32_collision_test
			ADD KEY USING HASH ( crc ) ;

And then the final touch to put everything into a clean state :

		OPTIMIZE TABLE crc32_collision_test ;

## AND NOW, THE RESULTS ##

The results show that 2 293 757 rows among 100 millions have the same CRC value ; this represents 2,3% of the total set and was extracted using the following query :

		SELECT SUM(count)
		FROM
		   (
				SELECT 
					crc, 
					COUNT(crc) as 'count'
				FROM crc32_collision_test
				GROUP BY crc
				HAVING COUNT(crc) > 1
				ORDER BY COUNT(crc) DESC
		    ) as Selection ;

The exact number of rows having duplicate values (ie, rows having more than one matching CRC value) is 1 144 834 ; the maximum number of duplicates is 4, as this can be shown by the following query :

		SELECT 
			crc, 
			COUNT(crc) as 'count'
		FROM crc32_collision_test
		GROUP BY crc
		HAVING COUNT(crc) > 1
		ORDER BY COUNT(crc) DESC

## CONCLUSION ##

Finally, we can find out that in this 100-million rows data set, we have :

- 48 CRC values having 4 duplicates
- 8993 CRC values having 3 duplicates
- 1 135 793 CRC values having 2 duplicates

and no more ! this means that executing the following query to select a string :
		
		SELECT * FROM my_string_store WHERE checksum = CRC32( 'hello world' ) AND value = 'hello world'

will need at most 2 index scans for 2 * 1.135% of the cases ; less than 0.01% of other queries will require up to 4 index scans (Mysql will select all the keys having the CRC of the searched string, then it will perform string comparisons with the real string value on 2.3% of the queries before returning the desired row).

All the above index scans will operate on 8-bytes index entries (*type* field + *checksum* field).

So, definitely, CRC32 is a good compromise as a checksum algorithm used to differentiate string values ; it has the advantage of low-memory usage with regards to other checksum algorithms such as SHA1 (only 4 bytes to be compared with the 32 bytes needed for SHA1, if stored as a plain string) without bringing too many checksum collisions on the data set.

And setting the **UseCache** property of the string store object to *true* will definitely overcome the collision problem, at the expense of more memory usage. But this definitely depends on the data you are processing.