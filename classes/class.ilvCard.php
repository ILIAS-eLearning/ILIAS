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

/**
* Address values for the ADR type
*
* @type	integer
*/
define ("ADR_TYPE_NONE",   0);
define ("ADR_TYPE_DOM",    1);
define ("ADR_TYPE_INTL",   2);
define ("ADR_TYPE_POSTAL", 4);
define ("ADR_TYPE_PARCEL", 8);
define ("ADR_TYPE_HOME",  16);
define ("ADR_TYPE_WORK",  32);
define ("ADR_TYPE_PREF",  64);

/**
* Communication values for the TEL type
*
* @type	integer
*/
define ("TEL_TYPE_NONE",     0);
define ("TEL_TYPE_HOME",     1);
define ("TEL_TYPE_MSG",      2);
define ("TEL_TYPE_WORK",     4);
define ("TEL_TYPE_PREF",     8);
define ("TEL_TYPE_VOICE",   16);
define ("TEL_TYPE_FAX",     32);
define ("TEL_TYPE_CELL",    64);
define ("TEL_TYPE_VIDEO",  128);
define ("TEL_TYPE_PAGER",  256);
define ("TEL_TYPE_BBS",    512);
define ("TEL_TYPE_MODEM", 1024);
define ("TEL_TYPE_CAR",   2048);
define ("TEL_TYPE_ISDN",  4096);
define ("TEL_TYPE_PCS",   8192);

/**
* Communication values for the EMAIL type
*
* @type	integer
*/
define ("EMAIL_TYPE_NONE",     0);
define ("EMAIL_TYPE_INTERNET", 1);
define ("EMAIL_TYPE_x400",     2);
define ("EMAIL_TYPE_PREF",     4);

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

// Delivery Addressing Types
//
// These types are concerned with information related to the delivery
// addressing or label for the vCard object.

/**
* Sets the value for the vCard ADR type.
*
* Sets the value for the vCard ADR type to specify
* the components of the delivery address for the vCard object.
*
* Type example:
*
* ADR;TYPE=dom,home,postal,parcel:;;123 Main
*   Street;Any Town;CA;91921-1234
*
* Type special notes: The structured type value consists of a sequence
* of address components. The component values MUST be specified in
* their corresponding position. The structured type value corresponds,
* in sequence, to the post office box; the extended address; the street
* address; the locality (e.g., city); the region (e.g., state or
* province); the postal code; the country name. When a component value
* is missing, the associated component separator MUST still be
* specified.
* 
* The text components are separated by the SEMI-COLON character (ASCII
* decimal 59). Where it makes semantic sense, individual text
* components can include multiple text values (e.g., a "street"
* component with multiple lines) separated by the COMMA character
* (ASCII decimal 44).
* 
* The type can include the type parameter "TYPE" to specify the
* delivery address type. The TYPE parameter values can include "dom" to
* indicate a domestic delivery address; "intl" to indicate an
* international delivery address; "postal" to indicate a postal
* delivery address; "parcel" to indicate a parcel delivery address;
* "home" to indicate a delivery address for a residence; "work" to
* indicate delivery address for a place of work; and "pref" to indicate
* the preferred delivery address when more than one address is
* specified. These type parameter values can be specified as a
* parameter list (i.e., "TYPE=dom;TYPE=postal") or as a value list
* (i.e., "TYPE=dom,postal"). This type is based on semantics of the
* X.520 geographical and postal addressing attributes. The default is
* "TYPE=intl,postal,parcel,work". The default can be overridden to some
* other set of values by specifying one or more alternate values. For
* example, the default can be reset to "TYPE=dom,postal,work,home" to
* specify a domestic delivery address for postal delivery to a
* residence that is also used for work.
*
* @param string $po_box Post office box
* @param string $extended_address Extended address
* @param string $street_address Street address
* @param string $locality Locality (e.g. city)
* @param string $region Region (e.g. state or province)
* @param string $postal_code Postal code
* @param string $country Country
* @param	integer $type The address type (can be combined with the + operator)
* @access	public
*/
	function setAddress(
		$po_box = "",
		$extended_address = "",
		$street_address = "",
		$locality = "",
		$region = "",
		$postal_code = "",
		$country = "",
		$type = ADR_TYPE_INTL + ADR_TYPE_POSTAL+ ADR_TYPE_PARCEL + ADR_TYPE_WORK
	)
	{
	}
	
