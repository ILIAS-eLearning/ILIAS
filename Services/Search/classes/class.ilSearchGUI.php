<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Search/classes/class.ilSearchBaseGUI.php';

define('SEARCH_FAST',1);
define('SEARCH_DETAILS',2);
define('SEARCH_AND','and');
define('SEARCH_OR','or');


/**
* Class ilSearchGUI
*
* GUI class for 'simple' search
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* @ilCtrl_Calls ilSearchGUI: ilPropertyFormGUI
* @ilCtrl_Calls ilSearchGUI: ilObjectGUI, ilContainerGUI
* @ilCtrl_Calls ilSearchGUI: ilObjCategoryGUI, ilObjCourseGUI, ilObjFolderGUI, ilObjGroupGUI
* @ilCtrl_Calls ilSearchGUI: ilObjRootFolderGUI
* 
* @ingroup	ServicesSearch
*/
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
		global $ilUser, $lng;

		$lng->loadLanguageModule("search");
		
		$this->obj_types = array (
			"lms" => $lng->txt("learning_resources"),
			"glo" => $lng->txt("objs_glo"),
			"wiki" => $lng->txt("objs_wiki"),
			"mcst" => $lng->txt("objs_mcst"),
			"fil" => $lng->txt("objs_file"),
			"frm" => $lng->txt("objs_frm"),
			"exc" => $lng->txt("objs_exc"),
			"tst" => $lng->txt("search_tst_svy"),
			"mep" => $lng->txt("objs_mep")
			);
		
		// put form values into "old" post variables
		$this->initStandardSearchForm();
		$this->form->checkInput();
		reset($this->obj_types);
		foreach($this->obj_types as $k => $t)
		{
			$_POST["search"]["details"][$k] = $_POST[$k];
		}
		$_POST["search"]["string"] = $_POST["term"];
		$_POST["search"]["combination"] = $_POST["combination"];
		$_POST["search"]["type"] = $_POST["type"];
		$_SESSION['search_root'] = $_POST["area"];

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
		global $rbacsystem, $ilCtrl;


		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case "ilpropertyformgui":
				$this->initStandardSearchForm();
				$this->prepareOutput();
				$ilCtrl->setReturn($this, "");
				return $ilCtrl->forwardCommand($this->form);
				break;
			
			default:
				$this->initUserSearchCache();
				if(!$cmd)
				{
					$cmd = "showSavedResults";
				}
				$this->prepareOutput();
				$this->handleCommand($cmd);
				break;
		}
		return true;
	}
	
	function remoteSearch()
	{
		$this->setString(ilUtil::stripSlashes($_POST['queryString']));
		$this->setRootNode((int) $_POST['root_id']);
		$this->performSearch();
		
	}

	/**
	* Data resource for autoComplete
	*/
	function autoComplete()
	{
		$q = $_REQUEST["query"];
		include_once("./Services/Search/classes/class.ilSearchAutoComplete.php");
		$list = ilSearchAutoComplete::getList($q);
		echo $list;
		exit;

	}
	
	function showSearch()
	{
		global $ilLocator, $ilCtrl;

		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.search.html','Services/Search');

		$this->initStandardSearchForm();
		$this->tpl->setVariable("FORM", $this->form->getHTML());

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

	/**
	* Init standard search form.
	*/
	public function initStandardSearchForm()
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// search term
		$ti = new ilTextInputGUI($lng->txt("search_search_term"), "term");
		$ti->setMaxLength(200);
		$ti->setSize(30);
		$ti->setValue($this->getString());
		$dsSchema = array("resultsList" => 'response.results',
			"fields" => array('term'));
		$ti->setDataSource($ilCtrl->getLinkTarget($this, "autoComplete"));
		$ti->setDataSourceSchema($dsSchema);
		$ti->setDataSourceResultFormat($dsFormatCallback);
		$ti->setDataSourceDelimiter($dsDelimiter);
		$this->form->addItem($ti);
		
		// term combination 
		$radg = new ilRadioGroupInputGUI($lng->txt("search_term_combination"),
			"combination");
		$radg->setValue(($this->getCombination() == SEARCH_AND) ? "and" : "or");
		$op1 = new ilRadioOption($lng->txt("search_any_word"), "or");
		$radg->addOption($op1);
		$op2 = new ilRadioOption($lng->txt("search_all_words"), "and");
		$radg->addOption($op2);
		$this->form->addItem($radg);
		
		// search area
		include_once("./Services/Form/classes/class.ilRepositorySelectorInputGUI.php");
		$ti = new ilRepositorySelectorInputGUI($lng->txt("search_area"), "area");
		$ti->setSelectText($lng->txt("search_select_search_area"));
		$this->form->addItem($ti);
		$ti->readFromSession();
		
		// search type
		$radg = new ilRadioGroupInputGUI($lng->txt("search_type"), "type");
		$radg->setValue($this->getType() == SEARCH_FAST ? SEARCH_FAST : SEARCH_DETAILS);
		$op1 = new ilRadioOption($lng->txt("search_fast_info"), SEARCH_FAST);
		$radg->addOption($op1);
		$op2 = new ilRadioOption($lng->txt("search_details_info"), SEARCH_DETAILS);
		
			// resource types
			$details = $this->getDetails();
			reset($this->obj_types);
			foreach ($this->obj_types as $k => $t)
			{
				$cb = new ilCheckboxInputGUI($t, $k);
				$cb->setChecked($details[$k]);
				$op2->addSubItem($cb);
			}
			
		$radg->addOption($op2);
		$this->form->addItem($radg);
		
		
		// search command
		$this->form->addCommandButton("performSearch", $lng->txt("search"));
	                
		$this->form->setTitle($lng->txt("search"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	 
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
			$this->addPager($result_obj,'max_page');

			include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultPresentation.php';
			$presentation = new ilLuceneSearchResultPresentation($this, ilLuceneSearchResultPresentation::MODE_STANDARD);
			$presentation->setResults($result_obj->getResultsForPresentation());
			$presentation->setPreviousNext($this->prev_link, $this->next_link);
			#$presentation->setSearcher($searcher);

			if($presentation->render())
			{
//				$this->tpl->setVariable('SEARCH_RESULTS',$presentation->getHTML());
				$this->tpl->setVariable('RESULTS_TABLE',$presentation->getHTML(true));
			}
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
		

	/**
	 * Perform search
	 */
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

		if($result->isLimitReached())
		{
			$message = sprintf($this->lng->txt('search_limit_reached'),$this->settings->getMaxHits());
			ilUtil::sendInfo($message);
		}

		// Step 6: show results
		$this->addPager($result,'max_page');
		
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearchResultPresentation.php';
		$presentation = new ilLuceneSearchResultPresentation($this, ilLuceneSearchResultPresentation::MODE_STANDARD);
		$presentation->setResults($result->getResultsForPresentation());
		$presentation->setPreviousNext($this->prev_link, $this->next_link);

		if($presentation->render())
		{
//			$this->tpl->setVariable('SEARCH_RESULTS',$presentation->getHTML());
			$this->tpl->setVariable('RESULTS_TABLE',$presentation->getHTML(true));
		}

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

		if (!$this->settings->getHideAdvancedSearch())
		{
			$this->tpl->setCurrentBlock("tab");
			$this->tpl->setVariable("TAB_TYPE","tabinactive");
			$this->tpl->setVariable("TAB_LINK",$this->ctrl->getLinkTargetByClass('iladvancedsearchgui'));
			$this->tpl->setVariable("TAB_TEXT",$this->lng->txt("search_advanced"));
			$this->tpl->parseCurrentBlock();
		}
	}

	// PRIVATE
	function &__performDetailsSearch(&$query_parser,&$result)
	{
		foreach($this->getDetails() as $type => $enabled)
		{
			if(!$enabled)
			{
				continue;
			}

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
			if(!$detail_type)
			{
				continue;
			}
			
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
					$filter[] = 'wpg';
					break;
			}
		}
		return $filter ? $filter : array();
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
