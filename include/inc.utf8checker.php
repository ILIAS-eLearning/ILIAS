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
* UTF8 Checker
*
* @package ilias-develop
*
* There is no way in the standard PHP 4.x installation to check wether a file is in UTF8 or not.
* This code is grabbed from the comments at http://www.phpbuilder.com/lists/php-i18n/2003051/0020.php
* posted by Cestmir Hybl.
* It tries to find out that a string $AStr is in UTF8 or not.
*
**/

function isUTF8 ($aStr) {
	$field = "/^(";
	$field .="[\x09|\x0A|\x0D]|[\x20-\x7E]|";
	$field .="[\xC2-\xDF][\x80-\xBF]|";
	$field .="\xE0[\xA0-\xBF][\x80-\xBF]|";
	$field .="[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|";
	$field .="\xED[\x80-\x9F][\x80-\xBF]|";
	$field .="\xF0[\x90-\xBF][\x80-\xBF]{2}|";
	$field .="[\xF1-\xF3][\x80-\xBF]{3}|";
	$field .="\xF4[\x80-\x8F][\x80-\xBF]{2}";
	$field .=")*$/s";
	return preg_match($field, $AStr);
}

function seems_not_utf8($AStr)
{

	$ptrASCII = '[\x00-\x7F]';
	$ptr2Octet = '[\xC2-\xDF][\x80-\xBF]';
	$ptr3Octet = '[\xE0-\xEF][\x80-\xBF]{2}';
	$ptr4Octet = '[\xF0-\xF4][\x80-\xBF]{3}';
	$ptr5Octet = '[\xF8-\xFB][\x80-\xBF]{4}';
	$ptr6Octet = '[\xFC-\xFD][\x80-\xBF]{5}';

	if (preg_match("/^($ptrASCII|$ptr2Octet|$ptr3Octet|$ptr4Octet|$ptr5Octet|$ptr6Octet)*$/s", $AStr))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function getUTF8String($aStr) {
	if (!isUTF8($aStr)) {	
		
		return utf8_encode($aStr);
		
	}
	return $aStr;
}

?>