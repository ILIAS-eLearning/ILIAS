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

/**
 * Class ilField
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */
class ilBiblField extends ActiveRecord implements ilBiblFieldInterface
{
    public const TABLE_NAME = 'il_bibl_field';

    public static function returnDbTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function getConnectorContainerName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     * @con_is_notnull true
     * @con_is_primary true
     * @con_is_unique  true
     * @con_sequence   true
     */
    protected ?int $id = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     50
     * @con_is_notnull true
     */
    protected ?string $identifier = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     * @con_is_notnull true
     */
    protected int $data_type = 0;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     3
     */
    protected ?int $position = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     * @con_is_notnull true
     */
    protected bool $is_standard_field = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function isStandardField(): bool
    {
        return $this->is_standard_field;
    }

    public function setIsStandardField(bool $is_standard_field): void
    {
        $this->is_standard_field = $is_standard_field;
    }

    public function getDataType(): int
    {
        return $this->data_type;
    }

    public function setDataType(int $data_type): void
    {
        $this->data_type = $data_type;
    }
}
