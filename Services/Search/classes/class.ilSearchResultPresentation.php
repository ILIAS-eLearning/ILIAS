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

/** 
* Presentation of search results using object list gui
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesSearch
*/
class ilSearchResultPresentation
{
	const MODE_LUCENE = 1;
	const MODE_STANDARD = 2;
	
	protected $mode;
	
	protected $tpl;
	protected $lng;

	private $results = array();
	private $subitem_ids = array();
	private $has_more_ref_ids = array();
	private $all_references = null;
	private $searcher = null;
	
	private $container = null;
	
	/**
	 * Constructor 
	 * @param object	$container container gui object
	 */
	public function __construct($container = null, $a_mode = self::MODE_LUCENE)
	{
		global $tpl,$lng,$ilCtrl;
		
		$this->mode = $a_mode;
		$this->lng = $lng;
		$this->container = $container;
		$this->ctrl = $ilCtrl;
		
		$this->initReferences();
		
		if(isset($_GET['details']))
		{
			include_once './Services/Object/classes/class.ilSubItemListGUI.php';
			ilSubItemListGUI::setShowDetails((int) $_GET['details']);
		}
	}
	
	/**
	 * Get container gui
	 */
	public function getContainer()
	{
		return $this->container;
	}
	
	public function getMode()
	{
		return $this->mode;
	}
	
	/**
	 * Set result array 
	 * @param array $a_result_data result array
	 */
	public function setResults($a_result_data)
	{
		$this->results = $a_result_data;
	}
	
	/**
	 * get results 
	 * @return array result array
	 */
	public function getResults()
	{
		return $this->results ? $this->results : array();
	}
	
	/**
	 * Set subitem ids
	 * Used for like and fulltext search
	 * @param array $a_subids array ($obj_id => array(page1_id,page2_id);
	 * @return 
	 */
	public function setSubitemIds($a_subids)
	{
		$this->subitem_ids = $a_subids;
	}
	
	/**
	 * Get subitem ids
	 * @return 
	 */
	public function getSubitemIds()
	{
		return $this->subitem_ids ? $this->subitem_ids : array();
	}
	
	/**
	 * Get subitem ids for an object
	 * @param int $a_obj_id
	 * @return 
	 */
	public function getSubitemIdsByObject($a_obj_id)
	{
		return (isset($this->subitem_ids[$a_obj_id]) and $this->subitem_ids[$a_obj_id]) ?
			$this->subitem_ids[$a_obj_id] :
			array();
	}
	
	

	/**
	 * Check if more than one reference is visible 
	 */
	protected function parseResultReferences()
	{
		global $ilAccess;
		
		foreach($this->getResults() as $ref_id => $obj_id)
		{
			$this->all_references[$ref_id][] = $ref_id;
			$counter = 0;
			foreach(ilObject::_getAllReferences($obj_id) as $new_ref)
			{
				if($new_ref == $ref_id)
				{
					continue;
				}
				if(!$ilAccess->checkAccess('read','',$new_ref))
				{
					continue;
				}
				$this->all_references[$ref_id][] = $new_ref;
				++$counter;
			}
			$this->has_more_ref_ids[$ref_id] = $counter;
		}
	}
	
	protected function hasMoreReferences($a_ref_id)
	{
		if(!isset($this->has_more_ref_ids[$a_ref_id]) or 
			!$this->has_more_ref_ids[$a_ref_id] or
			isset($_SESSION['vis_references'][$a_ref_id]))
		{
			return false;
		}
		
		return $this->has_more_ref_ids[$a_ref_id];
	}
	
	protected function getAllReferences($a_ref_id)
	{
		if(isset($_SESSION['vis_references'][$a_ref_id]))
		{
			return $this->all_references[$a_ref_id] ? $this->all_references[$a_ref_id] : array();
		}
		else
		{
			return array($a_ref_id);	
		}
	}
	
	/**
	 * Get HTML 
	 * @return string HTML 
	 */
	public function getHTML($a_new = false)
	{
		return $this->thtml;
	}
	
	/**
	 * set searcher 
	 * @param
	 * @return
	 */
	public function setSearcher($a_searcher)
	{
		$this->searcher = $a_searcher;
	}
	
	/**
	 * Parse results 
	 * @param void
	 * @return string html
	 */
	public function render()
	{
		return $this->renderItemList();
	}
	
