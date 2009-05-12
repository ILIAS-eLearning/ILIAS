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
* TableGUI class for results by question
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesTest
* 
*/
class ilResultsByQuestionTableGUI extends ilTable2GUI
{

	function ilResultsByQuestionTableGUI($a_parent_obj, $a_parent_cmd = "")
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn($lng->txt("question_title"), "question_title", "35%");
		$this->addColumn($lng->txt("number_of_answers"), "number_of_answers", "15%");
		$this->addColumn($lng->txt("output"), "", "25%");
		$this->addColumn($lng->txt("file_uploads"), "", "25%");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.table_results_by_question_row.html", "Modules/Test");
		$this->setDefaultOrderField("question_title");
		$this->setDefaultOrderDirection("asc");
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		$this->tpl->setVariable("QUESTION_TITLE", $a_set[0]);
		$this->tpl->setVariable("NUMBER_OF_ANSWERS", $a_set[1]);
		$this->tpl->setVariable("PDF_EXPORT", $a_set[2]);
		$this->tpl->setVariable("FILE_UPLOADS", $a_set[3]);
	}

}
?>
