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
 * Class ilBiblOverviewModel
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */
class ilBiblOverviewModel extends ActiveRecord implements ilBiblOverviewModelInterface
{
    const TABLE_NAME = 'il_bibl_overview_model';
    
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
    protected ?int $ovm_id = 0;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     */
    protected int $file_type_id;
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     32
     */
    protected string $literature_type;
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     512
     */
    protected string $pattern;
    
    public function getOvmId() : ?int
    {
        return $this->ovm_id;
    }
    
    public function setOvmId(int $ovm_id) : void
    {
        $this->ovm_id = $ovm_id;
    }
    
    public function getFileTypeId() : int
    {
        return $this->file_type_id;
    }
    
    public function setFileTypeId(int $file_type) : void
    {
        $this->file_type_id = $file_type;
    }
    
    public function getLiteratureType() : string
    {
        return $this->literature_type;
    }
    
    public function setLiteratureType(string $literature_type) : void
    {
        $this->literature_type = $literature_type;
    }
    
    public function getPattern() : string
    {
        return $this->pattern;
    }
    
    public function setPattern(string $pattern) : void
    {
        $this->pattern = $pattern;
    }
}