	/**
	* Set previous next
	*/
	function setPreviousNext($a_p, $a_n)
	{
		$this->prev = $a_p;
		$this->next = $a_n;
	}
	
	
	/**
	 * Render item list 
	 * @return void
	 */
	protected function renderItemList()
	{
		global $tree,$ilBench;

		$this->html = '';
		
		$ilBench->start('Lucene','2000_pr');
		$this->parseResultReferences();
		$ilBench->stop('Lucene','2000_pr');
		
		include_once("./Services/Object/classes/class.ilObjectListGUIPreloader.php");
		$preloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_SEARCH);
			
		$set = array();
		foreach($this->getResults() as $c_ref_id => $obj_id)
		{
			$ilBench->start('Lucene','2100_res');
			foreach($this->getAllReferences($c_ref_id) as $ref_id)
			{
				$ilBench->start('Lucene','2120_tree');
				if(!$tree->isInTree($ref_id))
				{
					continue;
				}
				$ilBench->stop('Lucene','2120_tree');
				
				$obj_type = ilObject::_lookupType($obj_id);
				
				$set[] = array(
					"ref_id"		=> $ref_id, 
					"obj_id"		=> $obj_id,
					"title"			=> $this->lookupTitle($obj_id,0),
					"title_sort"	=> ilObject::_lookupTitle($obj_id),
					"description"	=> $this->lookupDescription($obj_id,0),
					"type"			=> $obj_type,
					"relevance"		=> $this->getRelevance($obj_id),
					"s_relevance"	=> sprintf("%03d",$this->getRelevance($obj_id))
				);
								
				$preloader->addItem($obj_id, $obj_type, $ref_id);					
			}
			$ilBench->stop('Lucene','2100_res');
		}

		if(!count($set))
		{
			return false;
		}
		
		$preloader->preload();
		unset($preloader);
		
		$ilBench->start('Lucene','2900_tb');
		include_once("./Services/Search/classes/class.ilSearchResultTableGUI.php");
		$result_table = new ilSearchResultTableGUI($this->container, "showSavedResults", $this);
		$result_table->setCustomPreviousNext($this->prev, $this->next);
		
		$result_table->setData($set);
		$this->thtml = $result_table->getHTML();
		$ilBench->stop('Lucene','2900_tb');
		
