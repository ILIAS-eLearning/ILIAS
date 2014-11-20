<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Payment/classes/class.ilPaymentPrices.php';
include_once 'Services/Payment/classes/class.ilPaymentObject.php';
include_once 'Services/Payment/classes/class.ilFileDataShop.php';
include_once 'Services/Payment/classes/class.ilShopUtils.php';

/**
* Class ilShopResultPresentationGUI
*
* @author Nadia Ahmad <nahmad@databay.de>
* @author Michael Jansen <mjansen@databay.de>
* @version $Id:$
* 
* @ingroup ServicesPayment
*/
class ilShopResultPresentationGUI
{	
	protected $search_cache = null;

	public $tpl;
	public $lng;

	public $result = 0;


	private $sort_field = '';
	private $sort_direction = '';

	public function ilShopResultPresentationGUI($result)
	{ 
		global $lng, $ilCtrl, $ilUser;

		$this->lng = $lng;
		$this->result = $result;

		$this->type_ordering = array(
			"cat", "crs", "grp", "chat", "frm", "wiki", "lres",
			"glo", "webr", "lm", "sahs", "htlm", "file",'mcst', "exc", 
			"tst", "svy", "sess","mep", "qpl", "spl");

		$this->ctrl = $ilCtrl;

		include_once('Services/Search/classes/class.ilUserSearchCache.php');
		$this->search_cache = ilUserSearchCache::_getInstance($ilUser->getId());
	}

	public function showResults()
	{
		// Get results
		 $this->showTopics();
	}

	public function showSpecials()
	{
		// Get specials
		$oContainerTpl = new ilTemplate ('tpl.shop_container.html', true, true, 'Services/Payment');
		include_once './Services/Payment/classes/class.ilShopTopic.php';
		include_once './Services/Payment/classes/class.ilShopTopics.php';
		$results = $this->result;

		ilShopTopics::_getInstance()->setIdFilter(false);
		ilShopTopics::_getInstance()->read();
		$html = '';

		if(count($topics = ilShopTopics::_getInstance()->getTopics()))
		{
			foreach($topics as $oTopic)
			{
				$html .= $this->renderItems($oContainerTpl, $results, array('id' => $oTopic->getId(), 'title' => $oTopic->getTitle()));
			}
			$html .= $this->renderItems($oContainerTpl, $results, array('id' => 0, 'title' => $this->lng->txt('payment_no_topic')));
		}
		else
		{
			$html .= $this->renderItems($oContainerTpl, $results, array('id' => 0, 'title' => $this->lng->txt('payment_no_topic')));
		}
		
		return $html;
	}


	public function showTopics()
	{
		$oContainerTpl = new ilTemplate ('tpl.shop_container.html', true, true, 'Services/Payment');
		include_once './Services/Payment/classes/class.ilShopTopic.php';
		include_once './Services/Payment/classes/class.ilShopTopics.php';
		$results = $this->result;

		ilShopTopics::_getInstance()->setIdFilter(false);
		ilShopTopics::_getInstance()->read();
		
		$html = '';
		if(count($topics = ilShopTopics::_getInstance()->getTopics()))
		{
			foreach($topics as $oTopic)
			{
				$html .= $this->renderItems($oContainerTpl, $results, array('id' => $oTopic->getId(), 'title' => $oTopic->getTitle()));
			}
			$html .=  $this->renderItems($oContainerTpl, $results, array('id' => 0, 'title' => $this->lng->txt('payment_no_topic')));
		}
		else
		{
			$html .= $this->renderItems($oContainerTpl, $results, array('id' => 0, 'title' => $this->lng->txt('payment_no_topic')));
		}

		return $html;
	}
	
	public function setTypeOrdering($a_type_ordering_array)
	{
		$this->type_ordering = $a_type_ordering_array;
	}	
	public function getTypeOrdering()
	{
		return $this->type_ordering;
	}	
	public function setSortField($a_sort_field)
	{
		$this->sort_field = $a_sort_field;
	}	
	public function getSortField()
	{
		return $this->sort_field;
	}	
	public function setSortDirection($a_sort_direction)
	{
		$this->sort_direction = $a_sort_direction;
	}	
	public function getSortDirection()
	{
		return $this->sort_direction;
	}
	
