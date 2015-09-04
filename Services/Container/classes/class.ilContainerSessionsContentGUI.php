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
	protected $force_details = array();
	
	/**
	* Constructor
	*
	*/
	function __construct($container_gui_obj)
	{
		global $lng;
		
		parent::__construct($container_gui_obj);
		$this->lng = $lng;
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
		if(in_array($a_session_id,$this->force_details))
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
		global $lng,$ilTabs;

		// see bug #7452
//		$ilTabs->setSubTabActive($this->getContainerObject()->getType().'_content');


		include_once 'Services/Object/classes/class.ilObjectListGUIFactory.php';

		$tpl = new ilTemplate("tpl.container_page.html", true, true,
			"Services/Container");

		// Feedback
		// @todo
//		$this->__showFeedBack();

		$this->__showMaterials($tpl);
			
		// @todo: Move this completely to GUI class?
/*		$this->getContainerGUI()->adminCommands = $this->adminCommands;
		$this->getContainerGUI()->showAdministrationPanel($tpl);
		$this->getContainerGUI()->showPossibleSubObjects();
		$this->getContainerGUI()->showPermanentLink($tpl);*/

		return $tpl->get();
	}

	/**
	* Show Materials
	*/
	function __showMaterials($a_tpl)
	{
		global $lng;

		$this->items = $this->getContainerObject()->getSubItems($this->getContainerGUI()->isActiveAdministrationPanel());
		$this->clearAdminCommandsDetermination();
		
		$this->initRenderer();
		
		$output_html = $this->getContainerGUI()->getContainerPageHTML();
		
		// get embedded blocks
		if ($output_html != "")
		{
			$output_html = $this->insertPageEmbeddedBlocks($output_html);
		}
		
		if (is_array($this->items["sess"]) ||
			$this->items['sess_link']['prev']['value'] ||
			$this->items['sess_link']['next']['value'])
		{
			$this->items['sess'] = ilUtil::sortArray($this->items['sess'],'start','ASC',true,true);
			
			if($this->items['sess_link']['prev']['value'])
			{
				$prefix = $this->renderSessionLimitLink(true);
			}
			if($this->items['sess_link']['next']['value'])
			{
				$postfix = $this->renderSessionLimitLink(false);
			}	
			
			$this->renderer->addTypeBlock("sess", $prefix, $postfix);
			$this->renderer->setBlockPosition("sess", 1);
			
			$position = 1;
			
			foreach($this->items["sess"] as $item_data)
			{											
				if (!$this->renderer->hasItem($item_data["child"]))
				{	
					$html = $this->renderItem($item_data, $position++, true);
					if ($html != "")
					{
						$this->renderer->addItemToBlock("sess", $item_data["type"], $item_data["child"], $html);
					}
				}
			}			
		}

		$pos = $this->getItemGroupsHTML(1);
		
		if (is_array($this->items["_all"]))
		{
			$this->renderer->addCustomBlock("_all", $lng->txt("content"));
			$this->renderer->setBlockPosition("_all", ++$pos);
						
			$position = 1;
			
			foreach($this->items["_all"] as $item_data)
			{
				// #14599
				if ($item_data["type"] == "sess" || $item_data["type"] == "itgr")
				{
					continue;
				}
				
				if (!$this->renderer->hasItem($item_data["child"]))
				{					
					$html = $this->renderItem($item_data, $position++, true);
					if ($html != "")
					{
						$this->renderer->addItemToBlock("_all", $item_data["type"], $item_data["child"], $html);
					}
				}
			}			
		}

		$output_html .= $this->renderer->getHTML();
		
		$a_tpl->setVariable("CONTAINER_PAGE_CONTENT", $output_html);
	}

	/**
	 * Show link to show/hide all previous/next sessions
	 * @return string
	 */
	protected function renderSessionLimitLink($a_previous = true)
	{
		global $lng, $ilUser, $ilCtrl;
		
		$lng->loadLanguageModule('crs');

		$tpl = new ilTemplate('tpl.container_list_item.html',true,true,
			"Services/Container");
		$tpl->setVariable('DIV_CLASS','ilContainerListItemOuter');
		$tpl->setCurrentBlock('item_title_linked');

		if($a_previous)
		{
			$prefp = $ilUser->getPref('crs_sess_show_prev_'.$this->getContainerObject()->getId());
			
			if($prefp)
			{
				$tpl->setVariable('TXT_TITLE_LINKED',$lng->txt('crs_link_hide_prev_sessions'));
				$ilCtrl->setParameterByClass(get_class($this->getContainerGUI()),'crs_prev_sess',(int) !$prefp);
				$tpl->setVariable('HREF_TITLE_LINKED',$ilCtrl->getLinkTargetByClass(get_class($this->getContainerGUI())));
				$ilCtrl->clearParametersByClass(get_class($this->getContainerGUI()));
			}
			else
			{
				$tpl->setVariable('TXT_TITLE_LINKED',$lng->txt('crs_link_show_all_prev_sessions'));
				$ilCtrl->setParameterByClass(get_class($this->getContainerGUI()),'crs_prev_sess',(int) !$prefp);
				$tpl->setVariable('HREF_TITLE_LINKED',$ilCtrl->getLinkTargetByClass(get_class($this->getContainerGUI())));
				$ilCtrl->clearParametersByClass(get_class($this->getContainerGUI()));
			}
		}
		else
		{
			$prefn = $ilUser->getPref('crs_sess_show_next_'.$this->getContainerObject()->getId());

			if($prefn)
			{
				$tpl->setVariable('TXT_TITLE_LINKED',$lng->txt('crs_link_hide_next_sessions'));
				$ilCtrl->setParameterByClass(get_class($this->getContainerGUI()),'crs_next_sess',(int) !$prefn);
				$tpl->setVariable('HREF_TITLE_LINKED',$ilCtrl->getLinkTargetByClass(get_class($this->getContainerGUI())));
				$ilCtrl->clearParametersByClass(get_class($this->getContainerGUI()));
			}
			else
			{
				$tpl->setVariable('TXT_TITLE_LINKED',$lng->txt('crs_link_show_all_next_sessions'));
				$ilCtrl->setParameterByClass(get_class($this->getContainerGUI()),'crs_next_sess',(int) !$prefn);
				$tpl->setVariable('HREF_TITLE_LINKED',$ilCtrl->getLinkTargetByClass(get_class($this->getContainerGUI())));
				$ilCtrl->clearParametersByClass(get_class($this->getContainerGUI()));
			}
		}
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}
	
	
	/**
	 * add footer row
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function addFooterRow($tpl)
	{
		global $ilCtrl;

		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
		
		$tpl->setCurrentBlock('container_details_row');
		$tpl->setVariable('TXT_DETAILS',$this->lng->txt('details'));
		$tpl->parseCurrentBlock();
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

		include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
		if($session = ilSessionAppointment::lookupNextSessionByCourse($this->getContainerObject()->getRefId()))
		{
			$this->force_details = $session;
		}
		elseif($session = ilSessionAppointment::lookupLastSessionByCourse($this->getContainerObject()->getRefId()))
		{
			$this->force_details = array($session);
		}
	}
	
	

} // END class.ilContainerSessionsContentGUI
?>
