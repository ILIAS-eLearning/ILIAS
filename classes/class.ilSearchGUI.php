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

require_once "./classes/class.ilSearch.php";
require_once "./classes/class.ilTableGUI.php";

/**
* search
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version Id: $Id$
* 
* @package application
*/
class ilSearchGUI
{
	/**
	* ilias object
	* @var object DB
	* @access public
	*/	
	var $ilias;
	
	/**
	* search object
	* @var object 
	* @access public
	*/	
	var $search;
	var $tpl;
	var $lng;
	var $tree;

	// TABLE VARS
	var $res_type;
	var $offset;
	var $sort_by;
	var $sort_order;

	var $message;

	/**
	* Constructor
	* @access	public
	*/
	function ilSearchGUI($a_user_id = 0)
	{
		global $ilias,$tpl,$lng;

		// DEFINE SOME CONSTANTS
		define("RESULT_LIMIT",2);

		$_SESSION["viewmode"] = "flat";
		
		// Initiate variables
		$this->ilias	=& $ilias;
		$this->tpl		=& $tpl;
		$this->lng		=& $lng;

		$this->res_type = $_GET["res_type"];
		$this->offset   = $_GET["offset"];
		
		// INITIATE SEARCH OBJECT
		$this->search =& new ilSearch($a_user_id);

		$this->tree = new ilTree(1);

		// SET BACK URL
		$this->__setReferer();

		if(isset($_POST["cmd"]["search"]))
		{
			$this->search();
		}
		if(isset($_POST["cmd"]["search_res"]))
		{
			$this->searchInResult();
		}
		if(!isset($_POST["cmd"]))
		{
			$this->__show();
		}
	}

	function getReferer()
	{
		return $_SESSION["search_referer"];
	}

	function search($a_search_type = 'new')
	{
		$this->search->setSearchString($_POST["search_str"]);
		$this->search->setCombination($_POST["combination"]);
		$this->search->setSearchFor($_POST["search_for"]);
		$this->search->setSearchIn($_POST["search_in"]);
		$this->search->setSearchType($a_search_type);
		if($this->search->validate($this->message))
		{
			$this->search->performSearch();
		}
		// TEMP MESSAGE
		if(in_array("grp",$_POST["search_for"]))
		{
			$this->message .= "Search in groups NOT IMPLEMENTED YET<br />";
		}
		$this->__show();
	}

	function searchInResult()
	{
		$this->search("result");
	}

	// PRIVATE METHODS
	function __showResult()
	{
		if(!$this->search->getResults())
		{
			$this->message .= "Nothing found<br />";
			return false;
		}
		if($this->search->getResultByType("usr") and ( !$this->res_type or $this->res_type == 'usr'))
		{
			$this->__showUserResult();
		}
		if($this->search->getResultByType("dbk") and ( !$this->res_type or $this->res_type == 'dbk'))
		{
			$this->__showDigiLibResult();
		}
		if($this->search->getResultByType("lm") and ( !$this->res_type or $this->res_type == 'lm'))
		{
			$this->__showLearningModuleResult();
		}
	}
	function __showUserResult()
	{
		$tbl = new ilTableGUI(0,false);

		$tbl->setTitle("User search","icon_usr_b.gif","User search");
		$tbl->setHeaderNames(array($this->lng->txt("login"),$this->lng->txt("firstname")
								   ,$this->lng->txt("lastname"),"Anzeigen"));
		$tbl->setHeaderVars(array("login","firstname","lastname",""),array("res_type" => "usr"));

		$tbl->setColumnWidth(array("25%","25%","25%","25%"));

		$tbl->setData($this->__formatUserResult($this->search->getResultByType("usr")));

		// control
		$tbl->setOrderColumn($this->sort_by);
		$tbl->setOrderDirection($this->sort_order);
		$tbl->setLimit(RESULT_LIMIT);
		$tbl->setOffset($this->offset);
		$tbl->setMaxCount(count($this->search->getResultByType("usr")));

		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// render table
		$this->tpl->setVariable("USER",$tbl->render());
		unset($tbl);
	}
		
