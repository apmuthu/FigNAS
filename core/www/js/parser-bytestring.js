/*
	parser-bytestring.js

	Part of XigmaNAS (https://www.xigmanas.com).
	Copyright (c) 2018-2019 XigmaNAS <info@xigmanas.com>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice, this
	   list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright notice,
	   this list of conditions and the following disclaimer in the documentation
	   and/or other materials provided with the distribution.
 
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
	ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
	ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 
	The views and conclusions contained in the software and documentation are those
	of the authors and should not be interpreted as representing official policies
	of XigmaNAS, either expressed or implied.
 */
/*
 *	This tablesorter parser extracts and converts a formatted byte string into
 *	its numberic value (see function format_bytes). The format of the (case
 *	insensitive) byte string is defined in regex byte_string_regexp and must be
 *	
 *	- one or more digits
 *	- optional decimals character and decimals
 *	- the unit
 *	
 *	valid: 1MB, 2.48GiB, 512b
 *	invalid: 1M, 2,48GiB, 512, 120 kB
 *	
 */
( function( $ ) {
	var
		unit_calc = {
			yib : Math.pow( 1024 , 8 ),
			yb  : 1e24,
			zib : Math.pow( 1024 , 7 ),
			zb  : 1e21,
			eib : Math.pow( 1024 , 6 ),
			eb  : 1e18,
			pib : Math.pow( 1024 , 5 ),
			pb  : 1e15,
			tib : Math.pow( 1024 , 4 ),
			tb  : 1e12,
			gib : Math.pow( 1024 , 3 ),
			gb  : 1e9,
			mib : Math.pow( 1024 , 2 ),
			mb  : 1e6,
			kib : 1024,
			kb  : 1e3,
			b   : 1
		},
		byte_string_regexp = /^(\d+\.?\d*)(yib|yb|zib|zb|eib|eb|pib|pb|tib|tb|gib|gb|mib|mb|kib|kb|b)$/i ;
		
	$.tablesorter.addParser({
		id: 'bytestring',
		is: function() {
			return false ;
		},
		format: function( txt , table , cell , cell_index ) {
			var
				return_data = 0 ,
				test = txt.match( byte_string_regexp ) ,
				unit_key ;
			if ( test ) {
				if ( ( typeof test[ 1 ] !== 'undefined' ) && ( typeof test [ 2 ] !== 'undefined' ) ) {
					unit_key = test[ 2 ].toLowerCase() ;
					if ( unit_calc.hasOwnProperty( unit_key ) ) {
						return_data = unit_calc[ unit_key ] * test[ 1 ] ;
					}
				}
			}
			return return_data ;
		},
		type: 'numeric'
	});
})( jQuery ) ;
