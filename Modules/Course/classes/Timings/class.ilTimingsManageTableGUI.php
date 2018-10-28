<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TableGUI class for timings administration
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 * @ingroup ModulesCourse
 */
class ilTimingsManageTableGUI extends ilTable2GUI
{
	/**
	 * @var \ilLogger
	 */
	private $logger = null;

	private $container = null;
	private $main_container = null;
	
	/**
	 * Constructor
	 */
	public function __construct($a_parent_class, $a_parent_cmd, ilObject $a_container_obj, ilObjCourse $a_main_container)
	{
		global $DIC;

		$this->logger = $DIC->logger()->obj();

		$this->container = $a_container_obj;
		$this->main_container = $a_main_container;
		$this->setId('manage_timings_'.$this->getContainerObject()->getRefId());
		
		parent::__construct($a_parent_class, $a_parent_cmd);
	}
	
	/**
	 * @return ilObject
	 */
	public function getContainerObject()
	{
		return $this->container;
	}
	
	/**
	 * @return ilObjectCourse
	 */
	public function getMainContainer()
	{
		return $this->main_container;
	}
	
	/**
	 * Init table
	 */
	public function init()
	{
		$this->setFormAction($GLOBALS['ilCtrl']->getFormAction($this->getParentObject()));
		$this->setRowTemplate('tpl.crs_manage_timings_row.html','Modules/Course');
		
		$this->setTitle($this->lng->txt('edit_timings_list'));
		
		$this->addColumn($this->lng->txt('title'),'');
		$this->addColumn($this->lng->txt('crs_timings_short_active'),'');
		$this->addColumn($this->lng->txt('crs_timings_short_start_end'),'','',FALSE,'','Dauer in Tagen');
		$this->addColumn($this->lng->txt('crs_timings_time_frame'),'');
		$this->addColumn($this->lng->txt('crs_timings_short_changeable'),'');
		$this->addColumn($this->lng->txt('crs_timings_short_limit_start_end'),'');
		
		$this->addCommandButton('updateManagedTimings', $this->lng->txt('save'));
		$this->addCommandButton('timingsOff', $this->lng->txt('cancel'));
		
		
		$this->setShowRowsSelector(FALSE);
	}
	
