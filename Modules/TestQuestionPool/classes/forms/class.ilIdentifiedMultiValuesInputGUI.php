<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * @author		Björn Heyser <bheyser@databay.de>
 */
abstract class ilIdentifiedMultiValuesInputGUI extends ilTextInputGUI implements ilMultiValuesItem
{
    const ELEMENT_DEFAULT_ADD_CMD = 'addElement';
    const ELEMENT_DEFAULT_REMOVE_CMD = 'removeElement';
    const ELEMENT_DEFAULT_MOVE_UP_CMD = 'moveUpElement';
    const ELEMENT_DEFAULT_MOVE_DOWN_CMD = 'moveDownElement';
    
    protected $elementAddCmd = self::ELEMENT_DEFAULT_ADD_CMD;
    protected $elementRemoveCmd = self::ELEMENT_DEFAULT_REMOVE_CMD;
    protected $elementMoveUpCommand = self::ELEMENT_DEFAULT_MOVE_UP_CMD;
    protected $elementMoveDownCommand = self::ELEMENT_DEFAULT_MOVE_DOWN_CMD;

    protected $identified_multi_values = array();
    
    protected $formValuesManipulationChain = array();

    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        
        $this->addFormValuesManipulator(new ilFormSubmitRecursiveSlashesStripper());
        $this->addFormValuesManipulator(new ilIdentifiedMultiValuesJsPositionIndexRemover());
    }
    
    public function getElementAddCmd()
    {
        return $this->elementAddCmd;
    }
    
    /**
     * @param string $elementAddCmd
     */
    public function setElementAddCmd($elementAddCmd)
    {
        $this->elementAddCmd = $elementAddCmd;
    }
    
    public function getElementRemoveCmd()
    {
        return $this->elementRemoveCmd;
    }
    
    public function setElementRemoveCmd($elementRemoveCmd)
    {
        $this->elementRemoveCmd = $elementRemoveCmd;
    }
    
    public function getElementMoveUpCommand()
    {
        return $this->elementMoveUpCommand;
    }
    
    public function setElementMoveUpCommand($elementMoveUpCommand)
    {
        $this->elementMoveUpCommand = $elementMoveUpCommand;
    }
    
    public function getElementMoveDownCommand()
    {
        return $this->elementMoveDownCommand;
    }
    
    public function setElementMoveDownCommand($elementMoveDownCommand)
    {
        $this->elementMoveDownCommand = $elementMoveDownCommand;
    }
    
    public function setValues($values)
    {
        throw new ilFormException('setter unsupported, use setIdentifiedMultiValues() instead!');
    }
    
    public function getValues()
    {
        throw new ilFormException('setter unsupported, use setIdentifiedMultiValues() instead!');
    }
    
    public function setValue($value) : void
    {
        throw new ilFormException('setter unsupported, use setIdentifiedMultiValues() instead!');
    }
    
    public function getValue()
    {
        throw new ilFormException('setter unsupported, use setIdentifiedMultiValues() instead!');
    }
    
    public function setMultiValues(array $values) : void
    {
        throw new ilFormException('setter unsupported, use setIdentifiedMultiValues() instead!');
    }
    
    public function getMultiValues() : array
    {
        throw new ilFormException('setter unsupported, use setIdentifiedMultiValues() instead!');
    }
    
    final public function setIdentifiedMultiValues($values)
    {
        $this->identified_multi_values = $this->prepareMultiValuesInput($values);
    }
    
    final public function getIdentifiedMultiValues()
    {
        return $this->identified_multi_values;
    }
    
    protected function getMultiValueSubFieldId($identifier, $subFieldIndex)
    {
        $tempPostVar = $this->getMultiValuePostVarSubField($identifier, $subFieldIndex);
        $multiValueFieldId = $this->getFieldIdFromPostVar($tempPostVar);
        
        return $multiValueFieldId;
    }
    
    protected function getMultiValuePosIndexedFieldId($identifier, $positionIndex)
    {
        $tempPostVar = $this->getMultiValuePostVarPosIndexed($identifier, $positionIndex);
        $multiValueFieldId = $this->getFieldIdFromPostVar($tempPostVar);
        
        return $multiValueFieldId;
    }
    
    protected function getMultiValuePosIndexedSubFieldId($identifier, $subFieldIndex, $positionIndex)
    {
        $tempPostVar = $this->getMultiValuePostVarSubFieldPosIndexed($identifier, $subFieldIndex, $positionIndex);
        $multiValueFieldId = $this->getFieldIdFromPostVar($tempPostVar);
        
        return $multiValueFieldId;
    }
    
    protected function getFieldIdFromPostVar($tempPostVar)
    {
        $basicPostVar = $this->getPostVar();
        $this->setPostVar($tempPostVar);
        
        // uses getPostVar() internally, our postvar does not have the counter included
        $multiValueFieldId = $this->getFieldId();
        // now ALL brackets ("[", "]") are escaped, even the ones for the counter
        
        $this->setPostVar($basicPostVar);
        return $multiValueFieldId;
    }
    
    protected function getPostVarSubField($subFieldIndex)
    {
        return $this->getSubFieldCompletedPostVar($subFieldIndex, $this->getPostVar());
    }
    
    protected function getMultiValuePostVarSubField($identifier, $subFieldIndex)
    {
        $elemPostVar = $this->getMultiValuePostVar($identifier);
        $elemPostVar = $this->getSubFieldCompletedPostVar($subFieldIndex, $elemPostVar);
        
        return $elemPostVar;
    }
    
    protected function getMultiValuePostVarSubFieldPosIndexed($identifier, $subFieldIndex, $positionIndex)
    {
        $elemPostVar = $this->getMultiValuePostVarPosIndexed($identifier, $positionIndex);
        $elemPostVar = $this->getSubFieldCompletedPostVar($subFieldIndex, $elemPostVar);
        
        return $elemPostVar;
    }
    
    protected function getMultiValuePostVarPosIndexed($identifier, $positionIndex)
    {
        $elemPostVar = $this->getMultiValuePostVar($identifier);
        $elemPostVar .= "[$positionIndex]";
        
        return $elemPostVar;
    }
    
    protected function getMultiValuePostVar($identifier)
    {
        $elemPostVar = $this->getPostVar();
        $elemPostVar .= "[$identifier]";
        return $elemPostVar;
    }
    
    protected function buildMultiValueSubmitVar($identifier, $positionIndex, $submitCommand)
    {
        $elemSubmitVar = "cmd[{$submitCommand}][{$this->getFieldId()}]";
        $elemSubmitVar .= "[$identifier][$positionIndex]";
        
        return $elemSubmitVar;
    }
    
    final public function setValueByArray(array $a_values) : void
    {
        if (!is_array($a_values[$this->getPostVar()])) {
            $a_values[$this->getPostVar()] = array();
        }
        
        $a_values[$this->getPostVar()] = $this->prepareMultiValuesSubmit(
            $a_values[$this->getPostVar()]
        );
        
        $this->setIdentifiedMultiValuesByArray($a_values);
    }
    
    protected function setIdentifiedMultiValuesByArray($a_values)
    {
        $this->identified_multi_values = $a_values[$this->getPostVar()];
    }
    
    final public function checkInput() : bool
    {
        if (!is_array($_POST[$this->getPostVar()])) {
            $_POST[$this->getPostVar()] = array();
        }
        
        $_POST[$this->getPostVar()] = $this->prepareMultiValuesSubmit(
            $_POST[$this->getPostVar()]
        );
        
        return $this->onCheckInput();
    }
    
    abstract public function onCheckInput();
    
    final protected function prepareMultiValuesInput($values)
    {
        foreach ($this->getFormValuesManipulators() as $manipulator) {
            /* @var ilFormValuesManipulator $manipulator */
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
    
    protected function getFormValuesManipulators()
    {
        return $this->formValuesManipulationChain;
    }
    
    protected function addFormValuesManipulator(ilFormValuesManipulator $manipulator)
    {
        $this->formValuesManipulationChain[] = $manipulator;
    }
    
    /**
     * @param $subFieldIndex
     * @param $elemPostVar
     * @return mixed
     */
    protected function getSubFieldCompletedPostVar($subFieldIndex, $elemPostVar)
    {
        $fieldPostVar = "{$this->getPostVar()}[$subFieldIndex]";
        $elemPostVar = str_replace($this->getPostVar(), $fieldPostVar, $elemPostVar);
        return $elemPostVar;
    }
    
    public function prepareReprintable(assQuestion $question)
    {
        $this->setIdentifiedMultiValues($this->getIdentifiedMultiValues());
    }
}
