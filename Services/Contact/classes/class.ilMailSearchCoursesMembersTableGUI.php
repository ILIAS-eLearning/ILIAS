<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* 
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesMail
*/
include_once('Services/Table/classes/class.ilTable2GUI.php');


class ilMailSearchCoursesMembersTableGUI extends ilTable2GUI
{
	protected $lng = null;
	protected $ctrl;
	protected $parentObject;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object	parent object
	 * @param string	type; valid values are 'crs' for courses and
	 *			and 'grp' for groups
	 * 
	 */
	public function __construct($a_parent_obj, $type = 'crs')
	{
	 	global $lng,$ilCtrl, $ilUser, $lng;
		parent::__construct($a_parent_obj);
		$lng->loadLanguageModule('crs');
		$this->parentObject = $a_parent_obj;
		$mode = array();
		if ($type == 'crs')
		{
			$mode["checkbox"] = 'search_crs';
			$mode["short"] = 'crs';
			$mode["long"] = 'course';
			$mode["lng_type"] = $lng->txt('course');
			$mode["view"] = "crs_members";
			$mode["tableprefix"] = "crstable_members";
		}
		else if ($type == 'grp')
		{
			$mode["checkbox"] = 'search_grp';
			$mode["short"] = 'grp';
			$mode["long"] = 'group';
			$mode["lng_type"] = $lng->txt('group');
			$mode["view"] = "grp_members";
			$mode["tableprefix"] = "grptable_members";
		}		
		$ilCtrl->setParameter($a_parent_obj, 'view', $mode['view']);
		if ($_GET['ref'] != '')
			$ilCtrl->setParameter($a_parent_obj, 'ref', $_GET['ref']);
		if (is_array($_POST[$mode["checkbox"]]))
			$ilCtrl->setParameter($a_parent_obj, $mode["checkbox"], implode(',', $_POST[$mode["checkbox"]]));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$ilCtrl->clearParameters($a_parent_obj);

		$this->setPrefix($mode['tableprefix']);
		$this->setSelectAllCheckbox('search_members[]');
		$this->setRowTemplate('tpl.mail_search_courses_members_row.html', 'Services/Contact');

		// setup columns
		$this->addColumn('', 'select', '1%', true);
		$this->addColumn($lng->txt('login'), 'USR_LOGIN', '24%');
		$this->addColumn($lng->txt('name'), 'USR_NAME', '24%');
		$this->addColumn($lng->txt($mode['long']), 'CRS_GRP', '24%');
		$this->addColumn($lng->txt('mail_in_addressbook'), 'USR_IN_ADDRESSBOOK', '24%');
		
		$this->addCommandButton('cancel', $lng->txt('cancel'));
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
		foreach ($a_set as $key => $value)
		{
			$this->tpl->setVariable(strtoupper($key), $value);
		}
	}	
} 
?>
