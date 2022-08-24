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
 ********************************************************************
 */

/**
 * Class ilDclBaseFieldModel
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id:
 */
class ilDclFormulaRecordFieldModel extends ilDclBaseRecordFieldModel
{
    protected string $expression = '';
    protected string $parsed_value = '';

    public function __construct(ilDclBaseRecordModel $record, ilDclBaseFieldModel $field)
    {
        parent::__construct($record, $field);

        $this->expression = $this->getField()->getProperty(ilDclBaseFieldModel::PROP_FORMULA_EXPRESSION);
    }

    public function addHiddenItemsToConfirmation(ilConfirmationGUI $confirmation): void
    {
    }

    /**
     * Do nothing, value is runtime only and not stored in DB
     */
    protected function loadValue(): void
    {
    }

    /**
     * Set value for record field
     * @param int|float $value
     * @param bool      $omit_parsing If true, does not parse the value and stores it in the given format
     */
    public function setValue($value, bool $omit_parsing = false): void
    {
        unset($value);
    }

    /**
     * Do nothing, value is runtime only and not stored in DB
     */
    public function doUpdate(): void
    {
    }

    /**
     * Do nothing, value is runtime only and not stored in DB
     */
    protected function doRead(): void
    {
    }

    /**
     * Do nothing, value is runtime only and not stored in DB
     */
    public function delete(): void
    {
    }

    public function getFormInput(): string
    {
        return $this->parse();
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

    /**
     * Parse expression
     */
    protected function parse(): string
    {
        if (!$this->parsed_value && $this->expression) {
            $parser = new ilDclExpressionParser($this->expression, $this->getRecord(), $this->getField());
            try {
                $this->parsed_value = $parser->parse();
            } catch (ilException $e) {
                return $this->lng->txt('dcl_error_parsing_expression') . ' (' . $e->getMessage() . ')';
            }
        }

        return $this->parsed_value;
    }
}