/**
* Sets the value for the vCard LABEL type.
*
* Sets the value for the vCard LABEL type to specify
* the formatted text corresponding to delivery
* address of the object the vCard represents
*
* Type example: A multi-line address label.
*
* LABEL;TYPE=dom,home,postal,parcel:Mr.John Q. Public\, Esq.\n
*   Mail Drop: TNE QB\n123 Main Street\nAny Town\, CA  91921-1234
*   \nU.S.A.
*
* Type special notes: The type value is formatted text that can be used
* to present a delivery address label for the vCard object. The type
* can include the type parameter "TYPE" to specify delivery label type.
* The TYPE parameter values can include "dom" to indicate a domestic
* delivery label; "intl" to indicate an international delivery label;
* "postal" to indicate a postal delivery label; "parcel" to indicate a
* parcel delivery label; "home" to indicate a delivery label for a
* residence; "work" to indicate delivery label for a place of work; and
* "pref" to indicate the preferred delivery label when more than one
* label is specified. These type parameter values can be specified as a
* parameter list (i.e., "TYPE=dom;TYPE=postal") or as a value list
* (i.e., "TYPE=dom,postal"). This type is based on semantics of the
* X.520 geographical and postal addressing attributes. The default is
* "TYPE=intl,postal,parcel,work". The default can be overridden to some
* other set of values by specifying one or more alternate values. For
* example, the default can be reset to "TYPE=intl,post,parcel,home" to
* specify an international delivery label for both postal and parcel
* delivery to a residential location.
*
* @param string $label The address label
* @param	integer $type The address type (can be combined with the + operator)
* @access	public
*/
	function setLabel($label = "", $type = ADR_TYPE_INTL + ADR_TYPE_POSTAL+ ADR_TYPE_PARCEL + ADR_TYPE_WORK)
	{
	}

// Telecommunications Addressing Types
//
// These types are concerned with information associated with the
// telecommunications addressing of the object the vCard represents.
	
/**
* Sets the value for the vCard TEL type.
*
* Sets the value for the vCard TEL type to specify
* the telephone number for telephony communication 
* with the object the vCard represents.
*
* Type example:
*
* TEL;TYPE=work,voice,pref,msg:+1-213-555-1234
*
* Type special notes: The value of this type is specified in a
* canonical form in order to specify an unambiguous representation of
* the globally unique telephone endpoint. This type is based on the
* X.500 Telephone Number attribute.
* 
* The type can include the type parameter "TYPE" to specify intended
* use for the telephone number. The TYPE parameter values can include:
* "home" to indicate a telephone number associated with a residence,
* "msg" to indicate the telephone number has voice messaging support,
* "work" to indicate a telephone number associated with a place of
* vwork, "pref" to indicate a preferred-use telephone number, "voice" to
* indicate a voice telephone number, "fax" to indicate a facsimile
* telephone number, "cell" to indicate a cellular telephone number,
* "video" to indicate a video conferencing telephone number, "pager" to
* indicate a paging device telephone number, "bbs" to indicate a
* bulletin board system telephone number, "modem" to indicate a MODEM
* connected telephone number, "car" to indicate a car-phone telephone
* number, "isdn" to indicate an ISDN service telephone number, "pcs" to
* indicate a personal communication services telephone number. The
* default type is "voice". These type parameter values can be specified
* as a parameter list (i.e., "TYPE=work;TYPE=voice") or as a value list
* (i.e., "TYPE=work,voice"). The default can be overridden to another
* set of values by specifying one or more alternate values. For
* example, the default TYPE of "voice" can be reset to a WORK and HOME,
* VOICE and FAX telephone number by the value list
* "TYPE=work,home,voice,fax".
*
* @param string $number The phone number
* @param	integer $type The address type (can be combined with the + operator)
* @access	public
*/
	function setPhone($number = "", $type = TEL_TYPE_VOICE)
	{
	}
	
