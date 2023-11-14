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
 * @author		Björn Heyser <bheyser@databay.de>
 */
abstract class ilIdentifiedMultiValuesInputGUI extends ilTextInputGUI implements ilMultiValuesItem
{
    public const ELEMENT_DEFAULT_ADD_CMD = 'addElement';
    public const ELEMENT_DEFAULT_REMOVE_CMD = 'removeElement';
    public const ELEMENT_DEFAULT_MOVE_UP_CMD = 'moveUpElement';
    public const ELEMENT_DEFAULT_MOVE_DOWN_CMD = 'moveDownElement';

    protected string $element_add_cmd = self::ELEMENT_DEFAULT_ADD_CMD;
    protected string $element_remove_cmd = self::ELEMENT_DEFAULT_REMOVE_CMD;
    protected string $element_move_up_cmd = self::ELEMENT_DEFAULT_MOVE_UP_CMD;
    protected string $element_move_down_cmd = self::ELEMENT_DEFAULT_MOVE_DOWN_CMD;

    protected $identified_multi_values = [];
    protected $formValuesManipulationChain = [];

    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);

        $this->addFormValuesManipulator(new ilFormSubmitRecursiveSlashesStripper());
        $this->addFormValuesManipulator(new ilIdentifiedMultiValuesJsPositionIndexRemover());
    }

    public function getElementAddCmd(): string
    {
        return $this->element_add_cmd;
    }

    public function setElementAddCmd(string $element_add_cmd): void
    {
        $this->element_add_cmd = $element_add_cmd;
    }

    public function getElementRemoveCmd(): string
    {
        return $this->element_remove_cmd;
    }

    public function setElementRemoveCmd(string $element_remove_cmd): void
    {
        $this->element_remove_cmd = $element_remove_cmd;
    }

    public function getElementMoveUpCommand(): string
    {
        return $this->element_move_up_cmd;
    }

    public function setElementMoveUpCommand(string $element_move_up_cmd): void
    {
        $this->element_move_up_cmd = $element_move_up_cmd;
    }

    public function getElementMoveDownCommand(): string
    {
        return $this->element_move_down_cmd;
    }

    public function setElementMoveDownCommand(string $element_move_down_cmd): void
    {
        $this->element_move_down_cmd = $element_move_down_cmd;
    }

    public function setValues($values): void
    {
        throw new ilFormException('setter unsupported, use setIdentifiedMultiValues() instead!');
    }

    /**
     * @return mixed
     * @throws ilFormException
     */
    public function getValues()
    {
        throw new ilFormException('setter unsupported, use setIdentifiedMultiValues() instead!');
    }

    public function setValue($value): void
    {
        throw new ilFormException('setter unsupported, use setIdentifiedMultiValues() instead!');
    }

    public function getValue()
    {
        throw new ilFormException('setter unsupported, use setIdentifiedMultiValues() instead!');
    }

    public function setMultiValues(array $values): void
    {
        throw new ilFormException('setter unsupported, use setIdentifiedMultiValues() instead!');
    }

    public function getMultiValues(): array
    {
        throw new ilFormException('setter unsupported, use setIdentifiedMultiValues() instead!');
    }

    final public function setIdentifiedMultiValues($values): void
    {
        $this->identified_multi_values = $this->prepareMultiValuesInput($values);
    }

    final public function getIdentifiedMultiValues(): array
    {
        return $this->identified_multi_values;
    }

    protected function getMultiValueSubFieldId($identifier, $sub_field_index): string
    {
        $temp_post_var = $this->getMultiValuePostVarSubField($identifier, $sub_field_index);
        return $this->getFieldIdFromPostVar($temp_post_var);
    }

    protected function getMultiValuePosIndexedFieldId($identifier, $position_index): string
    {
        $temp_post_var = $this->getMultiValuePostVarPosIndexed($identifier, $position_index);
        return $this->getFieldIdFromPostVar($temp_post_var);
    }

    protected function getMultiValuePosIndexedSubFieldId($identifier, $sub_field_index, $position_index): string
    {
        $temp_post_var = $this->getMultiValuePostVarSubFieldPosIndexed($identifier, $sub_field_index, $position_index);
        return $this->getFieldIdFromPostVar($temp_post_var);
    }

    protected function getFieldIdFromPostVar($temp_post_var): string
    {
        $basic_post_var = $this->getPostVar();
        $this->setPostVar($temp_post_var);

        // uses getPostVar() internally, our postvar does not have the counter included
        $multi_value_field_id = $this->getFieldId();
        // now ALL brackets ("[", "]") are escaped, even the ones for the counter

        $this->setPostVar($basic_post_var);
        return $multi_value_field_id;
    }

    protected function getPostVarSubField($sub_field_index)
    {
        return $this->getSubFieldCompletedPostVar($sub_field_index, $this->getPostVar());
    }

    protected function getMultiValuePostVarSubField($identifier, $sub_field_index)
    {
        $elem_post_var = $this->getMultiValuePostVar($identifier);
        return $this->getSubFieldCompletedPostVar($sub_field_index, $elem_post_var);
    }

    protected function getMultiValuePostVarSubFieldPosIndexed($identifier, $sub_field_index, $position_index)
    {
        $elem_post_var = $this->getMultiValuePostVarPosIndexed($identifier, $position_index);
        return $this->getSubFieldCompletedPostVar($sub_field_index, $elem_post_var);
    }

    protected function getMultiValuePostVarPosIndexed($identifier, $positionIndex): string
    {
        $elem_post_var = $this->getMultiValuePostVar($identifier);
        $elem_post_var .= "[$positionIndex]";

        return $elem_post_var;
    }

    protected function getMultiValuePostVar($identifier): string
    {
        $elem_post_var = $this->getPostVar();
        $elem_post_var .= "[$identifier]";
        return $elem_post_var;
    }

    protected function buildMultiValueSubmitVar($identifier, $position_index, $submit_cmd): string
    {
        $elem_submit_var = "cmd[{$submit_cmd}][{$this->getFieldId()}]";
        $elem_submit_var .= "[$identifier][$position_index]";

        return $elem_submit_var;
    }

    final public function setValueByArray(array $a_values): void
    {
        if (!isset($a_values[$this->getPostVar()]) || !is_array($a_values[$this->getPostVar()])) {
            $a_values[$this->getPostVar()] = [];
        }

        $a_values[$this->getPostVar()] = $this->prepareMultiValuesSubmit(
            $a_values[$this->getPostVar()]
        );

        $this->setIdentifiedMultiValuesByArray($a_values);
    }

    protected function setIdentifiedMultiValuesByArray($a_values): void
    {
        $this->identified_multi_values = $a_values[$this->getPostVar()];
    }

    /**
     * @return string[]
     */
    public function getInput(): array
    {
        $values = $this->arrayArray($this->getPostVar());

        return $this->prepareMultiValuesSubmit($values);
    }

    final public function checkInput(): bool
    {
        return $this->onCheckInput();
    }

    abstract public function onCheckInput();

    final protected function prepareMultiValuesInput($values)
    {
        foreach ($this->getFormValuesManipulators() as $manipulator) {
            $values = $manipulator->manipulateFormInputValues($values);
        }

        return $values;
    }

    final protected function prepareMultiValuesSubmit($values)
    {
        foreach ($this->getFormValuesManipulators() as $manipulator) {
            /* @var ilFormValuesManipulator $manipulator */
            $values = $manipulator->manipulateFormSubmitValues($values);
        }

        return $values;
    }

    protected function getFormValuesManipulators(): array
    {
        return $this->formValuesManipulationChain;
    }

    protected function addFormValuesManipulator(ilFormValuesManipulator $manipulator): void
    {
        $this->formValuesManipulationChain[] = $manipulator;
    }

    /**
     * @param $subFieldIndex
     * @param $elem_post_var
     * @return mixed
     */
    protected function getSubFieldCompletedPostVar($subFieldIndex, $elem_post_var)
    {
        $field_post_var = "{$this->getPostVar()}[$subFieldIndex]";
        return str_replace($this->getPostVar(), $field_post_var, $elem_post_var);
    }

    public function prepareReprintable(assQuestion $question): void
    {
        $this->setIdentifiedMultiValues($this->getIdentifiedMultiValues());
    }
}