		return true;
	}
	
	
	// searcher
	/**
	 * get relevance 
	 * @param
	 * @return
	 */
	public function getRelevance($a_obj_id)
	{
		if($this->getMode() == self::MODE_LUCENE)
		{
			return $this->searcher->getResult()->getRelevance($a_obj_id);
		}
		return 0;
	}
	
	/**
	 * 
	 * @param
	 * @return
	 */
	public function lookupTitle($a_obj_id,$a_sub_id)
	{
		if($this->getMode() != self::MODE_LUCENE or !is_object($this->searcher->getHighlighter()))
		{
			return ilObject::_lookupTitle($a_obj_id);
		}
		if(strlen($title = $this->searcher->getHighlighter()->getTitle($a_obj_id,$a_sub_id)))
		{
			return $title;
		}
		return ilObject::_lookupTitle($a_obj_id);
	}
	
	/**
	 * 
	 * @param
	 * @return
	 */
	public function lookupDescription($a_obj_id,$a_sub_id)
	{
		if($this->getMode() != self::MODE_LUCENE or !is_object($this->searcher->getHighlighter()))
		{
			return ilObject::_lookupDescription($a_obj_id);
		}
		if(strlen($title = $this->searcher->getHighlighter()->getDescription($a_obj_id,$a_sub_id)))
		{
			return $title;
		}
		return ilObject::_lookupDescription($a_obj_id);
	}
	
	/**
	 * get content 
	 * @param
	 * @return
	 */
	public function lookupContent($a_obj_id,$a_sub_id)
	{
		if($this->getMode() != self::MODE_LUCENE or !is_object($this->searcher->getHighlighter()))
		{
			return '';
		}
		return $this->searcher->getHighlighter()->getContent($a_obj_id,$a_sub_id);
	}
	
	/**
	 * Append path, relevance information
	 */
	public function appendAdditionalInformation($item_list_gui,$ref_id,$obj_id,$type)
	{
		$sub = $this->appendSubItems($item_list_gui,$ref_id,$obj_id,$type);
		$path = $this->appendPath($ref_id);
		$more = $this->appendMorePathes($ref_id);
		#$rel = $this->appendRelevance($obj_id);
		
		if(!strlen($sub) and 
			!strlen($path) and
			!strlen($more) and
			!strlen($rel))
		{
			return '';
		}
		$tpl = new ilTemplate('tpl.lucene_additional_information.html',true,true,'Services/Search');
		$tpl->setVariable('SUBITEM',$sub);
		if(strlen($path)) {
			$tpl->setVariable('PATH',$path);
		}
		if(strlen($more)) {
			$tpl->setVariable('MORE_PATH',$more);
		}
		if(strlen($rel)) {
			$tpl->setVariable('RELEVANCE',$rel);
		}
		
		$item_list_gui->setAdditionalInformation($tpl->get());
		//$item_list_gui->setAdditionalInformation("Hello");
	}
	
	
	/**
	 * Append path  
	 * @return
	 */
	protected function appendPath($a_ref_id)
	{
		include_once './Services/Tree/classes/class.ilPathGUI.php';
		$path_gui = new ilPathGUI();
		$path_gui->enableTextOnly(false);
		$path_gui->setUseImages(false);
		
		$tpl = new ilTemplate('tpl.lucene_path.html',true,true,'Services/Search');
		$tpl->setVariable('PATH_ITEM',$path_gui->getPath(ROOT_FOLDER_ID,$a_ref_id));
		return $tpl->get();	
	}
	
	/**
	 * Append more occurences link 
	 * @return
	 */
	protected function appendMorePathes($a_ref_id)
	{
		if($this->getMode() != self::MODE_LUCENE)
		{
			return '';
		}
		
		
		if(!$num_refs = $this->hasMoreReferences($a_ref_id))
		{
			return '';
		}
		$tpl = new ilTemplate('tpl.lucene_more_references.html',true,true,'Services/Search');
		$this->ctrl->setParameter($this->getContainer(),'refs',$a_ref_id);
		$tpl->setVariable('MORE_REFS_LINK',$this->ctrl->getLinkTarget($this->getContainer(),''));
		$this->ctrl->clearParameters($this->getContainer());
		
		$tpl->setVariable('TXT_MORE_REFS',sprintf($this->lng->txt('lucene_all_occurrences'),$num_refs));
		return $tpl->get();
	}
	
	/**
	 * Append relevance 
	 * @return
	 */
	protected function appendRelevance($a_obj_id)
	{
		if($this->getMode() != self::MODE_LUCENE)
		{
			return '';
		}

		if(!((int) $this->getRelevance($a_obj_id)))
		{
			return '';
		}
		
		include_once './Services/Search/classes/class.ilSearchSettings.php';
		if(!ilSearchSettings::getInstance()->isRelevanceVisible())
		{
			return '';
		}

		$tpl = new ilTemplate('tpl.lucene_relevance.html',true,true,'Services/Search');
		
		include_once "Services/UIComponent/ProgressBar/classes/class.ilProgressBar.php";
		$pbar = ilProgressBar::getInstance();
		$pbar->setCurrent($this->getRelevance()); 
		
		$this->tpl->setCurrentBlock('relevance');
		$this->tpl->setVariable('REL_PBAR', $pbar->render());		
		$this->tpl->parseCurrentBlock();
		
		$html = $tpl->get();
		return $html;
	}
	
	/**
	 * Append subitems 
	 * @return
	 */
	protected function appendSubItems($item_list_gui,$ref_id,$obj_id,$a_type)
	{
		$subitem_ids = array();
		if($this->getMode() == self::MODE_STANDARD)
		{
			$subitem_ids = $this->getSubitemIdsByObject($obj_id);
			$highlighter = null;
		}
		elseif(is_object($this->searcher->getHighlighter()))
		{
			$subitem_ids = $this->searcher->getHighlighter()->getSubitemIds($obj_id);
			$highlighter = $this->searcher->getHighlighter();
		}
		
		if(!count($subitem_ids))
		{
			return;
		}
		
		// Build subitem list 
		include_once './Services/Search/classes/Lucene/class.ilLuceneSubItemListGUIFactory.php';
		$sub_list = ilLuceneSubItemListGUIFactory::getInstanceByType($a_type,$this->getContainer());
		$sub_list->setHighlighter($highlighter);
		$sub_list->init($item_list_gui,$ref_id,$subitem_ids);
		return $sub_list->getHTML();
		
	}
	
	protected function initReferences()
	{
		if(isset($_REQUEST['refs']))
		{
			$_SESSION['vis_references'][(int) $_REQUEST['refs']] = (int) $_REQUEST['refs'];
		}
	}
}
?>