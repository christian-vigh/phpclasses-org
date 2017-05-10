<?php
	// Adobe standard character sets (Standard, Mac, Windows, Pdf)
	$adobe_charsets 	=  array
	   (
		'A'			=>  array ( 0101, 0101, 0101, 0101 ),
	   	'AE'			=>  array ( 0341, 0256, 0306, 0306 ),
	   	'Aacute'		=>  array (    0, 0347, 0301, 0301 ),
	   	'Acircumflex'		=>  array (    0, 0345, 0302, 0302 ),
		'Adieresis'		=>  array (    0, 0200, 0304, 0304 ),
		'Agrave'		=>  array (    0, 0313, 0300, 0300 ),
		'Aring'			=>  array (    0, 0201, 0305, 0305 ),
		'Atilde'		=>  array (    0, 0314, 0303, 0303 ),
		'B'			=>  array ( 0102, 0102, 0102, 0102 ),
	   	'C' 			=>  array ( 0103, 0103, 0103, 0103 ),
		'Ccedilla'		=>  array (    0, 0202, 0307, 0307 ),
		'D'			=>  array ( 0104, 0104, 0104, 0104 ),
	   	'E' 			=>  array ( 0105, 0105, 0105, 0105 ),
		'Eacute'		=>  array (    0, 0203, 0311, 0311 ),
		'Ecircumflex'		=>  array (    0, 0346, 0312, 0312 ),
		'Edieresis'		=>  array (    0, 0350, 0313, 0313 ),
		'Egrave'		=>  array (    0, 0351, 0310, 0310 ),
		'Eth' 			=>  array (    0,    0, 0320, 0320 ),
		'Euro'			=>  array (    0,    0, 0200, 0240 ),
		'F'			=>  array ( 0106, 0106, 0106, 0106 ),
		'G'			=>  array ( 0107, 0107, 0107, 0107 ),
		'H'			=>  array ( 0110, 0110, 0110, 0110 ),
		'I'			=>  array ( 0111, 0111, 0111, 0111 ),
	   	'Iacute'		=>  array (    0, 0352, 0315, 0315 ),
	   	'Icircumflex'		=>  array (    0, 0353, 0316, 0316 ),
		'Idieresis'		=>  array (    0, 0354, 0317, 0317 ),
		'Igrave'		=>  array (    0, 0355, 0314, 0314 ),
		'J'			=>  array ( 0112, 0112, 0112, 0112 ),
		'K'			=>  array ( 0113, 0113, 0113, 0113 ),
		'L'			=>  array ( 0114, 0114, 0114, 0114 ),
		'Lslash'		=>  array ( 0350,    0,    0, 0225 ),
		'M'			=>  array ( 0115, 0115, 0115, 0115 ),
		'N'			=>  array ( 0116, 0116, 0116, 0116 ),
		'Ntilde'		=>  array (    0, 0204, 0321, 0321 ),
	   	'O'			=>  array ( 0117, 0117, 0117, 0117 ),
		'OE' 			=>  array ( 0352, 0316, 0214, 0226 ),
		'Oacute' 		=>  array (    0, 0356, 0323, 0323 ),
		'Ocircumflex'		=>  array (    0, 0357, 0324, 0324 ),
		'Odieresis'		=>  array (    0, 0205, 0326, 0326 ),
		'Ograve'		=>  array (    0, 0361, 0322, 0322 ),
		'Oslash' 		=>  array ( 0351, 0257, 0330, 0330 ),
		'Otilde' 		=>  array (    0, 0315, 0325, 0325 ),
	   	'P'			=>  array ( 0120, 0120, 0120, 0120 ),
	   	'Q'			=>  array ( 0121, 0121, 0121, 0121 ),
	   	'R'			=>  array ( 0122, 0122, 0122, 0122 ),
	   	'S'			=>  array ( 0123, 0123, 0123, 0123 ),
		'Scaron'		=>  array (    0,    0, 0212, 0227 ),
		'T'			=>  array ( 0124, 0124, 0124, 0124 ),
		'Thorn'			=>  array (    0,    0, 0336, 0336 ),
		'U'			=>  array ( 0125, 0125, 0125, 0125 ),
		'Uacute'		=>  array (    0, 0362, 0332, 0332 ),
		'Ucircumflex'		=>  array (    0, 0363, 0333, 0333 ),
		'Udieresis'		=>  array (    0, 0206, 0334, 0334 ),
		'Ugrave'		=>  array (    0, 0364, 0331, 0331 ),
		'V'			=>  array ( 0126, 0126, 0126, 0126 ),
		'W'			=>  array ( 0127, 0127, 0127, 0127 ),
		'X'			=>  array ( 0130, 0130, 0130, 0130 ),
		'Y'			=>  array ( 0131, 0131, 0131, 0131 ),
		'Yacute'		=>  array (    0,    0, 0335, 0335 ),
		'Ydieresis'		=>  array (    0, 0331, 0237, 0230 ),
		'Z'			=>  array ( 0132, 0132, 0132, 0132 ),
	   	'Zcaron'		=>  array (    0,    0, 0216, 0231 ),
		'a' 			=>  array ( 0141, 0141, 0141, 0141 ),
		'aacute'		=>  array (    0, 0207, 0341, 0341 ),
		'acircumflex'		=>  array (    0, 0211, 0342, 0342 ),
		'acute'			=>  array ( 0302, 0253, 0264, 0264 ),
		'adieresis'		=>  array (    0, 0212, 0344, 0344 ),
		'ae'			=>  array ( 0361, 0276, 0346, 0346 ),
		'agrave' 		=>  array (    0, 0210, 0340, 0340 ),
		'ampersand' 		=>  array ( 0046, 0046, 0046, 0046 ),
		'aring' 		=>  array (    0, 0214, 0345, 0345 ),
		'asciicircum' 		=>  array ( 0136, 0136, 0136, 0136 ),
		'asciitilde'		=>  array ( 0176, 0176, 0176, 0176 ),
		'asterisk' 		=>  array ( 0052, 0052, 0052, 0052 ),
		'at'			=>  array ( 0100, 0100, 0100, 0100 ),
		'atilde'		=>  array (    0, 0213, 0343, 0343 ),
		'b' 			=>  array ( 0142, 0142, 0142, 0142 ),
		'backslash' 		=>  array ( 0134, 0134, 0134, 0134 ),
		'bar' 			=>  array ( 0174, 0174, 0174, 0174 ),
		'braceleft'		=>  array ( 0173, 0173, 0173, 0173 ),
		'braceright' 		=>  array ( 0175, 0175, 0175, 0175 ),
		'bracketleft' 		=>  array ( 0133, 0133, 0133, 0133 ),
		'bracketright' 		=>  array ( 0135, 0135, 0135, 0135 ),
		'breve'			=>  array ( 0306, 0371,    0, 0030 ),
		'brokenbar' 		=>  array (    0,    0, 0246, 0246 ),
		'bullet' 		=>  array ( 0267, 0245, 0225, 0200 ),
		'c'			=>  array ( 0143, 0143, 0143, 0143 ),
		'caron'			=>  array ( 0317, 0377,    0, 0031 ),
		'ccedilla'		=>  array (    0, 0215, 0347, 0347 ),
		'cedilla'		=>  array ( 0313, 0374, 0270, 0270 ),
		'cent' 			=>  array ( 0242, 0242, 0242, 0242 ),
		'circumflex' 		=>  array ( 0303, 0366, 0210, 0032 ),
		'colon' 		=>  array ( 0072, 0072, 0072, 0072 ),
		'comma'			=>  array ( 0054, 0054, 0054, 0054 ),
		'copyright'		=>  array (    0, 0251, 0251, 0251 ),
		'currency'		=>  array ( 0250, 0333, 0244, 0244 ),
		'd'			=>  array ( 0144, 0144, 0144, 0144 ),
		'dagger' 		=>  array ( 0262, 0240, 0206, 0201 ),
		'daggerdbl' 		=>  array ( 0263, 0340, 0207, 0202 ),
		'degree' 		=>  array (    0, 0241, 0260, 0260 ),
		'dieresis'		=>  array ( 0310, 0254, 0250, 0250 ),
		'divide' 		=>  array (    0, 0326, 0367, 0367 ),
		'dollar' 		=>  array ( 0044, 0044, 0044, 0044 ),
		'dotaccent' 		=>  array ( 0307, 0372,    0, 0033 ),
		'dotlessi'		=>  array ( 0365, 0365, 0x131, 0232 ),
		'e' 			=>  array ( 0145, 0145, 0145, 0145 ),
		'eacute'		=>  array (    0, 0216, 0351, 0351 ),
		'ecircumflex'		=>  array (    0, 0220, 0352, 0352 ),
		'edieresis' 		=>  array (    0, 0221, 0353, 0353 ),
		'egrave'		=>  array (    0, 0217, 0350, 0350 ),
		'eight' 		=>  array ( 0070, 0070, 0070, 0070 ),
		'ellipsis' 		=>  array ( 0274, 0311, 0205, 0203 ),
		'emdash' 		=>  array ( 0320, 0321, 0227, 0204 ),
		'endash' 		=>  array ( 0261, 0320, 0226, 0205 ),
		'equal' 		=>  array ( 0075, 0075, 0075, 0075 ),
		'eth'			=>  array (    0,    0, 0360, 0360 ),
		'exclam' 		=>  array ( 0041, 0041, 0041, 0041 ),
		'exclamdown' 		=>  array ( 0241, 0301, 0241, 0241 ),
		'f' 			=>  array ( 0146, 0146, 0146, 0146 ),
		'fi' 			=>  array ( 0256, 0336, 0xFB01, 0223 ),
		'five' 			=>  array ( 0065, 0065, 0065, 0065 ),
		'fl' 			=>  array ( 0257, 0337, 0xFB02, 0224 ),
		'florin' 		=>  array ( 0246, 0304, 0203, 0206 ),
		'four'			=>  array ( 0064, 0064, 0064, 0064 ),
		'fraction'		=>  array ( 0244, 0332,    0, 0207 ),
		'g' 			=>  array ( 0147, 0147, 0147, 0147 ),
		'germandbls'		=>  array ( 0373, 0247, 0337, 0337 ),
		'grave' 		=>  array ( 0301, 0140, 0140, 0140 ),
		'greater' 		=>  array ( 0076, 0076, 0076, 0076 ),
		'guillemotleft'		=>  array ( 0253, 0307, 0253, 0253 ),
		'guillemotright' 	=>  array ( 0273, 0310, 0273, 0273 ),
		'guilsinglleft'		=>  array ( 0254, 0334, 0213, 0210 ),
		'guilsinglright'	=>  array ( 0255, 0335, 0233, 0211 ),
		'h'			=>  array ( 0150, 0150, 0150, 0150 ),
		'hungarumlaut'		=>  array ( 0315, 0375,    0, 0034 ),
		'hyphen' 		=>  array ( 0055, 0055, 0055, 0055 ),
		'i' 			=>  array ( 0151, 0151, 0151, 0151 ),
		'iacute'		=>  array (    0, 0222, 0355, 0355 ),
		'icircumflex' 		=>  array (    0, 0224, 0356, 0356 ),
		'idieresis'		=>  array (    0, 0225, 0357, 0357 ),
		'igrave' 		=>  array (    0, 0223, 0354, 0354 ),
		'j' 			=>  array ( 0152, 0152, 0152, 0152 ),
		'k' 			=>  array ( 0153, 0153, 0153, 0153 ),
		'l' 			=>  array ( 0154, 0154, 0154, 0154 ),
		'less'			=>  array ( 0074, 0074, 0074, 0074 ),
		'logicalnot' 		=>  array (    0, 0302, 0254, 0254 ),
		'lslash'		=>  array ( 0370,    0x142, 0x142, 0233 ),
		'm'			=>  array ( 0155, 0155, 0155, 0155 ),
		'macron'		=>  array ( 0305, 0370, 0257, 0257 ),
		'minus' 		=>  array (    0,    0,    0, 0212 ),
		'mu' 			=>  array (    0, 0265, 0265, 0265 ),
		'multiply'		=>  array (    0,    0, 0327, 0327 ),
		'n' 			=>  array ( 0156, 0156, 0156, 0156 ),
		'nine' 			=>  array ( 0071, 0071, 0071, 0071 ),
		'ntilde' 		=>  array (    0, 0226, 0361, 0361 ),
		'numbersign' 		=>  array ( 0043, 0043, 0043, 0043 ),
		'o'			=>  array ( 0157, 0157, 0157, 0157 ),
		'oacute' 		=>  array (    0, 0227, 0363, 0363 ),
		'ocircumflex' 		=>  array (    0, 0231, 0364, 0364 ),
		'odieresis'		=>  array (    0, 0232, 0366, 0366 ),
		'oe' 			=>  array ( 0372, 0317, 0234, 0234 ),
		'ogonek' 		=>  array ( 0316, 0376,    0, 0035 ),
		'ograve'		=>  array (    0, 0230, 0362, 0362 ),
		'one' 			=>  array ( 0061, 0061, 0061, 0061 ),
		'onehalf' 		=>  array (    0,    0, 0275, 0275 ),
		'onequarter' 		=>  array (    0,    0, 0274, 0274 ),
		'onesuperior'		=>  array (    0,    0, 0271, 0271 ),
		'ordfeminine' 		=>  array ( 0343, 0273, 0252, 0252 ),
		'ordmasculine' 		=>  array ( 0353, 0274, 0272, 0272 ),
		'oslash'		=>  array ( 0371, 0277, 0370, 0370 ),
		'otilde' 		=>  array (    0, 0233, 0365, 0365 ),
		'p'			=>  array ( 0160, 0160, 0160, 0160 ),
		'paragraph' 		=>  array ( 0266, 0246, 0266, 0266 ),
		'parenleft' 		=>  array ( 0050, 0050, 0050, 0050 ),
		'parenright'		=>  array ( 0051, 0051, 0051, 0051 ),
		'percent' 		=>  array ( 0045, 0045, 0045, 0045 ),
		'period' 		=>  array ( 0056, 0056, 0056, 0056 ),
		'periodcentered'	=>  array ( 0264, 0341, 0267, 0267 ),
		'perthousand' 		=>  array ( 0275, 0344, 0211, 0213 ),
		'plus' 			=>  array ( 0053, 0053, 0053, 0053 ),
		'plusminus' 		=>  array (    0, 0261, 0261, 0261 ),
		'q' 			=>  array ( 0161, 0161, 0161, 0161 ),
		'question' 		=>  array ( 0077, 0077, 0077, 0077 ),
		'questiondown' 		=>  array ( 0277, 0300, 0277, 0277 ),
		'quotedbl' 		=>  array ( 0042, 0042, 0042, 0042 ),
		'quotedblbase' 		=>  array ( 0271, 0343, 0204, 0214 ),
		'quotedblleft'		=>  array ( 0252, 0322, 0223, 0215 ),
		'quotedblright'		=>  array ( 0272, 0323, 0224, 0216 ),
		'quoteleft' 		=>  array ( 0140, 0324, 0221, 0217 ),
		'quoteright'		=>  array ( 0047, 0325, 0222, 0220 ),
		'quotesinglbase'	=>  array ( 0270, 0342, 0202, 0221 ),
		'quotesingle'		=>  array ( 0251, 0047, 0047, 0047 ),
		'r'			=>  array ( 0162, 0162, 0162, 0162 ),
		'registered' 		=>  array (    0, 0250, 0256, 0256 ),
		'ring' 			=>  array ( 0312, 0373,    0, 0036 ),
		's'			=>  array ( 0163, 0163, 0163, 0163 ),
		'scaron'		=>  array (    0,    0, 0232, 0235 ),
		'section'		=>  array ( 0247, 0244, 0247, 0247 ),
		'semicolon' 		=>  array ( 0073, 0073, 0073, 0073 ),
		'seven' 		=>  array ( 0067, 0067, 0067, 0067 ),
		'six' 			=>  array ( 0066, 0066, 0066, 0066 ),
		'slash' 		=>  array ( 0057, 0057, 0057, 0057 ),
		'space' 		=>  array ( 0040, 0040, 0040, 0040 ),
		'sterling'		=>  array ( 0243, 0243, 0243, 0243 ),
		't'			=>  array ( 0164, 0164, 0164, 0164 ),
		'thorn' 		=>  array (    0,    0, 0376, 0376 ),
		'three'			=>  array ( 0063, 0063, 0063, 0063 ),
		'threequarters'		=>  array (    0,    0, 0276, 0276 ),
		'threesuperior' 	=>  array (    0,    0, 0263, 0263 ),
		'tilde'			=>  array ( 0304, 0367, 0230, 0037 ),
		'trademark' 		=>  array (    0, 0252, 0231, 0222 ),
		'two' 			=>  array ( 0062, 0062, 0062, 0062 ),
		'twosuperior'		=>  array (    0,    0, 0262, 0262 ),
		'u' 			=>  array ( 0165, 0165, 0165, 0165 ),
		'uacute'		=>  array (    0, 0234, 0372, 0372 ),
		'ucircumflex' 		=>  array (    0, 0236, 0373, 0373 ),
		'udieresis'		=>  array (    0, 0237, 0374, 0374 ),
		'ugrave' 		=>  array (    0, 0235, 0371, 0371 ),
		'underscore' 		=>  array ( 0137, 0137, 0137, 0137 ),
		'v' 			=>  array ( 0166, 0166, 0166, 0166 ),
		'w' 			=>  array ( 0167, 0167, 0167, 0167 ),
		'x' 			=>  array ( 0170, 0170, 0170, 0170 ),
		'y' 			=>  array ( 0171, 0171, 0171, 0171 ),
		'yacute' 		=>  array (    0,    0, 0375, 0375 ),
		'ydieresis' 		=>  array (    0, 0330, 0377, 0377 ),
		'yen' 			=>  array ( 0245, 0264, 0245, 0245 ),
		'z'			=>  array ( 0172, 0172, 0172, 0172 ),
		'zcaron' 		=>  array (    0,    0, 0236, 0236 ),
		'zero' 			=>  array ( 0060, 0060, 0060, 0060 ),

		// Additions which are not described in the PDF specifications - much more foreign characters are available !
		// (see https://mupdf.com/docs/browse/source/pdf/pdf-glyphlist.h.html)
		// This table is currently far from being complete
		'Aogonek'		=>  array ( 0x104, 0x104, 0x104, 0x104 ),
		'aogonek'		=>  array ( 0x105, 0x105, 0x105, 0x105 ),
		'Cacute'		=>  array ( 0x106, 0x106, 0x106, 0x106 ),
		'cacute'		=>  array ( 0x107, 0x107, 0x107, 0x107 ),
		'Eogonek'		=>  array ( 0x118, 0x118, 0x118, 0x118 ),
		'eogonek'		=>  array ( 0x119, 0x119, 0x119, 0x119 ),
		'Iogonek'		=>  array ( 0x12E, 0x12E, 0x12E, 0x12E ),
		'iogonek'		=>  array ( 0x12F, 0x12F, 0x12F, 0x12F ),
		'Nacute'		=>  array ( 0x143, 0x143, 0x143, 0x143 ),
		'nacute'		=>  array ( 0x144, 0x144, 0x144, 0x144 ),
		'Odblacute'		=>  array ( 0x150, 0x150, 0x150, 0x150 ),
		'odblacute'		=>  array ( 0x151, 0x151, 0x151, 0x151 ),
		'Oogonek'		=>  array ( 0x1EA, 0x1EA, 0x1EA, 0x1EA ),
		'oogonek'		=>  array ( 0x1EB, 0x1EB, 0x1EB, 0x1EB ),
		'Sacute'		=>  array ( 0x15A, 0x15A, 0x15A, 0x15A ),
		'sacute'		=>  array ( 0x15B, 0x15B, 0x15B, 0x15B ),
		'Udblacute'		=>  array ( 0x170, 0x170, 0x170, 0x170 ),
		'udblacute'		=>  array ( 0x171, 0x171, 0x171, 0x171 ),
		'Uogonek'		=>  array ( 0x172, 0x172, 0x172, 0x172 ),
		'uogonek'		=>  array ( 0x173, 0x173, 0x173, 0x173 ),
		'Zacute'		=>  array ( 0x179, 0x179, 0x179, 0x179 ),
		'zacute'		=>  array ( 0x17A, 0x17A, 0x17A, 0x17A ),
		'Zdotaccent'		=>  array ( 0x17B, 0x17B, 0x17B, 0x17B ),
		'zdotaccent'		=>  array ( 0x17C, 0x17C, 0x17C, 0x17C ),

		// Don't know if these ones are official...
		'a0'			=>  array ( 0x2D, 0x2D, 0x2D, 0x2D ),			// A long hyphen
		'a1'			=>  array ( 0x2192, 0x2192, 0x2192, 0x2192 ),		// Right arrow
		'a2'			=>  array ( 0x2D, 0x2D, 0x2D, 0x2D ),			// Another hyphen, not so long
	   ) ;


