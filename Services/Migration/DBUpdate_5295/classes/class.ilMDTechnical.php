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
* Meta Data class (element technical)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDTechnical extends ilMDBase
{
    public function getPossibleSubelements()
    {
        $subs['Format'] = 'meta_format';
        $subs['Location'] = 'meta_location';
        if (!$this->getOrCompositeIds()) {
            $subs['Requirement'] = 'meta_requirement';
        }
        if (!$this->getRequirementIds()) {
            $subs['OrComposite'] = 'meta_or_composite';
        }
            
        return $subs;
    }

    // Methods for child objects (Format, Location, Requirement OrComposite)
    public function &getFormatIds()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDFormat.php';

        return ilMDFormat::_getIds($this->getRBACId(), $this->getObjId());
    }
    public function &getFormat($a_format_id)
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDFormat.php';

        if (!$a_format_id) {
            return false;
        }
        $for = new ilMDFormat($this, $a_format_id);
        $for->setMetaId($a_format_id);

        return $for;
    }
    public function &addFormat()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDFormat.php';

        $for = new ilMDFormat($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $for;
    }
    public function &getLocationIds()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDLocation.php';

        return ilMDLocation::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_technical');
    }
    public function &getLocation($a_location_id)
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDLocation.php';

        if (!$a_location_id) {
            return false;
        }
        $loc = new ilMDLocation();
        $loc->setMetaId($a_location_id);

        return $loc;
    }
    public function &addLocation()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDLocation.php';

        $loc = new ilMDLocation($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $loc->setParentId($this->getMetaId());
        $loc->setParentType('meta_technical');

        return $loc;
    }
    public function &getRequirementIds()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDRequirement.php';

        return ilMDRequirement::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_technical');
    }
    public function &getRequirement($a_requirement_id)
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDRequirement.php';

        if (!$a_requirement_id) {
            return false;
        }
        $rec = new ilMDRequirement();
        $rec->setMetaId($a_requirement_id);
        
        return $rec;
    }
    public function &addRequirement()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDRequirement.php';

        $rec = new ilMDRequirement($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $rec->setParentId($this->getMetaId());
        $rec->setParentType('meta_technical');

        return $rec;
    }
    public function &getOrCompositeIds()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDOrComposite.php';

        return ilMDOrComposite::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_technical');
    }
    public function &getOrComposite($a_or_composite_id)
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDOrComposite.php';

        if (!$a_or_composite_id) {
            return false;
        }
        $orc = new ilMDOrComposite($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $orc->setOrCompositeId($a_or_composite_id);
        $orc->setParentId($this->getMetaId());
        $orc->setParentType('meta_technical');

        return $orc;
    }
    public function &addOrComposite()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDOrComposite.php';

        $orc = new ilMDOrComposite($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $orc->setParentId($this->getMetaId());
        $orc->setParentType('meta_technical');

        return $orc;
    }

    // SET/GET
    public function setSize($a_size)
    {
        $this->size = $a_size;
    }
    public function getSize()
    {
        return $this->size;
    }
    public function setInstallationRemarks($a_val)
    {
        $this->installation_remarks = $a_val;
    }
    public function getInstallationRemarks()
    {
        return $this->installation_remarks;
    }
    public function setInstallationRemarksLanguage(&$lng_obj)
    {
        if (is_object($lng_obj)) {
            $this->installation_remarks_language = &$lng_obj;
        }
    }
    public function &getInstallationRemarksLanguage()
    {
        return is_object($this->installation_remarks_language) ? $this->installation_remarks_language : false;
    }
    public function getInstallationRemarksLanguageCode()
    {
        return is_object($this->installation_remarks_language) ? $this->installation_remarks_language->getLanguageCode() : false;
    }
    public function setOtherPlatformRequirements($a_val)
    {
        $this->other_platform_requirements = $a_val;
    }
    public function getOtherPlatformRequirements()
    {
        return $this->other_platform_requirements;
    }
    public function setOtherPlatformRequirementsLanguage(&$lng_obj)
    {
        if (is_object($lng_obj)) {
            $this->other_platform_requirements_language = &$lng_obj;
        }
    }
    public function &getOtherPlatformRequirementsLanguage()
    {
        return is_object($this->other_platform_requirements_language) ? $this->other_platform_requirements_language : false;
    }
    public function getOtherPlatformRequirementsLanguageCode()
    {
        return is_object($this->other_platform_requirements_language)
            ? $this->other_platform_requirements_language->getLanguageCode()
            : false;
    }
    public function setDuration($a_val)
    {
        $this->duration = $a_val;
    }
    public function getDuration()
    {
        return $this->duration;
    }
    
    

    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $fields = $this->__getFields();
        $fields['meta_technical_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_technical'));
        
        if ($this->db->insert('il_meta_technical', $fields)) {
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
                'il_meta_technical',
                $this->__getFields(),
                array("meta_technical_id" => array('integer',$this->getMetaId()))
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
            $query = "DELETE FROM il_meta_technical " .
                "WHERE meta_technical_id = " . $ilDB->quote($this->getMetaId(), 'integer');
            $res = $ilDB->manipulate($query);
            
            foreach ($this->getFormatIds() as $id) {
                $for = &$this->getFormat($id);
                $for->delete();
            }

            foreach ($this->getLocationIds() as $id) {
                $loc = &$this->getLocation($id);
                $loc->delete();
            }
            foreach ($this->getRequirementIds() as $id) {
                $req = &$this->getRequirement($id);
                $req->delete();
            }
            foreach ($this->getOrCompositeIds() as $id) {
                $orc = &$this->getOrComposite($id);
                $orc->delete();
            }

            return true;
        }
        return false;
    }
            

    public function __getFields()
    {
        return array('rbac_id' => array('integer',$this->getRBACId()),
                     'obj_id' => array('integer',$this->getObjId()),
                     'obj_type' => array('text',$this->getObjType()),
                     't_size' => array('text',$this->getSize()),
                     'ir' => array('text',$this->getInstallationRemarks()),
                     'ir_language' => array('text',$this->getInstallationRemarksLanguageCode()),
                     'opr' => array('text',$this->getOtherPlatformRequirements()),
                     'opr_language' => array('text',$this->getOtherPlatformRequirementsLanguageCode()),
                     'duration' => array('text',$this->getDuration()));
    }

    public function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDLanguageItem.php';

        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_technical " .
                "WHERE meta_technical_id = " . $ilDB->quote($this->getMetaId(), 'integer') . " ";

            $res = $ilDB->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId($row->rbac_id);
                $this->setObjId($row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setSize($row->t_size);
                $this->setInstallationRemarks($row->ir);
                $this->setInstallationRemarksLanguage(new ilMDLanguageItem($row->ir_language));
                $this->setOtherPlatformRequirements($row->opr);
                $this->setOtherPlatformRequirementsLanguage(new ilMDLanguageItem($row->opr_language));
                $this->setDuration($row->duration);
            }
            return true;
        }
        return false;
    }
                
    /*
     * XML Export of all meta data
     * @param object (xml writer) see class.ilMD2XML.php
     *
     */
    public function toXML(&$writer)
    {
        $writer->xmlStartTag('Technical');

        // Format
        foreach ($this->getFormatIds() as $id) {
            $for = &$this->getFormat($id);
            $for->toXML($writer);
        }

        // Size
        if (strlen($this->getSize())) {
            $writer->xmlElement('Size', null, $this->getSize());
        }
        
        // Location
        foreach ($this->getLocationIds() as $id) {
            $loc = &$this->getLocation($id);
            $loc->toXML($writer);
        }

        // Requirement
        foreach ($this->getRequirementIds() as $id) {
            $req = &$this->getRequirement($id);
            $req->toXML($writer);
        }

        // OrComposite
        foreach ($this->getOrCompositeIds() as $id) {
            $orc = &$this->getOrComposite($id);
            $orc->toXML($writer);
        }
        
        // InstallationRemarks
        if (strlen($this->getInstallationRemarks())) {
            $writer->xmlElement(
                'InstallationRemarks',
                array('Language' => $this->getInstallationRemarksLanguageCode()
                                      ? $this->getInstallationRemarksLanguageCode()
                                      : 'en'),
                $this->getInstallationRemarks()
            );
        }

        // OtherPlatformRequirements
        if (strlen($this->getOtherPlatformRequirements())) {
            $writer->xmlElement(
                'OtherPlatformRequirements',
                array('Language' => $this->getOtherPlatformRequirementsLanguageCode()
                                      ? $this->getOtherPlatformRequirementsLanguageCode()
                                      : 'en'),
                $this->getOtherPlatformRequirements()
            );
        }
        // Duration
        if (strlen($this->getDuration())) {
            $writer->xmlElement('Duration', null, $this->getDuration());
        }
        
        $writer->xmlEndTag('Technical');
    }
    // STATIC
    public static function _getId($a_rbac_id, $a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT meta_technical_id FROM il_meta_technical " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->meta_technical_id;
        }
        return false;
    }
}
