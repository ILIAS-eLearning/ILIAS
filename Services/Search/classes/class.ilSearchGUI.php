<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Class ilSearchGUI
*
* GUI class for 'simple' search
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilSearchBaseGUI.php';

class ilSearchGUI extends ilSearchBaseGUI
{

	/**
	* Constructor
	* @access public
	*/
	function ilSearchGUI()
	{
		parent::ilSearchBaseGUI();
	}

	/**
	* Control
	* @access public
	*/
	function &executeCommand()
	{
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = "showSearch";
				}

				$this->prepareOutput();
				$this->$cmd();
				break;
		}
		return true;
	}

	function showSearch()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.search.html','Services/Search');

		$this->tpl->setVariable("SEARCH_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_SEARCHTERM",$this->lng->txt("search_search_term"));
		$this->tpl->setVariable("TXT_AND",$this->lng->txt('search_all_words'));
		$this->tpl->setVariable("TXT_OR",$this->lng->txt('search_any_word'));
		$this->tpl->setVariable("BTN_SEARCH",$this->lng->txt('search'));

		// Check 'or' as default
		if($_POST['combination'] == 'and')
		{
			$this->tpl->setVariable("AND_CHECKED",'checked=checked');
		}
		else
		{
			$this->tpl->setVariable("OR_CHECKED",'checked=checked');
		}
		// Set old query string
		$this->tpl->setVariable("FORM_SEARCH_STR",ilUtil::prepareFormOutput($_POST['search_str'],true));


		return true;
	}

	function performSearch()
	{
		include_once 'Services/Search/classes/class.ilQueryParser.php';

		// Step 1: parse query string
		$query_parser = new ilQueryParser(ilUtil::stripSlashes($_POST['search_str']));
		$query_parser->setCombination($_POST['combination']);
		$query_parser->parse();

		if(!$query_parser->validate())
		{
			sendInfo($query_parser->getMessage());
			$this->showSearch();
			
			return false;
		}

		// Step 2: perform object search
		include_once 'Services/Search/classes/class.ilObjectSearch.php';

		$obj_search = new ilObjectSearch($query_parser);
		$result = $obj_search->performSearch();


		// Step 3: perform meta keyword search


		// Step 4: merge and validate results
		$result->filter();

		// Step 5: show search form 
		$this->showSearch();

		// Step 6: show results
		include_once 'Services/Search/classes/class.ilSearchResultPresentationGUI.php';

		$search_result_presentation = new ilSearchResultPresentationGUI($result);
		$this->tpl->setVariable("RESULTS",$search_result_presentation->showResults());

		return true;
	}

	function prepareOutput()
	{
		parent::prepareOutput();

		$this->tpl->setVariable("H_FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt('search'));

		$this->tpl->addBlockFile("TABS","tabs","tpl.tabs.html");

		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabactive");
		$this->tpl->setVariable("TAB_LINK",$this->ctrl->getLinkTarget($this));
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("search"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabinactive");
		$this->tpl->setVariable("TAB_LINK",$this->ctrl->getLinkTarget($this));
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("search"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabinactive");
		$this->tpl->setVariable("TAB_LINK",$this->ctrl->getLinkTargetByClass('ilsearchresultgui'));
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("search_search_results"));
		$this->tpl->parseCurrentBlock();
		
	}
}
?>
