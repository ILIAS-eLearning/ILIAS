<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Logging/classes/class.ilLoggingDBSettings.php';
include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Component logger with individual log levels by component id
 *
 *
 * @author Stefan Meyer
 * @version $Id$
 * 
 */
class ilLogComponentTableGUI extends ilTable2GUI
{
	protected $settings = null;




	public function __construct($a_parent_obj, $a_parent_cmd = "")
	{
		$this->setId('il_log_component');
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
	}
	
	/**
	 * init table
	 */
	public function init()
	{
		global $ilCtrl;
		
		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
		
		$this->settings = ilLoggingDBSettings::getInstance();
		
		$this->setRowTemplate('tpl.log_component_row.html','Services/Logging');
		$this->addColumn($this->lng->txt('log_component_col_component'), 'component_sortable');
		$this->addColumn($this->lng->txt('log_component_col_level'), 'level');
		
		$this->setDefaultOrderField('component_sortable');
		
		$this->addCommandButton('saveComponentLevels', $this->lng->txt('save'));
		$this->addCommandButton('resetComponentLevels', $this->lng->txt('log_component_btn_reset'));
		
		$this->setShowRowsSelector(FALSE);
		$this->setLimit(500);
	}
	
	/**
	 * Get settings
	 * @return ilLoggingDBSettings
	 */
	public function getSettings()
	{
		return $this->settings;
	}
	
	/**
	 * Parse table
	 */
	public function parse()
	{
		
		include_once './Services/Logging/classes/class.ilLogComponentLevels.php';
		$components = ilLogComponentLevels::getInstance()->getLogComponents();

		ilLoggerFactory::getLogger('log')->dump($components,  ilLogLevel::DEBUG);


		$rows = array();
		foreach($components as $component)
		{
			$row['id'] = $component->getComponentId();
			
			if($component->getComponentId() == 'log_root')
			{
				$row['component'] = 'Root';
				$row['component_sortable'] = '_'.$row['component'];
			}
			else
			{
				include_once './Services/Component/classes/class.ilComponent.php';
				$row['component'] = ilComponent::lookupComponentName($component->getComponentId());
				$row['component_sortable'] = $row['component'];
			}
			
			ilLoggerFactory::getLogger('log')->debug($component->getComponentId());
			$row['level'] = ($component->getLevel() ? $component->getLevel() : $this->getSettings()->getLevel());
			
			$rows[] = $row;
		}
		
		ilLoggerFactory::getLogger('log')->dump($rows,  ilLogLevel::DEBUG);
		
		
		$this->setMaxCount(count($rows));
		$this->setData($rows);
	}
	
	/**
	 * Fill row
	 * @param type $a_set
	 */
	public function fillRow($a_set)
	{
		$this->tpl->setVariable('CNAME',$a_set['component']);
		
		ilLoggerFactory::getLogger('log')->debug('Component Id : ' . $a_set['component_id']);
		if($a_set['id'] == 'log_root')
		{
			$this->tpl->setVariable('TXT_DESC', $GLOBALS['lng']->txt('log_component_root_desc'));
		}
		
		include_once './Services/Form/classes/class.ilSelectInputGUI.php';
		$levels = new ilSelectInputGUI('', 'level['.$a_set['id'].']');
		$levels->setOptions(ilLogLevel::getLevelOptions());
		$levels->setValue($a_set['level']);
		
		$this->tpl->setVariable('C_SELECT_LEVEL',$levels->render());
		
		
	}
	
}
?>