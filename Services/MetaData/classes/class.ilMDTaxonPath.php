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
* Meta Data class (element taxon_path)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDTaxonPath extends ilMDBase
{
    // METHODS OF CHILD OBJECTS (Taxon)
    public function &getTaxonIds()
    {
        include_once 'Services/MetaData/classes/class.ilMDTaxon.php';

        return ilMDTaxon::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_taxon_path');
    }
    public function &getTaxon($a_taxon_id)
    {
        include_once 'Services/MetaData/classes/class.ilMDTaxon.php';

        if (!$a_taxon_id) {
            return false;
        }
        $tax = new ilMDTaxon();
        $tax->setMetaId($a_taxon_id);

        return $tax;
    }
    public function &addTaxon()
    {
        include_once 'Services/MetaData/classes/class.ilMDTaxon.php';

        $tax = new ilMDTaxon($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $tax->setParentId($this->getMetaId());
        $tax->setParentType('meta_taxon_path');

        return $tax;
    }

    // SET/GET
    public function setSource($a_source)
    {
        $this->source = $a_source;
    }
    public function getSource()
    {
        return $this->source;
    }
    public function setSourceLanguage(&$lng_obj)
    {
        if (is_object($lng_obj)) {
            $this->source_language = $lng_obj;
        }
    }
    public function &getSourceLanguage()
    {
        return is_object($this->source_language) ? $this->source_language : false;
    }
    public function getSourceLanguageCode()
    {
        return is_object($this->source_language) ? $this->source_language->getLanguageCode() : false;
    }


    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $fields = $this->__getFields();
        $fields['meta_taxon_path_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_taxon_path'));
        
        if ($this->db->insert('il_meta_taxon_path', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return false;
    }

    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->getMetaId()) {
            if ($this->db->update(
                'il_meta_taxon_path',
                $this->__getFields(),
                array("meta_taxon_path_id" => array('integer',$this->getMetaId()))
            )) {
                return true;
            }
        }
        return false;
    }

    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_taxon_path " .
                "WHERE meta_taxon_path_id = " . $ilDB->quote($this->getMetaId(), 'integer');
            $res = $ilDB->manipulate($query);

            foreach ($this->getTaxonIds() as $id) {
                $tax = $this->getTaxon($id);
                $tax->delete();
            }
            
            return true;
        }
        return false;
    }
            

    public function __getFields()
    {
        return array('rbac_id'	=> array('integer',$this->getRBACId()),
                     'obj_id'	=> array('integer',$this->getObjId()),
                     'obj_type'	=> array('text',$this->getObjType()),
                     'parent_type' => array('text',$this->getParentType()),
                     'parent_id' => array('integer',$this->getParentId()),
                     'source'	=> array('text',$this->getSource()),
                     'source_language' => array('text',$this->getSourceLanguageCode()));
    }

    public function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_taxon_path " .
                "WHERE meta_taxon_path_id = " . $ilDB->quote($this->getMetaId(), 'integer');

            $res = $ilDB->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId($row->rbac_id);
                $this->setObjId($row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setParentId($row->parent_id);
                $this->setParentType($row->parent_type);
                $this->setSource($row->source);
                $this->source_language = new ilMDLanguageItem($row->source_language);
            }
        }
        return true;
    }

    /*
     * XML Export of all meta data
     * @param object (xml writer) see class.ilMD2XML.php
     *
     */
    public function toXML(&$writer)
    {
        $writer->xmlStartTag('TaxonPath');

        $writer->xmlElement(
            'Source',
            array('Language' => $this->getSourceLanguageCode()
                                           ? $this->getSourceLanguageCode()
                                           : 'en'),
            $this->getSource()
        );

        // Taxon
        $taxs = $this->getTaxonIds();
        foreach ($taxs as $id) {
            $tax =&$this->getTaxon($id);
            $tax->toXML($writer);
        }
        if (!count($taxs)) {
            include_once 'Services/MetaData/classes/class.ilMDTaxon.php';
            $tax = new ilMDTaxon($this->getRBACId(), $this->getObjId());
            $tax->toXML($writer);
        }
        
        $writer->xmlEndTag('TaxonPath');
    }

                

    // STATIC
    public static function _getIds($a_rbac_id, $a_obj_id, $a_parent_id, $a_parent_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT meta_taxon_path_id FROM il_meta_taxon_path " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id, 'integer') . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type, 'text');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = $row->meta_taxon_path_id;
        }
        return $ids ? $ids : array();
    }
}
