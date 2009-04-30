<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/** 
* 
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesMail
*/
include_once('Services/Table/classes/class.ilTable2GUI.php');


class ilMailSearchCoursesTableGUI extends ilTable2GUI
{
	protected $lng = null;
	protected $ctrl;
	protected $parentObject;
	protected $mode;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object	parent object
	 * @param string	type; valid values are 'crs' for courses and
	 *			'grp' for groups
	 */
	public function __construct($a_parent_obj, $type='crs')
	{
	 	global $lng,$ilCtrl, $ilUser, $lng;
		parent::__construct($a_parent_obj);
		$lng->loadLanguageModule('crs');
		
		$mode = array();
		
		if ($type == "crs")
		{
			$mode["short"] = "crs";
			$mode["long"] = "course";
			$mode["checkbox"] = "search_crs";
			$mode["tableprefix"] = "crstable";
			$mode["lng_mail"] = $lng->txt("mail_my_courses");
			$mode["view"] = "mycourses";
		}
		else if ($type == "grp")
		{
			$mode["short"] = "grp";
			$mode["long"] = "group";
			$mode["checkbox"] = "search_grp";
			$mode["tableprefix"] = "grptable";
			$mode["lng_mail"] = $lng->txt("mail_my_groups");
			$mode["view"] = "mygroups";
		}
				
		//$this->courseIds = $crs_ids;
		$this->parentObject = $a_parent_obj;
		$this->mode = $mode;
		
		$ilCtrl->setParameter($a_parent_obj, 'view', $mode['view']);
		if ($_GET['ref'] != '')
			$ilCtrl->setParameter($a_parent_obj, 'ref', $_GET['ref']);
		if (is_array($_POST[$mode["checkbox"]]))
			$ilCtrl->setParameter($a_parent_obj, $mode["checkbox"], implode(',', $_POST[$mode["checkbox"]]));

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$ilCtrl->clearParameters($a_parent_obj);

		$this->setPrefix($mode['tableprefix']);
		$this->setSelectAllCheckbox($mode["checkbox"].'[]');
		$this->setRowTemplate('tpl.mail_search_courses_row.html', 'Services/Contact');

		// setup columns
		$this->addColumn('', 'select', '1%', true);
		$this->addColumn($mode["lng_mail"], 'CRS_COURSES', '30%');
		$this->addColumn($lng->txt('path'), 'CRS_COURSES_PATHS', '30%');
		$this->addColumn($lng->txt('crs_count_members'), 'CRS_NO_MEMBERS', '30%');
		
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
		if ($a_set['hidden_members'])
		{
			$this->tpl->setCurrentBlock('caption_asterisk');
			$this->tpl->touchBlock('caption_asterisk');
			$this->tpl->parseCurrentBlock();	
		}
		foreach ($a_set as $key => $value)
		{
			$this->tpl->setVariable(strtoupper($key), $value);
		}
		$this->tpl->setVariable('SHORT', $this->mode["short"]);
	}	
} 
?>
