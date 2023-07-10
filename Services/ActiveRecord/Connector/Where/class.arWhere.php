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
 * Class arWhere
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arWhere extends arStatement
{
    public const TYPE_STRING = 1;
    public const TYPE_REGULAR = 2;
    protected int $type = self::TYPE_REGULAR;
    protected string $fieldname = '';
    /**
     * @var
     */
    protected $value;
    protected string $operator = '=';
    protected string $statement = '';
    protected string $link = 'AND';

    /**
     * @description Build WHERE Statement
     * @throws arException
     */
    public function asSQLStatement(ActiveRecord $activeRecord): string
    {
        $type = null;
        if ($this->getType() === self::TYPE_REGULAR) {
            $arField = $activeRecord->getArFieldList()->getFieldByName($this->getFieldname());
            $type = 'text';
            if ($arField instanceof arField) {
                $type = $arField->getFieldType();
                $statement = $activeRecord->getConnectorContainerName() . '.' . $this->getFieldname();
            } else {
                $statement = $this->getFieldname();
            }

            if (is_array($this->getValue())) {
                if (in_array($this->getOperator(), ['IN', 'NOT IN', 'NOTIN'])) {
                    $statement .= ' ' . $this->getOperator() . ' (';
                } else {
                    $statement .= ' IN (';
                }
                $values = [];
                foreach ($this->getValue() as $value) {
                    $values[] = $activeRecord->getArConnector()->quote($value, $type);
                }
                $statement .= implode(', ', $values);
                $statement .= ')';
            } else {
                if ($this->getValue() === null) {
                    $operator = 'IS';
                    if (in_array($this->getOperator(), ['IS', 'IS NOT'])) {
                        $operator = $this->getOperator();
                    }
                    $this->setOperator($operator);
                }
                $statement .= ' ' . $this->getOperator();
                $statement .= ' ' . $activeRecord->getArConnector()->quote($this->getValue(), $type);
            }
            $this->setStatement($statement);
        }

        return $this->getStatement();
    }

    public function setFieldname(string $fieldname): void
    {
        $this->fieldname = $fieldname;
    }

    public function getFieldname(): string
    {
        return $this->fieldname;
    }

    public function setOperator(string $operator): void
    {
        $this->operator = $operator;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    /**
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setStatement(string $statement): void
    {
        $this->statement = $statement;
    }

    public function getStatement(): string
    {
        return $this->statement;
    }

    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    public function getLink(): string
    {
        return $this->link;
    }
}
