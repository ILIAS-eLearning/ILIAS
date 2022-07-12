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
 * Meta Data class (element identifier)
 * @package ilias-core
 * @version $Id$
 */
class ilMDIdentifier extends ilMDBase
{
    private string $catalog = '';
    private string $entry = '';

    // SET/GET
    public function setCatalog(string $a_catalog) : void
    {
        $this->catalog = $a_catalog;
    }

    public function getCatalog() : string
    {
        return $this->catalog;
    }

    public function setEntry(string $a_entry) : void
    {
        $this->entry = $a_entry;
    }

    public function getEntry() : string
    {
        return $this->entry;
    }

    public function save() : int
    {
        $fields = $this->__getFields();
        $fields['meta_identifier_id'] = array('integer', $next_id = $this->db->nextId('il_meta_identifier'));

        if ($this->db->insert('il_meta_identifier', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update() : bool
    {
        return $this->getMetaId() && $this->db->update(
            'il_meta_identifier',
            $this->__getFields(),
            array("meta_identifier_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete() : bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_identifier " .
                "WHERE meta_identifier_id = " . $this->db->quote($this->getMetaId(), 'integer');
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
            'parent_type' => array('text', $this->getParentType()),
            'parent_id' => array('integer', $this->getParentId()),
            'catalog' => array('text', $this->getCatalog()),
            'entry' => array('text', $this->getEntry())
        );
    }

    public function read() : bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_identifier " .
                "WHERE meta_identifier_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setParentId((int) $row->parent_id);
                $this->setParentType($row->parent_type);
                $this->setCatalog($row->catalog);
                $this->setEntry($row->entry);
            }
        }
        return true;
    }

    public function toXML(ilXmlWriter $writer) : void
    {
        $entry_default = ($this->getObjId() === 0)
            ? "il_" . IL_INST_ID . "_" . $this->getObjType() . "_" . $this->getRBACId()
            : "il_" . IL_INST_ID . "_" . $this->getObjType() . "_" . $this->getObjId();

        $entry = $this->getEntry() ?: $entry_default;
        $catalog = $this->getCatalog();

        if ($this->getExportMode() && $this->getCatalog() !== "ILIAS_NID") {
            $entry = $entry_default;
            $catalog = "ILIAS";
        }

        if ($catalog !== '') {
            $writer->xmlElement('Identifier', array(
                'Catalog' => $catalog,
                'Entry' => $entry
            ));
        } else {
            $writer->xmlElement('Identifier', array('Entry' => $entry));
        }
    }

    // STATIC

    /**
     * @return int[]
     */
    public static function _getIds(int $a_rbac_id, int $a_obj_id, int $a_parent_id, string $a_parent_type) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_identifier_id FROM il_meta_identifier " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id, 'integer') . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type, 'text');

        $res = $ilDB->query($query);
        $ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = (int) $row->meta_identifier_id;
        }
        return $ids;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function _getEntriesForObj(int $a_rbac_id, int $a_obj_id, string $a_obj_type) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_identifier_id, catalog, entry FROM il_meta_identifier " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND obj_type = " . $ilDB->quote($a_obj_type, 'text');

        $res = $ilDB->query($query);
        $entries = array();
        while ($r = $ilDB->fetchAssoc($res)) {
            $entries[$r["meta_identifier_id"]] =
                array(
                    "catalog" => $r["catalog"],
                    "entry" => $r["entry"]
                );
        }
        return $entries;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function _getEntriesForRbacObj(int $a_rbac_id, string $a_obj_type = "") : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_identifier_id, catalog, entry, obj_id FROM il_meta_identifier " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer');

        if ($a_obj_type !== "") {
            $query .=
                " AND obj_type = " . $ilDB->quote($a_obj_type, 'text');
        }

        $res = $ilDB->query($query);
        $entries = array();
        while ($r = $ilDB->fetchAssoc($res)) {
            $entries[$r["meta_identifier_id"]] =
                array(
                    "catalog" => $r["catalog"],
                    "entry" => $r["entry"],
                    "obj_id" => $r["obj_id"]
                );
        }
        return $entries;
    }

    public static function existsIdInRbacObject(
        int $a_rbac_id,
        string $a_obj_type,
        string $a_catalog,
        string $a_entry
    ) : bool {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_identifier_id, obj_id FROM il_meta_identifier " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') .
            " AND obj_type = " . $ilDB->quote($a_obj_type, 'text') .
            " AND catalog = " . $ilDB->quote($a_catalog, 'text') .
            " AND entry = " . $ilDB->quote($a_entry, 'text');
        $s = $ilDB->query($query);
        if ($r = $ilDB->fetchAssoc($s)) {
            return true;
        }
        return false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function readIdData(int $a_rbac_id, string $a_obj_type, string $a_catalog, string $a_entry) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM il_meta_identifier " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') .
            " AND obj_type = " . $ilDB->quote($a_obj_type, 'text') .
            " AND catalog = " . $ilDB->quote($a_catalog, 'text') .
            " AND entry = " . $ilDB->quote($a_entry, 'text');
        $s = $ilDB->query($query);
        $data = array();
        while ($r = $ilDB->fetchAssoc($s)) {
            $data[] = $r;
        }
        return $data;
    }
}
