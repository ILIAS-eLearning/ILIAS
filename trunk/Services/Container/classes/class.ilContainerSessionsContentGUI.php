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
		global $ilAccess, $lng;

		$this->items = $this->getContainerObject()->getSubItems($this->getContainerGUI()->isActiveAdministrationPanel());
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
		if (
				is_array($this->items["sess"]) or 
				$this->items['sess_link']['prev']['value'] or
				$this->items['sess_link']['next']['value'])
		{
			$this->items['sess'] = ilUtil::sortArray($this->items['sess'],'start','ASC',true,true);
			
			// all rows
			$item_html = array();
			$position = 1;
			
			if($this->items['sess_link']['prev']['value'])
			{
				$item_html[] = $this->renderSessionLimitLink(true);
			}
			
			foreach($this->items["sess"] as $item_data)
			{
				if ($this->rendered_items[$item_data["child"]] !== true)
				{
					$html = $this->renderItem($item_data,$position++,true);

					if ($html != "")
					{
						$item_html[] = $html;
					}
				}
			}
			if($this->items['sess_link']['next']['value'])
			{
				$item_html[] = $this->renderSessionLimitLink(false);
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
		
		// item groups
		$this->getItemGroupsHTML($tpl);

		
		if (is_array($this->items["_all"]))
		{
			// all rows
			$item_html = array();
			$position = 1;
			foreach($this->items["_all"] as $item_data)
			{
				if ($this->rendered_items[$item_data["child"]] !== true)
				{
					if ($item_data["type"] == "sess" || $item_data["type"] == "itgr")
					{
						continue;
					}
					$html = $this->renderItem($item_data,$position++,true);
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
		
		$tpl->setCurrentBlock('details_img');
		
		$append = $this->details_level == 1 ? 'off' : '';
		$tpl->setCurrentBlock('details_img');
		$tpl->setVariable('DETAILS_SRC',ilUtil::getImagePath('details1'.$append.'.png'));
		$tpl->setVariable('DETAILS_ALT',$this->lng->txt('details').' 1');
		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->getContainerObject()->getRefId());
		$ilCtrl->setParameterByClass("ilrepositorygui", "details_level", "1");
		$tpl->setVariable('DETAILS_LINK',
			$ilCtrl->getLinkTargetByClass("ilrepositorygui", ""));
		$tpl->parseCurrentBlock();

		$append = $this->details_level == 2 ? 'off' : '';
		$tpl->setCurrentBlock('details_img');
		$tpl->setVariable('DETAILS_SRC',ilUtil::getImagePath('details2'.$append.'.png'));
		$tpl->setVariable('DETAILS_ALT',$this->lng->txt('details').' 2');
		$ilCtrl->setParameterByClass("ilrepositorygui", "details_level", "2");
		$tpl->setVariable('DETAILS_LINK',
			$ilCtrl->getLinkTargetByClass("ilrepositorygui", ""));
		$tpl->parseCurrentBlock();

		$append = $this->details_level == 3 ? 'off' : '';
		$tpl->setCurrentBlock('details_img');
		$tpl->setVariable('DETAILS_SRC',ilUtil::getImagePath('details3'.$append.'.png'));
		$tpl->setVariable('DETAILS_ALT',$this->lng->txt('details').' 3');
		$ilCtrl->setParameterByClass("ilrepositorygui", "details_level", "3");
		$tpl->setVariable('DETAILS_LINK',
			$ilCtrl->getLinkTargetByClass("ilrepositorygui", ""));
		$tpl->parseCurrentBlock();
		
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