/**
* Sets the value for the vCard EMAIL type.
*
* Sets the value for the vCard EMAIL type to specify
* the electronic mail address for communication with 
* the object the vCard represents.
*
* Type example:
*
* EMAIL;TYPE=internet:jqpublic@xyz.dom1.com
* EMAIL;TYPE=internet:jdoe@isp.net
* EMAIL;TYPE=internet,pref:jane_doe@abc.com
*
* Type special notes: The type can include the type parameter "TYPE" to
* specify the format or preference of the electronic mail address. The
* TYPE parameter values can include: "internet" to indicate an Internet
* addressing type, "x400" to indicate a X.400 addressing type or "pref"
* to indicate a preferred-use email address when more than one is
* specified. Another IANA registered address type can also be
* specified. The default email type is "internet". A non-standard value
* can also be specified.
*
* @param string $address The email address
* @param	integer $type The address type (can be combined with the + operator)
* @access	public
*/
	function setEmail($address = "", $type = EMAIL_TYPE_INTERNET)
	{
	}
	
/**
* Sets the value for the vCard MAILER type.
*
* Sets the value for the vCard MAILER type to specify
* the type of electronic mail software that is used by 
* the individual associated with the vCard.
*
* Type example:
*
* MAILER:PigeonMail 2.1
*
* Type special notes: This information can provide assistance to a
* correspondent regarding the type of data representation which can be
* used, and how they can be packaged. This property is based on the
* private MIME type X-Mailer that is generally implemented by MIME user
* agent products.
*
* @param string $name The mailer name
* @access	public
*/
	function setMailer($name = "")
	{
	}
	
// Geographical Types
//
// These types are concerned with information associated with
// geographical positions or regions associated with the object the
// vCard represents.

/**
* Sets the value for the vCard TZ type.
*
* Sets the value for the vCard TZ type to specify
* information related to the time zone of the
* object the vCard represents.
*
* Type example:
*
* TZ:-05:00
* TZ;VALUE=text:-05:00; EST; Raleigh/North America
*
* Type special notes: The type value consists of a single value.
*
* @param string $zone The timezone as utc-offset value
* @access	public
*/
	function setTimezone($zone = "")
	{
	}
	
/**
* Sets the value for the vCard GEO type.
*
* Sets the value for the vCard GEO type to specify
* information related to the global positioning of 
* the object the vCard represents.
*
* Type example:
*
* GEO:37.386013;-122.082932
*
* Type value: A single structured value consisting of two float values
* separated by the SEMI-COLON character (ASCII decimal 59).
* 
* Type special notes: This type specifies information related to the
* global position of the object associated with the vCard. The value
* specifies latitude and longitude, in that order (i.e., "LAT LON"
* ordering). The longitude represents the location east and west of the
* prime meridian as a positive or negative real number, respectively.
* The latitude represents the location north and south of the equator
* as a positive or negative real number, respectively. The longitude
* and latitude values MUST be specified as decimal degrees and should
* be specified to six decimal places. This will allow for granularity
* within a meter of the geographical position. The text components are
* separated by the SEMI-COLON character (ASCII decimal 59). The simple
* formula for converting degrees-minutes-seconds into decimal degrees
* is:
* 
* 		decimal = degrees + minutes/60 + seconds/3600.
*
* @param double $latitude The latitude of the position
* @param double $longitude The longitude of the position
* @access	public
*/
	function setPosition($latitude = "", $longitude = "")
	{
	}
	
// Organizational Types
//
// These types are concerned with information associated with
// characteristics of the organization or organizational units of the
// object the vCard represents.

/**
* Sets the value for the vCard TITLE type.
*
* Sets the value for the vCard TITLE type to specify
* the job title, functional position or function of 
* the object the vCard represents.
*
* Type example:
*
* TITLE:Director\, Research and Development
*
* Type special notes: This type is based on the X.520 Title attribute.
*
* @param string $title Job title
* @access	public
*/
	function setTitle($title = "")
	{
	}
	
/**
* Sets the value for the vCard ROLE type.
*
* Sets the value for the vCard ROLE type to specify
* information concerning the role, occupation, or business 
* category of the object the vCard represents.
*
* Type example:
*
* ROLE:Programmer
*
* Type special notes: This type is based on the X.520 Business Category
* explanatory attribute. This property is included as an organizational
* type to avoid confusion with the semantics of the TITLE type and
* incorrect usage of that type when the semantics of this type is
* intended.
*
* @param string $role Role title
* @access	public
*/
	function setRole($role = "")
	{
	}
	
