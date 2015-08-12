<?php

class ilLogComponentLevel
{
	private $compontent_id  = '';
	private $component_level = null;
	
	public  function __construct($a_component_id)
	{
		$this->compontent_id = $a_component_id;
	}
	
	public function getComponentId()
	{
		return $this->compontent_id;
	}
	
	public function setLevel($a_level)
	{
		$this->level = $a_level;
	}
	
	public function getLevel()
	{
		return $this->level;
	}
	
	public function update()
	{
		global $ilDB;
		
		$ilDB->replace(
				'log_components',
				array('component_id' => array('text' => $this->getComponentId())),
				array('log_level' => array('integer' => $this->getLevel()))
		);
	}
}
?>