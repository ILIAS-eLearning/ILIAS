<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Logging/classes/class.ilLoggingSettings.php';
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
		$this->settings = ilLoggingSettings::getInstance();
		
		$this->setRowTemplate('tpl.log_component_row.html','Services/Logging');
		$this->addColumn($this->lng->txt('log_component_col_component'), 'component');
		$this->addColumn($this->lng->txt('log_component_col_level'), 'level');
		
		$this->setDefaultOrderField('component');
		
		$this->addCommandButton('saveComponentLevels', $this->lng->txt('save'));
		$this->addCommandButton('resetComponentLevels', $this->lng->txt('log_component_btn_reset'));
	}
	
	/**
	 * Get settings
	 * @return ilLoggingSettings
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
		$all_components = ilLoggingSettings::readLogComponents();

		ilLoggerFactory::getLogger('log')->dump($all_components,  ilLogLevel::DEBUG);
		
		$rows = array();
		foreach($all_components as $component_id => $component_name)
		{
			$row['id'] = $component_id;
			$row['component'] = $component_name;
			$row['level'] = $this->getSettings()->getLevel();
			
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
		
	}
}
?>