/**
* Sets the value for the vCard LOGO type.
*
* Sets the value for the vCard LOGO type to specify
* a graphic image of a logo associated with the object 
* the vCard represents.
*
* Type example:
*
* LOGO;VALUE=uri:http://www.abc.com/pub/logos/abccorp.jpg
* LOGO;ENCODING=b;TYPE=JPEG:MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvcN
*   AQEEBQAwdzELMAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENvbW11bm
*   ljYXRpb25zIENvcnBvcmF0aW9uMRwwGgYDVQQLExNJbmZvcm1hdGlvbiBTeXN0
*   <...the remainder of "B" encoded binary data...>
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
* @param	string $logo A binary string containing the logo or an uri
* @param	string $type The IANA type of the image format
* @access	public
*/
	function setLogo($logo, $type = "")
		$value = "";
		$encoding = "";
		if (preg_match("/^http/", $logo))
		{
			$value = $this->encode($logo);
		}
		else
		{
			$encoding = "BASE64";
			$logo = base64_encode($logo);
		}
		$this->types["LOGO"] = array(
			"VALUE" => $value,
			"TYPE" => $type,
			"ENCODING" => $encoding,
			"LOGO" => $logo
		);
	}
	
/**
* Sets the value for the vCard AGENT type.
*
* Sets the value for the vCard AGENT type to specify
* information about another person who will act on behalf 
* of the individual or resource associated with the vCard.
*
* Type example:
*
* AGENT;VALUE=uri:
*   CID:JQPUBLIC.part3.960129T083020.xyzMail@host3.com
* AGENT:BEGIN:VCARD\nFN:Susan Thomas\nTEL:+1-919-555-
*   1234\nEMAIL\;INTERNET:sthomas@host.com\nEND:VCARD\n
*
* Type value: The default is a single vcard value. It can also be reset
* to either a single text or uri value. The text value can be used to
* specify textual information. The uri value can be used to specify
* information outside of this MIME entity.
* 
* Type special notes: This type typically is used to specify an area
* administrator, assistant, or secretary for the individual associated
* with the vCard. A key characteristic of the Agent type is that it
* represents somebody or something that is separately addressable.
*
* @param string $agent Agent type
* @access	public
*/
	function setAgent($agent = "")
	{
	}
	
/**
* Sets the value for the vCard ORG type.
*
* Sets the value for the vCard ORG type to specify
* the organizational name and units associated with the vCard.
*
* Type example:
*
* ORG:ABC\, Inc.;North American Division;Marketing
*
* Type value: A single structured text value consisting of components
* separated the SEMI-COLON character (ASCII decimal 59).
* 
* Type special notes: The type is based on the X.520 Organization Name
* and Organization Unit attributes. The type value is a structured type
* consisting of the organization name, followed by one or more levels
* of organizational unit names.
*
* @param string $organization Organization description
* @access	public
*/
	function setOrganization($organization = "")
	{
	}
	
// Explanatory Types
//
// These types are concerned with additional explanations, such as that
// related to informational notes or revisions specific to the vCard.

/**
* Sets the value for the vCard CATEGORIES type.
*
* Sets the value for the vCard CATEGORIES type to specify
* application category information about the vCard.
*
* Type example:
*
* CATEGORIES:TRAVEL AGENT
* CATEGORIES:INTERNET,IETF,INDUSTRY,INFORMATION TECHNOLOGY
*
* Type value: One or more text values separated by a COMMA character
* (ASCII decimal 44).
*
* @param array $categories Category information
* @access	public
*/
	function setCategories($categories)
	{
	}
	
/**
* Sets the value for the vCard NOTE type.
*
* Sets the value for the vCard NOTE type to specify
* supplemental information or a comment that is associated with the vCard.
*
* Type example:
*
* NOTE:This fax number is operational 0800 to 1715
*   EST\, Mon-Fri.
*
* Type value: A single text value.
*
* Type special notes: The type is based on the X.520 Description
* attribute.
*
* @param string $note A note or comment
* @access	public
*/
	function setNote($note = "")
	{
	}
	