	function __showDigiLibResult()
	{
		$tbl = new ilTableGUI(0,false);

		$tbl->setTitle("Digital Library search","icon_dbk_b.gif","Digital Library search");
		$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("context"),
								   "Anzeigen"));
		$tbl->setHeaderVars(array("title","context",""),array("res_type" => "dbk"));

		$tbl->setColumnWidth(array("50%","25%","25%"));

		$tbl->setData($this->__formatDigiLibResult($this->search->getResultByType("dbk")));

		// control
		$tbl->setOrderColumn($this->sort_by);
		$tbl->setOrderDirection($this->sort_order);
		$tbl->setLimit(RESULT_LIMIT);
		$tbl->setOffset($this->offset);
		$tbl->setMaxCount(count($this->search->getResultByType("dbk")));

		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// render table
		$this->tpl->setVariable("DIGILIB",$tbl->render());
		unset($tbl);
	}

	function __showLearningModuleResult()
	{
		$tbl = new ilTableGUI(0,false);

		$tbl->setTitle("Content Object search","icon_lm_b.gif","Content Object search");
		$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("context"),
								   "Anzeigen"));
		$tbl->setHeaderVars(array("title","context",""),array("res_type" => "lm"));

		$tbl->setColumnWidth(array("50%","25%","25%"));

		$tbl->setData($this->__formatDigiLibResult($this->search->getResultByType("lm")));

		// control
		$tbl->setOrderColumn($this->sort_by);
		$tbl->setOrderDirection($this->sort_order);
		$tbl->setLimit(RESULT_LIMIT);
		$tbl->setOffset($this->offset);
		$tbl->setMaxCount(count($this->search->getResultByType("lm")));

		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		// render table
		$this->tpl->setVariable("LEARNING_MODULE",$tbl->render());
		unset($tbl);
	}

	function __show()
	{
		// SHOW SEARCH PAGE
		$this->tpl->addBlockFile("CONTENT","content","tpl.search.html");
		infoPanel();
		$this->tpl->setVariable("SEARCH_ACTION","./search.php");
		$this->tpl->setVariable("TXT_SEARCH",$this->lng->txt("search"));
		
		if($this->message)
		{
			sendInfo($this->message);
		}

		$this->tpl->setVariable("FORM_SEARCH_STR",$this->search->getSearchString());
		$this->tpl->setVariable("OR_CHECKED",$this->search->getCombination() == "or" ? "checked=\"checked\"" : "");
		$this->tpl->setVariable("AND_CHECKED",$this->search->getCombination() == "and" ? "checked=\"checked\"" : "");

		$search_for = $this->search->getSearchFor();
		$this->tpl->setVariable("USR_CHECKED",in_array("usr",$search_for) ? "checked=\"checked\"" : "");
		$this->tpl->setVariable("GRP_CHECKED",in_array("grp",$search_for) ? "checked=\"checked\"" : "");
		$this->tpl->setVariable("LM_CHECKED",in_array("lm",$search_for) ? "checked=\"checked\"" : "");
		$this->tpl->setVariable("DBK_CHECKED",in_array("dbk",$search_for) ? "checked=\"checked\"" : "");

		$search_in = array("meta" => $this->lng->txt("meta"),"content" => $this->lng->txt("content"));

		$this->tpl->setVariable("LM_SELECT",ilUtil::formSelect("","search_in[lm]",$search_in,false,true));
		$this->tpl->setVariable("DBK_SELECT",ilUtil::formSelect("","search_in[dbk]",$search_in,false,true));
		
		$this->__showLocator();
		$this->__showTabs();
		$this->__showResult();
		
	}

	function __setReferer()
	{
		$_SESSION["referer"] = stristr($_SESSION["referer"],"adm_object.php") === false 
			? $_SESSION["referer"]
			: "adm_index.php";
		$_SESSION["search_referer"] = stristr($_SESSION["referer"],"search.php") === false 
			? $_SESSION["referer"] 
			: $_SESSION["search_referer"];
		
		return true;
	}

	function __showLocator()
	{
		$this->tpl->addBlockFile("LOCATOR","locator","tpl.locator.html");

		$this->tpl->setCurrentBlock("locator_separator");
		$this->tpl->setVariable("LINK_ITEM","./search.php");
		$this->tpl->setVariable("ITEM",$this->lng->txt("mail_search_word"));
		$this->tpl->parseCurrentBlock();
	}
		
	function __showTabs()
	{
		$this->tpl->addBlockFile("TABS","tabs","tpl.tabs.html");

		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabinactive");
		$this->tpl->setVariable("TAB_LINK",$this->getReferer());
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("back"));
		$this->tpl->parseCurrentBlock();

		return true;
	}
	function __formatLink($res_data)
	{
		if(is_array($res_data))
		{
			for($ii = 0; $ii < count($res_data); ++$ii)
			{
				if($res_data[$ii]["link"])
				{
					$res_data[$ii]["link"] = "<a href=\"".$res_data[$ii]["link"]."\" target=\"".$res_data[$ii]["target"]."\".>Show";
				}
				unset($res_data[$ii]["target"]);
			}
		}
		return $res_data;
	}

	function __formatUserResult($a_res)
	{
		if(!is_array($a_res))
		{
			return array();
		}
		include_once "./classes/class.ilObjectFactory.php";

		$counter = 0;
		foreach($a_res as $user)
		{
			$tmp_obj = ilObjectFactory::getInstanceByObjId($user["id"]);
			
			$f_result[$counter]["login"]		= $tmp_obj->getLogin();
			$f_result[$counter]["firstname"]	= $tmp_obj->getFirstname();
			$f_result[$counter]["lastname"]		= $tmp_obj->getLastname();
			$f_result[$counter]["link"]			= $user["link"];
			$f_result[$counter]["target"]       = $user["target"];
			
			unset($tmp_obj);
			++$counter;
		}
		return $this->__sortAndLimit($this->__formatLink($f_result));
	}
	
	function __formatDigiLibResult($a_res)
	{
		if(!is_array($a_res))
		{
			return array();
		}
		include_once "./classes/class.ilObjectFactory.php";

		$counter = 0;
		foreach($a_res as $book)
		{
			$tmp_obj = ilObjectFactory::getInstanceByRefId($book["id"]);
			
			$f_result[$counter]["title"]		= $tmp_obj->getTitle();
			$f_result[$counter]["context"]		= $this->__getContextPath($book["id"]);
			$f_result[$counter]["link"]			= $book["link"];
			$f_result[$counter]["target"]       = $book["target"];
			
			unset($tmp_obj);
			++$counter;
		}
		return $this->__sortAndLimit($this->__formatLink($f_result));
	}

	function __formatLearningModuleResult($a_res)
	{
		if(!is_array($a_res))
		{
			return array();
		}
		include_once "./classes/class.ilObjectFactory.php";

		$counter = 0;
		foreach($a_res as $book)
		{
			$tmp_obj = ilObjectFactory::getInstanceByRefId($book["id"]);
			
			$f_result[$counter]["title"]		= $tmp_obj->getTitle();
			$f_result[$counter]["context"]		= $this->__getContextPath($book["id"]);
			$f_result[$counter]["link"]			= $book["link"];
			$f_result[$counter]["target"]       = $book["target"];
			
			unset($tmp_obj);
			++$counter;
		}
		return $this->__sortAndLimit($this->__formatLink($f_result));
	}

	function __sortAndLimit($a_res)
	{
		include_once "./include/inc.sort.php";

		$a_res = sortArray($a_res,$this->sort_by,$this->sort_order);
		return array_slice($a_res,$this->offset,RESULT_LIMIT);
	}

	function __getContextPath($a_endnode_id, $a_startnode_id = 1)
	{
		$path = "";

		$tmpPath = $this->tree->getPathFull($a_endnode_id, $a_startnode_id);

		// count -1, to exclude the learning module itself
		for ($i = 1; $i < (count($tmpPath) - 1); $i++)
		{
			if ($path != "")
			{
				$path .= " > ";
			}

			$path .= $tmpPath[$i]["title"];
		}
		return $path;
	}
		
} // END class.Search
?>