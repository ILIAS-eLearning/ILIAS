<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

include_once('Services/Search/classes/class.ilSearchResultPresentationGUI.php');
include_once 'payment/classes/class.ilPaymentPrices.php';
include_once 'payment/classes/class.ilPaymentObject.php';
include_once 'Services/Payment/classes/class.ilFileDataShop.php';

/**
* Class ilShopResultPresentationGUI
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
* 
* @ingroup ServicesPayment
*/
class ilShopResultPresentationGUI extends ilSearchResultPresentationGUI
{	
	private $sort_field = '';
	private $sort_direction = '';

	public function ilShopResultPresentationGUI($result)
	{
		parent::__construct($result);		
	}	
	
	function showResults()
	{
		// Get results
		$results = $this->result->getResultsForPresentation();

		return $html = $this->renderItemList($results);
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
		global $ilUser;
		
		$items_counter = 0;
					
		$cur_obj_type = '';
		$tpl = $this->newBlockTemplate();
		$first = true;
		
		foreach($this->type_ordering as $act_type)
		{
			$item_html = array();

			if(count($results[$topic['id']][$act_type]))
			{
				foreach($results[$topic['id']][$act_type] as $key => $item)
				{
					$oPaymentObject =
						new ilPaymentObject($ilUser, ilPaymentObject::_lookupPobjectId($item['ref_id']));					
					$oPrice = new ilPaymentPrices($oPaymentObject->getPobjectId());
					$lowest_price = $oPrice->getLowestPrice();
					
					$results[$topic['id']][$act_type][$key]['price'] = 
						ilPaymentPrices::_formatPriceToFloat($lowest_price['unit_value'], 
															 $lowest_price['sub_unit_value']);
					$results[$topic['id']][$act_type][$key]['price_string'] =										 
						($oPrice->getNumberOfPrices() > 1 ? $this->lng->txt('price_from').' ' : '').
					    ilPaymentPrices::_formatPriceToString($lowest_price['unit_value'], $lowest_price['sub_unit_value']);

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
					if ($cur_obj_type != $item['type'])
					{
						include_once 'classes/class.ilObjectListGUIFactory.php';	
						$item_list_gui = ilObjectListGUIFactory::_getListGUIByType($item['type']);
					}
					
					$item_list_gui->enableDelete(false);						
					$item_list_gui->enableCut(false);
					$item_list_gui->enableLink(false);
					$item_list_gui->enableSubscribe(false);															
					$item_list_gui->enablePayment(true);
					$item_list_gui->enableCommands(false);
					$item_list_gui->enablePath(false);
					
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
						$tpl_pinfo->setVariable('ALT', $item['title']);
						$tpl_pinfo->parseCurrentBlock();
					}
									
					$item_list_gui->addSubItemHTML($tpl_pinfo->get());
					
					$html = $item_list_gui->getListItemHTML(
						$item['ref_id'],
						$item['obj_id'], 
						$item['title'], 
						$item['description']
					);

					if($html)
					{
						$html = $this->__appendChildLinks($html, $item, $item_list_gui);
						$item_html[$item['ref_id']] = $html;
					}
				}		
				
				// output block for resource type
				if(count($item_html) > 0)
				{
					// separator row
					if (!$first)
					{
						$this->addSeparatorRow($tpl);
					}
					$first = false;
						
					// add a header for each resource type
					$this->addHeaderRow($tpl, $act_type);
					$this->resetRowType();
						
					// content row
					foreach($item_html as $ref_id => $html)
					{
						$this->addStandardRow($tpl, $html, $ref_id);
					}
					
					++$items_counter;
				}
			}
		}
		
		if($items_counter > 0)
		{
			$oContainerTpl->setCurrentBlock('loop');			
			$oContainerTpl->setVariable('TOPIC_TITLE', $topic['title']);
			$oContainerTpl->setVariable('COTAINER_LIST_BLOCK', $tpl->get());
			$oContainerTpl->parseCurrentBlock();
		}
	}
	
	public function renderItemList($results)
	{
		global $ilUser;

		$oContainerTpl = new ilTemplate ('tpl.shop_container.html', true, true, 'Services/Payment');	

		foreach($this->result->getTopics() as $oTopic)
		{
			$this->renderItems($oContainerTpl, $results, array('id' => $oTopic->getId(), 'title' => $oTopic->getTitle()));
		}
		$this->renderItems($oContainerTpl, $results, array('id' => 0, 'title' => $this->lng->txt('payment_no_topic')));
		
		return $oContainerTpl->get();
	}
	
	/**
	* adds a standard row to a block template
	*
	* @param	object		$a_tpl		block template
	* @param	string		$a_html		html code
	* @access	private
	*/
	function addStandardRow(&$a_tpl, $a_html,$a_ref_id)
	{
		$this->cur_row_type = ($this->cur_row_type == "row_type_1")
			? "row_type_2"
			: "row_type_1";

		$a_tpl->touchBlock($this->cur_row_type);
		$a_tpl->setCurrentBlock("container_standard_row");
		$a_tpl->setVariable("BLOCK_ROW_CONTENT", $a_html);
		
		// add checkbox for saving results
		#$a_tpl->setVariable("BLOCK_ROW_CHECK",ilUtil::formCheckbox(0,'result[]',$a_ref_id));
		#$a_tpl->setVariable("ITEM_ID",$a_ref_id);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}
}

?>