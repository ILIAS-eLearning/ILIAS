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
* Shows all items grouped by type.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilContainerByTypeContentGUI extends ilContainerContentGUI
{
	protected $force_details;
	
	/**
	* Constructor
	*
	*/
	function __construct($container_gui_obj)
	{
		parent::__construct($container_gui_obj);
		$this->initDetails();
	}
	
	/**
	 * get details level
	 *
	 * @access public
	 * @param	int	$a_session_id
	 * @return	int	DEATAILS_LEVEL
	 */
	public function getDetailsLevel($a_session_id)
	{
		if($this->getContainerGUI()->isActiveAdministrationPanel())
		{
			return self::DETAILS_DEACTIVATED;
		}
		if(isset($_SESSION['sess']['expanded'][$a_session_id]))
		{
			return $_SESSION['sess']['expanded'][$a_session_id];
		}
		if($a_session_id == $this->force_details)
		{
			return self::DETAILS_ALL;
		}
		else
		{
			return self::DETAILS_TITLE;
		}
	}
	

	/**
	* Get content HTML for main column.
	*/
	function getMainContent()
	{
		global $ilBench, $ilAccess;

		$tpl = new ilTemplate("tpl.container_page.html", true, true,
			"Services/Container");
		
		// get all sub items
		$ilBench->start("ilContainerGUI", "0100_getSubItems");
		$this->items = $this->getContainerObject()->getSubItems(
			$this->getContainerGUI()->isActiveAdministrationPanel());
		$ilBench->stop("ilContainerGUI", "0100_getSubItems");

		// Show introduction, if repository is empty
		// @todo: maybe we move this
		if (count($this->items) == 0 &&
			$this->getContainerObject()->getRefId() == ROOT_FOLDER_ID &&
			$ilAccess->checkAccess("write", "", $this->getContainerObject()->getRefId()))
		{
			$html = $this->getIntroduction();
			$tpl->setVariable("CONTAINER_PAGE_CONTENT", $html);
		}
		else	// show item list otherwise
		{
			$html = $this->renderItemList();
			$tpl->setVariable("CONTAINER_PAGE_CONTENT", $html);
		}

		return $tpl->get();
	}
	
	/**
	* Render Items
	*/
	function renderItemList()
	{		
		include_once("Services/Object/classes/class.ilObjectListGUIFactory.php");
	
		$this->clearAdminCommandsDetermination();
	
		$this->initRenderer();
		
		// text/media page content
		$output_html = $this->getContainerGUI()->getContainerPageHTML();
		
		// get embedded blocks
		if ($output_html != "")
		{
			$output_html = $this->insertPageEmbeddedBlocks($output_html);
		}

		// item groups
		$pos = $this->getItemGroupsHTML();
		
		// iterate all types
		foreach ($this->getGroupedObjTypes() as $type => $v)
		{			
			if(is_array($this->items[$type]) &&
				$this->renderer->addTypeBlock($type))
			{				
				$this->renderer->setBlockPosition($type, ++$pos);
				
				$position = 1;
				
				foreach($this->items[$type] as $item_data)
				{
					$item_ref_id = $item_data["child"];
					
					if(!$this->renderer->hasItem($item_ref_id))
					{						
						$html = $this->renderItem($item_data, $position++);
						if ($html != "")
						{											
							$this->renderer->addItemToBlock($type, $item_data["type"], $item_ref_id, $html);
						}
					}
				}				
			}
		}
		
		$output_html .= $this->renderer->getHTML();
		
		return $output_html;
	}
	
	/**
	 * init details
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function initDetails()
	{
		global $ilUser;
		
		if($_GET['expand'])
		{
			if($_GET['expand'] > 0)
			{
				$_SESSION['sess']['expanded'][abs((int) $_GET['expand'])] = self::DETAILS_ALL;
			}
			else
			{
				$_SESSION['sess']['expanded'][abs((int) $_GET['expand'])] = self::DETAILS_TITLE;
			}
		}
		
		
		if($this->getContainerObject()->getType() == 'crs')
		{
			include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
			if($session = ilSessionAppointment::lookupNextSessionByCourse($this->getContainerObject()->getRefId()))
			{
				$this->force_details = $session;
			}
			elseif($session = ilSessionAppointment::lookupLastSessionByCourse($this->getContainerObject()->getRefId()))
			{
				$this->force_details = $session;
			}
		}
	}
	

} // END class.ilContainerSimpleContentGUI
?>
