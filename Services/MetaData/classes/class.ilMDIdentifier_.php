<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Meta Data class (element identifier_)
 * @package ilias-core
 * @version $Id$
 */
class ilMDIdentifier_ extends ilMDBase
{
    private string $catalog = '';
    private string $entry = '';

    // SET/GET
    public function setCatalog(string $a_catalog): void
    {
        $this->catalog = $a_catalog;
    }

    public function getCatalog(): string
    {
        return $this->catalog;
    }

    public function setEntry(string $a_entry): void
    {
        $this->entry = $a_entry;
    }

    public function getEntry(): string
    {
        return $this->entry;
    }

    public function save(): int
    {
        $fields = $this->__getFields();
        $fields['meta_identifier__id'] = array('integer', $next_id = $this->db->nextId('il_meta_identifier_'));

        if ($this->db->insert('il_meta_identifier_', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update(): bool
    {
        return $this->getMetaId() && $this->db->update(
            'il_meta_identifier_',
            $this->__getFields(),
            array("meta_identifier__id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete(): bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_identifier_ " .
                "WHERE meta_identifier__id = " . $this->db->quote($this->getMetaId(), 'integer');
            $res = $this->db->manipulate($query);
            return true;
        }
        return false;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function __getFields(): array
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

    public function read(): bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_identifier_ " .
                "WHERE meta_identifier__id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setParentId((int) $row->parent_id);
                $this->setParentType($row->parent_type);
                $this->setCatalog($row->catalog ?? '');
                $this->setEntry($row->entry ?? '');
            }
        }
        return true;
    }

    public function toXML(ilXmlWriter $writer): void
    {
        $writer->xmlElement('Identifier_', array(
            'Catalog' => $this->getCatalog(),
            'Entry' => $this->getEntry() ?: "ID1"
        ));
    }

    // STATIC

    /**
     * @return int[]
     */
    public static function _getIds(int $a_rbac_id, int $a_obj_id, int $a_parent_id, string $a_parent_type): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_identifier__id FROM il_meta_identifier_ " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id, 'integer') . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type, 'text');

        $res = $ilDB->query($query);
        $ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = (int) $row->meta_identifier__id;
        }
        return $ids;
    }
}