	public function sortResult($result)
	{
		if ($this->sort_direction != '' && $this->sort_field != '')		
		{
			switch ($this->sort_field)
			{
				case 'price':
					$numeric_sort = true;
					break;
				default:
					$numeric_sort = false;
					break;
			}
			
			$result = ilUtil::sortArray($result, $this->sort_field, $this->sort_direction, $numeric_sort);
		}
		
		return $result;
	}	
	
	private function renderItems($oContainerTpl, $results, $topic)
	{
		// main shop_content
		global $ilUser;
		
		$items_counter = 0;
				
		$cur_obj_type = '';
		$tpl = $this->newBlockTemplate();

		foreach($this->type_ordering as $act_type)
		{
			$item_html = array();

			if(count($results[(int)$topic['id']][$act_type]))
			{
				foreach($results[$topic['id']][$act_type] as $key => $item)
				{
					// price presentation
					$oPaymentObject = new ilPaymentObject($ilUser, ilPaymentObject::_lookupPobjectId($item['ref_id']));					
					$oPrice = new ilPaymentPrices((int)$oPaymentObject->getPobjectId());
					$lowest_price = $oPrice->getLowestPrice();

					$special_icon = ' ';
					if($oPaymentObject->getSpecial() == '1')
					{
						$special_icon = ilShopUtils::_getSpecialObjectSymbol();
					}
					$results[$topic['id']][$act_type][$key]['title'] = $item['title'].' '.
					$results[$topic['id']][$act_type][$key]['special_icon'] = $special_icon;

					$results[$topic['id']][$act_type][$key]['price'] = $lowest_price['price'];

					$paymethod_icon = ilShopUtils::_getPaymethodSymbol($oPaymentObject->getPayMethod());
					$shoppingcart_icon = ilShopUtils::_addToShoppingCartSymbol($item['ref_id']);

					$results[$topic['id']][$act_type][$key]['price_string'] =										 
						($oPrice->getNumberOfPrices() > 1 ? $this->lng->txt('price_from').' ' : '').
						ilPaymentPrices::_formatPriceToString($lowest_price['price']).' '.

					//shoppingcart icon
					$results[$topic['id']][$act_type][$key]['shoppingcart_icon'] = $shoppingcart_icon.' '.
					// paymethod icon
					$results[$topic['id']][$act_type][$key]['paymethod_icon'] = $paymethod_icon;

					// authors
					include_once 'Services/MetaData/classes/class.ilMD.php';
					$md_obj = new ilMD($item['obj_id'], 0, $item['type']);
					if(is_object($md_section = $md_obj->getLifecycle()))
					{
						$sep = $ent_str = "";
						foreach(($ids = $md_section->getContributeIds()) as $con_id)
						{
							$md_con = $md_section->getContribute($con_id);
							if ($md_con->getRole() == "Author")
							{
								foreach($ent_ids = $md_con->getEntityIds() as $ent_id)
								{
									$md_ent = $md_con->getEntity($ent_id);
									$ent_str = $ent_str.$sep.$md_ent->getEntity();
									$sep = ", ";
								}
							}
						}
						$results[$topic['id']][$act_type][$key]['author'] = $ent_str;
					}				
				}
				
				$results[$topic['id']][$act_type] = $this->sortResult($results[$topic['id']][$act_type]);

				foreach($results[$topic['id']][$act_type] as $key => $item)
				{
					// get list gui class for each object type
					list($item, $html) = $this->getObjectListItem($cur_obj_type, $item);
				
					if($html)
					{
						$item_html[$item['ref_id']] = $html;
					}
				}		
				
				// output block for resource type
				if(count($item_html) > 0)
				{
					// add a header for each resource type
					$tpl->setCurrentBlock("container_header_row_image");
					$tpl->setVariable("HEADER_IMG", ilObject::_getIcon('','', $act_type));
					$tpl->setVariable("HEADER_ALT", $this->lng->txt("objs_".$act_type));
					$tpl->setVariable("BLOCK_HEADER_CONTENT",  $this->lng->txt("objs_".$act_type));
					
					$this->resetRowType();
					// content row
					foreach($item_html as $ref_id => $html)
					{
						$this->addStandardRow($tpl, $html);
					}

					++$items_counter;
				}
			}
		}
		if($items_counter > 0)
		{			
			$oContainerTpl->setCurrentBlock('loop_item');
			$oContainerTpl->setCurrentBlock('topic_title');
			$oContainerTpl->setVariable('TOPIC_TITLE', $topic['title']);
			$oContainerTpl->parseCurrentBlock('topic_title');

			$oContainerTpl->setVariable('CONTAINER_LIST_BLOCK', $html);
			$oContainerTpl->parseCurrentBlock('loop_item');
			$tpl->setContent($oContainerTpl->get());
			$container_html = $tpl->get();
			
			return $container_html;
		}
	}
	
