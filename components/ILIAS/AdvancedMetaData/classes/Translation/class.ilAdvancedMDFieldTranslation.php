<?php

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

declare(strict_types=1);

/**
 * Class ilAdvancedMDFieldTranslation
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldTranslation
{
    public const TABLE_NAME = 'adv_md_field_int';

    private int $field_id;
    private string $title;
    private string $description;
    private string $lang_key;

    private ilDBInterface $db;

    /**
     * ilAdvancedMDFieldTranslation constructor.
     */
    public function __construct(int $field_id, string $title, string $description, string $lang_key)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->field_id = $field_id;
        $this->title = $title;
        $this->description = $description;
        $this->lang_key = $lang_key;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getFieldId(): int
    {
        return $this->field_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getLangKey(): string
    {
        return $this->lang_key;
    }

    public function update(): void
    {
        $query = 'select *  from ' . self::TABLE_NAME . ' ' .
            'where field_id = ' . $this->db->quote($this->getFieldId(), ilDBConstants::T_INTEGER) . ' ' .
            'and lang_code = ' . $this->db->quote($this->getLangKey(), ilDBConstants::T_TEXT) . ' ';
        $res = $this->db->query($query);
        if (!$res->numRows()) {
            $this->insert();
            return;
        }

        $query = 'update ' . self::TABLE_NAME . ' ' .
            'set title = ' . $this->db->quote($this->getTitle(), ilDBConstants::T_TEXT) . ', ' .
            'description = ' . $this->db->quote($this->getDescription(), ilDBConstants::T_TEXT) . ' ' .
            'where field_id = ' . $this->db->quote($this->getFieldId(), ilDBConstants::T_INTEGER) . ' ' .
            'and lang_code = ' . $this->db->quote($this->getLangKey(), ilDBConstants::T_TEXT);

        $this->db->manipulate($query);
    }

    public function delete(): void
    {
        $query = 'delete from ' . self::TABLE_NAME . ' ' .
            'where field_id = ' . $this->db->quote($this->getFieldId(), ilDBConstants::T_INTEGER) . ' and ' .
            'lang_code = ' . $this->db->quote($this->getLangKey(), ilDBConstants::T_TEXT);
        $this->db->manipulate($query);
    }

    public function insert(): void
    {
        $query = 'insert into ' . self::TABLE_NAME . ' (field_id, title, lang_code, description) ' .
            'values (  ' .
            $this->db->quote($this->getFieldId(), ilDBConstants::T_INTEGER) . ', ' .
            $this->db->quote($this->getTitle(), ilDBConstants::T_TEXT) . ', ' .
            $this->db->quote($this->getLangKey(), ilDBConstants::T_TEXT) . ', ' .
            $this->db->quote($this->getDescription(), ilDBConstants::T_TEXT) . ' ' .
            ')';
        $this->db->manipulate($query);
    }
}
