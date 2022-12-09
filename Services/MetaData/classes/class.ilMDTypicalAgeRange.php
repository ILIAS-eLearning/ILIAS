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
 * Meta Data class (element typicalagerange)
 * @package ilias-core
 * @version $Id$
 */
class ilMDTypicalAgeRange extends ilMDBase
{
    private string $typical_age_range = '';
    private ?ilMDLanguageItem $typical_age_range_language = null;
    private string $typical_age_range_minimum = '';
    private string $typical_age_range_maximum = '';

    // SET/GET
    public function setTypicalAgeRange(string $a_typical_age_range): void
    {
        $this->typical_age_range = $a_typical_age_range;
    }

    public function getTypicalAgeRange(): string
    {
        return $this->typical_age_range;
    }

    public function setTypicalAgeRangeLanguage(ilMDLanguageItem $lng_obj): void
    {
        $this->typical_age_range_language = $lng_obj;
    }

    public function getTypicalAgeRangeLanguage(): ?ilMDLanguageItem
    {
        return is_object($this->typical_age_range_language) ? $this->typical_age_range_language : null;
    }

    public function getTypicalAgeRangeLanguageCode(): string
    {
        return is_object($this->typical_age_range_language) ? $this->typical_age_range_language->getLanguageCode() : '';
    }

    public function setTypicalAgeRangeMinimum(string $a_min): void
    {
        $this->typical_age_range_minimum = $a_min;
    }

    public function getTypicalAgeRangeMinimum(): string
    {
        return $this->typical_age_range_minimum;
    }

    public function setTypicalAgeRangeMaximum(string $a_max): void
    {
        $this->typical_age_range_maximum = $a_max;
    }

    public function getTypicalAgeRangeMaximum(): string
    {
        return $this->typical_age_range_maximum;
    }

    public function save(): int
    {
        $fields = $this->__getFields();
        $fields['meta_tar_id'] = array('integer', $next_id = $this->db->nextId('il_meta_tar'));

        if ($this->db->insert('il_meta_tar', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update(): bool
    {
        $this->__parseTypicalAgeRange();

        return $this->getMetaId() && $this->db->update(
            'il_meta_tar',
            $this->__getFields(),
            array("meta_tar_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete(): bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_tar " .
                "WHERE meta_tar_id = " . $this->db->quote($this->getMetaId(), 'integer');
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
            'typical_age_range' => array('text', $this->getTypicalAgeRange()),
            'tar_language' => array('text', $this->getTypicalAgeRangeLanguageCode()),
            'tar_min' => array('text', $this->getTypicalAgeRangeMinimum()),
            'tar_max' => array('text', $this->getTypicalAgeRangeMaximum())
        );
    }

    public function read(): bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_tar " .
                "WHERE meta_tar_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setParentId((int) $row->parent_id);
                $this->setParentType((string) $row->parent_type);
                $this->setTypicalAgeRange((string) $row->typical_age_range);
                $this->setTypicalAgeRangeLanguage(new ilMDLanguageItem($row->tar_language ?? ''));
                $this->setTypicalAgeRangeMinimum((string) $row->tar_min);
                $this->setTypicalAgeRangeMaximum((string) $row->tar_max);
            }
        }
        return true;
    }

    public function toXML(ilXmlWriter $writer): void
    {
        $writer->xmlElement(
            'TypicalAgeRange',
            array(
                'Language' => $this->getTypicalAgeRangeLanguageCode() ?: 'en'
            ),
            $this->getTypicalAgeRange()
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

        $query = "SELECT meta_tar_id FROM il_meta_tar " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id, 'integer') . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type, 'text');

        $res = $ilDB->query($query);
        $ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = (int) $row->meta_tar_id;
        }

        return $ids;
    }

    // PRIVATE
    public function __parseTypicalAgeRange(): bool
    {
        if (preg_match("/\s*(\d*)\s*(-?)\s*(\d*)/", $this->getTypicalAgeRange(), $matches)) {
            if (!$matches[2] and !$matches[3]) {
                $min = $max = $matches[1];
            } elseif ($matches[2] and !$matches[3]) {
                $min = $matches[1];
                $max = 99;
            } else {
                $min = $matches[1];
                $max = $matches[3];
            }
            $this->setTypicalAgeRangeMaximum((string) $max);
            $this->setTypicalAgeRangeMinimum((string) $min);

            return true;
        }

        if (!$this->getTypicalAgeRange()) {
            $this->setTypicalAgeRangeMinimum('-1');
            $this->setTypicalAgeRangeMaximum('-1');
        }

        return true;
    }
}