	public function renderItemList($results)
	{
		$oContainerTpl = new ilTemplate ('tpl.shop_container.html', true, true, 'Services/Payment');	

		foreach($this->result->getTopics() as $oTopic)
		{
			$this->renderItems($oContainerTpl, $results, array('id' => $oTopic->getId(), 'title' => $oTopic->getTitle()));
		}
		$this->renderItems($oContainerTpl, $results, array('id' => 0, 'title' => $this->lng->txt('payment_no_topic')));
		
		return $oContainerTpl->get();
	}
	
	/**
	 * @param $a_tpl
	 * @param $a_html
	 * @param $a_ref_id
	 */
	public function addStandardRow($a_tpl, $a_html)
	{
		$this->cur_row_type = ($this->cur_row_type == "row_type_1")
			? "row_type_2"
			: "row_type_1";

		$a_tpl->touchBlock($this->cur_row_type);
		$a_tpl->setCurrentBlock("container_standard_row");
		$a_tpl->setVariable("BLOCK_ROW_CONTENT", $a_html);
		
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}

	/**
	* returns a new list block template
	*
	* @access	private
	* @return	object		block template
	*/
	public function newBlockTemplate()
	{
		$tpl = new ilTemplate ("tpl.container_list_block.html",true, true, "Services/Container");
		$this->cur_row_type = "row_type_1";

		return $tpl;
	}

//	public function __appendChildLinks($html, $item, &$item_list_gui)
//	{
//		if(!count($item['child']))
//		{
//			return $html;
//		}
//		$tpl = new ilTemplate('tpl.detail_links.html',true,true,'Services/Search');
//
//		switch($item['type'])
//		{
//			case 'lm':
//				$tpl->setVariable("HITS",$this->lng->txt('search_hits'));
//				include_once 'Modules/LearningModule/classes/class.ilLMObject.php';
//				if(is_array($item['child']))
//				{
//					foreach($item['child'] as $child)
//					{
//						$tpl->setCurrentBlock("link_row");
//
//						switch(ilLMObject::_lookupType($child))
//						{
//							case 'pg':
//								$tpl->setVariable("CHAPTER_PAGE", $this->lng->txt('obj_pg'));
//								break;
//							case 'st':
//								$tpl->setVariable("CHAPTER_PAGE", $this->lng->txt('obj_st'));
//								break;
//						}
//						$item_list_gui->setChildId($child);
//						$tpl->setVariable("SEPERATOR", ' -> ');
//						$tpl->setVariable("LINK", $item_list_gui->getCommandLink('page'));
//						$tpl->setVariable("TARGET", $item_list_gui->getCommandFrame('page'));
//						$tpl->setVariable("TITLE", ilLMObject::_lookupTitle($child));
//						$tpl->parseCurrentBlock();
//					}
//				
//				}
//				else
//				{
//					$child = (int)$item['child']; 
//					$tpl->setCurrentBlock("link_row");
//
//					switch(ilLMObject::_lookupType($child))
//					{
//						case 'pg':
//							$tpl->setVariable("CHAPTER_PAGE", $this->lng->txt('obj_pg'));
//							break;
//						case 'st':
//							$tpl->setVariable("CHAPTER_PAGE", $this->lng->txt('obj_st'));
//							break;
//					}
//					$item_list_gui->setChildId($child);
//					$tpl->setVariable("SEPERATOR", ' -> ');
//					$tpl->setVariable("LINK", $item_list_gui->getCommandLink('page'));
//					$tpl->setVariable("TARGET", $item_list_gui->getCommandFrame('page'));
//					$tpl->setVariable("TITLE", ilLMObject::_lookupTitle($child));
//					$tpl->parseCurrentBlock();
//				}
//				break;
//
//			case 'glo':
//				$tpl->setVariable("HITS",$this->lng->txt('search_hits'));
//				include_once './Modules/Glossary/classes/class.ilGlossaryTerm.php';
//
//				$this->lng->loadLanguageModule('content');
//				foreach($item['child'] as $child)
//					{
//						$tpl->setCurrentBlock("link_row");
//						$tpl->setVariable("CHAPTER_PAGE",$this->lng->txt('cont_term'));
//						$tpl->setVariable("SEPERATOR",': ');
//						$tpl->setVariable("LINK",ilLink::_getLink($item['ref_id'],'git',array('target' => 'git_'.$child.'_'.$item['ref_id'])));
//						$tpl->setVariable("TITLE",ilGlossaryTerm::_lookGlossaryTerm($child));
//						$tpl->parseCurrentBlock();
//					}
//				break;
//
//			case 'wiki':
//				$tpl->setVariable("HITS",$this->lng->txt('search_hits'));
//				include_once './Modules/Wiki/classes/class.ilWikiPage.php';
//				include_once './Modules/Wiki/classes/class.ilWikiUtil.php';
//
//				$this->lng->loadLanguageModule('wiki');
//				foreach($item['child'] as $child)
//				{
//					$page_title = ilWikiPage::lookupTitle($child);
//					$tpl->setCurrentBlock("link_row");
//					$tpl->setVariable("CHAPTER_PAGE",$this->lng->txt('wiki_page'));
//					$tpl->setVariable("SEPERATOR",': ');
//					$tpl->setVariable("LINK",ilLink::_getLink($item['ref_id'],'wiki',array(), "_".
//						ilWikiUtil::makeUrlTitle($page_title)));
//					$tpl->setVariable("TITLE", $page_title);
//					$tpl->parseCurrentBlock();
//				}
//				break;
//
//			case 'mcst':
//				$tpl->setVariable("HITS",$this->lng->txt('search_hits'));
//				include_once("./Services/News/classes/class.ilNewsItem.php");
//			
//				foreach($item['child'] as $child)
//				{
//					$tpl->setCurrentBlock("link_row");
//					//$tpl->setVariable("CHAPTER_PAGE",$this->lng->txt('item'));
//
//					$item_list_gui->setChildId($child);
//					//$tpl->setVariable("SEPERATOR",': ');
//					$tpl->setVariable("LINK", $item_list_gui->getCommandLink('listItems'));
//					$tpl->setVariable("TARGET", $item_list_gui->getCommandFrame(''));
//					$tpl->setVariable("TITLE", ilNewsItem::_lookupTitle($child));
//					$tpl->parseCurrentBlock();
//				}
//				break;
//
//			default:
//				;
//		}
//
//		return $html .$tpl->get();
//	}

