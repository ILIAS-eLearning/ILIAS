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
		define("RESULT_LIMIT",10);

		
		// Initiate variables
		$this->ilias	=& $ilias;
		$this->tpl		=& $tpl;
		$this->lng		=& $lng;
		$this->lng->loadLanguageModule("search");


		$this->res_type   = $_GET["res_type"];
		$this->offset	  = $_GET["offset"];
		$this->sort_by	  = $_GET["sort_by"];
		$this->sort_order = $_GET["sort_order"];
		
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
		$this->__show();
	}

	function searchInResult()
	{
		$this->search("result");
	}

	// PRIVATE METHODS
	function __showResult()
	{
		if(!$this->search->getNumberOfResults() && $this->search->getSearchFor())
		{
			$this->message .= $this->lng->txt("search_no_match")."<br />";
			return false;
		}
		if($this->search->getResultByType("usr") and ( !$this->res_type or $this->res_type == 'usr'))
		{
			$this->__showResultTable("usr");
		}
		if($res = $this->search->getResultByType("dbk") and ( !$this->res_type or $this->res_type == 'dbk'))
		{
			if(count($res["meta"]))
			{
				$this->__showResultTable("dbk","meta");
			}
			if(count($res["content"]))
			{
				$this->__showResultTable("dbk","content");
			}
		}
		if($res = $this->search->getResultByType("lm") and ( !$this->res_type or $this->res_type == 'lm'))
		{
			if(count($res["meta"]))
			{
				$this->__showResultTable("lm","meta");
			}
			if(count($res["content"]))
			{
				$this->__showResultTable("lm","content");
			}
		}
	}
		
	function __showResultTable($a_type,$a_search_in_type = '')
	{
		// FOR ALL TYPES
		$tbl = new ilTableGUI(0,false);

		// SWITCH 'usr','dbk','lm'
		switch($a_type)
		{
			case "usr":
				$tbl->setTitle($this->lng->txt("search_user"),"icon_usr_b.gif",$this->lng->txt("search_user"));
				$tbl->setHeaderNames(array($this->lng->txt("login"),$this->lng->txt("firstname")
										   ,$this->lng->txt("lastname"),$this->lng->txt("search_show_result")));
				$tbl->setHeaderVars(array("login","firstname","lastname",""),array("res_type" => "usr"));
				$tbl->setColumnWidth(array("25%","25%","25%","25%"));
				$tbl->setData(array_values($this->__formatUserResult($this->search->getResultByType("usr"))));
				break;
				
			case "dbk":
				// SWITCH 'meta','content'
				switch($a_search_in_type)
				{
					case "meta":
						$tbl->setTitle($this->lng->txt("search_dbk_meta"),"icon_dbk_b.gif",$this->lng->txt("search_dbk_meta"));
						$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("context"),
												   $this->lng->txt("search_show_result")));
						$tbl->setHeaderVars(array("title","context",""),array("res_type" => "dbk"));
						
						$tbl->setColumnWidth(array("50%","30%","20%"));
						
						$tmp_res = $this->search->getResultByType("dbk");
						$tbl->setData($this->__formatDigiLibResult($tmp_res["meta"],"meta"));
						break;

					case "content":
						$tbl->setTitle($this->lng->txt("search_dbk_content"),"icon_dbk_b.gif",$this->lng->txt("search_dbk_content"));
						$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("page"),$this->lng->txt("context"),
												   $this->lng->txt("search_show_result")));
						$tbl->setHeaderVars(array("title","page","context",""),array("res_type" => "dbk"));
						
						$tbl->setColumnWidth(array("30%","20%","30%","20%"));
						
						$tmp_res = $this->search->getResultByType("dbk");
						$tbl->setData($this->__formatDigiLibResult($tmp_res["content"],"content"));
						break;
				}
				break;
			
			case "lm":

				// SWITCH 'meta','content'
				switch($a_search_in_type)
				{
					case "meta":
						$tbl->setTitle($this->lng->txt("search_lm_meta"),"icon_lm_b.gif",$this->lng->txt("search_lm_meta"));
						$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("context"),
												   $this->lng->txt("search_show_result")));
						$tbl->setHeaderVars(array("title","context",""),array("res_type" => "lm"));
						
						$tbl->setColumnWidth(array("50%","30%","20%"));
						
						$tmp_res = $this->search->getResultByType("lm");
						$tbl->setData($this->__formatLearningModuleResult($tmp_res["meta"],"meta"));
						break;

					case "content":
						$tbl->setTitle($this->lng->txt("search_lm_content"),"icon_lm_b.gif",$this->lng->txt("search_lm_content"));
						$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("page"),$this->lng->txt("context"),
												   $this->lng->txt("search_show_result")));
						$tbl->setHeaderVars(array("title","page","context",""),array("res_type" => "lm"));
						
						$tbl->setColumnWidth(array("30%","20%","30%","20%"));
						
						$tmp_res = $this->search->getResultByType("lm");
						$tbl->setData($this->__formatLearningModuleResult($tmp_res["content"],"content"));
						break;
				}
				break;
		}
		$tbl->setOrderColumn($this->sort_by);
		$tbl->setOrderDirection($this->sort_order);
		$tbl->setLimit(RESULT_LIMIT);
		$tbl->setOffset($this->offset);
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$this->tpl->setVariable(strtoupper($a_type),$tbl->render());
		unset($tbl);
	}

	function __show()
	{
		// SHOW SEARCH PAGE
		$this->tpl->addBlockFile("CONTENT","content","tpl.search.html");
		infoPanel();
		$this->tpl->setVariable("SEARCH_ACTION","./search.php");
		$this->tpl->setVariable("TXT_SEARCH",$this->lng->txt("search"));
		
		$this->__showLocator();
		$this->__showTabs();
		$this->__showResult();

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

		$search_in = array("meta" => $this->lng->txt("search_meta"),"content" => $this->lng->txt("search_content"));

		$this->tpl->setVariable("LM_SELECT",ilUtil::formSelect($this->search->getSearchInByType("lm")
															   ,"search_in[lm]",$search_in,false,true));
		$this->tpl->setVariable("DBK_SELECT",ilUtil::formSelect($this->search->getSearchInByType("dbk")
																,"search_in[dbk]",$search_in,false,true));
		// TABLE TEXT
		$this->tpl->setVariable("TXT_USER",$this->lng->txt("obj_usr"));
		$this->tpl->setVariable("TXT_GROUPS",$this->lng->txt("obj_grp"));
		$this->tpl->setVariable("TXT_LM",$this->lng->txt("obj_lm"));
		$this->tpl->setVariable("TXT_DBK",$this->lng->txt("obj_dbk"));


		// TEXT VARIABLES
		$this->tpl->setVariable("TXT_SEARCHTERM",$this->lng->txt("search_search_term"));
		$this->tpl->setVariable("TXT_CONCATENATION",$this->lng->txt("search_concatenation"));
		$this->tpl->setVariable("TXT_SEARCH_FOR",$this->lng->txt("search_search_for"));

		// BUTTONS
		$this->tpl->setVariable("BTN_SEARCH",$this->lng->txt("search"));
		$this->tpl->setVariable("BTN_SEARCH_RESULT",$this->lng->txt("search_in_result"));
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
		$this->tpl->addBlockFile("STATUSLINE","locator","tpl.locator.html");

		$this->tpl->setCurrentBlock("locator_separator");
		$this->tpl->setVariable("LINK_ITEM","./search.php");
		$this->tpl->setVariable("ITEM",$this->lng->txt("mail_search_word"));
		$this->tpl->parseCurrentBlock();
	}
		
	function __showTabs()
	{
		if($this->res_type)
		{
			$this->tpl->addBlockFile("TABS","tabs","tpl.tabs.html");
			
			$this->tpl->setCurrentBlock("tab");
			$this->tpl->setVariable("TAB_TYPE","tabinactive");
			$this->tpl->setVariable("TAB_LINK","search.php");
			$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("search_all_results"));
			$this->tpl->parseCurrentBlock();
		}
		return true;
	}
	function __formatLink($a_link,$a_target)
	{
		return "<a href=\"".$a_link."\" target=\"".$a_target."\".>".$this->lng->txt("search_show_result");
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
			
			$f_result[$counter][]	= $tmp_obj->getLogin();
			$f_result[$counter][]	= $tmp_obj->getFirstname();
			$f_result[$counter][]	= $tmp_obj->getLastname();
			$f_result[$counter][]	= $this->__formatLink($user["link"],$user["target"]);

			unset($tmp_obj);
			++$counter;
		}
		return $f_result;
	}

	function __formatDigiLibResult($a_res,$a_search_in)
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
			switch($a_search_in)
			{
				case "meta":
					$f_result[$counter][]		= $tmp_obj->getTitle();
					$f_result[$counter][]		= $this->__getContextPath($book["id"]);
					break;
					
				case "content":
					$f_result[$counter][]		= $tmp_obj->getTitle();

					// GET INSTANCE OF PAGE OBJECT
					include_once ("content/classes/class.ilLMObjectFactory.php");

					$tmp_page_obj = ilLMObjectFactory::getInstance($tmp_obj, $book["page_id"]);
					$tmp_page_obj->setLMId($book["id"]);

					$f_result[$counter][] = $tmp_page_obj->getPresentationTitle();
					$f_result[$counter][]		= $this->__getContextPath($book["id"]);

					unset($tmp_page_obj);
					break;
			}
			$f_result[$counter][] = $this->__formatLink($book["link"],$book["target"]);
			unset($tmp_obj);
			++$counter;
		}

		return $f_result;

	}

	function __formatLearningModuleResult($a_res,$a_search_in)
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
			switch($a_search_in)
			{
				case "meta":
					$f_result[$counter][]		= $tmp_obj->getTitle();
					$f_result[$counter][]		= $this->__getContextPath($book["id"]);
					break;
					
				case "content":
					$f_result[$counter][]		= $tmp_obj->getTitle();

					// GET INSTANCE OF PAGE OBJECT
					include_once ("content/classes/class.ilLMObjectFactory.php");

					$tmp_page_obj = ilLMObjectFactory::getInstance($tmp_obj, $book["page_id"]);
					$tmp_page_obj->setLMId($book["id"]);

					$f_result[$counter][] = $tmp_page_obj->getPresentationTitle();
					$f_result[$counter][] = $this->__getContextPath($book["id"]);

					unset($tmp_page_obj);
					break;
			}
			$f_result[$counter][] = $this->__formatLink($book["link"],$book["target"]);
			unset($tmp_obj);
			++$counter;
		}
		return $f_result;
	}

	function __getContextPath($a_endnode_id, $a_startnode_id = 1)
	{
		$path = "";
		
		if(!$this->tree->isInTree($a_startnode_id) or !$this->tree->isInTree($a_endnode_id))
		{
			return '';
		}
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