<?php

require_once "Services/ADT/classes/Bridges/class.ilADTSearchBridgeSingle.php";

abstract class ilADTSearchBridgeMulti extends ilADTSearchBridgeSingle
{	
	protected function setDefinition(ilADTDefinition $a_adt_def)
	{
		if($this->isValidADTDefinition($a_adt_def))
		{			
			$def = $this->convertADTDefinitionToMulti($a_adt_def);
			$this->adt = ilADTFactory::getInstance()->getInstanceByDefinition($def);			
			return;
		}
				
		throw new Exception('ilADTSearchBridge type mismatch.');
	}	
	
	/**
	 * Convert definition to multi version
	 * 
	 * @return ilADTDefinition
	 */
	abstract protected function convertADTDefinitionToMulti(ilADTDefinition $a_adt_def); 
}

?>