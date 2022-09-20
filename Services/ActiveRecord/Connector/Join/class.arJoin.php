<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class arJoin
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arJoin extends arStatement
{
    public const TYPE_NORMAL = self::TYPE_INNER;
    public const TYPE_LEFT = 'LEFT';
    public const TYPE_RIGHT = 'RIGHT';
    public const TYPE_INNER = 'INNER';
    public const AS_TEXT = ' AS ';
    protected string $type = self::TYPE_NORMAL;
    protected string $table_name = '';
    protected array $fields = array('*');
    protected string $operator = '=';
    protected string $on_first_field = '';
    protected string $on_second_field = '';
    protected bool $full_names = false;
    protected bool $both_external = false;
    protected bool $is_mapped = false;

    protected function asStatementText(ActiveRecord $ar, string $as = ' AS '): string
    {
        $return = ' ' . $this->getType() . ' ';
        $return .= ' JOIN ' . $this->getTableName() . $as . $this->getTableNameAs();
        if ($this->getBothExternal()) {
            $return .= ' ON ' . $this->getOnFirstField() . ' ' . $this->getOperator() . ' ';
        } else {
            $return .= ' ON ' . $ar->getConnectorContainerName() . '.' . $this->getOnFirstField() . ' ' . $this->getOperator() . ' ';
        }

        return $return . ($this->getTableNameAs() . '.' . $this->getOnSecondField());
    }

    public function asSQLStatement(ActiveRecord $ar): string
    {
        return $this->asStatementText($ar, self::AS_TEXT);
    }

    public function setLeft(): void
    {
        $this->setType(self::TYPE_LEFT);
    }

    public function setRght(): void
    {
        $this->setType(self::TYPE_RIGHT);
    }

    public function setInner(): void
    {
        $this->setType(self::TYPE_INNER);
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setOnFirstField(string $on_first_field): void
    {
        $this->on_first_field = $on_first_field;
    }

    public function getOnFirstField(): string
    {
        return $this->on_first_field;
    }

    public function setOnSecondField(string $on_second_field): void
    {
        $this->on_second_field = $on_second_field;
    }

    public function getOnSecondField(): string
    {
        return $this->on_second_field;
    }

    public function setOperator(string $operator): void
    {
        $this->operator = $operator;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setTableName(string $table_name): void
    {
        $this->table_name = $table_name;
    }

    public function getTableName(): string
    {
        return $this->table_name;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setBothExternal(bool $both_external): void
    {
        $this->both_external = $both_external;
    }

    public function getBothExternal(): bool
    {
        return $this->both_external;
    }

    public function setFullNames(bool $full_names): void
    {
        $this->full_names = $full_names;
    }

    public function getFullNames(): bool
    {
        return $this->full_names;
    }

    public function isIsMapped(): bool
    {
        return $this->is_mapped;
    }

    public function setIsMapped(bool $is_mapped): void
    {
        $this->is_mapped = $is_mapped;
    }
}
