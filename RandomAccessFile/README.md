# INTRODUCTION #

The **RandomAccessFile** class is used to manage random access files, ie files having fixed-length records.

You can insert new records, copy one set of records to another destination in the same file, swap or truncate records.

The class also allows you to access file data as if it were an array or an iterator.

See the file [example.php](example.php "example.php") for a short example on how to use this class.

# HISTORY #

## VERSION 1.1 ##

- Added support for fixed-size and variable-size headers at the beginning of a random access file.


## VERSION 1.0 ##

- Initial version



## NOTES FOR USERS OF VERSION 1.0 ##

The version 1.0 was not able to process headers in random access files.

In version 1.1, a parameter, *$header\_size*, has been added as the third parameter of the class constructor. If you have code that already uses this class, then you should modify it to specify an additional value of 0 or false at the third position in the constructor (between the *$record\_size* and the *$cache\_size* parameters), if you are also specifying the optional *$cache\_size* and *$filler* parameters that appear after *$record\_size*.

# REFERENCE #

## METHODS ##

### CONSTRUCTOR ###

	$rf	=  new  RandomAccessFile ( $filename, $record_size, $header_size = false, $cache_size = false, $filler = "\0" ) ;

Instantiates a RandomAccessFile object, without opening the specified file.

The parameters are the following :

**$filename** *(string)* - Random file name.

**$record_size** *(integer)* - Size of a record.

**$cache_size** *(integer)* - When not null, indicates how many records from the random file should be cached into memory.

**$filler** *(char)* - Character to be used for filling when an incomplete record is written.

**$header_size** *(integer)* - Size of an optional fixed header at the start of the file. The default is *false*, which means *no header*. If your random access file contains a variable-length header, whose size is specified into some fixed-part of the header itself, then you can also specify a callback function that must have the following signature :

	integer function  mycallback ( $fd ) ;

*$fd* being the file descriptor which will allow you to read the part of the header that contains the real header size, then return this size. Note that the callback will be called whenever the **Open()** method is called.

### CLOSE ###

	$rf -> Close ( ) ;

Closes an already opened random access file.

Nothing happens if the file is already closed. Note that the class destructor systemtically closes the file.


### COPY ###

	$rf -> Copy ( $from, $to, $count = 1 ) ;

Copies *$count* record starting from record *from* to record *to*. Record numbers always start from zero.

This method can handle situations where origin and destination overlap.

The method returns the number of records effectively copied. This value can be lower than the specified number of records if :

- $from + $count - 1 goes past the end of file

- A read or write error occurred during the copy (consider this as a paranoid check)

Note that :

- The *$to* parameter can be specified past the end of file. In this case intermediate records will be created using the filler character.

- Similarly, at some point during the copy, the current destination record can go past the end of file ; in this case, the new record(s) will be appended to the random file.


### ISOPENED ###

	$status		=  $rf -> IsOpened ( ) ;

Checks if the random access file is opened.


### ISREADONLY ###

	$status		=  $rf -> IsReadOnly ( ) ;

Checks if the random access file has been opened in read-only mode.


### OPEN ###

	$rf -> Open ( $read_only = false ) ;

Opens the random file that has been instantiated.

By default, a random file is opened in write mode. Specify true for the *$read_only* parameter to open it in read-only mode.

### READ ###

	$data	=  $rf -> Read ( $record ) ;

Reads the record whose index has been specified (record numbers start from zero).

Note that this is equivalent to :

	$data 	=  $rf [ $record ] ;

Returns false if :

- The specified record number is past the end of file

- An unlikely IO error occurred

### SWAP ###

	$rf -> Swap ( $from, $to, $count = 1 ) ;

Swaps one or more record contents.

Although this method handles overlapping ranges, the result may seem counter-intuitive. This method should be used on non-overlapping ranges.

The method returns the actual number of records swapped.

### TRUNCATE ###

 	$rf -> Truncate ( $start_record ) ;

Truncates the random access file up to *$start_record* - 1.

### WRITE ###

	$rf -> Write ( $record, $data ) ;

Writes data in the specified record. Parameters are the following :

**$record** *(integer)* - Record number. If the specified record number is past the end of file, then empty records will be added using the filler character.

**$data** *(string)* - Record data. If the data is greater than the file's record size, it will be truncated. If smaller, it will be padded using the filler character.

Note that this is equivalent to :

	$rf [ $record ] 	=  $data ;

The last record of an existing file can be incomplete (such a situation is allowed for example when you use the RandomAccessFile class for fast access to existing text files). 

If the specified record number is past the end of file, then the last incomplete record (if any) will be filled using the filler character and intermediate records initialized with the filler character will be inserted as needed.


## PROPERTIES ##

### CacheMisses, CacheHits ###

Number of cache misses and cache hits since the file was opened.

### CacheSize ###

Number of cached records.

### Filename ###

Underlying random access file name.

### Filler ###

Filler character to be used when expanding incomplete records or inserting new ones.

### Header ###

This property will contain header data, once the **Open()** method has been called.

### HeaderSize ###

Contains the header size, as specified to the constructor, or returned by the **GetHeaderSize()** method when implemented by derived classes.

This property can also be set manually before calling the **Open()** method (but note that the results will be unpredictable if it is set after calling the method). 

### RecordSize ###

Random access file record size.

