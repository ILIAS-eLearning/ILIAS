<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
*
* Class to report exception
*
* @author Roland Küstermann <roland@kuestermann.com>
* @version $Id: class.ilExerciseException.php 12992 2007-01-25 10:04:26Z rkuester $
*
*
*
*/

include_once 'Services/Exceptions/classes/class.ilException.php';



class ilFileException extends ilException
{
    public static $ID_MISMATCH = 0;
    public static $ID_DEFLATE_METHOD_MISMATCH = 1;
    public static $DECOMPRESSION_FAILED = 2;
    /**
	 * A message isn't optional as in build in class Exception
	 *
	 * @access public
	 *
	 */
	public function __construct($a_message,$a_code = 0)
	{
	 	parent::__construct($a_message,$a_code);
	}
}

?>
