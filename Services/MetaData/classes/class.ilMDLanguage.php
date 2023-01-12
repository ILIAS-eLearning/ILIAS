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
 * Meta Data class (element language)
 * @package ilias-core
 * @version $Id$
 */
class ilMDLanguage extends ilMDBase
{
    private ?ilMDLanguageItem $language = null;

    public static function _lookupFirstLanguage(int $a_rbac_id, int $a_obj_id, string $a_obj_type): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $lang = '';
        $query = "SELECT language FROM il_meta_language " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND obj_type = " . $ilDB->quote($a_obj_type, 'text') . " " .
            "AND parent_type = 'meta_general' " .
            "ORDER BY meta_language_id ";
        $ilDB->setLimit(1, 0);
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $lang = $row->language;
        }
        return $lang;
    }

    // SET/GET
    public function setLanguage(ilMDLanguageItem $lng_obj): void
    {
        $this->language = $lng_obj;
    }

    public function getLanguage(): ?ilMDLanguageItem
    {
        return is_object($this->language) ? $this->language : null;
    }

    public function getLanguageCode(): string
    {
        return is_object($this->language) ? $this->language->getLanguageCode() : '';
    }

    public function save(): int
    {
        $fields = $this->__getFields();
        $fields['meta_language_id'] = array('integer', $next_id = $this->db->nextId('il_meta_language'));
        if ($this->db->insert('il_meta_language', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update(): bool
    {
        return $this->getMetaId() && $this->db->update(
            'il_meta_language',
            $this->__getFields(),
            array("meta_language_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete(): bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_language " .
                "WHERE meta_language_id = " . $this->db->quote($this->getMetaId(), 'integer');
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
            'language' => array('text', $this->getLanguageCode())
        );
    }

    public function read(): bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_language " .
                "WHERE meta_language_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setParentId((int) $row->parent_id);
                $this->setParentType($row->parent_type);
                $this->setLanguage(new ilMDLanguageItem($row->language ?? ''));
            }
        }
        return true;
    }

    public function toXML(ilXmlWriter $writer): void
    {
        $writer->xmlElement(
            'Language',
            array(
                'Language' => $this->getLanguageCode() ?: 'en'
            ),
            $this->getLanguage()
        );
    }

    // STATIC

    /**
     * @return int[]
     */
    public static function _getIds(int $a_rbac_id, int $a_obj_id, int $a_parent_id, string $a_parent_type): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_language_id FROM il_meta_language " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id, 'integer') . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type, 'text');

        $res = $ilDB->query($query);
        $ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = (int) $row->meta_language_id;
        }
        return $ids;
    }
}
