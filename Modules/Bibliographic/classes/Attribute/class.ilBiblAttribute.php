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
 * Class ilBiblAttribute
 *
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 */
class ilBiblAttribute extends ActiveRecord implements ilBiblAttributeInterface
{
    public static function returnDbTableName() : string
    {
        return 'il_bibl_attribute';
    }


    public function getConnectorContainerName() : string
    {
        return 'il_bibl_attribute';
    }


    /**
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    11
     */
    protected int $entry_id;
    /**
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    32
     */
    protected string $name;
    /**
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    4000
     */
    protected string $value;
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


    public function getEntryId() : int
    {
        return $this->entry_id;
    }


    /**
     * @param mixed $entry_id
     */
    public function setEntryId(int $entry_id) : void
    {
        $this->entry_id = $entry_id;
    }


    public function getName() : string
    {
        return $this->name;
    }


    /**
     * @param mixed $name
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }


    public function getValue() : string
    {
        return $this->value;
    }


    /**
     * @param mixed $value
     */
    public function setValue(string $value) : void
    {
        $this->value = $value;
    }


    public function getId() : ?int
    {
        return $this->id;
    }


    /**
     * @param mixed $id
     */
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
}
