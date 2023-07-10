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
 * Class ilBiblEntry
 * @author     Gabriel Comte
 * @author     Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblEntry extends ActiveRecord implements ilBiblEntryInterface
{
    public const TABLE_NAME = 'il_bibl_entry';

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
     * @con_fieldtype  integer
     * @con_length     4
     */
    protected ?int $data_id = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     50
     * @con_is_notnull true
     */
    protected ?string $type = null;

    protected string $overview = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getDataId(): int
    {
        return $this->data_id;
    }

    public function setDataId(int $data_id): void
    {
        $this->data_id = $data_id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getOverview(): string
    {
        return $this->overview;
    }
}
