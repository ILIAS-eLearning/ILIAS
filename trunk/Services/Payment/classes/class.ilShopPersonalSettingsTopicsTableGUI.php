<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* 
* Class ilShopPersonalSettingsTopicsTableGUI
* 
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$ 
* 
* @ingroup ServicesPayment
* 
*/
include_once('Services/Table/classes/class.ilTable2GUI.php');


class ilShopPersonalSettingsTopicsTableGUI extends ilTable2GUI
{
	protected $lng = null;
	protected $ctrl;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_parent_obj, $a_parent_cmd = '')
	{
	 	global $lng, $ilCtrl;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;
	 	
	 	parent::__construct($a_parent_obj, $a_parent_cmd);
	 	$this->setId('shop_topics_settings_tbl');
	 	$this->addColumn($this->lng->txt('title'), 'title' , '70%');
	 	$this->addColumn($this->lng->txt('pay_sorting_value'), 'sorting' , '30%');
	 		 	
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate('tpl.shop_personal_settings_topics_list_row.html', 'Services/Payment');
		$this->setDefaultOrderField('pt_topic_sort');
		$this->setDefaultOrderDirection('asc');
	}
	
	/**
	 * Fill row
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function fillRow($a_set)
	{		
		$this->tpl->setVariable('VAL_ID', $a_set['id']);
		$this->tpl->setVariable('VAL_TITLE', $a_set['title']);
		$this->tpl->setVariable('VAL_SORTING_TEXTINPUT', ilUtil::formInput('sorting['.$a_set['id'].']', $a_set['sorting']));	}
	
	/**
	 * Parse records
	 *
	 * @access public
	 * @param array array of record objects
	 * 
	 */
	public function parseRecords($a_topics)
	{
	 	foreach($a_topics as $topic)
	 	{
			$tmp_arr['id'] = $topic->getId();
			$tmp_arr['title'] = $topic->getTitle();
			$tmp_arr['sorting'] = $topic->getCustomSorting();
			
			$records_arr[] = $tmp_arr;
	 	} 	
	 	
	 	if (!count($a_topics))
		{			
			$this->disable('header');
			$this->disable('footer');

			$this->setNoEntriesText($this->lng->txt('no_topics_yet'));
		}
	 	
	 	$this->setData($records_arr ? $records_arr : array());
	}
}
?>