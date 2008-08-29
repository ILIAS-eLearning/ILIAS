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
	protected $details_level;
	protected $force_details = 0;
	
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
	 * @param
	 * @return
	 */
	public function getDetailsLevel($a_session_id)
	{
		if($this->getContainerGUI()->isActiveAdministrationPanel())
		{
			return self::DETAILS_ALL;
		}
		if($this->details_level == self::DETAILS_TITLE)
		{
			return $this->details_level;
		}
		if($a_session_id == $this->force_details)
		{
			return self::DETAILS_ALL;
		}
		return $this->details_level;
	}


	/**
	* Get content HTML for main column. 
	*/
	function getMainContent()
	{
		global $lng,$ilTabs;

		$ilTabs->setSubTabActive($this->getContainerObject()->getType().'_content');


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
		if (is_array($this->items["sess"]))
		{
			$this->items['sess'] = ilUtil::sortArray($this->items['sess'],'start','ASC',true,true);
			
			// all rows
			$item_html = array();
			$position = 1;
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
			$this->addFooterRow($tpl);
			$this->addSeparatorRow($tpl);
		}
		if (is_array($this->items["_all"]))
		{
			// all rows
			$item_html = array();
			$position = 1;
			foreach($this->items["_all"] as $item_data)
			{
				if ($this->rendered_items[$item_data["child"]] !== true)
				{
					if ($item_data["type"] == "sess")
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
	 * add footer row
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function addFooterRow($tpl)
	{
		$tpl->setCurrentBlock('details_img');
		
		$append = $this->details_level == 1 ? 'off' : '';
		$tpl->setCurrentBlock('details_img');
		$tpl->setVariable('DETAILS_SRC',ilUtil::getImagePath('details1'.$append.'.gif'));
		$tpl->setVariable('DETAILS_ALT',$this->lng->txt('details').' 1');
		$tpl->setVariable('DETAILS_LINK','repository.php?ref_id='.$this->getContainerObject()->getRefId().'&details_level=1');
		$tpl->parseCurrentBlock();

		$append = $this->details_level == 2 ? 'off' : '';
		$tpl->setCurrentBlock('details_img');
		$tpl->setVariable('DETAILS_SRC',ilUtil::getImagePath('details2'.$append.'.gif'));
		$tpl->setVariable('DETAILS_ALT',$this->lng->txt('details').' 2');
		$tpl->setVariable('DETAILS_LINK','repository.php?ref_id='.$this->getContainerObject()->getRefId().'&details_level=2');
		$tpl->parseCurrentBlock();

		$append = $this->details_level == 3 ? 'off' : '';
		$tpl->setCurrentBlock('details_img');
		$tpl->setVariable('DETAILS_SRC',ilUtil::getImagePath('details3'.$append.'.gif'));
		$tpl->setVariable('DETAILS_ALT',$this->lng->txt('details').' 3');
		$tpl->setVariable('DETAILS_LINK','repository.php?ref_id='.$this->getContainerObject()->getRefId().'&details_level=3');
		$tpl->parseCurrentBlock();
		
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
		
		if(isset($_GET['details_level']))
		{
			$this->details_level = (int) $_GET['details_level'];
			ilObjUser::_writePref($ilUser->getId(),'crs_session_details',$this->details_level);
		}
		else
		{
			$this->details_level = $ilUser->getPref('crs_session_details') ? $ilUser->getPref('crs_session_details') : self::DETAILS_TITLE_DESC;
		}
		
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
	
	

} // END class.ilContainerSessionsContentGUI
?>
