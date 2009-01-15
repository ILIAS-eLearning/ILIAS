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

define('SEARCH_FAST',1);
define('SEARCH_DETAILS',2);
define('SEARCH_AND','and');
define('SEARCH_OR','or');

class ilSearchGUI extends ilSearchBaseGUI
{
	protected $search_cache = null;
	
	var $root_node;
	var $combination;
	var $string;
	var $type;

	/**
	* Constructor
	* @access public
	*/
	function ilSearchGUI()
	{
		global $ilUser;
		
		$this->root_node = $_SESSION['search_root'] ? $_SESSION['search_root'] : ROOT_FOLDER_ID;
		$this->setType($_POST['search']['type'] ? $_POST['search']['type'] : $_SESSION['search']['type']);
		$this->setCombination($_POST['search']['combination'] ? $_POST['search']['combination'] : $_SESSION['search']['combination']);
		$this->setString($_POST['search']['string'] ? $_POST['search']['string'] : $_SESSION['search']['string']);
		$this->setDetails($_POST['search']['details'] ? $_POST['search']['details'] : $_SESSION['search']['details']);
		
		parent::ilSearchBaseGUI();
	}


	/**
	* Set/get type of search (detail or 'fast' search)
	* @access public
	*/
	function setType($a_type)
	{
		$_SESSION['search']['type'] = $this->type = $a_type;
	}
	function getType()
	{
		return $this->type ? $this->type : SEARCH_FAST;
	}
	/**
	* Set/get combination of search ('and' or 'or')
	* @access public
	*/
	function setCombination($a_combination)
	{
		$_SESSION['search']['combination'] = $this->combination = $a_combination;
	}
	function getCombination()
	{
		return $this->combination ? $this->combination : SEARCH_OR;
	}
	/**
	* Set/get search string
	* @access public
	*/
	function setString($a_str)
	{
		$_SESSION['search']['string'] = $this->string = $a_str;
	}
	function getString()
	{
		return $this->string;
	}
	/**
	* Set/get details (object types for details search)
	* @access public
	*/
	function setDetails($a_details)
	{
		$_SESSION['search']['details'] = $this->details = $a_details;
	}
	function getDetails()
	{
		return $this->details ? $this->details : array();
	}

		
	function getRootNode()
	{
		return $this->root_node ? $this->root_node : ROOT_FOLDER_ID;
	}
	function setRootNode($a_node_id)
	{
		$_SESSION['search_root'] = $this->root_node = $a_node_id;
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
				$this->initUserSearchCache();
				if(!$cmd)
				{
					$cmd = "showSavedResults";
				}
				$this->prepareOutput();
				$this->$cmd();
				break;
		}
		return true;
	}

	function saveResult()
	{
		include_once 'Services/Search/classes/class.ilUserResult.php';
		include_once 'Services/Search/classes/class.ilSearchFolder.php';

		global $ilUser;

		if(!$_POST['folder'])
		{
			ilUtil::sendInfo($this->lng->txt('search_select_one'));
			$this->showSavedResults();

			return false;
		}
		if(!count($_POST['id']))
		{
			ilUtil::sendInfo($this->lng->txt('search_select_one_result'));
			$this->showSavedResults();

			return false;
		}

		$folder_obj =& new ilSearchFolder($ilUser->getId(),(int) $_POST['folder']);

		foreach($_POST['id'] as $ref_id)
		{
			$title = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
			$target = addslashes(serialize(array('type' => ilObject::_lookupType(ilObject::_lookupObjId($ref_id)),
												 'id'	=> $ref_id)));

			$search_res_obj =& new ilUserResult($ilUser->getId());
			$search_res_obj->setTitle($title);
			$search_res_obj->setTarget($target);

			$folder_obj->assignResult($search_res_obj);
			unset($search_res_obj);
		}
		ilUtil::sendInfo($this->lng->txt('search_results_saved'));
		$this->showSavedResults();

	}


	function showSearch()
	{
		global $ilLocator;

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.search.html','Services/Search');

		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('search'));
		$this->tpl->setVariable("TXT_SEARCHAREA",$this->lng->txt('search_area'));
		$this->tpl->setVariable("SEARCH_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_SEARCHTERM",$this->lng->txt("search_search_term"));
		$this->tpl->setVariable("TXT_AND",$this->lng->txt('search_all_words'));
		$this->tpl->setVariable("TXT_OR",$this->lng->txt('search_any_word'));
		$this->tpl->setVariable("BTN_SEARCH",$this->lng->txt('search'));

		// Check 'or' as default
		if($this->getCombination() == SEARCH_AND)
		{
			$this->tpl->setVariable("AND_CHECKED",'checked=checked');
		}
		else
		{
			$this->tpl->setVariable("OR_CHECKED",'checked=checked');
		}
		// Set old query string
		$this->tpl->setVariable("FORM_SEARCH_STR",ilUtil::prepareFormOutput($this->getString(),true));

		$this->tpl->setVariable("HREF_UPDATE_AREA",$this->ctrl->getLinkTarget($this,'showSelectRoot'));
		$this->tpl->setVariable("UPDATE_AREA",$this->lng->txt('search_change'));

		// SEARCHTYPE
		$this->tpl->setVariable("TXT_SEARCH_TYPE",$this->lng->txt('search_type'));
		$this->tpl->setVariable("INFO_FAST",$this->lng->txt('search_fast_info'));
		$this->tpl->setVariable("INFO_DETAILS",$this->lng->txt('search_details_info'));

		$this->tpl->setVariable("CHECK_FAST",ilUtil::formRadioButton($this->getType() == SEARCH_FAST ? 1 : 0,
																	 'search[type]',
																	 SEARCH_FAST ));

		$this->tpl->setVariable("CHECK_DETAILS",ilUtil::formRadioButton($this->getType() == SEARCH_DETAILS ? 1 : 0,
																	 'search[type]',
																	 SEARCH_DETAILS));
		// SEARCH DETAILS
		$this->tpl->setVariable("LMS",$this->lng->txt('learning_resources'));
		$this->tpl->setVariable("GLO",$this->lng->txt('objs_glo'));
		$this->tpl->setVariable("MEP",$this->lng->txt('objs_mep'));
		$this->tpl->setVariable("TST",$this->lng->txt('search_tst_svy'));
		$this->tpl->setVariable("FOR",$this->lng->txt('objs_frm'));
		$this->tpl->setVariable("EXC",$this->lng->txt('objs_exc'));
		$this->tpl->setVariable("MCST",$this->lng->txt('objs_mcst'));
		$this->tpl->setVariable("WIKI",$this->lng->txt('objs_wiki'));
		$this->tpl->setVariable("FIL",$this->lng->txt('objs_file'));

		
		$details = $this->getDetails();
		$this->tpl->setVariable("CHECK_GLO",ilUtil::formCheckbox($details['glo'] ? 1 : 0,'search[details][glo]',1));
		$this->tpl->setVariable("CHECK_LMS",ilUtil::formCheckbox($details['lms'] ? 1 : 0,'search[details][lms]',1));
		$this->tpl->setVariable("CHECK_MEP",ilUtil::formCheckbox($details['mep'] ? 1 : 0,'search[details][mep]',1));
		$this->tpl->setVariable("CHECK_TST",ilUtil::formCheckbox($details['tst'] ? 1 : 0,'search[details][tst]',1));
		$this->tpl->setVariable("CHECK_FOR",ilUtil::formCheckbox($details['frm'] ? 1 : 0,'search[details][frm]',1));
		$this->tpl->setVariable("CHECK_EXC",ilUtil::formCheckbox($details['exc'] ? 1 : 0,'search[details][exc]',1));
		$this->tpl->setVariable("CHECK_FIL",ilUtil::formCheckbox($details['fil'] ? 1 : 0,'search[details][fil]',1));
		$this->tpl->setVariable("CHECK_MCST",ilUtil::formCheckbox($details['mcst'] ? 1 : 0,'search[details][mcst]',1));
		$this->tpl->setVariable("CHECK_WIKI",ilUtil::formCheckbox($details['wiki'] ? 1 : 0,'search[details][wiki]',1));



		// SEARCHAREA
		if($this->getRootNode() == ROOT_FOLDER_ID)
		{
			$this->tpl->setVariable("SEARCHAREA",$this->lng->txt('search_in_magazin'));
		}
		else
		{
			$text = $this->lng->txt('search_below')." '";
			$text .= ilObject::_lookupTitle(ilObject::_lookupObjId($this->getRootNode()));
			$text .= "'";
			$this->tpl->setVariable("SEARCHAREA",$text);
		}

		return true;
	}

	function showSelectRoot()
	{
		global $tree;

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.search_root_selector.html','Services/Search');

		include_once 'Services/Search/classes/class.ilSearchRootSelector.php';

		ilUtil::sendInfo($this->lng->txt('search_area_info'));

		$exp = new ilSearchRootSelector($this->ctrl->getLinkTarget($this,'showSelectRoot'));
		$exp->setExpand($_GET["search_root_expand"] ? $_GET["search_root_expand"] : $tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'showSelectRoot'));

		// build html-output
		$exp->setOutput(0);

		$this->tpl->setVariable("EXPLORER",$exp->getOutput());
	}

	function selectRoot()
	{
		$this->setRootNode((int) $_GET['root_id']);
		$this->showSavedResults();

		return true;
	}

	
	function showSavedResults()
	{
		global $ilUser;

		// Read old result sets
		include_once 'Services/Search/classes/class.ilSearchResult.php';
	
		$result_obj = new ilSearchResult($ilUser->getId());
		$result_obj->read();
		$result_obj->filterResults($this->getRootNode());

		$this->showSearch();

		// Show them
		if(count($result_obj->getResults()))
		{
			$this->__showSearchInResults();
			$this->addPager($result_obj,'max_page');

			include_once 'Services/Search/classes/class.ilSearchResultPresentationGUI.php';
			$search_result_presentation = new ilSearchResultPresentationGUI($result_obj);
			$this->tpl->setVariable("RESULTS",$search_result_presentation->showResults());
		}

		return true;
	}

	function searchInResults()
	{
		$this->search_mode = 'in_results';
		$this->search_cache->setResultPageNumber(1);
		unset($_SESSION['max_page']);
		$this->performSearch();

		return true;
	}
		

	function performSearch()
	{
		global $ilUser;
	
		if(!isset($_GET['page_number']) and $this->search_mode != 'in_results' )
		{
			unset($_SESSION['max_page']);
			$this->search_cache->delete();
		}

		if($this->getType() == SEARCH_DETAILS and !$this->getDetails())
		{
			ilUtil::sendInfo($this->lng->txt('search_choose_object_type'));
			$this->showSearch();

			return false;
		}

		
		// Step 1: parse query string
		if(!is_object($query_parser =& $this->__parseQueryString()))
		{
			ilUtil::sendInfo($query_parser);
			$this->showSearch();
			
			return false;
		}
		// Step 2: perform object search. Get an ObjectSearch object via factory. Depends on fulltext or like search type.
		$result =& $this->__searchObjects($query_parser);

		// Step 3: perform meta keyword search. Get an MetaDataSearch object.
		$result_meta =& $this->__searchMeta($query_parser,'keyword');
		$result->mergeEntries($result_meta);

		$result_meta =& $this->__searchMeta($query_parser,'contribute');
		$result->mergeEntries($result_meta);
	
		$result_meta =& $this->__searchMeta($query_parser,'title');
		$result->mergeEntries($result_meta);
	
		$result_meta =& $this->__searchMeta($query_parser,'description');
		$result->mergeEntries($result_meta);
	
		// Perform details search in object specific tables
		if($this->getType() == SEARCH_DETAILS)
		{
			$result = $this->__performDetailsSearch($query_parser,$result);
		}
		// Step 5: Search in results
		if($this->search_mode == 'in_results')
		{
			include_once 'Services/Search/classes/class.ilSearchResult.php';

			$old_result_obj = new ilSearchResult($ilUser->getId());
			$old_result_obj->read();

			$result->diffEntriesFromResult($old_result_obj);
		}
			

		// Step 4: merge and validate results
		$result->filter($this->getRootNode(),$query_parser->getCombination() == 'and');
		$result->save();
		$this->showSearch();

		if(!count($result->getResults()))
		{
			ilUtil::sendInfo($this->lng->txt('search_no_match'));
		}
		else
		{
			$this->__showSearchInResults();
		}

		if($result->isLimitReached())
		{
			$message = sprintf($this->lng->txt('search_limit_reached'),$this->settings->getMaxHits());
			ilUtil::sendInfo($message);
		}

		// Step 6: show results
		$this->addPager($result,'max_page');

		include_once 'Services/Search/classes/class.ilSearchResultPresentationGUI.php';
		$search_result_presentation = new ilSearchResultPresentationGUI($result);
		$this->tpl->setVariable("RESULTS",$search_result_presentation->showResults());

		return true;
	}

		

	function prepareOutput()
	{
		parent::prepareOutput();

		$this->tpl->addBlockFile("TABS","tabs","tpl.tabs.html");

		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabactive");
		$this->tpl->setVariable("TAB_LINK",$this->ctrl->getLinkTarget($this));
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("search"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabinactive");
		$this->tpl->setVariable("TAB_LINK",$this->ctrl->getLinkTargetByClass('iladvancedsearchgui'));
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("search_advanced"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tab");
		$this->tpl->setVariable("TAB_TYPE","tabinactive");
		$this->tpl->setVariable("TAB_LINK",$this->ctrl->getLinkTargetByClass('ilsearchresultgui'));
		$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("search_search_results"));
		$this->tpl->parseCurrentBlock();
		
	}

	// PRIVATE
	function &__performDetailsSearch(&$query_parser,&$result)
	{
		foreach($this->getDetails() as $type => $always_one)
		{
			switch($type)
			{
				case 'lms':
					$content_search =& ilObjectSearchFactory::_getLMContentSearchInstance($query_parser);
					$content_search->setFilter($this->__getFilter());
					$result->mergeEntries($content_search->performSearch());

					if($this->settings->enabledLucene())
					{
						$htlm_search =& ilObjectSearchFactory::_getHTLMSearchInstance($query_parser);
						$result->mergeEntries($htlm_search->performSearch());
					}
					break;

				case 'frm':
					$forum_search =& ilObjectSearchFactory::_getForumSearchInstance($query_parser);
					$forum_search->setFilter($this->__getFilter());
					$result->mergeEntries($forum_search->performSearch());
					break;

				case 'glo':
					// Glossary term definition pages
					$gdf_search =& ilObjectSearchFactory::_getLMContentSearchInstance($query_parser);
					$gdf_search->setFilter($this->__getFilter());
					$result->mergeEntries($gdf_search->performSearch());
					// Glossary terms
					$gdf_term_search =& ilObjectSearchFactory::_getGlossaryDefinitionSearchInstance($query_parser);
					$result->mergeEntries($gdf_term_search->performSearch());
					break;

				case 'exc':
					$exc_search =& ilObjectSearchFactory::_getExerciseSearchInstance($query_parser);
					$exc_search->setFilter($this->__getFilter());
					$result->mergeEntries($exc_search->performSearch());
					break;

				case 'mcst':
					$mcst_search =& ilObjectSearchFactory::_getMediaCastSearchInstance($query_parser);
					$result->mergeEntries($mcst_search->performSearch());
					break;

				case 'tst':
					$tst_search =& ilObjectSearchFactory::_getTestSearchInstance($query_parser);
					$tst_search->setFilter($this->__getFilter());
					$result->mergeEntries($tst_search->performSearch());
					break;

				case 'mep':
					$mep_search =& ilObjectSearchFactory::_getMediaPoolSearchInstance($query_parser);
					$mep_search->setFilter($this->__getFilter());
					$result->mergeEntries($mep_search->performSearch());
					break;

				case 'fil':
					if($this->settings->enabledLucene())
					{
						$file_search =& ilObjectSearchFactory::_getFileSearchInstance($query_parser);
						$result->mergeEntries($file_search->performSearch());
					}
					break;
					
				case 'wiki':
					$wiki_search =& ilObjectSearchFactory::_getWikiContentSearchInstance($query_parser);
					$wiki_search->setFilter($this->__getFilter());
					$result->mergeEntries($wiki_search->performSearch());

					/*$result_meta =& $this->__searchMeta($query_parser,'title');
					$result->mergeEntries($result_meta);
					$result_meta =& $this->__searchMeta($query_parser,'description');
					$result->mergeEntries($result_meta);*/
					break;

			}
		}
		return $result;
	}

	/**
	* parse query string, using query parser instance
	* @return object of query parser or error message if an error occured
	* @access public
	*/
	function &__parseQueryString()
	{
		include_once 'Services/Search/classes/class.ilQueryParser.php';

		$query_parser = new ilQueryParser(ilUtil::stripSlashes($this->getString()));
		$query_parser->setCombination($this->getCombination());
		$query_parser->parse();

		if(!$query_parser->validate())
		{
			return $query_parser->getMessage();
		}
		return $query_parser;
	}
	/**
	* Search in obect title,desctiption
	* @return object result object
	* @access public
	*/
	function &__searchObjects(&$query_parser)
	{
		include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';

		$obj_search =& ilObjectSearchFactory::_getObjectSearchInstance($query_parser);
		if($this->getType() == SEARCH_DETAILS)
		{
			$obj_search->setFilter($this->__getFilter());
		}
		return $obj_search->performSearch();
	}


	/**
	* Search in object meta data (keyword)
	* @return object result object
	* @access public
	*/
	function &__searchMeta(&$query_parser,$a_type)
	{
		include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';

		$meta_search =& ilObjectSearchFactory::_getMetaDataSearchInstance($query_parser);
		if($this->getType() == SEARCH_DETAILS)
		{
			$meta_search->setFilter($this->__getFilter());
		}
		switch($a_type)
		{
			case 'keyword':
				$meta_search->setMode('keyword');
				break;

			case 'contribute':
				$meta_search->setMode('contribute');
				break;

			case 'title':
				$meta_search->setMode('title');
				break;

			case 'description':
				$meta_search->setMode('description');
				break;
		}
	    return $meta_search->performSearch();
	}
	/**
	* Get object type for filter (If detail search is enabled)
	* @return array object types
	* @access public
	*/
	function __getFilter()
	{
		if($this->getType() != SEARCH_DETAILS)
		{
			return false;
		}
		
		foreach($this->getDetails() as $key => $detail_type)
		{
			switch($key)
			{
				case 'lms':
					$filter[] = 'lm';
					$filter[] = 'dbk';
					$filter[] = 'pg';
					$filter[] = 'st';
					$filter[] = 'sahs';
					$filter[] = 'htlm';
					break;

				case 'frm':
					$filter[] = 'frm';
					break;

				case 'glo':
					$filter[] = 'glo';
					break;

				case 'exc':
					$filter[] = 'exc';
					break;

				case 'mcst':
					$filter[] = 'mcst';
					break;

				case 'tst':
					$filter[] = 'tst';
					$filter[] = 'svy';
					$filter[] = 'qpl';
					$filter[] = 'spl';
					break;

				case 'mep':
					$filter[] = 'mep';
					$filter[] = 'mob';
					break;

				case 'fil':
					$filter[] = 'file';
					break;
					
				case 'wiki':
					$filter[] = 'wiki';
					break;
			}
		}
		return $filter ? $filter : array();
	}

	/**
	* Show search in results button. If search was successful
	* @return void
	* @access public
	*/
	function __showSearchInResults()
	{
		$this->tpl->setCurrentBlock("search_results");
		$this->tpl->setVariable("BTN_SEARCHRESULTS",$this->lng->txt('search_in_result'));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("save_result");
		$this->tpl->setVariable("DOWNRIGHT",ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable("BTN_SAVE_RESULT",$this->lng->txt('save'));
		$this->tpl->setVariable("SELECT_FOLDER",$this->__getFolderSelect());
		$this->tpl->parseCurrentBlock();

		return true;
	}

	function __getFolderSelect()
	{
		global $ilUser;

		include_once 'Services/Search/classes/class.ilSearchFolder.php';

		// INITIATE SEARCH FOLDER OBJECT
		$folder_obj =& new ilSearchFolder($ilUser->getId());


		$subtree = $folder_obj->getSubtree();

		$options[0] = $this->lng->txt("search_select_one_folder_select");
		$options[$folder_obj->getRootId()] = $this->lng->txt("search_save_as_select")." ".$this->lng->txt("search_search_results");
		
		foreach($subtree as $node)
		{
			if($node["obj_id"] == $folder_obj->getRootId())
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
		return ilUtil::formSelect(0,'folder',$options,false,true);
	}
	/**
	 * Init user search cache
	 *
	 * @access private
	 * 
	 */
	protected function initUserSearchCache()
	{
		global $ilUser;
		
		include_once('Services/Search/classes/class.ilUserSearchCache.php');
		$this->search_cache = ilUserSearchCache::_getInstance($ilUser->getId());
		if($_GET['page_number'])
		{
			$this->search_cache->setResultPageNumber((int) $_GET['page_number']);
		}
	}
	
}
?>
