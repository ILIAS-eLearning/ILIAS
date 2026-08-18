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

declare(strict_types=1);

use ILIAS\components\DataCollection\Fields\Formula\FormulaParser\ExpressionParser;
use ILIAS\components\DataCollection\Fields\Formula\FormulaParser\Substitution\FieldSubstitution;

class ilDclFormulaRecordFieldModel extends ilDclBaseRecordFieldModel
{
    protected string $expression = '';
    protected string $parsed_value = '';

    public function __construct(ilDclBaseRecordModel $record, ilDclBaseFieldModel $field)
    {
        parent::__construct($record, $field);

        $this->expression = (string) $this->getField()->getProperty(ilDclBaseFieldModel::PROP_FORMULA_EXPRESSION);
    }

    public function addHiddenItemsToConfirmation(ilConfirmationGUI $confirmation): void
    {
    }

    protected function loadValue(): void
    {
    }

    public function setValue($value, bool $omit_parsing = false): void
    {
    }

    public function doUpdate(): void
    {
    }

    protected function doRead(): void
    {
    }

    public function delete(): void
    {
    }

    public function getHTML(): string
    {
        return $this->parse();
    }

    public function getExportValue(): string
    {
        return $this->parse();
    }

    public function getValue(): string
    {
        return $this->parse();
    }

    protected function parse(): string
    {
        if (!$this->parsed_value && $this->expression) {
            $substitution = new FieldSubstitution(
                $this->getRecord(),
                $this->getField()
            );

            $parser = new ExpressionParser(
                $this->expression,
                $substitution
            );

            try {
                $this->parsed_value = $parser->parse();
            } catch (ilException $e) {
                return $this->lng->txt('dcl_error_parsing_expression') . ' (' . $e->getMessage() . ')';
            }
        }

        return $this->parsed_value;
    }
}
