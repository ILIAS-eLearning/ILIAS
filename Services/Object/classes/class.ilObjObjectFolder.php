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
* Class ilObjObjectFolder
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @extends ilObject
*/

require_once "./Services/Object/classes/class.ilObject.php";

class ilObjObjectFolder extends ilObject
{
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id, $a_call_by_reference = true)
    {
        $this->type = "objf";
        parent::__construct($a_id, $a_call_by_reference);
    }


    /**
    * delete objectfolder and all related data
    * DISABLED
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        // DISABLED
        return false;

        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }
        
        // put here objectfolder specific stuff
        
        // always call parent delete function at the end!!
        return true;
    }
} // END class.ilObjObjectFolder