/**
* Sets the value for the vCard PRODID type.
*
* Sets the value for the vCard PRODID type to specify
* the identifier for the product that created the vCard object.
*
* Type example:
*
* PRODID:-//ONLINE DIRECTORY//NONSGML Version 1//EN
*
* Type value: A single text value.
* 
* Type special notes: Implementations SHOULD use a method such as that
* specified for Formal Public Identifiers in ISO 9070 to assure that
* the text value is unique.
*
* @param string $product_id Product identifier
* @access	public
*/
	function setProductId($product_id = "")
	{
	}
	
/**
* Sets the value for the vCard REV type.
*
* Sets the value for the vCard REV type to specify
* revision information about the current vCard.
*
* Type example:
*
* REV:1995-10-31T22:27:10Z
* REV:1997-11-15
*
* Type value: The default is a single date-time value. Can also be
* reset to a single date value.
* 
* Type special notes: The value distinguishes the current revision of
* the information in this vCard for other renditions of the
* information.
*
* @param string $revision_date Revision date
* @access	public
*/
	function setRevision($revision_date = "")
	{
	}
	
/**
* Sets the value for the vCard SORT-STRING type.
*
* Sets the value for the vCard SORT-STRING type to specify
* the family name or given name text to be used for 
* national-language-specific sorting of the FN and N types.
*
* Type examples: For the case of family name sorting, the following
* examples define common sort string usage with the FN and N types.
* 
*   FN:Rene van der Harten
*   N:van der Harten;Rene;J.;Sir;R.D.O.N.
*   SORT-STRING:Harten
* 
*   FN:Robert Pau Shou Chang
*   N:Pau;Shou Chang;Robert
*   SORT-STRING:Pau
* 
*   FN:Osamu Koura
*   N:Koura;Osamu
*   SORT-STRING:Koura
* 
*   FN:Oscar del Pozo
*   N:del Pozo Triscon;Oscar
*   SORT-STRING:Pozo
* 
*   FN:Chistine d'Aboville
*   N:d'Aboville;Christine
*   SORT-STRING:Aboville
*
* Type value: A single text value.
* 
* Type special notes: The sort string is used to provide family name or
* given name text that is to be used in locale- or national-language-
* specific sorting of the formatted name and structured name types.
* Without this information, sorting algorithms could incorrectly sort
* this vCard within a sequence of sorted vCards.  When this type is
* present in a vCard, then this family name or given name value is used
* for sorting the vCard.
*
* @param string $string Sort string
* @access	public
*/
	function setSortString($string = "")
	{
	}
	
/**
* Sets the value for the vCard SOUND type.
*
* Sets the value for the vCard SOUND type to specify
* a digital sound content information that annotates some 
* aspect of the vCard. By default this type is used to specify 
* the proper pronunciation of the name type value of the vCard.
*
* Type example:
*
* SOUND;TYPE=BASIC;VALUE=uri:CID:JOHNQPUBLIC.part8.
*   19960229T080000.xyzMail@host1.com
* 
* SOUND;TYPE=BASIC;ENCODING=b:MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvcN
*   AQEEBQAwdzELMAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENvbW11bm
*   ljYXRpb25zIENvcnBvcmF0aW9uMRwwGgYDVQQLExNJbmZvcm1hdGlvbiBTeXN0
*   <...the remainder of "B" encoded binary data...>
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
* specify the audio format type. The TYPE parameter values MUST be one
* of the IANA registered audio formats or a non-standard audio format.
*
* @param string $sound Binary string containing the sound
* @param string $type The IANA registered sound type
* @access	public
*/
	function setSound($sound = "", $type = "")
	{
	}
	
/**
* Sets the value for the vCard UID type.
*
* Sets the value for the vCard UID type to specify
* a value that represents a globally unique identifier 
* corresponding to the individual or resource associated
* with the vCard.
*
* Type example:
*
* UID:19950401-080045-40000F192713-0052
*
* Type value: A single text value.
* 
* Type special notes: The type is used to uniquely identify the object
* that the vCard represents.
* 
* The type can include the type parameter "TYPE" to specify the format
* of the identifier. The TYPE parameter value should be an IANA
* registered identifier format. The value can also be a non-standard
* format.
*
* @param string $uid Globally unique identifier
* @param string $type IANA registered identifier format
* @access	public
*/
	function setUID($uid = "", $type = "")
	{
	}
	
