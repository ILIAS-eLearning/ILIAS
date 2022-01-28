<?php declare(strict_types=1);
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
 * @package ilias-core
 * @version $Id$
 */
class ilMDTechnical extends ilMDBase
{

    private string $size = '';
    private string $installation_remarks = '';
    private ?ilMDLanguageItem $installation_remarks_language = null;
    private string $other_platform_requirements = '';
    private ?ilMDLanguageItem $other_platform_requirements_language = null;
    private string $duration = '';

    /**
     * @return array<string, string>
     */
    public function getPossibleSubelements() : array
    {
        $subs['Format']   = 'meta_format';
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

    /**
     * @return int[]
     */
    public function getFormatIds() : array
    {


        return ilMDFormat::_getIds($this->getRBACId(), $this->getObjId());
    }

    public function getFormat(int $a_format_id) : ?ilMDFormat
    {


        if (!$a_format_id) {
            return null;
        }
        $for = new ilMDFormat($this->getRBACId(), $a_format_id);
        $for->setMetaId($a_format_id);

        return $for;
    }

    public function addFormat() : ilMDFormat
    {


        $for = new ilMDFormat($this->getRBACId(), $this->getObjId(), $this->getObjType());

        return $for;
    }

    /**
     * @return int[]
     */
    public function getLocationIds() : array
    {


        return ilMDLocation::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_technical');
    }

    public function getLocation(int $a_location_id) : ?ilMDLocation
    {


        if (!$a_location_id) {
            return null;
        }
        $loc = new ilMDLocation();
        $loc->setMetaId($a_location_id);

        return $loc;
    }

    public function addLocation() : ilMDLocation
    {


        $loc = new ilMDLocation($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $loc->setParentId($this->getMetaId());
        $loc->setParentType('meta_technical');

        return $loc;
    }

    /**
     * @return int[]
     */
    public function getRequirementIds() : array
    {


        return ilMDRequirement::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_technical');
    }

    public function getRequirement(int $a_requirement_id) : ?ilMDRequirement
    {


        if (!$a_requirement_id) {
            return null;
        }
        $rec = new ilMDRequirement();
        $rec->setMetaId($a_requirement_id);

        return $rec;
    }

    public function addRequirement() : ilMDRequirement
    {


        $rec = new ilMDRequirement($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $rec->setParentId($this->getMetaId());
        $rec->setParentType('meta_technical');

        return $rec;
    }

    /**
     * @return int[]
     */
    public function getOrCompositeIds() : array
    {


        return ilMDOrComposite::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_technical');
    }

    public function getOrComposite(int $a_or_composite_id) : ?ilMDOrComposite
    {


        if (!$a_or_composite_id) {
            return null;
        }
        $orc = new ilMDOrComposite($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $orc->setOrCompositeId($a_or_composite_id);
        $orc->setParentId($this->getMetaId());
        $orc->setParentType('meta_technical');

        return $orc;
    }

    public function addOrComposite() : ilMDOrComposite
    {


        $orc = new ilMDOrComposite($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $orc->setParentId($this->getMetaId());
        $orc->setParentType('meta_technical');

        return $orc;
    }

    // SET/GET
    public function setSize(string $a_size) : void
    {
        $this->size = $a_size;
    }

    public function getSize() : string
    {
        return $this->size;
    }

    public function setInstallationRemarks(string $a_val) : void
    {
        $this->installation_remarks = $a_val;
    }

    public function getInstallationRemarks() : string
    {
        return $this->installation_remarks;
    }

    public function setInstallationRemarksLanguage(ilMDLanguageItem $lng_obj) : void
    {
        if (is_object($lng_obj)) {
            $this->installation_remarks_language = $lng_obj;
        }
    }

    public function getInstallationRemarksLanguage() : ?ilMDLanguageItem
    {
        return is_object($this->installation_remarks_language) ? $this->installation_remarks_language : null;
    }

    public function getInstallationRemarksLanguageCode() : string
    {
        return is_object($this->installation_remarks_language) ? $this->installation_remarks_language->getLanguageCode() : '';
    }

    public function setOtherPlatformRequirements(string $a_val) : void
    {
        $this->other_platform_requirements = $a_val;
    }

    public function getOtherPlatformRequirements() : string
    {
        return $this->other_platform_requirements;
    }

    public function setOtherPlatformRequirementsLanguage(ilMDLanguageItem $lng_obj) : void
    {
        if (is_object($lng_obj)) {
            $this->other_platform_requirements_language = &$lng_obj;
        }
    }

    public function getOtherPlatformRequirementsLanguage() : ?ilMDLanguageItem
    {
        return is_object($this->other_platform_requirements_language) ? $this->other_platform_requirements_language : null;
    }

    public function getOtherPlatformRequirementsLanguageCode() : string
    {
        return is_object($this->other_platform_requirements_language)
            ? $this->other_platform_requirements_language->getLanguageCode()
            : '';
    }

    public function setDuration(string $a_val) : void
    {
        $this->duration = $a_val;
    }

    public function getDuration() : string
    {
        return $this->duration;
    }

    public function save() : int
    {

        $fields                      = $this->__getFields();
        $fields['meta_technical_id'] = array('integer', $next_id = $this->db->nextId('il_meta_technical'));

        if ($this->db->insert('il_meta_technical', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update() : bool
    {

        if ($this->getMetaId()) {
            if ($this->db->update(
                'il_meta_technical',
                $this->__getFields(),
                array("meta_technical_id" => array('integer', $this->getMetaId()))
            )) {
                return true;
            }
        }
        return false;
    }

    public function delete() : bool
    {

        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_technical " .
                "WHERE meta_technical_id = " . $this->db->quote($this->getMetaId(), 'integer');
            $res   = $this->db->manipulate($query);

            foreach ($this->getFormatIds() as $id) {
                $for = $this->getFormat($id);
                $for->delete();
            }

            foreach ($this->getLocationIds() as $id) {
                $loc = $this->getLocation($id);
                $loc->delete();
            }
            foreach ($this->getRequirementIds() as $id) {
                $req = $this->getRequirement($id);
                $req->delete();
            }
            foreach ($this->getOrCompositeIds() as $id) {
                $orc = $this->getOrComposite($id);
                $orc->delete();
            }

            return true;
        }
        return false;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function __getFields() : array
    {
        return array(
            'rbac_id'      => array('integer', $this->getRBACId()),
            'obj_id'       => array('integer', $this->getObjId()),
            'obj_type'     => array('text', $this->getObjType()),
            't_size'       => array('text', $this->getSize()),
            'ir'           => array('text', $this->getInstallationRemarks()),
            'ir_language'  => array('text', $this->getInstallationRemarksLanguageCode()),
            'opr'          => array('text', $this->getOtherPlatformRequirements()),
            'opr_language' => array('text', $this->getOtherPlatformRequirementsLanguageCode()),
            'duration'     => array('text', $this->getDuration())
        );
    }

    public function read() : bool
    {


        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_technical " .
                "WHERE meta_technical_id = " . $this->db->quote($this->getMetaId(), 'integer') . " ";

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
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

    public function toXML(ilXmlWriter $writer) : void
    {
        $writer->xmlStartTag('Technical');

        // Format
        foreach ($this->getFormatIds() as $id) {
            $for = $this->getFormat($id);
            $for->toXML($writer);
        }

        // Size
        if (strlen($this->getSize())) {
            $writer->xmlElement('Size', null, $this->getSize());
        }

        // Location
        foreach ($this->getLocationIds() as $id) {
            $loc = $this->getLocation($id);
            $loc->toXML($writer);
        }

        // Requirement
        foreach ($this->getRequirementIds() as $id) {
            $req = $this->getRequirement($id);
            $req->toXML($writer);
        }

        // OrComposite
        foreach ($this->getOrCompositeIds() as $id) {
            $orc = $this->getOrComposite($id);
            $orc->toXML($writer);
        }

        // InstallationRemarks
        if (strlen($this->getInstallationRemarks())) {
            $writer->xmlElement(
                'InstallationRemarks',
                array(
                    'Language' => $this->getInstallationRemarksLanguageCode()
                        ? $this->getInstallationRemarksLanguageCode()
                        : 'en'
                ),
                $this->getInstallationRemarks()
            );
        }

        // OtherPlatformRequirements
        if (strlen($this->getOtherPlatformRequirements())) {
            $writer->xmlElement(
                'OtherPlatformRequirements',
                array(
                    'Language' => $this->getOtherPlatformRequirementsLanguageCode()
                        ? $this->getOtherPlatformRequirementsLanguageCode()
                        : 'en'
                ),
                $this->getOtherPlatformRequirements()
            );
        }
        // Duration
        if (strlen($this->getDuration())) {
            $writer->xmlElement('Duration', null, $this->getDuration());
        }

        $writer->xmlEndTag('Technical');
    }

    public static function _getId(int $a_rbac_id, int $a_obj_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_technical_id FROM il_meta_technical " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->meta_technical_id;
        }
        return 0;
    }
}
