<?php

require_once "Services/ADT/classes/Bridges/class.ilADTFormBridge.php";

class ilADTTextFormBridge extends ilADTFormBridge
{
	protected $multi; // [bool]
	protected $multi_rows; // [int]
	protected $multi_cols; // [int]
		
	//
	// properties
	// 
	
	/**
	 * Set multi-line
	 *
	 * @param string $a_value
	 * @param int $a_cols
	 * @param int $a_rows
	 */
	public function setMulti($a_value, $a_cols = null, $a_rows = null)
	{		
	 	$this->multi = (bool)$a_value;
		$this->multi_rows = ($a_rows === null) ? null : (int)$a_rows;
		$this->multi_cols = ($a_cols === null) ? null : (int)$a_cols;
	}

	/**
	 * Is multi-line?
	 *
	 * @return bool
	 */
	public function isMulti()
	{
	 	return $this->multi;
	}
	
	
	//
	// form
	// 
	
	protected function isValidADT(ilADT $a_adt) 
	{
		return ($a_adt instanceof ilADTText);
	}
	
	public function addToForm()
	{		
		$def = $this->getADT()->getCopyOfDefinition();
		
		if(!$this->isMulti())
		{
			$text = new ilTextInputGUI($this->getTitle(), $this->getElementId());
						
			if($def->getMaxLength())
			{
				$max = $def->getMaxLength();
				$size = $text->getSize();

				$text->setMaxLength($max);

				if($size && $max < $size)
				{
					$text->setSize($max);
				}
			}
				
		}
		else
		{
			$text = new ilTextAreaInputGUI($this->getTitle(), $this->getElementId());
			if($this->multi_rows)
			{
				$text->setRows($this->multi_rows);
			}
			if($this->multi_cols)
			{
				$text->setCols($this->multi_cols);
			}
		}
		
		$this->addBasicFieldProperties($text, $def);
	
		$text->setValue($this->getADT()->getText());	
		
		$this->addToParentElement($text);
	}
	
	public function importFromPost()
	{
		// ilPropertyFormGUI::checkInput() is pre-requisite
		$this->getADT()->setText($this->getForm()->getInput($this->getElementId()));
	
		$field = $this->getForm()->getItemByPostvar($this->getElementId());
		$field->setValue($this->getADT()->getText());
	}	
}

?>