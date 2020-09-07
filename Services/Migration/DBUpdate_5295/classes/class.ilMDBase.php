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
* Meta Data class
* always instantiate this class first to set/get single meta data elements
*
* @package ilias-core
* @version $Id$
*/

class ilMDBase
{
    /*
     * object id (NOT ref_id!) of rbac object (e.g for page objects the obj_id
     * of the content object; for media objects this is set to 0, because their
     * object id are not assigned to ref ids)
     */
    public $rbac_id;

    /*
     * obj_id (e.g for structure objects the obj_id of the structure object)
     */
    public $obj_id;

    /*
     * type of the object (e.g st,pg,crs ...)
     */
    public $obj_type;
    
    /*
     * export mode, if true, first Identifier will be
     * set to ILIAS/il_<INSTALL_ID>_<TYPE>_<ID>
     */
    public $export_mode = false;

    /**
     * @var ilLogger
     */
    protected $log;

    /*
     * constructor
     *
     * @param	$a_rbac_id	int		object id (NOT ref_id!) of rbac object (e.g for page objects
     *								the obj_id of the content object; for media objects this
     *								is set to 0, because their object id are not assigned to ref ids)
     * @param	$a_obj_id	int		object id (e.g for structure objects the obj_id of the structure object)
     * @param	$a_type		string	type of the object (e.g st,pg,crs ...)
     */
    public function __construct(
        $a_rbac_id = 0,
        $a_obj_id = 0,
        $a_type = 0
    ) {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($a_obj_id == 0) {
            $a_obj_id = $a_rbac_id;
        }

        $this->db = $ilDB;
        $this->log = ilLoggerFactory::getLogger("meta");

        $this->rbac_id = $a_rbac_id;
        $this->obj_id = $a_obj_id;
        $this->obj_type = $a_type;
    }

    // SET/GET
    public function setRBACId($a_id)
    {
        $this->rbac_id = $a_id;
    }
    public function getRBACId()
    {
        return $this->rbac_id;
    }
    public function setObjId($a_id)
    {
        $this->obj_id = $a_id;
    }
    public function getObjId()
    {
        return $this->obj_id;
    }
    public function setObjType($a_type)
    {
        $this->obj_type = $a_type;
    }
    public function getObjType()
    {
        return $this->obj_type;
    }
    public function setMetaId($a_meta_id, $a_read_data = true)
    {
        $this->meta_id = $a_meta_id;

        if ($a_read_data) {
            $this->read();
        }
    }
    public function getMetaId()
    {
        return $this->meta_id;
    }
    public function setParentType($a_parent_type)
    {
        $this->parent_type = $a_parent_type;
    }
    public function getParentType()
    {
        return $this->parent_type;
    }
    public function setParentId($a_id)
    {
        $this->parent_id = $a_id;
    }
    public function getParentId()
    {
        return $this->parent_id;
    }
    
    public function setExportMode($a_export_mode = true)
    {
        $this->export_mode = $a_export_mode;
    }
    
    public function getExportMode()
    {
        return $this->export_mode;
    }


    /*
     * Should be overwritten in all inherited classes
     *
     * @access public
     * @return bool
     */
    public function validate()
    {
        return false;
    }

    /*
     * Should be overwritten in all inherited classes
     *
     * @access public
     * @return bool
     */
    public function update()
    {
        return false;
    }

    /*
     * Should be overwritten in all inherited classes
     *
     * @access public
     * @return bool
     */
    public function save()
    {
        return false;
    }
    /*
     * Should be overwritten in all inherited classes
     *
     * @access public
     * @return bool
     */
    public function delete()
    {
    }

    /*
     * Should be overwritten in all inherited classes
     * XML Export of all meta data
     * @param object (xml writer) see class.ilMD2XML.php
     *
     */
    public function toXML(&$writer)
    {
    }
}
