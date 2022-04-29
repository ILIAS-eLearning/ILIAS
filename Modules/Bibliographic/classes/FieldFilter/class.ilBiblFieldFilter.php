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
 * Class ilBiblFieldFilter
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */
class ilBiblFieldFilter extends ActiveRecord implements ilBiblFieldFilterInterface
{
    const TABLE_NAME = 'il_bibl_filter';
    
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
     * @con_is_unique  true
     * @con_sequence   true
     */
    protected ?int $id = 0;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     * @con_is_notnull true
     * @con_is_unique  true
     */
    protected int $field_id;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     * @con_is_notnull true
     * @con_is_unique  true
     */
    protected int $object_id;
    /**
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    1
     */
    protected int $filter_type;
    
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
    
    public function getObjectId() : int
    {
        return $this->object_id;
    }
    
    public function setObjectId(int $object_id) : void
    {
        $this->object_id = $object_id;
    }
    
    public function getFilterType() : int
    {
        return $this->filter_type;
    }
    
    public function setFilterType(int $filter_type) : void
    {
        $this->filter_type = $filter_type;
    }
}
