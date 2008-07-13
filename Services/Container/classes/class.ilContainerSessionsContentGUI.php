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

include_once("./Services/Container/classes/class.ilContainerContentGUI.php");

/**
* Shows all items in one block.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilContainerSessionsContentGUI extends ilContainerContentGUI
{
	/**
	* Constructor
	*
	*/
	function __construct($container_gui_obj)
	{
		parent::__construct($container_gui_obj);
	}


	/**
	* Get content HTML for main column. 
	*/
	function getMainContent()
	{
		global $lng;

		include_once './classes/class.ilObjectListGUIFactory.php';

		$tpl = new ilTemplate ("tpl.container_page.html", true, true,
			"Services/Container");

		// Feedback
		// @todo
//		$this->__showFeedBack();

		$this->__showMaterials($tpl);
			
		// @todo: Move this completely to GUI class?
		$this->getContainerGUI()->showAdministrationPanel($tpl);
		$this->getContainerGUI()->showPermanentLink($tpl);

		return $tpl->get();
	}

	/**
	* Show Materials
	*/
	function __showMaterials($a_tpl)
	{
		global $ilAccess, $lng;

		$this->items = $this->getContainerObject()->getSubItems();
		$this->clearAdminCommandsDetermination();
		
		$output_html = $this->getContainerGUI()->getContainerPageHTML();
		
		// get embedded blocks
		if ($output_html != "")
		{
			$output_html = $this->insertPageEmbeddedBlocks($output_html);
		}

		// sessions
		$done_sessions = false;
		$tpl = $this->newBlockTemplate();
		if (is_array($this->items["sess"]))
		{
			$this->items['sess'] = ilUtil::sortArray($this->items['sess'],'start','DESC',true,true);
			
			// all rows
			$item_html = array();
			foreach($this->items["sess"] as $item_data)
			{
				if ($this->rendere_items[$item_data["child"]] !== true)
				{
					$html = $this->renderItem($item_data, true);
					if ($html != "")
					{
						$item_html[] = $html;
					}
				}
			}
			
			// if we have at least one item, output the block
			if (count($item_html) > 0)
			{
				$this->addHeaderRow($tpl, "", $lng->txt("objs_sess"));
				foreach($item_html as $h)
				{
					$this->addStandardRow($tpl, $h);
				}
				$done_sessions = true;
			}
		}

		// all other items
		if ($done_sessions)
		{
			$this->addSeparatorRow($tpl);
		}
		if (is_array($this->items["_all"]))
		{
			// all rows
			$item_html = array();
			foreach($this->items["_all"] as $item_data)
			{
				if ($this->rendere_items[$item_data["child"]] !== true)
				{
					if ($item_data["type"] == "sess")
					{
						continue;
					}
					$html = $this->renderItem($item_data, true);
					if ($html != "")
					{
						$item_html[] = $html;
					}
				}
			}
			
			// if we have at least one item, output the block
			if (count($item_html) > 0)
			{
				$this->addHeaderRow($tpl, "", $lng->txt("content"));
				foreach($item_html as $h)
				{
					$this->addStandardRow($tpl, $h);
				}
			}
		}

		$output_html .= $tpl->get();
		
		$a_tpl->setVariable("CONTAINER_PAGE_CONTENT", $output_html);
	}

} // END class.ilContainerSessionsContentGUI
?>
