<?php

include_once "Services/Exceptions/classes/class.ilException.php";

class ilADTDBException extends ilException
{
	protected $a_col;
	
	public function getColumn()
	{
		return $this->col;
	}
	
	public function setColumn($a_col)
	{
		$this->col = $a_col;
	}		
}

?>