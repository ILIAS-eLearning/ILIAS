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
 * Class ilBiblTranslation
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblTranslation extends ActiveRecord implements ilBiblTranslationInterface
{
    const TABLE_NAME = 'il_bibl_translation';
    
    public static function returnDbTableName() : string
    {
        return self::TABLE_NAME;
    }
    
    public function getConnectorContainerName() : string
    {
        return self::TABLE_NAME;
    }
    
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     * @con_is_notnull true
     * @con_is_primary true
     * @con_sequence   true
     */
    protected ?int $id = 0;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_is_notnull true
     * @con_is_unique  true
     */
    protected int $field_id = 0;
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     2
     * @con_is_notnull true
     */
    protected string $language_key = '';
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     256
     */
    protected string $translation = '';
    /**
     * @con_has_field  true
     * @con_fieldtype  clob
     */
    protected string $description = '';
    
    public function getId() : ?int
    {
        return $this->id;
    }
    
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    
    public function getFieldId() : int
    {
        return $this->field_id;
    }
    
    public function setFieldId(int $field_id) : void
    {
        $this->field_id = $field_id;
    }
    
    public function getLanguageKey() : string
    {
        return $this->language_key;
    }
    
    public function setLanguageKey(string $language_key) : void
    {
        $this->language_key = $language_key;
    }
    
    public function getTranslation() : string
    {
        return $this->translation;
    }
    
    public function setTranslation(string $translation) : void
    {
        $this->translation = $translation;
    }
    
    public function getDescription() : string
    {
        return $this->description;
    }
    
    public function setDescription(string $description) : void
    {
        $this->description = $description;
    }
}