	/**
	 * Fill table row
	 * @param type $set
	 */
	public function fillRow($set)
	{
		// title
		if(strlen($set['title_link']))
		{
			$this->tpl->setCurrentBlock('title_link');
			$this->tpl->setVariable('TITLE_LINK',$set['title_link']);
			$this->tpl->setVariable('TITLE_LINK_NAME',$set['title']);
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock('title_plain');
			$this->tpl->setVariable('TITLE',$set['title']);
			$this->tpl->parseCurrentBlock();
		}
		if(strlen($set['desc']))
		{
			$this->tpl->setCurrentBlock('item_description');
			$this->tpl->setVariable('DESC',$set['desc']);
			$this->tpl->parseCurrentBlock();
		}
		// active
		$this->tpl->setVariable('NAME_ACTIVE','item['.$set['ref_id'].'][active]');
		$this->tpl->setVariable('CHECKED_ACTIVE', ($set['item']['timing_type']  == ilObjectActivation::TIMINGS_PRESETTING) ? 'checked="checked"' : '');
		
		// start
		if($this->getMainContainer()->getTimingMode() == ilCourseConstants::IL_CRS_VIEW_TIMING_ABSOLUTE)
		{
			include_once './Services/Form/classes/class.ilDateTimeInputGUI.php';
			$dt_input = new ilDateTimeInputGUI('', 'item['.$set['ref_id'].'][sug_start]');
			$dt_input->setMode(ilDateTimeInputGUI::MODE_INPUT);
			$dt_input->setShowEmpty(TRUE);
			$dt_input->setDate(new ilDate($set['item']['suggestion_start'],IL_CAL_UNIX));
			
			$this->tpl->setVariable('start_abs');
			$this->tpl->setVariable('SUG_START',$dt_input->render());
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock('start_rel');
			$this->tpl->setVariable('START_REL_VAL',(int) $set['item']['suggestion_start_rel']);
			$this->tpl->setVariable('START_REL_NAME', 'item['.$set['ref_id'].'][sug_start_rel]');
			$this->tpl->parseCurrentBlock();
		}
		// duration
		if($this->getMainContainer()->getTimingMode() == ilCourseConstants::IL_CRS_VIEW_TIMING_ABSOLUTE)
		{
			$duration = intval(($set['item']['suggestion_end'] - $set['item']['suggestion_start']) / (60*60*24));
			
			$GLOBALS['ilLog']->write($set['item']['suggestion_end'] - $set['item']['suggestion_start']);
		}
		else
		{
			$duration = $set['item']['suggestion_end_rel'] - $set['item']['suggestion_start_rel'];
		}
		$this->tpl->setVariable('NAME_DURATION_A','item['.$set['ref_id'].'][duration_a]');
		$this->tpl->setVariable('VAL_DURATION_A', (int) $duration);
		
		// changeable
		$this->tpl->setVariable('NAME_CHANGE','item['.$set['ref_id'].'][change]');
		$this->tpl->setVariable('CHECKED_CHANGE', $set['item']['changeable'] ? 'checked="checked"' : '');
		
		// latest end
		if($this->getMainContainer()->getTimingMode() == ilCourseConstants::IL_CRS_VIEW_TIMING_ABSOLUTE)
		{
			include_once './Services/Form/classes/class.ilDateTimeInputGUI.php';
			$dt_input = new ilDateTimeInputGUI('', 'item['.$set['ref_id'].'][lim_end]');
			$dt_input->setMode(ilDateTimeInputGUI::MODE_INPUT);
			$dt_input->setShowEmpty(TRUE);
			$dt_input->setDate(new ilDate($set['item']['latest_end'],IL_CAL_UNIX));
			
			$this->tpl->setCurrentBlock('end_abs');
			$this->tpl->setVariable('LIM_END',$dt_input->render());
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock('end_rel');
			$this->tpl->setVariable('END_REL_VAL',(int) $set['item']['latest_end_rel']);
			$this->tpl->setVariable('END_REL_NAME', 'item['.$set['ref_id'].'][lim_end_rel]');
			$this->tpl->parseCurrentBlock();
		}
		
		
	}
	
	
	/**
	 * Parse table content
	 */
	public function parse($a_item_data)
	{
		$rows = array();
		foreach($a_item_data as $item)
		{
			$current_row = array();
			
			// no item groups
			if($item['type'] == 'itgr')
			{
				continue;
			}
			$current_row['ref_id'] = $item['ref_id'];
			$current_row = $this->parseTitle($current_row, $item);
			
			$current_row['item'] = $item;
			
			
			$rows[] = $current_row;
		}
		$this->setData($rows);
	}
	
	
	
	/**
	 * Parse title
	 */
	protected function parseTitle($current_row, $item)
	{
		include_once './Services/Link/classes/class.ilLink.php';
		switch($item['type'])
		{
			case 'fold':
			case 'grp':
				$current_row['title'] = $item['title'];
				$current_row['title_link'] = ilLink::_getLink($item['ref_id'],$item['type']);
				break;
			
			case 'sess':
				if(strlen($item['title']))
				{
					$current_row['title'] = $item['title'];
				}
				else
				{
					include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
					$app_info = ilSessionAppointment::_lookupAppointment(ilObject::_lookupObjId($item['ref_id']));
					$current_row['title'] = ilSessionAppointment::_appointmentToString(
							$app_info['start'],
							$app_info['end'],
							$app_info['fullday']
					);
				}
				$current_row['title_link'] = ilLink::_getLink($item['ref_id'],$item['type']);
				break;
				
			default:
				$current_row['title'] = $item['title'];
				$current_row['title_link'] = '';
				break;
				
		}
		$current_row['desc'] = $item['desc'];
		
		return $current_row;
	}
}
?>
