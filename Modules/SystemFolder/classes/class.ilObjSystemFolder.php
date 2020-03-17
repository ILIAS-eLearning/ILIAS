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
* Class ilObjSystemFolder
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @extends ilObject
*/

require_once "./Services/Object/classes/class.ilObject.php";

class ilObjSystemFolder extends ilObject
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id, $a_call_by_reference = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->type = "adm";
        parent::__construct($a_id, $a_call_by_reference);
    }


    /**
    * delete systemfolder and all related data
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

        // put here systemfolder specific stuff

        // always call parent delete function at the end!!
        return true;
    }

    /**
    * get all translations for header title
    *
    * @access	public
    * @return	array
    */
    public function getHeaderTitleTranslations()
    {
        /**
         * @var $ilDB ilDB
         */
        $ilDB = $this->db;

        $q = "SELECT * FROM object_translation WHERE obj_id = " .
            $ilDB->quote($this->getId(), 'integer') . " ORDER BY lang_default DESC";
        $r = $ilDB->query($q);

        $num = 0;

        while ($row = $ilDB->fetchObject($r)) {
            $data["Fobject"][$num] = array("title" => $row->title,
                                          "desc" => ilUtil::shortenText($row->description, ilObject::DESC_LENGTH, true),
                                          "lang" => $row->lang_code
                                          );
            $num++;
        }

        // first entry is always the default language
        $data["default_language"] = 0;

        return $data ? $data : array();
    }

    // remove all Translations of current category
    public function removeHeaderTitleTranslations()
    {
        $ilDB = $this->db;
        
        $query = "DELETE FROM object_translation WHERE obj_id= " .
            $ilDB->quote($this->getId(), 'integer');
        $res = $ilDB->manipulate($query);
    }

    // add a new translation to current category
    public function addHeaderTitleTranslation($a_title, $a_desc, $a_lang, $a_lang_default)
    {
        $ilDB = $this->db;
        
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

    public static function _getId()
    {
        /**
         * @var $ilDB ilDB
         */
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT obj_id FROM object_data WHERE type = " . $ilDB->quote('adm', 'text');
        $r = $ilDB->query($q);
        $row = $ilDB->fetchObject($r);

        return $row->obj_id;
    }

    public static function _getHeaderTitle()
    {
        /**
         * @var $ilDB ilDB
         * @var $ilUser ilObjUser
         */
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        $id = ilObjSystemFolder::_getId();

        $q = "SELECT title,description FROM object_translation " .
            "WHERE obj_id = " . $ilDB->quote($id, 'integer') . " " .
            "AND lang_default = 1";
        $r = $ilDB->query($q);
        $row = $ilDB->fetchObject($r);
        $title = $row->title;

        $q = "SELECT title,description FROM object_translation " .
            "WHERE obj_id = " . $ilDB->quote($id, 'integer') . " " .
            "AND lang_code = " .
            $ilDB->quote($ilUser->getCurrentLanguage(), 'text') . " " .
            "AND NOT lang_default = 1";
        $r = $ilDB->query($q);
        $row = $ilDB->fetchObject($r);

        if ($row) {
            $title = $row->title;
        }

        return $title;
    }

    public function _getHeaderTitleDescription()
    {
        $ilDB = $this->db;
        $ilUser = $this->user;
        
        $id = ilObjSystemFolder::_getId();

        $q = "SELECT title,description FROM object_translation " .
            "WHERE obj_id = " . $ilDB->quote($id, 'integer') . " " .
            "AND lang_default = 1";
        $r = $ilDB->query($q);
        $row = $ilDB->fetchObject($r);
        $description = $row->description;

        $q = "SELECT title,description FROM object_translation " .
            "WHERE obj_id = " . $ilDB->quote($id, 'integer') . " " .
            "AND lang_code = " .
            $ilDB->quote($ilUser->getPref("language"), 'text') . " " .
            "AND NOT lang_default = 1";
        $r = $ilDB->query($q);
        $row = $ilDB->fetchObject($r);

        if ($row) {
            $description = ilUtil::shortenText($row->description, ilObject::DESC_LENGTH, true);
        }

        return $description;
    }
} // END class.ilObjSystemFolder
