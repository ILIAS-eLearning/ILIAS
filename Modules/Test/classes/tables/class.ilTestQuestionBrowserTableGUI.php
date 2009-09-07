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

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesGroup
*/

class ilTestQuestionBrowserTableGUI extends ilTable2GUI
{
	protected $writeAccess = false;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_write_access = false)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
	
		$this->setWriteAccess($a_write_access);
		
		$this->setFormName('questionbrowser');
		$this->setStyle('table', 'fullwidth');
		$this->addColumn('','f','1%');
		$this->addColumn($this->lng->txt("tst_question_title"),'title', '');
		$this->addColumn($this->lng->txt("description"),'description', '');
		$this->addColumn($this->lng->txt("tst_question_type"),'ttype', '');
		$this->addColumn($this->lng->txt("author"),'author', '');
		$this->addColumn($this->lng->txt("create_date"),'created', '');
		$this->addColumn($this->lng->txt("last_update"),'updated', '');
		$this->addColumn($this->lng->txt("qpl"),'qpl', '');
	
		if ($this->getWriteAccess())
		{
			$this->addMultiCommand('insertQuestions', $this->lng->txt('insert'));
		}
	
		$this->setPrefix('q_id');
		$this->setSelectAllCheckbox('q_id');
		
		$this->setRowTemplate("tpl.il_as_tst_question_browser_row.html", "Modules/Test");

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");
		
		$this->enable('sort');
		$this->enable('header');
		$this->enable('select_all');
		$this->setFilterCommand('filterAvailableQuestions');
		$this->setResetCommand('resetfilterAvailableQuestions');
		$this->initFilter();
	}

	/**
	* Init filter
	*/
	function initFilter()
	{
		global $lng, $rbacreview, $ilUser;
		
		// title
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["title"] = $ti->getValue();
		
		// description
		$ti = new ilTextInputGUI($lng->txt("description"), "description");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["description"] = $ti->getValue();
		
		// questiontype
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		include_once("./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php");
		$types = ilObjQuestionPool::_getQuestionTypes();
		$options = array();
		$options[""] = $lng->txt('filter_all_question_types');
		foreach ($types as $translation => $row)
		{
			$options[$row['type_tag']] = $translation;
		}

		$si = new ilSelectInputGUI($this->lng->txt("question_type"), "type");
		$si->setOptions($options);
		$this->addFilterItem($si);
		$si->readFromSession();
		$this->filter["type"] = $si->getValue();
		
		// author
		$ti = new ilTextInputGUI($lng->txt("author"), "author");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["author"] = $ti->getValue();
		
		// question pool
		$ti = new ilTextInputGUI($lng->txt("qpl"), "qpl");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["qpl"] = $ti->getValue();
		
	}

	/**
	 * fill row 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function fillRow($data)
	{
		global $ilUser,$ilAccess;

		$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
		$this->tpl->setVariable("QUESTION_TITLE", $data["title"]);
		$this->tpl->setVariable("QUESTION_COMMENT", $data["description"]);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$this->tpl->setVariable("QUESTION_TYPE", assQuestion::_getQuestionTypeName($data["type_tag"]));
		$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
		include_once "./classes/class.ilFormat.php";
		$this->tpl->setVariable("QUESTION_CREATED", ilDatePresentation::formatDate(new ilDate($data['created'],IL_CAL_UNIX)));
		$this->tpl->setVariable("QUESTION_UPDATED", ilDatePresentation::formatDate(new ilDate($data["tstamp"],IL_CAL_UNIX)));
		$this->tpl->setVariable("QUESTION_POOL", $data['qpl']);
	}
	
	public function setWriteAccess($value)
	{
		$this->writeAccess = $value;
	}
	
	public function getWriteAccess()
	{
		return $this->writeAccess;
	}
}
?>