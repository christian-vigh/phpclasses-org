# INTRODUCTION #

The **CircularBuffer** class implements a circular buffer, ie a buffer that can store a limited number of items. If you add items that exceed the capacity of the circular buffer, then the older items will be "forgotten" to leave room to the ones you want to add.

Circular buffers are often used when you have to face the following trade-offs :

- The amount of memory you are authorized to use is limited
- As time goes by, you can accept to discard some of the oldest data you're storing into the circular buffer

 ## Creating a circular buffer ##

Creating a circular buffer is easy ; just fix yourself a limit on the number of items you want to store :

	$buffer 	=  new CircularBuffer ( 1000 ) ; 	// The circular buffer will be able to store at most 1000 elements

## Adding items to a circular buffer ##

Adding items is easy ; just use the circular buffer as an array to append your latest items :

	$buffer [] 	=  "This is a log message" ;

## Retrieving items ##

Since the **CircularBuffer** class implements the ArrayAccess, Coutable and IteratorAggregate interface, you can perform the following operations :

- Retrieve the number of items present in the buffer using the PHP *count()* function
- Using the array notation to retrieve a particular element (eg : $buffer [0]). Note that index 0 will always return you the oldest element in the circular buffer, if it's not empty)
- Enumerate the items currently stored in the circular buffer using a *foreach* construct.

# REFERENCE #

## Constructor ##

The **CircularBuffer** class constructor has 3 different signatures, described in the following sections :

### $buffer = new CircularBuffer ( $size ) ; ###

Creates a circular buffer that is able to store *$size* elements.

### $buffer = new CircularBuffer ( $array ) ; ###

Creates a circular buffer and initializes it with the contents of *$array*.

The size of the circular buffer will be the size of the specified array.

### $buffer = new CircularBuffer ( $array, $size ) ; ###

Creates a circular buffer and initializes it with the contents of *$array*.

The circular buffer will have *$size* size elements. If *$size* is less than the number of items in *$array*, the circular buffer will be initialized with its first *$size* elements. If *$size* is greater, then emty elements will be added to the circular buffer.

## $buffer -> Reset ( ) ; ##

Empties the contents of the circular buffer.
