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
* requires PHP 5 and iconv (which is enabled by default under PHP5)
* It tries to find out that a string $AStr is in UTF8 or not.
*
**/

function isUTF8 ($AStr) {
	if (@iconv('UTF-8', 'UTF-8',$AStr) == $AStr) 
	{
		return true;
	}
	else
	{
		return false;
	}
}


function seems_not_utf8($AStr)
{
	if (!@iconv('UTF-8', 'UTF-8',$AStr) == $AStr) 
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