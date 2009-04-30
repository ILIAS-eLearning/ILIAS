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

include_once('classes/class.ilLink.php');

/**
* @ingroup ServicesSearch
* Class ilSearchResultPresaentationGUI
*
* class for presentastion of search results. Called from class.ilSearchGUI or class.ilAdvancedSearchGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package ilias-core
*/
class ilSearchResultPresentationGUI
{
	
	
	protected $search_cache = null;

	var $tpl;
	var $lng;

	var $result = 0;

	function ilSearchResultPresentationGUI(&$result)
	{
		global $tpl,$lng,$ilCtrl,$ilUser;

		$this->lng =& $lng;
		
		$this->result =& $result;

		$this->type_ordering = array(
			"cat", "crs", "grp", "chat", "frm", "wiki", "lres",
			"glo", "webr", "file",'mcst', "exc",
			"tst", "svy", "sess","mep", "qpl", "spl");

		$this->ctrl =& $ilCtrl;
		
		include_once('Services/Search/classes/class.ilUserSearchCache.php');
		$this->search_cache = ilUserSearchCache::_getInstance($ilUser->getId());
	}

	function &showResults()
	{
		// Get results
		$results = $this->result->getResultsForPresentation();
		return $html =& $this->renderItemList($results);
	}

	function &renderItemList(&$results)
	{
		global $objDefinition;

		$html = '';

		$cur_obj_type = "";
		$tpl =& $this->newBlockTemplate();
		$first = true;
		
		foreach($this->type_ordering as $act_type)
		{
			$item_html = array();

			if (is_array($results[$act_type]))
			{
				foreach($results[$act_type] as $key => $item)
				{
					// get list gui class for each object type
					if ($cur_obj_type != $item["type"])
					{
						include_once 'Services/Search/classes/class.ilSearchObjectListFactory.php';

						$item_list_gui = ilSearchObjectListFactory::_getInstance($item['type']);
					}

					$html = $item_list_gui->getListItemHTML(
						$item["ref_id"],
						$item["obj_id"], 
						$item["title"], 
						$item["description"]);

					if($html)
					{
						$html = $this->__appendChildLinks($html,$item,$item_list_gui);
						$item_html[$item["ref_id"]] = $html;
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
				}
			}
		}

		
		return $tpl->get();
	}

	/**
	* adds a header row to a block template
	*
	* @param	object		$a_tpl		block template
	* @param	string		$a_type		object type
	* @access	private
	*/
	function addHeaderRow(&$a_tpl, $a_type)
	{
		if ($a_type != "lres")
		{
			$icon = ilUtil::getImagePath("icon_".$a_type.".gif");
			$title = $this->lng->txt("objs_".$a_type);
		}
		else
		{
			$icon = ilUtil::getImagePath("icon_lm.gif");
			$title = $this->lng->txt("learning_resources");
		}

		$a_tpl->setCurrentBlock("container_header_row_image");
		$a_tpl->setVariable("HEADER_IMG", $icon);
		$a_tpl->setVariable("HEADER_ALT", $title);
		$a_tpl->setVariable("BLOCK_HEADER_CONTENT", $title);
		$a_tpl->parseCurrentBlock();


		#$a_tpl->setCurrentBlock("container_header_row");
		#$a_tpl->parseCurrentBlock();
		#$a_tpl->touchBlock("container_row");
	}

	function resetRowType()
	{
		$this->cur_row_type = "";
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
		$a_tpl->setVariable("BLOCK_ROW_CHECK",ilUtil::formCheckbox(0,'result[]',$a_ref_id));
		$a_tpl->setVariable("ITEM_ID",$a_ref_id);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}

	/**
	* returns a new list block template
	*
	* @access	private
	* @return	object		block template
	*/
	function &newBlockTemplate()
	{
		$tpl =& new ilTemplate ("tpl.container_list_block.html",true, true,
			"Services/Container");
		$this->cur_row_type = "row_type_1";

		return $tpl;
	}

	function addSeparatorRow(&$a_tpl)
	{
		$a_tpl->touchBlock("separator_row");
		$a_tpl->touchBlock("container_row");
	}

	function __appendChildLinks($html,$item,&$item_list_gui)
	{
		if(!count($item['child']))
		{
			return $html;
		}
		$tpl = new ilTemplate('tpl.detail_links.html',true,true,'Services/Search');
		$tpl->setVariable("HITS",$this->lng->txt('search_hits'));
		
		switch($item['type'])
		{
			case 'lm':
				include_once 'Modules/LearningModule/classes/class.ilLMObject.php';
				foreach($item['child'] as $child)
				{
					$tpl->setCurrentBlock("link_row");
					
					switch(ilLMObject::_lookupType($child))
					{
						case 'pg':
							$tpl->setVariable("CHAPTER_PAGE",$this->lng->txt('obj_pg'));
							break;
						case 'st':
							$tpl->setVariable("CHAPTER_PAGE",$this->lng->txt('obj_st'));
							break;
					}
					$item_list_gui->setChildId($child);
					$tpl->setVariable("SEPERATOR",' -> ');
					$tpl->setVariable("LINK",$item_list_gui->getCommandLink('page'));
					$tpl->setVariable("TARGET",$item_list_gui->getCommandFrame('page'));
					$tpl->setVariable("TITLE",ilLMObject::_lookupTitle($child));
					$tpl->parseCurrentBlock();
				}
				break;

			case 'frm':
				include_once './Modules/Forum/classes/class.ilObjForum.php';
				
				foreach($item['child'] as $child)
				{
					$thread_post = explode('_',$child);

					$tpl->setCurrentBlock("link_row");
					$tpl->setVariable("CHAPTER_PAGE",$this->lng->txt('thread'));

					$item_list_gui->setChildId($thread_post);
					$tpl->setVariable("SEPERATOR",': ');
					$tpl->setVariable("LINK",$item_list_gui->getCommandLink('posting'));
					$tpl->setVariable("TARGET",$item_list_gui->getCommandFrame(''));
					$tpl->setVariable("TITLE",ilObjForum::_lookupThreadSubject($thread_post[0]));
					$tpl->parseCurrentBlock();
				}
				break;
							
			case 'glo':
				include_once './Modules/Glossary/classes/class.ilGlossaryTerm.php';

				$this->lng->loadLanguageModule('content');
				foreach($item['child'] as $child)
				{
					$tpl->setCurrentBlock("link_row");
					$tpl->setVariable("CHAPTER_PAGE",$this->lng->txt('cont_term'));
					$tpl->setVariable("SEPERATOR",': ');
					$tpl->setVariable("LINK",ilLink::_getLink($item['ref_id'],'git',array('target' => 'git_'.$child.'_'.$item['ref_id'])));
					$tpl->setVariable("TITLE",ilGlossaryTerm::_lookGlossaryTerm($child));
					$tpl->parseCurrentBlock();
				}
				break;

			case 'wiki':
				include_once './Modules/Wiki/classes/class.ilWikiPage.php';
				include_once './Modules/Wiki/classes/class.ilWikiUtil.php';

				$this->lng->loadLanguageModule('wiki');
				foreach($item['child'] as $child)
				{
					$page_title = ilWikiPage::lookupTitle($child);
					$tpl->setCurrentBlock("link_row");
					$tpl->setVariable("CHAPTER_PAGE",$this->lng->txt('wiki_page'));
					$tpl->setVariable("SEPERATOR",': ');
					$tpl->setVariable("LINK",ilLink::_getLink($item['ref_id'],'wiki',array(), "_".
						ilWikiUtil::makeUrlTitle($page_title)));
					$tpl->setVariable("TITLE", $page_title);
					$tpl->parseCurrentBlock();
				}
				break;

			case 'mcst':
				include_once("./Services/News/classes/class.ilNewsItem.php");
			
				foreach($item['child'] as $child)
				{
					$tpl->setCurrentBlock("link_row");
					//$tpl->setVariable("CHAPTER_PAGE",$this->lng->txt('item'));

					$item_list_gui->setChildId($child);
					//$tpl->setVariable("SEPERATOR",': ');
					$tpl->setVariable("LINK", $item_list_gui->getCommandLink('listItems'));
					$tpl->setVariable("TARGET", $item_list_gui->getCommandFrame(''));
					$tpl->setVariable("TITLE", ilNewsItem::_lookupTitle($child));
					$tpl->parseCurrentBlock();
				}
				break;

			default:
				;
		}

		return $html . $tpl->get();
	}
	

}
?>