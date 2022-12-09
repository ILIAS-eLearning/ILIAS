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
 * Meta Data class (element annotation)
 * @package ilias-core
 * @version $Id$
 */
class ilMDAnnotation extends ilMDBase
{
    private string $entity = '';
    private string $date = '';
    private string $description = '';
    private ?ilMDLanguageItem $description_language = null;

    // SET/GET
    public function setEntity(string $a_entity): void
    {
        $this->entity = $a_entity;
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function setDate(string $a_date): void
    {
        $this->date = $a_date;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function setDescription(string $a_desc): void
    {
        $this->description = $a_desc;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescriptionLanguage(ilMDLanguageItem $lng_obj): void
    {
        $this->description_language = $lng_obj;
    }

    public function getDescriptionLanguage(): ilMDLanguageItem
    {
        return $this->description_language;
    }

    public function getDescriptionLanguageCode(): string
    {
        if (is_object($this->description_language)) {
            return $this->description_language->getLanguageCode();
        }
        return '';
    }

    public function save(): int
    {
        $fields = $this->__getFields();
        $fields['meta_annotation_id'] = array('integer', $next_id = $this->db->nextId('il_meta_annotation'));

        if ($this->db->insert('il_meta_annotation', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update(): bool
    {
        return $this->getMetaId() && $this->db->update(
            'il_meta_annotation',
            $this->__getFields(),
            array("meta_annotation_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete(): bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_annotation " .
                "WHERE meta_annotation_id = " . $this->db->quote($this->getMetaId(), 'integer');
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
            'entity' => array('clob', $this->getEntity()),
            'a_date' => array('clob', $this->getDate()),
            'description' => array('clob', $this->getDescription()),
            'description_language' => array('text', $this->getDescriptionLanguageCode())
        );
    }

    public function read(): bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_annotation " .
                "WHERE meta_annotation_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setEntity($row->entity ?? '');
                $this->setDate($row->a_date ?? '');
                $this->setDescription($row->description ?? '');
                $this->description_language = new ilMDLanguageItem($row->description_language ?? '');
            }
        }
        return true;
    }

    public function toXML(ilXmlWriter $writer): void
    {
        $writer->xmlStartTag('Annotation');
        $writer->xmlElement('Entity', null, $this->getEntity());
        $writer->xmlElement('Date', null, $this->getDate());
        $writer->xmlElement(
            'Description',
            array(
                'Language' => $this->getDescriptionLanguageCode() ?: 'en'
            ),
            $this->getDescription()
        );
        $writer->xmlEndTag('Annotation');
    }

    // STATIC

    /**
     * @return int[]
     */
    public static function _getIds(int $a_rbac_id, int $a_obj_id): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT meta_annotation_id FROM il_meta_annotation " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        $ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = (int) $row->meta_annotation_id;
        }
        return $ids;
    }
}