/**
* Sets the value for the vCard URL type.
*
* Sets the value for the vCard URL type to specify
* a uniform resource locator associated with the object that the 
* vCard refers to.
*
* Type example:
*
* URL:http://www.ilias.de/index.html
*
* Type value: A single text value.
*
* @param string $uri URL
* @access	public
*/
	function setURL($uri = "")
	{
	}
	
/**
* Sets the value for the vCard VERSION type.
*
* Sets the value for the vCard VERSION type to specify
* the version of the vCard specification used
*
* Type example:
*
* VERSION:3.0
*
* Type special notes: The property MUST be present in the vCard object.
* The value MUST be "3.0" if the vCard corresponds to the vCard 3.0 specification.
*
* @param string $version Version string
* @access	public
*/
	function setVersion($version = "3.0")
	{
	}
	
// Security Types
//
// These types are concerned with the security of communication pathways
// or access to the vCard.

/**
* Sets the value for the vCard CLASS type.
*
* Sets the value for the vCard CLASS type to specify
* the access classification for a vCard object.
*
* Type example:
*
* CLASS:PUBLIC
* CLASS:PRIVATE
* CLASS:CONFIDENTIAL
*
* Type value: A single text value.
* 
* Type special notes: An access classification is only one component of
* the general security model for a directory service. The
* classification attribute provides a method of capturing the intent of
* the owner for general access to information described by the vCard
* object.
*
* @param string $classification Classification string
* @access	public
*/
	function setClassification($classification = "")
	{
	}
	
/**
* Sets the value for the vCard KEY type.
*
* Sets the value for the vCard KEY type to specify
* a public key or authentication certificate associated 
* with the object that the vCard represents.
*
* Type example:
*
* KEY;ENCODING=b:MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvcNAQEEBQA
*   wdzELMAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENbW11bmljYX
*   Rpb25zIENvcnBvcmF0aW9uMRwwGgYDVQQLExNJbmZvcm1hdGlvbiBTeXN0
*   ZW1zMRwwGgYDVQQDExNyb290Y2EubmV0c2NhcGUuY29tMB4XDTk3MDYwNj
*   E5NDc1OVoXDTk3MTIwMzE5NDc1OVowgYkxCzAJBgNVBAYTAlVTMSYwJAYD
*   VQQKEx1OZXRzY2FwZSBDb21tdW5pY2F0aW9ucyBDb3JwLjEYMBYGA1UEAx
*   MPVGltb3RoeSBBIEhvd2VzMSEwHwYJKoZIhvcNAQkBFhJob3dlc0BuZXRz
*   Y2FwZS5jb20xFTATBgoJkiaJk/IsZAEBEwVob3dlczBcMA0GCSqGSIb3DQ
*   EBAQUAA0sAMEgCQQC0JZf6wkg8pLMXHHCUvMfL5H6zjSk4vTTXZpYyrdN2
*   dXcoX49LKiOmgeJSzoiFKHtLOIboyludF90CgqcxtwKnAgMBAAGjNjA0MB
*   EGCWCGSAGG+EIBAQQEAwIAoDAfBgNVHSMEGDAWgBT84FToB/GV3jr3mcau
*   +hUMbsQukjANBgkqhkiG9w0BAQQFAAOBgQBexv7o7mi3PLXadkmNP9LcIP
*   mx93HGp0Kgyx1jIVMyNgsemeAwBM+MSlhMfcpbTrONwNjZYW8vJDSoi//y
*   rZlVt9bJbs7MNYZVsyF1unsqaln4/vy6Uawfg8VUMk1U7jt8LYpo4YULU7
*   UZHPYVUaSgVttImOHZIKi4hlPXBOhcUQ==
*
* Type encoding: The encoding MUST be reset to "b" using the ENCODING
* parameter in order to specify inline, encoded binary data. If the
* value is a text value, then the default encoding of 8bit is used and
* no explicit ENCODING parameter is needed.
* 
* Type value: A single value. The default is binary. It can also be
* reset to text value. The text value can be used to specify a text
* key.
* 
* Type special notes: The type can also include the type parameter TYPE
* to specify the public key or authentication certificate format. The
* parameter type should specify an IANA registered public key or
* authentication certificate format. The parameter type can also
* specify a non-standard format.
*
* @param string $key Public key
* @param string $type IANA registered public key or authentication certificate format
* @access	public
*/
	function setKey($key = "", $type = "")
	{
	}
	
?>