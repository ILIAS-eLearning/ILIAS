<?php

class ilADTBooleanDefinition extends ilADTDefinition
{		
	// comparison
		
	public function isComparableTo(ilADT $a_adt)
	{
		// has to be boolean-based
		return ($a_adt instanceof ilADTBoolean);
	}	
}

?>