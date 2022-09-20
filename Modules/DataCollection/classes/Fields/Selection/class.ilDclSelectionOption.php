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
 ********************************************************************
 */

/**
 * Class ilDclSelectionOption
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDclSelectionOption extends ActiveRecord
{
    public const DB_TABLE_NAME = "il_dcl_sel_opts";

    public static function returnDbTableName(): string
    {
        return self::DB_TABLE_NAME;
    }

    /**
     * @var int
     * @db_has_field        true
     * @db_is_unique        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     * @db_sequence         true
     */
    protected ?int $id;
    /**
     * @var int
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $field_id;
    /**
     * @var int
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $opt_id;
    /**
     * @var int
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $sorting;
    /**
     * @var string
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected string $value;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getFieldId(): int
    {
        return $this->field_id;
    }

    public function setFieldId(int $field_id): void
    {
        $this->field_id = $field_id;
    }

    public function getOptId(): int
    {
        return $this->opt_id;
    }

    public function setOptId(int $opt_id): void
    {
        $this->opt_id = $opt_id;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): void
    {
        $this->sorting = $sorting;
    }

    public static function storeOption(int $field_id, int $opt_id, int $sorting, string $value): void
    {
        /** @var ilDclSelectionOption $option */
        $option = self::where(array("field_id" => $field_id, "opt_id" => $opt_id))->first();
        if (!$option) {
            $option = new self();
        }
        $option->setFieldId($field_id);
        $option->setOptId($opt_id);
        $option->setSorting($sorting);
        $option->setValue($value);
        $option->store();
    }

    public static function flushOptions(int $field_id): void
    {
        foreach (self::getAllForField($field_id) as $option) {
            $option->delete();
        }
    }

    /**
     * @return self[]
     */
    public static function getAllForField(int $field_id): array
    {
        return self::where(array("field_id" => $field_id))->orderBy('sorting')->get();
    }

    /**
     * @param array|string|int $opt_ids
     * @throws arException
     */
    public static function getValues(int $field_id, $opt_ids): array
    {
        $operators = array('field_id' => '=');
        if (is_array($opt_ids)) {
            if (empty($opt_ids)) {
                return array();
            }
            $operators['opt_id'] = 'IN';
        } else {
            $operators['opt_id'] = '=';
        }
        $return = array();
        foreach (self::where(
            array("field_id" => $field_id, "opt_id" => $opt_ids),
            $operators
        )->orderBy('sorting')->get() as $opt) {
            $return[] = $opt->getValue();
        }

        return $return;
    }

    /**
     * @param ilDclSelectionOption $original_option
     */
    public function cloneOption(ilDclSelectionOption $original_option): void
    {
        $this->setValue($original_option->getValue());
        $this->setSorting($original_option->getSorting());
        $this->setOptId($original_option->getOptId());
    }
}
