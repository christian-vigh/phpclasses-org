# INTRODUCTION #

This package introduces two classes : **BitString** and **BitStringIterator**.

A bit string can take as input a string or another bit string. The least significant bit in the string
will be the least significant bit of the first byte of the string ; the most significant bit will be
the most significant bit of the last byte of the string.

This may seem counter-natural, as we would expect the reverse situation. However, the string is 
internally converted to an array of little-endian integers (4 bytes on 32 bits systems, 8 bytes on
64-bits ones).

The original intent of this class was to retrieve color values from a stream of bytes ; each color
having a number of bits per components, and a number of components per color (for example, 24-bits
RGB colors have a number of bits per components of 8, and a number of components per color of 3, one
for each of the red, green, blue components).

Anyway, it can be used to retrieve any portion of bits within a bit string using the *BitString::GetBits*
method. The **BitStringIterator** class has been designed to retrieve things like colors values, but can be 
used for other purposes.


# LIMITATIONS #

These classes have been designed with performance in mind ; for this reason, no more than 32 bits can
be retrieved at once by the GetBits() method on 32-bits platforms, and no more than 64 on 64-bits
platforms. This is not a design issue, but rather a design choice.

# MORE TO COME #

In its current state, the **BitString** class acts as a read-only class : it only extracts bits from a bit string flow.

More features will be added in the future, such as setting bits or performing bitwise operations with other bit strings.

# REFERENCE #

## BitString class ##

### Methods ###

#### Constructor ####

	$bitstring	=  new BitString ( $data, $data_size_in_bits = false, $filler = 0 ) ;

Creates a bit string based on the supplied string data. The data can be later accessed through one of the following methods :

- By calling the GetBits() method to retrieve a group of bits
- By using the array access operator to retrieve an individual bit
- By using the iterator interface to retrieve individual bits

The parameters are the following :

- **$data** (*string or BitString*) : Data to be used to build the BitString. It can either be :
	- A string, which will be converted to integer values of size PHP\_INT_\SIZE, in little-endian order.
	- An existing BitString object, which will be duplicated.


- **$data\_size\_in\_bits** (*integer*) : If not specified, the total number of bits will be the length of *$data* * 8. If specified, indicates the exact number of bits to consider ; *$data* will be truncated if this parameter is shorter that *strlen($data)*, and expanded with the filler value if greater.

- **$filler** (*byte*) : Filler value, for unset bits, specified as a byte. This value is used when the total number of bits does not fit on a byte boundary, or when *$data* needs to be expanded because *$data\_size\_in\_bits* is greater than *strlen($data) * 8*. A value of *false*, *null* or empty string will be interpreted as 0x00.


#### GetBits ####

	public function  GetBits ( $offset, $bit_count )

Returns *$bit\_count* bits starting at the specified offset in the BitString.


### Interfaces ###

#### Countable ####

The *count()* function, when applied to a **BitString** object, will return the actual number of bits.

#### ArrayAccess ####

Allows to access a **BitString** object as an array, and retrieve each bit. The following example prints out all the bits in a BitString :

	$bs 	=  new BiString ( "ABCDEF" ) ;

	for  ( $i = 0 ; $i  <  count ( $bs ) ; $i ++ )
		echo $bs [$i] ;

#### Iterator ####

Allows to loop through each bit of a **BiString** object using a *foreach()* construct. The following example prints out all the bits in a BitString :

	$bs 	=  new BiString ( "ABCDEF" ) ;

	foreach  ( $bs  as  $bit )
		echo $bit ;

## BitStringIterator class ##

Allows to iterate through a **BitString** object and retrieve groups of bits as array.

This class was originally implemented to read a stream of values representing colors, with a specific number of bits per color component, and a number of components per color, but it can be used for many other purposes.

### Constructor ###

	$iterator	=  new BitStringIterator ( $data, $bits_per_component, $component_count = 1 ) ;

The **BitStringIterator** class allows to iterate through a **BitString** by groups of *$bits\_per\_component* bits.

An array of *$component\_count* elements will be returned upon each iteration.

The parameters are the following :

- **$data** (*string or BitString*) : BitString to be iterated. If a simple string is specified, it will be internally converted to a **BitString** object.

- **$bits\_per\_component** (*integer*) : Number of bits per component.

*$component\_count* (*integer*) : Number of components having *$bits\_per\_component* bits to be returned upon each iteration.

