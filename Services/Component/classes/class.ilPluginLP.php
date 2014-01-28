<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectLP.php";

/**
 * Plugin to lp connector
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package ServicesComponent
 */
class ilPluginLP extends ilObjectLP
{	
	protected $status; // [mixed]
	
	const INACTIVE_PLUGIN = -1;
	
	protected function __construct($a_obj_id)
	{		
		parent::__construct($a_obj_id);
		
		$this->initPlugin();
	}
	
	protected function initPlugin()
	{				
		// active plugin?
		include_once 'Services/Repository/classes/class.ilRepositoryObjectPluginSlot.php';	
		if(ilRepositoryObjectPluginSlot::isTypePluginWithLP(ilObject::_lookupType($this->obj_id)))
		{
			$obj = ilObjectFactory::getInstanceByObjId($this->obj_id, false); // #12640
			if($obj && $obj instanceof ilLPStatusPluginInterface)
			{
				$this->status = $obj;
			}
		}	
		// inactive plugin?
		else if(ilRepositoryObjectPluginSlot::isTypePluginWithLP(ilObject::_lookupType($this->obj_id), false))
		{
			$this->status = self::INACTIVE_PLUGIN;
		}						
	}
	
	public function getPluginInstance()
	{
		return $this->status;
	}	
	
	public function getDefaultMode()
	{		
		return ilLPObjSettings::LP_MODE_UNDEFINED;
	}
	
	public function getValidModes()
	{						
		return array(		
			ilLPObjSettings::LP_MODE_UNDEFINED,
			ilLPObjSettings::LP_MODE_PLUGIN
		);		
	}	
	
	public function getCurrentMode()
	{		
		if($this->status !== null)
		{
			return ilLPObjSettings::LP_MODE_PLUGIN;
		}		
		return ilLPObjSettings::LP_MODE_UNDEFINED;
	}
}

?>