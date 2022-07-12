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
 * Created by PhpStorm.
 * User: fschmid
 * Date: 20.11.17
 * Time: 16:20
 */
/**
 * Class ilBiblTableQueryInfo
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblTableQueryFilterInterface
{
    public function getFieldName() : string;
    
    public function setFieldName(string $field_name) : void;
    
    /**
     * @return string|array
     */
    public function getFieldValue();
    
    /**
     * @param string|array $field_value
     */
    public function setFieldValue($field_value) : void;
    
    public function getOperator() : string;
    
    public function setOperator(string $operator) : void;
}
