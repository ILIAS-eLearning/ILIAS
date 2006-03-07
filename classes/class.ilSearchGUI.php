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
require_once "./classes/class.ilSearchGUIExplorer.php";
require_once "./classes/class.ilSearchFolder.php";

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

	var $user_id;
	var $folder_id;
	var $folder_obj;
	var $viewmode;

	var $message;

	/**
	* Constructor
	* @access	public
	*/
	function ilSearchGUI($a_user_id = 0)
	{
		global $ilias,$tpl,$lng;

		// DEFINE SOME CONSTANTS
		#define("RESULT_LIMIT",$ilias->account->getPref("hits_per_page") ? $ilias->account->getPref("hits_per_page") : 3);
		define("RESULT_LIMIT",10);
		// Initiate variables
		$this->ilias	=& $ilias;
		$this->tpl		=& $tpl;
		$this->lng		=& $lng;
		$this->lng->loadLanguageModule("search");


		$this->folder_id  = $_GET["folder_id"];
		$this->res_type   = $_GET["res_type"];
		$this->offset	  = $_GET["offset"];
		$this->sort_by	  = $_GET["sort_by"];
		$this->sort_order = $_GET["sort_order"];
		
		$this->setFolderId($_GET["folder_id"]);
		$this->setViewmode($_GET["viewmode"]);

		$this->setUserId($a_user_id);

		// INITIATE SEARCH OBJECT
		$this->search =& new ilSearch($a_user_id,true);
		$this->tree = new ilTree(1);

		// INITIATE SEARCH FOLDER OBJECT
		$this->folder_obj =& new ilSearchFolder($a_user_id);

		$this->performAction();
	}

	function performAction()
	{
		if(isset($_POST["cmd"]["search"]))
		{
			$this->search();
			return true;
		}
		if(isset($_POST["cmd"]["search_res"]))
		{
			$this->searchInResult();
			return true;
		}
		if(!isset($_POST["cmd"]))
		{
			$this->__show();
			return true;
		}

		// cmd is dbk_content or dbk_meta or lm_content or lm_meta or grp_ or usr
		$this->__saveResult();
		$this->__show();

		return true;
	}

	// SET/GET
	function setUserId($a_user_id)
	{
		$this->user_id = $a_user_id;
	}
	function getUserId()
	{
		return $this->user_id;
	}

	function setFolderId($a_folder_id)
	{
		$this->folder_id = $a_folder_id ? $a_folder_id : 1;
	}
	function getFolderId()
	{
		return $this->folder_id;
	}
	function setViewmode($a_viewmode)
	{
		switch($a_viewmode)
		{
			case "flat":
				$this->viewmode = "flat";
				$_SESSION["s_viewmode"] = "flat";
				break;

			case "tree":
				$this->viewmode = "tree";
				$_SESSION["s_viewmode"] = "tree";
				break;
				
			default:
				$this->viewmode = $_SESSION["s_viewmode"] ? $_SESSION["s_viewmode"] : "flat";
				break;
		}
	}
	function getViewmode()
	{
		return $this->viewmode;
	}

	function search($a_search_type = 'new')
	{
		global $ilBench;

		$ilBench->start("Search", "search");
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
		$this->offset = 0;
		$this->__show();
		$ilBench->stop("Search", "search");
	}

	function searchInResult()
	{
		$this->search("result");
	}

	// PRIVATE METHODS
	function __getFolderSelect($a_type,$a_search_in_type = '')
	{
		$subtree = $this->folder_obj->getSubtree();

		$options[0] = $this->lng->txt("search_select_one_folder_select");
		$options[$this->folder_obj->getRootId()] = $this->lng->txt("search_save_as_select")." ".$this->lng->txt("search_search_results");
		
		foreach($subtree as $node)
		{
			if($node["obj_id"] == $this->folder_obj->getRootId())
			{
				continue;
			}
			// CREATE PREFIX
			$prefix = $this->lng->txt("search_save_as_select");
			for($i = 1; $i < $node["depth"];++$i)
			{
				$prefix .= "&nbsp;&nbsp;";
			}
			$options[$node["obj_id"]] = $prefix.$node["title"];
		}
		return $select_str = ilUtil::formSelect(0,$a_type."_".$a_search_in_type,$options,false,true);
	}

	function __showResult()
	{
		global $ilBench;

		$ilBench->start("Search", "showResult");

		if(!$this->search->getNumberOfResults() && $this->search->getSearchFor())
		{
			$this->message .= $this->lng->txt("search_no_match")."<br />";
			return false;
		}
		if($this->search->getResultByType("usr") and ( !$this->res_type or $this->res_type == 'usr'))
		{
			$this->__showResultTable("usr");
		}
		if($this->search->getResultByType("grp") and ( !$this->res_type or $this->res_type == 'grp'))
		{
			$this->__showResultTable("grp");
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
				$ilBench->start("Search", "showResultTable_lm_content");
				$this->__showResultTable("lm","content");$ilBench->start("Search", "showResultTable_lm_content");
				$ilBench->stop("Search", "showResultTable_lm_content");
			}
		}

		$ilBench->stop("Search", "showResult");

	}

	function __addAction(&$tpl,$a_type,$a_search_in_type = '')
	{
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION","search.php");
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_select");
		$tpl->setVariable("SELECT_ACTION",$this->__getFolderSelect($a_type,$a_search_in_type));
		$tpl->setVariable("BTN_NAME",$a_type."_".$a_search_in_type);
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("ok"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->setVariable("COLUMN_COUNTS",5);
		//$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();
	}

	function __showResultTable($a_type, $a_search_in_type = '')
	{
		global $ilBench;

		// FOR ALL TYPES
		$tbl = new ilTableGUI(0,false);

		#$tpl =& new ilTemplate ("tpl.table.html", true, true);

		#$tpl->addBlockFile(strtoupper($a_type),$a_type,"tpl.table.html");

		$this->__addAction($tbl->getTemplateObject(),$a_type,$a_search_in_type);

		// SWITCH 'usr','dbk','lm','grp'
		switch($a_type)
		{
			case "usr":
				$ilBench->start("Search", "showResultTable_usr");
				$tbl->setTitle($this->lng->txt("search_user"),"icon_usr_b.gif",$this->lng->txt("search_user"));
				$tbl->setHeaderNames(array("",$this->lng->txt("login"),$this->lng->txt("firstname")
										   ,$this->lng->txt("lastname"),$this->lng->txt("search_show_result")));
				$tbl->setHeaderVars(array("","login","firstname","lastname",""),array("res_type" => "usr"));
				$tbl->setColumnWidth(array("3%","25%","25%","25%","25%"));
				$tbl->setData(array_values($this->__formatUserResult($this->search->getResultByType("usr"))));
				$ilBench->stop("Search", "showResultTable_usr");
				break;

			case "grp":
				$ilBench->start("Search", "showResultTable_grp");
				$tbl->setTitle($this->lng->txt("search_group"),"icon_grp_b.gif",$this->lng->txt("search_group"));
				$tbl->setHeaderNames(array("",$this->lng->txt("title"),$this->lng->txt("description"),
										   $this->lng->txt("search_show_result")));
				$tbl->setHeaderVars(array("","title","description",""),array("res_type" => "grp"));
				$tbl->setColumnWidth(array("3%","25%","25%","22%"));
				$tbl->setData(array_values($this->__formatGroupResult($this->search->getResultByType("grp"))));
				$ilBench->stop("Search", "showResultTable_grp");
				break;


			case "dbk":
				// SWITCH 'meta','content'
				switch($a_search_in_type)
				{
					case "meta":
						$ilBench->start("Search", "showResultTable_dbk_meta");
						$tbl->setTitle($this->lng->txt("search_dbk_meta"),"icon_dbk_b.gif",$this->lng->txt("search_dbk_meta"));
						$tbl->setHeaderNames(array("",$this->lng->txt("title"),$this->lng->txt("context"),
												   $this->lng->txt("search_show_result")));
						$tbl->setHeaderVars(array("","title","context",""),array("res_type" => "dbk"));

						$tbl->setColumnWidth(array("3%","50%","30%","17%"));

						$tmp_res = $this->search->getResultByType("dbk");
						$tbl->setData($this->__formatDigiLibResult($tmp_res["meta"],"meta"));
						$ilBench->stop("Search", "showResultTable_dbk_meta");
						break;

					case "content":
						$ilBench->start("Search", "showResultTable_dbk_content");
						$tbl->setTitle($this->lng->txt("search_dbk_content"),"icon_dbk_b.gif",$this->lng->txt("search_dbk_content"));
						$tbl->setHeaderNames(array("",$this->lng->txt("title"),$this->lng->txt("page"),$this->lng->txt("context"),
												   $this->lng->txt("search_show_result")));
						$tbl->setHeaderVars(array("","title","page","context",""),array("res_type" => "dbk"));

						$tbl->setColumnWidth(array("3%","30%","20%","30%","17%"));

						$tmp_res = $this->search->getResultByType("dbk");
						$tbl->setData($this->__formatDigiLibResult($tmp_res["content"],"content"));
						$ilBench->stop("Search", "showResultTable_dbk_content");
						break;
				}
				break;

			case "lm":

				// SWITCH 'meta','content'
				switch($a_search_in_type)
				{
					case "meta":
						$ilBench->start("Search", "showResultTable_lm_meta");
						$tbl->setTitle($this->lng->txt("search_lm_meta"),"icon_lm_b.gif",$this->lng->txt("search_lm_meta"));
						$tbl->setHeaderNames(array("",$this->lng->txt("title"),$this->lng->txt("context"),
												   $this->lng->txt("search_show_result")));
						$tbl->setHeaderVars(array("","title","context",""),array("res_type" => "lm"));

						$tbl->setColumnWidth(array("3%","50%","30%","17%"));

						$tmp_res = $this->search->getResultByType("lm");
						$tbl->setData($this->__formatLearningModuleResult($tmp_res["meta"],"meta"));
						$ilBench->stop("Search", "showResultTable_lm_meta");
						break;

					case "content":
						$ilBench->start("Search", "showResultTable_lm_content");
						$tbl->setTitle($this->lng->txt("search_lm_content"),"icon_lm_b.gif",$this->lng->txt("search_lm_content"));
						$tbl->setHeaderNames(array("",$this->lng->txt("title"),$this->lng->txt("page"),$this->lng->txt("context"),
												   $this->lng->txt("search_show_result")));
						$tbl->setHeaderVars(array("","title","page","context",""),array("res_type" => "lm"));

						$tbl->setColumnWidth(array("3%","30%","20%","30%","17%"));

						$tmp_res = $this->search->getResultByType("lm");
						$tbl->setData($this->__formatLearningModuleResult($tmp_res["content"],"content"));
						$ilBench->stop("Search", "showResultTable_lm_content");
						break;
				}
				break;
		}
		$tbl->setOrderColumn($this->sort_by);
		$tbl->setOrderDirection($this->sort_order);
		$tbl->disable("sort");
		$tbl->setLimit(RESULT_LIMIT);
		$tbl->setOffset($this->offset);
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		#$tpl->setVariable(strtoupper($a_type),$tbl->render());

		#$tbl->setTemplate($tpl);
		$tbl->render();
		$this->tpl->setVariable(strtoupper($a_type), $tbl->tpl->get());
		unset($tbl);
	}

	function __show()
	{
		// SHOW SEARCH PAGE
		$this->tpl->addBlockFile("CONTENT","content","tpl.search.html");
		$this->tpl->addBlockFile("STATUSLINE","statusline","tpl.statusline.html");
		infoPanel();
		$this->tpl->setVariable("SEARCH_ACTION","./search.php");

		#$this->tpl->setVariable("TXT_SEARCH",$this->lng->txt("search"));

		$this->__showHeader();
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
        $this->tpl->setVariable("TXT_AND",$this->lng->txt("search_and"));
        $this->tpl->setVariable("TXT_OR",$this->lng->txt("search_or"));

		$search_for = $this->search->getSearchFor();

		$this->tpl->setVariable("LM_CHECKED",in_array("lm",$search_for) ? "checked=\"checked\"" : "");
		$this->tpl->setVariable("DBK_CHECKED",in_array("dbk",$search_for) ? "checked=\"checked\"" : "");

		// hide options if user is not logged in
		if ($this->ilias->account->getId() != ANONYMOUS_USER_ID)
		{
			$this->tpl->setVariable("USR_CHECKED",in_array("usr",$search_for) ? "checked=\"checked\"" : "");
			$this->tpl->setVariable("GRP_CHECKED",in_array("grp",$search_for) ? "checked=\"checked\"" : "");
		}

		$search_in = array("meta" => $this->lng->txt("search_meta"),"content" => $this->lng->txt("search_content"));

		$this->tpl->setVariable("LM_SELECT",ilUtil::formSelect($this->search->getSearchInByType("lm")
															   ,"search_in[lm]",$search_in,false,true));
		$this->tpl->setVariable("DBK_SELECT",ilUtil::formSelect($this->search->getSearchInByType("dbk")
																,"search_in[dbk]",$search_in,false,true));
		// TABLE TEXT
		// hide options if user is not logged in
		$this->tpl->setVariable("TXT_LM",$this->lng->txt("obj_lm"));
		$this->tpl->setVariable("TXT_DBK",$this->lng->txt("obj_dbk"));

		if ($this->ilias->account->getId() != ANONYMOUS_USER_ID)
		{
			$this->tpl->setVariable("TXT_USER",$this->lng->txt("obj_usr"));
			$this->tpl->setVariable("TXT_GROUPS",$this->lng->txt("obj_grp"));
		}

		// TEXT VARIABLES
		$this->tpl->setVariable("TXT_SEARCHTERM",$this->lng->txt("search_search_term"));
		$this->tpl->setVariable("TXT_CONCATENATION",$this->lng->txt("search_concatenation"));
		$this->tpl->setVariable("TXT_SEARCH_FOR",$this->lng->txt("search_search_for"));

		// BUTTONS
		$this->tpl->setVariable("BTN_SEARCH",$this->lng->txt("search"));
		$this->tpl->setVariable("BTN_SEARCH_RESULT",$this->lng->txt("search_in_result"));
	}

	function __showHeader()
	{
		if($this->getFolderId() == $this->folder_obj->getRootId())
		{
			$this->tpl->setVariable("TXT_HEADER",$this->lng->txt("search"));
		}
		else
		{
			// TODO SHOW TITLE OF SEARCH RESULT
		}
	}

	function __showLocator()
	{
		$this->tpl->addBlockFile("LOCATOR","locator","tpl.locator.html");


		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("LINK_ITEM","./search.php");
		$this->tpl->setVariable("LINK_TARGET", ilFrameTargetInfo::_getFrame("MainContent"));
		$this->tpl->setVariable("ITEM",$this->lng->txt("mail_search_word"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("locator");
		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();

	}
		
	function __showTabs()
	{
		$this->tpl->addBlockFile("TABS","tabs","tpl.tabs.html");

		// SEARCH ADMINISTRATION
		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabinactive");
		$this->tpl->setVariable("TAB_LINK","search_administration.php");
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("search_search_results"));
		$this->tpl->parseCurrentBlock();

		if($this->res_type)
		{
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
			if($counter < $this->offset or $counter >= $this->offset + RESULT_LIMIT)
			{
				++$counter;
				$f_result[$counter] = array();
				continue;
			}

			if(!ilObjectFactory::ObjectIdExists($user["id"]))
			{
				++$counter;
				continue;
			}
			$tmp_obj = ilObjectFactory::getInstanceByObjId($user["id"]);
			
			$f_result[$counter][]	= ilUtil::formCheckbox(0,"usr[]",$counter);
			$f_result[$counter][]	= $tmp_obj->getLogin();
			$f_result[$counter][]	= $tmp_obj->getFirstname();
			$f_result[$counter][]	= $tmp_obj->getLastname();

			list($user["link"],$user["target"]) = ilObjUser::_getLinkToObject($user["id"]);
			$f_result[$counter][]	= $this->__formatLink($user["link"],$user["target"]);

			unset($tmp_obj);
			++$counter;
		}
		return $f_result ? $f_result : array();
	}

	function __formatGroupResult($a_res)
	{
		if(!is_array($a_res))
		{
			return array();
		}
		include_once "./classes/class.ilObjectFactory.php";

		$counter = 0;
		foreach($a_res as $group)
		{
			if($counter < $this->offset or $counter >= $this->offset + RESULT_LIMIT)
			{
				++$counter;
				$f_result[$counter] = array();
				continue;
			}

			if(!$this->tree->isInTree($group["id"]))
			{
				++$counter;
				continue;
			}
			$tmp_obj = ilObjectFactory::getInstanceByRefId($group["id"]);
			
			$f_result[$counter][]	= ilUtil::formCheckbox(0,"grp[]",$counter);
			$f_result[$counter][]	= $tmp_obj->getTitle();
			$f_result[$counter][]	= $tmp_obj->getDescription();

			list($group["link"],$group["target"]) = ilObjGroup::_getLinkToObject($group["id"]);
			$f_result[$counter][]	= $this->__formatLink($group["link"],$group["target"]);

			unset($tmp_obj);
			++$counter;
		}
		return $f_result ? $f_result : array();
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
			if($counter < $this->offset or $counter >= $this->offset + RESULT_LIMIT)
			{
				++$counter;
				$f_result[$counter] = array();
				continue;
			}

			if(!$this->tree->isInTree($book["id"]))
			{
				++$counter;
				continue;
			}
			$tmp_obj = ilObjectFactory::getInstanceByRefId($book["id"]);
			switch($a_search_in)
			{
				case "meta":
					$f_result[$counter][]		= ilUtil::formCheckbox(0,"dbk[meta][]",$counter);
					$f_result[$counter][]		= $tmp_obj->getTitle();
					$f_result[$counter][]		= $this->__getContextPath($book["id"]);

					include_once "./content/classes/class.ilObjDlBook.php";
					list($book["link"],$book["target"]) = ilObjDlBook::_getLinkToObject($book["id"],"meta");
					$f_result[$counter][] = $this->__formatLink($book["link"],$book["target"]);

					break;

				case "content":
					// GET INSTANCE OF PAGE OBJECT
					include_once ("content/classes/class.ilLMObjectFactory.php");

					$tmp_page_obj = ilLMObjectFactory::getInstance($tmp_obj, $book["page_id"]);
					if(!is_object($tmp_page_obj))
					{
						++$counter;
						continue;
					}
					$tmp_page_obj->setLMId($book["id"]);

					$f_result[$counter][]		= ilUtil::formCheckbox(0,"dbk[content][]",$counter);
					$f_result[$counter][]		= $tmp_obj->getTitle();
					//$f_result[$counter][] = $tmp_page_obj->getPresentationTitle();
					$f_result[$counter][] =
						ilLMPageObject::_getPresentationTitle($book["page_id"],$tmp_obj->getPageHeader());
					$f_result[$counter][]		= $this->__getContextPath($book["id"]);

					include_once "./content/classes/class.ilObjDlBook.php";
					list($book["link"],$book["target"]) = ilObjDlBook::_getLinkToObject($book["id"],"content",$book["page_id"]);

					$f_result[$counter][] = $this->__formatLink($book["link"],$book["target"]);

					unset($tmp_page_obj);
					break;
			}
			unset($tmp_obj);
			++$counter;
		}

		return $f_result ? $f_result : array();
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
			if($counter < $this->offset or $counter >= $this->offset + RESULT_LIMIT)
			{
				++$counter;
				$f_result[$counter] = array();
				continue;
			}

			if(!$this->tree->isInTree($book["id"]))
			{
				++$counter;
				continue;
			}
			$tmp_obj = ilObjectFactory::getInstanceByRefId($book["id"]);
			switch($a_search_in)
			{
				case "meta":
					$f_result[$counter][]		= ilUtil::formCheckbox(0,"lm[meta][]",$counter);
					$f_result[$counter][]		= $tmp_obj->getTitle();
					$f_result[$counter][]		= $this->__getContextPath($book["id"]);

					include_once "./content/classes/class.ilObjContentObject.php";

					list($book["link"],$book["target"]) = ilObjContentObject::_getLinkToObject($book["id"],"meta");
					$f_result[$counter][] = $this->__formatLink($book["link"],$book["target"]);
					break;
					
				case "content":
					// GET INSTANCE OF PAGE OBJECT
					include_once ("content/classes/class.ilLMObjectFactory.php");

					$tmp_page_obj = ilLMObjectFactory::getInstance($tmp_obj, $book["page_id"]);
					if(!is_object($tmp_page_obj))
					{
						++$counter;
						continue;
					}
					$tmp_page_obj->setLMId($book["id"]);

					$f_result[$counter][]		= ilUtil::formCheckbox(0,"lm[content][]",$counter);
					$f_result[$counter][]		= $tmp_obj->getTitle();
					//$f_result[$counter][] = $tmp_page_obj->getPresentationTitle();
					$f_result[$counter][] =
						ilLMPageObject::_getPresentationTitle($book["page_id"],$tmp_obj->getPageHeader());
					$f_result[$counter][] = $this->__getContextPath($book["id"]);

					include_once "./content/classes/class.ilObjContentObject.php";

					list($book["link"],$book["target"]) = ilObjContentObject::_getLinkToObject($book["id"],"content",$book["page_id"]);
					
					$f_result[$counter][] = $this->__formatLink($book["link"],$book["target"]);

					unset($tmp_page_obj);
					break;
			}

			unset($tmp_obj);
			++$counter;
		}
		return $f_result ? $f_result : array();
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

	function __saveResult()
	{
		// VALIDATE
		if(isset($_POST["cmd"]["dbk_content"]))
		{
			$type = "dbk";
			$subtype = "content";

			if(!is_array($_POST["dbk"]["content"]))
			{
				$this->message = $this->lng->txt("search_select_one_result")."<br />";
			}
		}
		if(isset($_POST["cmd"]["dbk_meta"]))
		{
			$type = "dbk";
			$subtype = "meta";
			if(!is_array($_POST["dbk"]["meta"]))
			{
				$this->message = $this->lng->txt("search_select_one_result")."<br />";
			}
		}
		if(isset($_POST["cmd"]["lm_content"]))
		{
			$type = "lm";
			$subtype = "content";
			if(!is_array($_POST["lm"]["content"]))
			{
				$this->message = $this->lng->txt("search_select_one_result")."<br />";
			}
		}
		if(isset($_POST["cmd"]["lm_meta"]))
		{
			$type = "lm";
			$subtype = "meta";
			if(!is_array($_POST["lm"]["meta"]))
			{
				$this->message = $this->lng->txt("search_select_one_result")."<br />";
			}
		}
		if(isset($_POST["cmd"]["grp_"]))
		{
			$type = "grp";
			$subtype = "";
			if(!is_array($_POST["grp"]))
			{
				$this->message = $this->lng->txt("search_select_one_result")."<br />";
			}
		}
		if(isset($_POST["cmd"]["usr_"]))
		{
			$type = "usr";
			$subtype = "";
			if(!is_array($_POST["usr"]))
			{
				$this->message = $this->lng->txt("search_select_one_result")."<br />";
			}
		}
		// NO FOLDER SELECTED
		if(!$_POST[$type."_".$subtype])
		{
			$this->message .= $this->lng->txt("search_select_one")."<br />";
		}

		if(!$this->message)
		{
			$this->__save($type,$subtype);
		}
	}

	function __save($a_type,$a_subtype = '')
	{
		include_once "./classes/class.ilSearchResult.php";

		$tmp_folder_obj =& new ilSearchFolder($this->getUserId(),$_POST[$a_type."_".$a_subtype]);
		// GET RESULT SET
		$tmp_result = $this->search->getResultByType($a_type);

		switch($a_type)
		{
			case "lm":
			case "dbk":
				foreach($_POST[$a_type][$a_subtype] as $result_id)
				{
					if(!$this->tree->isInTree($tmp_result[$a_subtype][$result_id]["id"]))
					{
						continue;
					}
					$tmp_obj = ilObjectFactory::getInstanceByRefId($tmp_result[$a_subtype][$result_id]["id"]);
					$title = $tmp_obj->getTitle();
					
					if($a_subtype == "meta")
					{
						$target = addslashes(serialize(array("type" => $a_type,
															 "subtype" => $a_subtype,
															 "id" => $tmp_result[$a_subtype][$result_id]["id"])));
					}
					else
					{
						include_once ("content/classes/class.ilLMObjectFactory.php");

						$tmp_page_obj = ilLMObjectFactory::getInstance($tmp_obj,$tmp_result[$a_subtype][$result_id]["page_id"]);
						if(!is_object($tmp_page_obj))
						{
							continue;
						}
						$tmp_page_obj->setLMId($tmp_result[$a_subtype][$result_id]["id"]);

						//$title .= " -> ".$tmp_page_obj->getPresentationTitle();
						$title .= " -> ".
							ilLMPageObject::_getPresentationTitle($tmp_result[$a_subtype][$result_id]["page_id"], $tmp_obj->getPageHeader());

						$target = addslashes(serialize(array("type" => $a_type,
															 "subtype" => $a_subtype,
															 "id" => $tmp_result[$a_subtype][$result_id]["id"],
															 "page_id" => $tmp_result[$a_subtype][$result_id]["page_id"])));
					}
					$search_res_obj =& new ilSearchResult($this->getUserId());
					$search_res_obj->setTitle($title);
					$search_res_obj->setTarget($target);

					$tmp_folder_obj->assignResult($search_res_obj);

					unset($search_res_obj);
				}
				break;
				
			case "grp":
				foreach($_POST["grp"] as $result_id)
				{
					$tmp_obj = ilObjectFactory::getInstanceByRefId($tmp_result[$result_id]["id"]);
			
					$title	= $tmp_obj->getTitle();
					if($tmp_obj->getDescription())
					{
						$title .= " (".$tmp_obj->getDescription().")";
					}
					$target = addslashes(serialize(array("type" => $a_type,
														 "id" => $tmp_result[$result_id]["id"])));
					$search_res_obj =& new ilSearchResult($this->getUserId());
					$search_res_obj->setTitle($title);
					$search_res_obj->setTarget($target);

					$tmp_folder_obj->assignResult($search_res_obj);

					unset($search_res_obj);
				}
				break;
			case "usr":
				foreach($_POST["usr"] as $result_id)
				{

					$tmp_obj = ilObjectFactory::getInstanceByObjId($tmp_result[$result_id]["id"]);
			
					$title	= $tmp_obj->getFirstname();
					$title .= " ".$tmp_obj->getLastname();
					$title .= " (".$tmp_obj->getLogin().")";

					$target = addslashes(serialize(array("type" => $a_type,
														 "id" => $tmp_result[$result_id]["id"])));

					$search_res_obj =& new ilSearchResult($this->getUserId());
					$search_res_obj->setTitle($title);
					$search_res_obj->setTarget($target);

					$tmp_folder_obj->assignResult($search_res_obj);

					unset($search_res_obj);
				}
				break;
		}

		unset($tmp_folder_obj);
		
		$this->message = $this->lng->txt("search_results_saved");
	}
} // END class.Search
?>
