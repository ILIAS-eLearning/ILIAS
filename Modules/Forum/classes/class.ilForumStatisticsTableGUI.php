<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * Class ilForumStatisticsTableGUI
 *
 * @author	Michael Jansen <mjansen@databay.de>
 * @version	$Id$
 *
 * @ingroup ModulesForum
 */
class ilForumStatisticsTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 * 
	 * @access	public
	 *
	 */
	public function __construct($a_parent_obj, $a_parent_cmd = "")
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setRowTemplate('tpl.statistics_table_row.html', 'Modules/Forum');
		$this->addColumn($this->lng->txt('frm_statistics_ranking'), 'ranking', '25%');
		$this->addColumn($this->lng->txt('login'), 'login', '25%');
		$this->addColumn($this->lng->txt('lastname'), 'lastname', '25%');
		$this->addColumn($this->lng->txt('firstname'), 'firstname', '25%');

		$this->setDefaultOrderField('ranking');
		$this->setDefaultOrderDirection('desc');
		
    	$this->enable('hits');
    	$this->enable('sort');
	}
	
	/**
	 * Should this field be sorted numeric?
	 *
	 * @access	public
	 * @param	string
	 * @return	boolean	numeric ordering
	 */
	public function numericOrdering($a_field)
	{
		switch($a_field)
		{
			case 'ranking':
				return true;
			
			default:
				return false;
		}
	}
}