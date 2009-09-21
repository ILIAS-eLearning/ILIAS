<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for evaluation of all users
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesTest
* 
*/
class ilEvaluationAllTableGUI extends ilTable2GUI
{
	protected $specialcolumns;
	protected $anonymity;

	public function __construct($a_parent_obj, $a_parent_cmd, $special_columns, $anonymity = false)
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->setSpecialColumns($special_columns);
		$this->setFormName('evaluation_all');
		$this->setStyle('table', 'fullwidth');
		$this->addColumn($lng->txt("name"), "name", "");
		$this->addColumn($lng->txt("login"), "login", "");
		$this->anonymity = $anonymity;
		if ($this->hasSpecialColumn('gender'))
		{
			$this->addColumn($lng->txt("gender"), "gender", "");
		}
		if ($this->hasSpecialColumn('email'))
		{
			$this->addColumn($lng->txt("email"), "email", "");
		}
		if ($this->hasSpecialColumn('institution'))
		{
			$this->addColumn($lng->txt("institution"), "institution", "");
		}
		if ($this->hasSpecialColumn('street'))
		{
			$this->addColumn($lng->txt("street"), "street", "");
		}
		if ($this->hasSpecialColumn('city'))
		{
			$this->addColumn($lng->txt("city"), "city", "");
		}
		if ($this->hasSpecialColumn('zipcode'))
		{
			$this->addColumn($lng->txt("zipcode"), "zipcode", "");
		}
		if ($this->hasSpecialColumn('country'))
		{
			$this->addColumn($lng->txt("country"), "country", "");
		}
		if ($this->hasSpecialColumn('department'))
		{
			$this->addColumn($lng->txt("department"), "department", "");
		}
		if ($this->hasSpecialColumn('matriculation'))
		{
			$this->addColumn($lng->txt("matriculation"), "matriculation", "");
		}
		$this->addColumn($lng->txt("tst_reached_points"), "reached", "");
		$this->addColumn($lng->txt("tst_mark"), "tst_mark", "");
		if ($this->hasSpecialColumn('ects_grade'))
		{
			$this->addColumn($lng->txt("ects_grade"), "ects_grade", "");
		}
		$this->addColumn($lng->txt("tst_answered_questions"), "answered", "");
		$this->addColumn($lng->txt("working_time"), "working_time", "");
		$this->addColumn($lng->txt("detailed_evaluation"), "details", "");

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.table_evaluation_all.html", "Modules/Test");
		$this->setDefaultOrderField("name");
		$this->setDefaultOrderDirection("asc");
		$this->enable('sort');
		$this->enable('header');

		$this->setFilterCommand('filterEvaluation');
		$this->setResetCommand('resetfilterEvaluation');
		$this->initFilter();
	}

	/**
	* Should this field be sorted numeric?
	*
	* @return	boolean		numeric ordering; default is false
	*/
	function numericOrdering($a_field)
	{
		switch ($a_field)
		{
			case 'name':
				if ($this->anonymity)
				{
					return true;
				}
				else
				{
					return false;
				}
				break;
			default:
				return false;
				break;
		}
	}
	
	/**
	* Init filter
	*/
	function initFilter()
	{
		global $lng, $rbacreview, $ilUser;
		
		// name
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ti = new ilTextInputGUI($lng->txt("name"), "name");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["name"] = $ti->getValue();
		
		// group
		$ti = new ilTextInputGUI($lng->txt("grp"), "group");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["group"] = $ti->getValue();
		
		// course
		$ti = new ilTextInputGUI($lng->txt("course"), "course");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["course"] = $ti->getValue();
		
		// passed tests
		include_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
		$si = new ilCheckboxInputGUI($this->lng->txt("passed_only"), "passed_only");
//		$si->setOptionTitle();
		$this->addFilterItem($si);
		$si->readFromSession();
		$this->filter["passedonly"] = $si->getValue();
	}

	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($data)
	{
		$this->tpl->setVariable("NAME", $data['name']);
		$this->tpl->setVariable("LOGIN", $data['login']);
		if ($this->hasSpecialColumn('gender'))
		{
			$this->tpl->setCurrentBlock('gender');
			$this->tpl->setVariable("GENDER", $this->lng->txt('gender_' . $data['gender']));
			$this->tpl->parseCurrentBlock();
		}
		if ($this->hasSpecialColumn('email'))
		{
			$this->tpl->setCurrentBlock('email');
			$this->tpl->setVariable("EMAIL", strlen($data['email']) ? $data['email'] : '&nbsp;');
			$this->tpl->parseCurrentBlock();
		}
		if ($this->hasSpecialColumn('institution'))
		{
			$this->tpl->setCurrentBlock('institution');
			$this->tpl->setVariable("INSTITUTION", strlen($data['institution']) ? $data['institution'] : '&nbsp;');
			$this->tpl->parseCurrentBlock();
		}
		if ($this->hasSpecialColumn('street'))
		{
			$this->tpl->setCurrentBlock('street');
			$this->tpl->setVariable("STREET", strlen($data['street']) ? $data['street'] : '&nbsp;');
			$this->tpl->parseCurrentBlock();
		}
		if ($this->hasSpecialColumn('city'))
		{
			$this->tpl->setCurrentBlock('city');
			$this->tpl->setVariable("CITY", strlen($data['city']) ? $data['city'] : '&nbsp;');
			$this->tpl->parseCurrentBlock();
		}
		if ($this->hasSpecialColumn('zipcode'))
		{
			$this->tpl->setCurrentBlock('zipcode');
			$this->tpl->setVariable("ZIPCODE", strlen($data['zipcode']) ? $data['zipcode'] : '&nbsp;');
			$this->tpl->parseCurrentBlock();
		}
		if ($this->hasSpecialColumn('country'))
		{
			$this->tpl->setCurrentBlock('country');
			$this->tpl->setVariable("COUNTRY", strlen($data['country']) ? $data['country'] : '&nbsp;');
			$this->tpl->parseCurrentBlock();
		}
		if ($this->hasSpecialColumn('department'))
		{
			$this->tpl->setCurrentBlock('department');
			$this->tpl->setVariable("DEPARTMENT", strlen($data['department']) ? $data['department'] : '&nbsp;');
			$this->tpl->parseCurrentBlock();
		}
		if ($this->hasSpecialColumn('matriculation'))
		{
			$this->tpl->setCurrentBlock('matriculation');
			$this->tpl->setVariable("MATRICULATION", strlen($data['matriculation']) ? $data['matriculation'] : '&nbsp;');
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("REACHED", $data['reached']);
		$this->tpl->setVariable("MARK", $data['mark']);
		if ($this->hasSpecialColumn('ects_grade'))
		{
			$this->tpl->setCurrentBlock('ects_grade');
			$this->tpl->setVariable("ECTS_GRADE", $data['ects_grade']);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("ANSWERED", $data['answered']);
		$this->tpl->setVariable("WORKING_TIME", $data['working_time']);
		$this->tpl->setVariable("DETAILED", $data['details']);
	}
	
	public function hasSpecialColumn($name)
	{
		return in_array($name, $this->specialcolumns);
	}
	
	public function setSpecialColumns($arr)
	{
		$this->specialcolumns = $arr;
	}

}
?>
