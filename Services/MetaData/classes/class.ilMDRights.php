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
 * Meta Data class (element rights)
 * @package ilias-core
 * @version $Id$
 */
class ilMDRights extends ilMDBase
{
    private string $costs = '';
    private string $caor = '';
    private string $description = '';
    private ?ilMDLanguageItem $description_language = null;

    /**
     * @param string[] $a_types
     * @param string[] $a_copyright
     * @return int[]
     */
    public static function lookupRightsByTypeAndCopyright(array $a_types, array $a_copyright): array
    {
        global $DIC;

        $db = $DIC->database();

        $query = 'SELECT rbac_id FROM il_meta_rights ' .
            'WHERE ' . $db->in('obj_type', $a_types, false, 'text') . ' ' .
            'AND ' . $db->in('description', $a_copyright, false, 'text');
        $res = $db->query($query);

        ilLoggerFactory::getLogger('meta')->info($query);

        $obj_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = $row->rbac_id;
        }
        return $obj_ids;
    }

    // SET/GET
    public function setCosts(string $a_costs): bool
    {
        switch ($a_costs) {
            case 'Yes':
            case 'No':
                $this->costs = $a_costs;
                return true;

            default:
                return false;
        }
    }

    public function getCosts(): string
    {
        return $this->costs;
    }

    public function setCopyrightAndOtherRestrictions(string $a_caor): bool
    {
        switch ($a_caor) {
            case 'Yes':
            case 'No':
                $this->caor = $a_caor;
                return true;

            default:
                return false;
        }
    }

    public function getCopyrightAndOtherRestrictions(): string
    {
        return $this->caor;
    }

    public function setDescription(string $a_description): void
    {
        $this->description = $a_description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescriptionLanguage(ilMDLanguageItem $lng_obj): void
    {
        $this->description_language = $lng_obj;
    }

    public function getDescriptionLanguage(): ?ilMDLanguageItem
    {
        return is_object($this->description_language) ? $this->description_language : null;
    }

    public function getDescriptionLanguageCode(): string
    {
        return is_object($this->description_language) ? $this->description_language->getLanguageCode() : '';
    }

    public function save(): int
    {
        $fields = $this->__getFields();
        $fields['meta_rights_id'] = array('integer', $next_id = $this->db->nextId('il_meta_rights'));

        if ($this->db->insert('il_meta_rights', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update(): bool
    {
        return $this->getMetaId() && $this->db->update(
            'il_meta_rights',
            $this->__getFields(),
            array("meta_rights_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete(): bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_rights " .
                "WHERE meta_rights_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $this->db->query($query);

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
            'costs' => array('text', $this->getCosts()),
            'cpr_and_or' => array('text', $this->getCopyrightAndOtherRestrictions()),
            'description' => array('text', $this->getDescription()),
            'description_language' => array('text', $this->getDescriptionLanguageCode())
        );
    }

    public function read(): bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_rights " .
                "WHERE meta_rights_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType((string) $row->obj_type);
                $this->setDescription((string) $row->description);
                $this->setDescriptionLanguage(new ilMDLanguageItem((string) $row->description_language));
                $this->setCosts((string) $row->costs);
                $this->setCopyrightAndOtherRestrictions((string) $row->cpr_and_or);
            }
            return true;
        }
        return false;
    }

    public function toXML(ilXmlWriter $writer): void
    {
        $writer->xmlStartTag('Rights', array(
            'Cost' => $this->getCosts() ?: 'No',
            'CopyrightAndOtherRestrictions' => $this->getCopyrightAndOtherRestrictions() ?: 'No'
        ));

        $writer->xmlElement(
            'Description',
            [
                'Language' => $this->getDescriptionLanguageCode() ?: 'en'
            ],
            ilMDCopyrightSelectionEntry::_lookupCopyright($this->getDescription())
        );
        $writer->xmlEndTag('Rights');
    }

    public function parseDescriptionFromImport(string $a_description): void
    {
        $entry_id = ilMDCopyrightSelectionEntry::lookupCopyrightByText($a_description);
        if (!$entry_id) {
            $this->setDescription($a_description);
        } else {
            $this->setDescription(ilMDCopyrightSelectionEntry::createIdentifier($entry_id));
        }
    }

    public static function _lookupDescription(int $a_rbac_id, int $a_obj_id): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT description FROM il_meta_rights " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

        if (isset($row) && isset($row->description)) {
            return $row->description;
        }
        return '';
    }

    // STATIC
    public static function _getId(int $a_rbac_id, int $a_obj_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_rights_id FROM il_meta_rights " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->meta_rights_id;
        }
        return 0;
    }
}
