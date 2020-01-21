<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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

require_once 'Services/Exceptions/classes/class.ilException.php';

/**
* Class for user related exception handling in ILIAS.
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
*/
class ilUserException extends ilException
{
    /**
    * Constructor
    *
    * A message is not optional as in build in class Exception
    *
    * @access public
    * @param	string	$a_message message
    *
    */
    public function __construct($a_message)
    {
        parent::__construct($a_message);
    }
}
