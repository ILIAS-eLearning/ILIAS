<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* RFC 2426 vCard MIME Directory Profile 3.0 class
*
* @author Helmut Schottmüller <hschottm@tzi.de>
* @version $Id$
*
* @package core
*/

class ilvCard 
{
/**
* An array containing the vCard types
*
* @type	array
*/
	var $types;

/**
* The filename of the vCard used when saving the vCard
*
* @type	string
*/
	var $filename;
	
/**
* Identification types
*
* @type	string
*/
	var $N_family_name;
	var $N_given_name;
	var $N_additional_names;
	var $N_honorific_prefixes;
	var $N_honorific_suffixes;
	
/**
* Encode data with "b" type encoding according to RFC 2045
*
* Encode data with "b" type encoding according to RFC 2045
*
* @param	string $string Binary string to be encoded
* @return Encoded binary string
* @access	public
*/
	function encode($string) {
		return escape(quoted_printable_encode($string));
	}
	
/**
* Escapes a string according to RFC 2426
*
* Escapes a string according to RFC 2426
*
* @param	string $string String to be escaped
* @return Escaped string
* @access	public
*/
	function escape($string) {
		$string = preg_replace("/([^\\]{0,1});/","\\1\;", $string);
		$string = preg_replace("/([^\\]{0,1})\\/","\\1\\", $string);
		$string = preg_replace("/([^\\]{0,1}),/","\\1\,", $string);
		$string = preg_replace("/\n/","\n", $string);
		return $string;
	}
	
/**
* Creates a quoted printable encoded string according to RFC 2045
*
* Creates a quoted printable encoded string according to RFC 2045.
* The method was taken from the php documentation discussion forum
*
* @param	string $input A binary string to encode
* @param	integer $line_max The maximum number of characters per line for the result string
* @return Quoted printable encoded string
* @access	public
*/
	function quoted_printable_encode($input, $line_max = 76) {
		$hex = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
		$lines = preg_split("/(?:\r\n|\r|\n)/", $input);
		$eol = "\r\n";
		$linebreak = "=0D=0A";
		$escape = "=";
		$output = "";
	
		for ($j=0;$j<count($lines);$j++) {
			$line = $lines[$j];
			$linlen = strlen($line);
			$newline = "";
			for($i = 0; $i < $linlen; $i++) {
				$c = substr($line, $i, 1);
				$dec = ord($c);
				if ( ($dec == 32) && ($i == ($linlen - 1)) ) { // convert space at eol only
					$c = "=20"; 
				} elseif ( ($dec == 61) || ($dec < 32 ) || ($dec > 126) ) { // always encode "\t", which is *not* required
					$h2 = floor($dec/16); $h1 = floor($dec%16); 
					$c = $escape.$hex["$h2"].$hex["$h1"]; 
				}
				if ( (strlen($newline) + strlen($c)) >= $line_max ) { // CRLF is not counted
					$output .= $newline.$escape.$eol; // soft line break; " =\r\n" is okay
					$newline = "    ";
				}
				$newline .= $c;
			} // end of for
			$output .= $newline;
			if ($j<count($lines)-1) $output .= $linebreak;
		}
		return trim($output);
	}

// Identification Types
//
// These types are used in the vCard profile to capture information
// associated with the identification and naming of the person or
// resource associated with the vCard.
	
/**
* Sets the value for the vCard FN type.
*
* Sets the value for the vCard FN type to specify
* the formatted text corresponding to the name
* of the object the vCard represents.
*
* Type example:
*
* FN:Mr. John Q. Public\, Esq.
*
* @param	string $formatted_name The formatted text
* @access	public
*/
	function setFormattedName($formatted_name)
	{
		$this->types["FN"] = $this->escape($formatted_name);
	}

/**
* Sets the value for the vCard N type.
*
* Sets the value for the vCard N type to specify
* the components of the name of the object the vCard represents.
* The N type MUST be present in the vCard object.
*
* Type example:
*
* N:Public;John;Quinlan;Mr.;Esq.
* N:Stevenson;John;Philip,Paul;Dr.;Jr.,M.D.,A.C.P.
*
* Type special note: The structured type value corresponds, in
* sequence, to the Family Name, Given Name, Additional Names, Honorific
* Prefixes, and Honorific Suffixes. The text components are separated
* by the SEMI-COLON character (ASCII decimal 59). Individual text
* components can include multiple text values (e.g., multiple
* Additional Names) separated by the COMMA character (ASCII decimal
* 44). This type is based on the semantics of the X.520 individual name
* attributes. The property MUST be present in the vCard object.
*
*
* @param	string $family_name The family name
* @param	string $given_name The given name
* @param	string $additional_names Additional names
* @param	string $honorific_prefixes Honorific prefixes
* @param	string $honorific_suffixes Honorific suffixes
* @access	public
*/
	function setName($family_name, $given_name = "", $additional_names = "", $honorific_prefixes = "", $honorific_suffixes = "")
	{
		$familynames = explode(",", $family_name);
		foreach ($familynames as $index => $familyname)
		{
			$familynames[$index] = $this->escape($familyname);
		}
		$givennames = explode(",", $given_name);
		foreach ($givennames as $index => $givenname)
		{
			$givennames[$index] = $this->escape($givenname);
		}
		$addnames = explode(",", $additional_names);
		foreach ($addnames as $index => $addname)
		{
			$addnames[$index] = $this->escape($addname);
		}
		$prefixes = explode(",", $honorific_prefixes);
		foreach ($prefixes as $index => $prefix)
		{
			$prefixes[$index] = $this->escape($prefix);
		}
		$suffixes = explode(",", $honorific_suffixes);
		foreach ($suffixes as $index => $suffix)
		{
			$suffixes[$index] = $this->escape($suffix);
		}

		$this->types["N"] = 
			join(",", $familynames) .
			";" .
			join(",", $givennames) .
			";" . 
			join(",", $addnames) .
			";" .
			join(",", $prefixes) .
			";" .
			join(",", $suffixes);

		$this->filename = "$given_name" . "_" . "$family_name" . ".vcf";
		if (strcmp($this->types["FN"], "") == 0)
		{		
			$this->types["FN"] = $this->setFormattedName(trim("$honorific_prefixes $given_name $additional_names $family_name $honorific_suffixes"));
		}
	}

/**
* Sets the value for the vCard NICKNAME type.
*
* Sets the value for the vCard NICKNAME type to specify
* the text corresponding to the nickname of the object 
* the vCard represents.
*
* Type example:
*
* NICKNAME:Robbie
* NICKNAME:Jim,Jimmie
*
* Type special note: The nickname is the descriptive name given instead
* of or in addition to the one belonging to a person, place, or thing.
* It can also be used to specify a familiar form of a proper name
* specified by the FN or N types.
*
* @param	string $formatted_name The formatted text
* @access	public
*/
	function setNickname($nickname)
	{
		$nicknames = explode(",", $nickname);
		foreach ($nicknames as $index => $nick)
		{
			$nicknames[$index] = $this->escape($nick);
		}
		$this->types["NICKNAME"] = join(",", $nicknames);
	}
	
/**
* Sets the value for the vCard PHOTO type.
*
* Sets the value for the vCard PHOTO type to specify
* an image or photograph information that annotates 
* some aspect of the object the vCard represents.
*
* Type example:
*
* PHOTO;VALUE=uri:http://www.abc.com/pub/photos
*   /jqpublic.gif
*
* PHOTO;ENCODING=b;TYPE=JPEG:MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvcN
*   AQEEBQAwdzELMAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENvbW11bm
*   ljYXRpb25zIENvcnBvcmF0aW9uMRwwGgYDVQQLExNJbmZvcm1hdGlvbiBTeXN0
* <...remainder of "B" encoded binary data...>
*
* Type encoding: The encoding MUST be reset to "b" using the ENCODING
* parameter in order to specify inline, encoded binary data. If the
* value is referenced by a URI value, then the default encoding of 8bit
* is used and no explicit ENCODING parameter is needed.
* 
* Type value: A single value. The default is binary value. It can also
* be reset to uri value. The uri value can be used to specify a value
* outside of this MIME entity.
* 
* Type special notes: The type can include the type parameter "TYPE" to
* specify the graphic image format type. The TYPE parameter values MUST
* be one of the IANA registered image formats or a non-standard image
* format.
*
* @param	string $photo A binary string containing the photo or an uri
* @param	string $type The IANA type of the image format
* @access	public
*/
	function setPhoto($photo, $type = "")
	{
		$value = "";
		$encoding = "";
		if (preg_match("/^http/", $photo))
		{
			$value = $this->encode($photo);
		}
		else
		{
			$encoding = "BASE64";
			$photo = base64_encode($photo);
		}
		$this->types["PHOTO"] = array(
			"VALUE" => $value,
			"TYPE" => $type,
			"ENCODING" => $encoding,
			"PHOTO" => $photo
		);
	}

/**
* Sets the value for the vCard BDAY type.
*
* Sets the value for the vCard BDAY type to specify
* the birth date of the object the vCard represents.
*
* Type example:
*
* BDAY:1996-04-15
* BDAY:1953-10-15T23:10:00Z
* BDAY:1987-09-27T08:30:00-06:00
*
* Type value: The default is a single date value.
* It can also be reset to a single date-time value.
*
* @param	integer $year The year of the birthday
* @param	integer $month The month of the birthday
* @param	integer $day The day of the birthday
* @access	public
*/
	function setBirthday($year, $month, $day)
	{
		if (($year < 1) or ($day < 1) or ($month < 1))
		{
			$this->types["BDAY"] = "";
		}
		else
		{
			$this->types["BDAY"] = sprintf("%04d%02d%02d", $year, $month, $day);
		}
	}
	
}

?>