	public function resetRowType()
	{
		$this->cur_row_type = "";
	}

	/**
	 * @param $cur_obj_type
	 * @param $item
	 * @return array
	 */
	private function getObjectListItem($cur_obj_type, $item)
	{
		if($cur_obj_type != $item['type'])
		{
			include_once 'Services/Object/classes/class.ilObjectListGUIFactory.php';
			$item_list_gui = ilObjectListGUIFactory::_getListGUIByType($item['type']);
		}
		$item_list_gui->initItem($item['ref_id'], $item['obj_id'], $item['title'], $item['description'], ilObjectListGUI::CONTEXT_SHOP);
		$item_list_gui->enableDelete(false);
		$item_list_gui->enableCut(false);
		$item_list_gui->enableCopy(false);
		$item_list_gui->enableLink(false);
		$item_list_gui->enableSubscribe(false);

		$item_list_gui->enablePayment(true);
		$item_list_gui->enableCommands(true);
		$item_list_gui->enablePath(false);
		$item_list_gui->insertCommands();

		$item_list_gui->enableInfoScreen(false);
		$item_list_gui->enableSubstitutions(false);
		$item_list_gui->enableNoticeProperties(false);
		$item_list_gui->enablePreconditions(false);
		$item_list_gui->enableProperties(false);
		$item_list_gui->setBoldTitle(true);

		if(ilPaymentObject::_hasAccess($item['ref_id']))
		{
			$item_list_gui->enableInfoScreen(true);
			$item_list_gui->enableCommands(true);
		}
//		else
//		{
			switch($item['type'])
			{
				case 'sahs':
					$demo_link = 'ilias.php?baseClass=ilSAHSPresentationGUI&ref_id=' . $item['ref_id'] . '&purchasetype=demo';
					break;

				case 'lm':
					$demo_link = 'ilias.php?baseClass=ilLMPresentationGUI&ref_id=' . $item['ref_id'] . '&purchasetype=demo';
					break;

				case 'exc':
					$demo_link = $this->ctrl->getLinkTargetByClass('ilshoppurchasegui', 'showDemoVersion') . '&purchasetype=demo&ref_id=' . $item["ref_id"];
					break;

				default:
					$demo_link = $this->ctrl->getLinkTargetByClass('ilshoppurchasegui', 'showDemoVersion') . '&purchasetype=demo&ref_id=' . $item["ref_id"];
					break;
			}

			$item['title'] = '<a href="' . $demo_link . '">' . $item["title"] . '</a>';
//		}

		$tpl_pinfo = new ilTemplate ('tpl.shop_item_info.html', true, true, 'Services/Payment');
		if($item['price_string'] != '')
		{
			$tpl_pinfo->setCurrentBlock('ploop');
			$tpl_pinfo->setVariable('PROPERTY', $this->lng->txt('price_a'));
			$tpl_pinfo->setVariable('VALUE', $item['price_string']);
			$tpl_pinfo->parseCurrentBlock();
		}
		if($item['author'] != '')
		{
			$tpl_pinfo->setCurrentBlock('ploop');
			$tpl_pinfo->setVariable('PROPERTY', $this->lng->txt('author'));
			$tpl_pinfo->setVariable('VALUE', $item['author']);
			$tpl_pinfo->parseCurrentBlock();
		}
		$oFile = new ilFileDataShop(ilPaymentObject::_lookupPobjectId($item['ref_id']));
		if(($webpath_file = $oFile->getCurrentImageWebPath()) !== false)
		{
			$tpl_pinfo->setCurrentBlock('image');
			$tpl_pinfo->setVariable('SRC', $webpath_file);
			$tpl_pinfo->setVariable('ALT', strip_tags($item['title']));
			$tpl_pinfo->parseCurrentBlock();
		}

		$item_list_gui->addSubItemHTML($tpl_pinfo->get());

		$html = $item_list_gui->getListItemHTML($item['ref_id'], $item['obj_id'], $item['title'], $item['description'], false, false, "", ilObjectListGUI::CONTEXT_SHOP);
		return array($item, $html);
	}
	
	public function showAdvancedSearchResults()
	{
		$this->result = $this->result->getResultsForPresentation();
		return $this->showSpecials();
	}
}