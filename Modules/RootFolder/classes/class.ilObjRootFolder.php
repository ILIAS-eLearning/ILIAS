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
* Class ilObjRootFolder
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$Id: class.ilObjRootFolder.php,v 1.12 2003/11/20 17:04:19 shofmann Exp $
*
* @extends ilObject
*/

require_once "./Services/Object/classes/class.ilObject.php";
require_once "./Services/Container/classes/class.ilContainer.php";

class ilObjRootFolder extends ilContainer
{
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id, $a_call_by_reference = true)
    {
        $this->type = "root";
        parent::__construct($a_id, $a_call_by_reference);
    }



    /**
    * delete rootfolder and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        // delete is disabled

        $message = get_class($this) . "::delete(): Can't delete root folder!";
        $this->ilias->raiseError($message, $this->ilias->error_obj->WARNING);
        return false;

        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // put here rootfolder specific stuff

        return true;
        ;
    }

    /**
    * get all translations from this category
    *
    * @access	public
    * @return	array
    */
    public function getTranslations()
    {
        global $ilDB;

        $q = "SELECT * FROM object_translation WHERE obj_id = " .
            $ilDB->quote($this->getId(), 'integer') . " ORDER BY lang_default DESC";
        $r = $this->ilias->db->query($q);

        $num = 0;

        $data["Fobject"] = array();
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $data["Fobject"][$num]= array("title"	=> $row->title,
                                          "desc"	=> $row->description,
                                          "lang"	=> $row->lang_code
                                          );
            $num++;
        }

        // first entry is always the default language
        $data["default_language"] = 0;

        return $data ? $data : array();
    }

    // remove translations of current category
    public function deleteTranslation($a_lang)
    {
        global $ilDB;

        $query = "DELETE FROM object_translation WHERE obj_id= " .
            $ilDB->quote($this->getId(), 'integer') . " AND lang_code = " .
            $ilDB->quote($a_lang, 'text');
        $res = $ilDB->manipulate($query);
    }

    // remove all Translations of current category
    public function removeTranslations()
    {
        global $ilDB;

        $query = "DELETE FROM object_translation WHERE obj_id= " .
            $ilDB->quote($this->getId(), 'integer');
        $res = $ilDB->manipulate($query);
    }

    // add a new translation to current category
    public function addTranslation($a_title, $a_desc, $a_lang, $a_lang_default)
    {
        global $ilDB;

        if (empty($a_title)) {
            $a_title = "NO TITLE";
        }

        $query = "INSERT INTO object_translation " .
             "(obj_id,title,description,lang_code,lang_default) " .
             "VALUES " .
             "(" . $ilDB->quote($this->getId(), 'integer') . "," .
             $ilDB->quote($a_title, 'text') . "," .
             $ilDB->quote($a_desc, 'text') . "," .
             $ilDB->quote($a_lang, 'text') . "," .
             $ilDB->quote($a_lang_default, 'integer') . ")";
        $res = $ilDB->manipulate($query);
        return true;
    }
    
    public function addAdditionalSubItemInformation(&$a_item_data)
    {
        include_once './Services/Object/classes/class.ilObjectActivation.php';
        ilObjectActivation::addAdditionalSubItemInformation($a_item_data);
    }
} // END class.ObjRootFolder
