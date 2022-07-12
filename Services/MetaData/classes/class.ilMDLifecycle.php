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
 * Meta Data class (element annotation)
 * @author  Stefan Meyer <meyer@leifos.com>
 * @package ilias-core
 * @version $Id$
 */
class ilMDLifecycle extends ilMDBase
{
    private ?ilMDLanguageItem $version_language = null;
    private string $version = "";
    private string $status = "";

    /**
     * @return array<string, string>
     */
    public function getPossibleSubelements() : array
    {
        $subs['Contribute'] = 'meta_contribute';

        return $subs;
    }

    /**
     * @return int[]
     */
    public function getContributeIds() : array
    {
        return ilMDContribute::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_lifecycle');
    }

    public function getContribute(int $a_contribute_id) : ?ilMDContribute
    {
        if (!$a_contribute_id) {
            return null;
        }
        $con = new ilMDContribute();
        $con->setMetaId($a_contribute_id);

        return $con;
    }

    public function addContribute() : ilMDContribute
    {
        $con = new ilMDContribute($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $con->setParentId($this->getMetaId());
        $con->setParentType('meta_lifecycle');

        return $con;
    }

    // SET/GET
    public function setStatus(string $a_status) : void
    {
        switch ($a_status) {
            case 'Draft':
            case 'Final':
            case 'Revised':
            case 'Unavailable':
                $this->status = $a_status;
                break;
        }
    }

    public function getStatus() : string
    {
        return $this->status;
    }

    public function setVersion(string $a_version) : void
    {
        $this->version = $a_version;
    }

    public function getVersion() : string
    {
        return $this->version;
    }

    public function setVersionLanguage(ilMDLanguageItem $lng_obj) : void
    {
        $this->version_language = $lng_obj;
    }

    public function getVersionLanguage() : ilMDLanguageItem
    {
        return $this->version_language;
    }

    public function getVersionLanguageCode() : string
    {
        return is_object($this->version_language) ? $this->version_language->getLanguageCode() : '';
    }

    public function save() : int
    {
        $fields = $this->__getFields();
        $fields['meta_lifecycle_id'] = array('integer', $next_id = $this->db->nextId('il_meta_lifecycle'));

        if ($this->db->insert('il_meta_lifecycle', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update() : bool
    {
        return $this->getMetaId() && $this->db->update(
            'il_meta_lifecycle',
            $this->__getFields(),
            array("meta_lifecycle_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete() : bool
    {
        // Delete 'contribute'
        foreach ($this->getContributeIds() as $id) {
            $con = $this->getContribute($id);
            $con->delete();
        }

        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_lifecycle " .
                "WHERE meta_lifecycle_id = " . $this->db->quote($this->getMetaId(), 'integer');
            $res = $this->db->manipulate($query);
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
            'rbac_id' => array('integer', $this->getRBACId()),
            'obj_id' => array('integer', $this->getObjId()),
            'obj_type' => array('text', $this->getObjType()),
            'lifecycle_status' => array('text', $this->getStatus()),
            'meta_version' => array('text', $this->getVersion()),
            'version_language' => array('text', $this->getVersionLanguageCode())
        );
    }

    public function read() : bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_lifecycle " .
                "WHERE meta_lifecycle_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setStatus((string) $row->lifecycle_status);
                $this->setVersion((string) $row->meta_version);
                $this->setVersionLanguage(new ilMDLanguageItem($row->version_language));
            }
        }
        return true;
    }

    public function toXML(ilXmlWriter $writer) : void
    {
        $writer->xmlStartTag('Lifecycle', array(
            'Status' => $this->getStatus() ?: 'Draft'
        ));
        $writer->xmlElement(
            'Version',
            array(
                'Language' => $this->getVersionLanguageCode() ?: 'en'
            ),
            $this->getVersion()
        );

        // contribute
        $contributes = $this->getContributeIds();
        foreach ($contributes as $id) {
            $con = $this->getContribute($id);
            $con->toXML($writer);
        }
        if (!count($contributes)) {
            $con = new ilMDContribute($this->getRBACId(), $this->getObjId());
            $con->toXML($writer);
        }
        $writer->xmlEndTag('Lifecycle');
    }

    // STATIC
    public static function _getId(int $a_rbac_id, int $a_obj_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_lifecycle_id FROM il_meta_lifecycle " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->meta_lifecycle_id;
        }
        return 0;
    }
}
