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
 * Class ilBiblTableQueryInfo
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblTableQueryFilter implements ilBiblTableQueryFilterInterface
{
    protected string $field_name = '';
    /**
     * @var string|array|null
     */
    protected $field_value;
    protected string $operator = '=';


    public function getFieldName(): string
    {
        return $this->field_name;
    }


    public function setFieldName(string $field_name): void
    {
        $this->field_name = $field_name;
    }


    public function getFieldValue()
    {
        return $this->field_value;
    }

    public function setFieldValue($field_value): void
    {
        assert(is_array($field_value) || is_string($field_value));
        $this->field_value = $field_value;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }


    public function setOperator(string $operator): void
    {
        $this->operator = $operator;
    }
}
