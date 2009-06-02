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

include_once("Services/Block/classes/class.ilBlockGUI.php");
include_once './Services/PersonalDesktop/interfaces/interface.ilDesktopItemHandling.php';

/**
* BlockGUI class for Selected Items on Personal Desktop
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilPDSelectedItemsBlockGUI: ilColumnGUI
*/
class ilPDSelectedItemsBlockGUI extends ilBlockGUI implements ilDesktopItemHandling
{
	const VIEW_MY_OFFERS = 0;
	const VIEW_MY_MEMBERSHIPS = 1;
	
	static $block_type = "pditems";
	
	private $view = self::VIEW_MY_OFFERS;
	private $allowed_views = array();
	
	/**
	* Constructor
	*/
	function ilPDSelectedItemsBlockGUI()
	{
		global $ilCtrl, $lng, $ilUser;
		
		parent::ilBlockGUI();
		
		$lng->loadLanguageModule('pd');
		
		//$this->setImage(ilUtil::getImagePath("icon_bm_s.gif"));		
		$this->setEnableNumInfo(false);
		$this->setLimit(99999);
		$this->setColSpan(2);
		$this->setAvailableDetailLevels(3, 1);
		$this->setBigMode(true);
		$this->lng = $lng;
		$this->allow_moving = false;		
		
		$this->determineViewSettings();
	}
	
    /**
     * @see ilDesktopItemHandling::addToDesk()
     */
    public function addToDeskObject()
    {
	 	global $ilCtrl;
		
		include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
	 	ilDesktopItemGUI::addToDesktop();
		$ilCtrl->redirectByClass('ilpersonaldesktopgui', 'show');
    }
    
    /**
     * @see ilDesktopItemHandling::removeFromDesk()
     */
    public function removeFromDeskObject()
    {
	 	global $ilCtrl;
		
		include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
	 	ilDesktopItemGUI::removeFromDesktop();
		$ilCtrl->redirectByClass('ilpersonaldesktopgui', 'show');
    }
	
	
	/**
     * Method to switch between the different views of personal items block
     *
     * @access public
     */
	public function changeView()
	{
		global $ilUser, $ilCtrl;
		
		if(in_array((int)$_GET['view'], $this->allowed_views))
		{
			$ilUser->writePref('pd_view', (int)$_GET['view']);
		}
		else
		{
			@reset($this->allowed_views);
			$view = (int)@current($this->allowed_views);
			$ilUser->writePref('pd_view', $view);
		}

		$ilCtrl->redirectByClass('ilpersonaldesktopgui', 'show');
	}
	
	/**
     * Sets the current view of the user and determines which views are allowed
     *
     * @access protected
     */
	protected function determineViewSettings()
	{
		global $ilSetting, $ilUser;
		
		$this->allowed_views = array();
		
		// determine view
		if($ilSetting->get('disable_my_offers') == 1 &&
		   $ilSetting->get('disable_my_memberships') == 1)
		{
			// if both views are disabled set default view (should not occur but we should prevent it)
			$ilSetting->set('personal_items_default_view', self::VIEW_MY_OFFERS);
			$this->allowed_views[] = self::VIEW_MY_OFFERS;
		}
		// both views are enabled, get default view
		else if($ilSetting->get('disable_my_offers') == 0 &&
		   		$ilSetting->get('disable_my_memberships') == 0)
		{
			$this->allowed_views[] = self::VIEW_MY_OFFERS;
			$this->allowed_views[] = self::VIEW_MY_MEMBERSHIPS;
		}
		else if($ilSetting->get('disable_my_offers') == 0 &&
		   		$ilSetting->get('disable_my_memberships') == 1)
		{
			$this->allowed_views[] = self::VIEW_MY_OFFERS;
		}
		else
		{
			$this->allowed_views[] = self::VIEW_MY_MEMBERSHIPS;
		}
		
		$this->view = (int)$ilUser->getPref('pd_view');
		if(!in_array($this->view, $this->allowed_views))
		{
			@reset($this->allowed_views);
			$view = (int)@current($this->allowed_views);
			$ilUser->writePref('writePref', $view);
			$this->view = $view;
		}
	}
	
	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function getBlockType()
	{
		return self::$block_type;
	}
	
	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function isRepositoryObject()
	{
		return false;
	}


	function getHTML()
	{
		global $ilCtrl, $ilSetting;
		
		// both views are activated (show buttons)
		if($ilSetting->get('disable_my_offers') == 0 &&
		   $ilSetting->get('disable_my_memberships') == 0)
		{
			$ilCtrl->setParameter($this, 'block_type', $this->getBlockType());			
			$ilCtrl->setParameter($this, 'view', self::VIEW_MY_OFFERS);
			$this->addHeaderLink($ilCtrl->getLinkTarget($this, 'changeView'), $this->lng->txt('pd_my_offers'),
				($this->view == self::VIEW_MY_OFFERS ? false : true) 
			);
			$ilCtrl->setParameter($this, 'view', self::VIEW_MY_MEMBERSHIPS);
			$this->addHeaderLink($ilCtrl->getLinkTarget($this, 'changeView'), $this->lng->txt('pd_my_memberships'),
				($this->view == self::VIEW_MY_MEMBERSHIPS ? false : true) 
			);
			$ilCtrl->clearParameters($this);
		}
		
		// workaround to show details row
		$this->setData(array("dummy"));

		switch((int)$this->view)
		{
			case self::VIEW_MY_MEMBERSHIPS:				
				$this->setTitle($this->lng->txt('pd_my_memberships'));
				$this->setContent($this->getMembershipItemsBlockHTML());
				break;
							
			case self::VIEW_MY_OFFERS:			
			default:
				if(!in_array(self::VIEW_MY_MEMBERSHIPS, $this->allowed_views))
				{
					$this->setTitle($this->lng->txt('selected_items'));
				}
				else
				{
					$this->setTitle($this->lng->txt('pd_my_offers'));
				}
				
				$this->setContent($this->getSelectedItemsBlockHTML());
				break;
		}
		
		if ($this->getContent() == "")
		{
			$this->setEnableDetailRow(false);
		}
		$ilCtrl->clearParametersByClass("ilpersonaldesktopgui");
		$ilCtrl->clearParameters($this);
		return parent::getHTML();
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");
		
		if(method_exists($this, $cmd))
		{
			return $this->$cmd();
		}
		else
		{
			$cmd .= 'Object';
			return $this->$cmd();
		}
	}

	function getContent()
	{
		return $this->content;
	}
	
	function setContent($a_content)
	{
		$this->content = $a_content;
	}
	
	/**
	* Fill data section
	*/
	function fillDataSection()
	{
		global $ilUser;
		
		if ($this->getContent() != "")
		{
			$this->tpl->setVariable("BLOCK_ROW", $this->getContent());
		}
		else
		{
			$this->setDataSection($this->getIntroduction());
		}
	}
	

	/**
	* block footer
	*/
	function fillFooter()
	{
		global $ilCtrl, $lng, $ilUser;

		$this->setFooterLinks();
		$this->fillFooterLinks();
		$this->tpl->setVariable("FCOLSPAN", $this->getColSpan());
		if ($this->tpl->blockExists("block_footer"))
		{
			$this->tpl->setCurrentBlock("block_footer");
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* Set footer links.
	*/
	function setFooterLinks()
	{
		global $ilUser, $ilCtrl, $lng;
		
		if ($this->getContent() == "")
		{
			$this->setEnableNumInfo(false);
			return "";
		}
		
		// by type
		if ($ilUser->getPref("pd_order_items") == 'location')
		{
			$this->addFooterLink( $lng->txt("by_type"),
				$ilCtrl->getLinkTarget($this,
				"orderPDItemsByType"),
				$ilCtrl->getLinkTarget($this,
				"orderPDItemsByType", "", true),
				"block_".$this->getBlockType()."_".$this->block_id
				);
		}
		else
		{
			$this->addFooterLink($lng->txt("by_type"));
		}

		// by location
		if ($ilUser->getPref("pd_order_items") == 'location')
		{
			$this->addFooterLink($lng->txt("by_location"));
		}
		else
		{
			$this->addFooterLink( $lng->txt("by_location"),
				$ilCtrl->getLinkTarget($this,
				"orderPDItemsByLocation"),
				$ilCtrl->getLinkTarget($this,
				"orderPDItemsByLocation", "", true),
				"block_".$this->getBlockType()."_".$this->block_id
				);
		}
	}
	
	/**
     * Gets all objects the current user is member of
     *
     * @access protected
     * @return Array $items array of objects
     */
	protected function getObjectsByMembership($types = array())
	{	
		global $tree, $ilUser, $ilObjDataCache;
		
		include_once 'Services/Membership/classes/class.ilParticipants.php';
		$items = array();
		
		if(is_array($types) && count($types))
		{
			foreach($types as $type)
			{
				switch($type)
				{
					case 'grp':
						$items = array_merge(ilParticipants::_getMembershipByType($ilUser->getId(), 'grp'), $items);
						break;
					case 'crs':
						$items = array_merge(ilParticipants::_getMembershipByType($ilUser->getId(), 'crs'), $items);
						break;
					default:				
						break;			
				}	
			}
		}
		else
		{
			$crs_mbs = ilParticipants::_getMembershipByType($ilUser->getId(), 'crs');
			$grp_mbs = ilParticipants::_getMembershipByType($ilUser->getId(), 'grp');
			$items = array_merge($crs_mbs, $grp_mbs);
		}

		$references = array();
		foreach($items as $key => $obj_id)
		{
			$item_references = ilObject::_getAllReferences($obj_id);
			if(is_array($item_references) && count($item_references))
			{
				foreach($item_references as $ref_id)
				{
					$references[$ref_id] =
						array('ref_id' => $ref_id,
							  'obj_id' => $obj_id, 
							  'type' => $ilObjDataCache->lookupType($obj_id),
							  'title' => $ilObjDataCache->lookupTitle($obj_id),
							  'description' => $ilObjDataCache->lookupDescription($obj_id),
							  'parent_ref' => $tree->getParentId($ref_id));
				}	
			}		
		}
		return is_array($references) ? $references : array();
	}
	
	/**
     * Generates the block html string by location ordering
     *
     * @access public
     * @param ilTemplate $tpl the current template instance
     */
	public function getMembershipItemsPerLocation($tpl)
	{
		global $ilUser, $rbacsystem, $objDefinition, $ilBench, $ilSetting, $ilObjDataCache, $rbacreview;
			
		$output = false;		
		
		$items = $this->getObjectsByMembership();
		$item_html = array();		
		if(count($items) > 0)
		{
			// preload object data cache
			$ref_ids = array();
			foreach($items as $item)
			{
				$ref_ids[] = $item;
			}
			reset($items);
			$ilObjDataCache->preloadReferenceCache($ref_ids);
			
			foreach($items as $item)
			{
				//echo "1";
				// get list gui class for each object type
				if ($cur_obj_type != $item["type"])
				{
					$item_list_gui =& $this->getItemListGUI($item["type"]);
					
					$item_list_gui->enableIcon(true);
					$item_list_gui->enableDelete(false);
					$item_list_gui->enableCut(false);
					$item_list_gui->enablePayment(false);
					$item_list_gui->enableLink(false);
					$item_list_gui->enableInfoScreen(false);
					$item_list_gui->enableSubscribe(false);
					if ($this->getCurrentDetailLevel() < 3)
					{
						//echo "3";
						$item_list_gui->enableDescription(false);
						$item_list_gui->enableProperties(false);
						$item_list_gui->enablePreconditions(false);
					}
					if ($this->getCurrentDetailLevel() < 2)
					{
						$item_list_gui->enableCommands(true, true);
					}
				}
				// render item row
				$ilBench->start("ilPersonalDesktopGUI", "getListHTML");
				
				if (is_object($item_list_gui))
				{
					$html = $item_list_gui->getListItemHTML($item["ref_id"],
					$item["obj_id"], $item["title"], $item["description"]);
					$ilBench->stop("ilPersonalDesktopGUI", "getListHTML");
					if ($html != "")
					{
						// BEGIN WebDAV: Use $item_list_gui to determine icon image type
						$item_html[] = array(
							"html" => $html, 
							"item_ref_id" => $item["ref_id"],
							"item_obj_id" => $item["obj_id"],
							"parent_ref" => $item["parent_ref"],
							"type" => $item["type"],
							'item_icon_image_type' => $item_list_gui->getIconImageType()
							);
						// END WebDAV: Use $item_list_gui to determine icon image type
					}
				}
			}
			
			// output block for resource type
			if (count($item_html) > 0)
			{
				$cur_parent_ref = 0;
				
				// content row
				foreach($item_html as $item)
				{
					// add a parent header row for each new parent
					if ($cur_parent_ref != $item["parent_ref"])
					{
						if ($ilSetting->get("icon_position_in_lists") == "item_rows")
						{
							$this->addParentRow($tpl, $item["parent_ref"], false);
						}
						else
						{
							$this->addParentRow($tpl, $item["parent_ref"]);
						}
						$this->resetRowType();
						$cur_parent_ref = $item["parent_ref"];
					}
					
					// BEGIN WebDAV: Use $item_list_gui to determine icon image type.
					$this->addStandardRow($tpl, $item["html"], $item["item_ref_id"], $item["item_obj_id"], 
						$item['item_icon_image_type'], 
						"th_".$cur_parent_ref);
					// END WebDAV: Use $item_list_gui to determine icon image type.
					$output = true;
				}
			}
		}
		
		return $output;
	}
	
	/**
     * Generates the block html string by type ordering
     *
     * @access public
     * @param ilTemplate $tpl the current template instance
     */
	public function getMembershipItemsPerType($tpl)
	{
		global $ilUser, $rbacsystem, $objDefinition, $ilBench, $ilSetting, $ilObjDataCache;

		$output = false;
		
		$objtype_groups = $objDefinition->getGroupedRepositoryObjectTypes(
			array("cat", "crs", "grp", "fold"));

		$types = array();
		foreach($objtype_groups as $grp => $grpdata)
		{
			$types[] = array(
				"grp" => $grp,
				"title" => $this->lng->txt("objs_".$grp),
				"types" => $grpdata["objs"]);
		}

		foreach ($types as $t)
		{			
			$type = $t["types"];
			$title = $t["title"];
			$grp = $t["grp"];

			$items = $this->getObjectsByMembership($type);
//var_dump($items);
			// preload object data cache
			$ref_ids = array();
			foreach($items as $item)
			{
				$ref_ids[] = $item;
			}
			reset($items);
			$ilObjDataCache->preloadReferenceCache($ref_ids);
			$item_html = array();
			
			if ($this->getCurrentDetailLevel() == 3)
			{
				$rel_header = "th_".$grp;
			}
			
			if (count($items) > 0)
			{
				$tstCount = 0;
				$unsetCount = 0;
				$progressCount = 0;
				$unsetFlag = 0;
				$progressFlag = 0;
				$completedFlag = 0;
				if (strcmp($a_type, "tst") == 0) {
					$items = $this->multiarray_sort($items, "used_tries; title");
					foreach ($items as $tst_item) {
						if (!isset($tst_item["used_tries"])) {
							$unsetCount++;
						}
						elseif ($tst_item["used_tries"] == 0) {
							$progressCount++;
						}
					}
				}
				
				foreach($items as $item)
				{
					// get list gui class for each object type
					if ($cur_obj_type != $item["type"])
					{
						$class = $objDefinition->getClassName($item["type"]);
						$location = $objDefinition->getLocation($item["type"]);
						$full_class = "ilObj".$class."ListGUI";
						include_once($location."/class.".$full_class.".php");
						$item_list_gui = new $full_class();
						$item_list_gui->enableIcon(true);
						$item_list_gui->enableDelete(false);
						$item_list_gui->enableCut(false);
						$item_list_gui->enablePayment(false);
						$item_list_gui->enableLink(false);
						$item_list_gui->enableInfoScreen(false);
						$item_list_gui->enableSubscribe(false);
						if ($this->getCurrentDetailLevel() < 3)
						{
							$item_list_gui->enableDescription(false);
							$item_list_gui->enableProperties(false);
							$item_list_gui->enablePreconditions(false);
							$item_list_gui->enableNoticeProperties(false);
						}
						if ($this->getCurrentDetailLevel() < 2)
						{
							$item_list_gui->enableCommands(true, true);
						}
					}
					// render item row
					$ilBench->start("ilPersonalDesktopGUI", "getListHTML");
					
					$html = $item_list_gui->getListItemHTML($item["ref_id"],
					$item["obj_id"], $item["title"], $item["description"]);
					$ilBench->stop("ilPersonalDesktopGUI", "getListHTML");
					if ($html != "")
					{
						// BEGIN WebDAV: Use $item_list_gui to determine icon image type
						$item_html[] = array(
							"html" => $html, 
							"item_ref_id" => $item["ref_id"],
							"item_obj_id" => $item["obj_id"],
							'item_icon_image_type' => (method_exists($item_list_gui, 'getIconImageType')) ?
									$item_list_gui->getIconImageType() :
									$item['type']
							);
						// END WebDAV: Use $item_list_gui to determine icon image type
					}
				}
				
				// output block for resource type
				if (count($item_html) > 0)
				{
					// add a header for each resource type
					if ($this->getCurrentDetailLevel() == 3)
					{
						if ($ilSetting->get("icon_position_in_lists") == "item_rows")
						{
							$this->addHeaderRow($tpl, $grp, false);
						}
						else
						{
							$this->addHeaderRow($tpl, $grp);
						}
						$this->resetRowType();
					}
					
					// content row
					foreach($item_html as $item)
					{
						if ($this->getCurrentDetailLevel() < 3 ||
						$ilSetting->get("icon_position_in_lists") == "item_rows")
						{
							// BEGIN WebDAV: Use $item_list_gui to determine icon image type
								$this->addStandardRow($tpl, $item["html"], $item["item_ref_id"], $item["item_obj_id"], 
									$item['item_icon_image_type'], 
									$rel_header);
							// END WebDAV: Use $item_list_gui to determine icon image type
						}
						else
						{
							$this->addStandardRow($tpl, $item["html"], $item["item_ref_id"], $item["item_obj_id"], "", $rel_header);
						}
						$output = true;
					}
				}
			}
		}
		
		return $output;
	}
	
	/**
     * Gateway method to distinguish between sorting type
     *
     * @access public
     * @return string the generated block html string
     */
	public function getMembershipItemsBlockHTML()
	{
		global $ilUser;

		$tpl = $this->newBlockTemplate();
		
		switch($ilUser->getPref('pd_order_items'))
		{
			case 'location':
			$ok = $this->getMembershipItemsPerLocation($tpl);
			break;
			
			default:
			$ok = $this->getMembershipItemsPerType($tpl);
			break;
		}
		
		return $tpl->get();
	}

	/**
	* get selected item block
	*/
	function getSelectedItemsBlockHTML()
	{
		global $ilUser, $rbacsystem, $objDefinition, $ilBench;
		
		$tpl =& $this->newBlockTemplate();
		
		switch ($ilUser->getPref("pd_order_items"))
		{
			case "location":
			$ok = $this->getSelectedItemsPerLocation($tpl);
			break;
			
			default:
			$ok = $this->getSelectedItemsPerType($tpl);
			break;
		}
		
		return $tpl->get();
	}

	/**
	* get selected items per type
	*/
	function getSelectedItemsPerType(&$tpl)
	{
		global $ilUser, $rbacsystem, $objDefinition, $ilBench, $ilSetting, $ilObjDataCache;
		
		$items = $ilUser->getDesktopItems();
		if (count($items) > 0)
		{
			// preload object data cache
			$ref_ids = array();
			foreach($items as $item)
			{
				$ref_ids[] = $item["ref_id"];
			}
			$ilObjDataCache->preloadReferenceCache($ref_ids);
		}
		
		$output = false;
		
		$objtype_groups = $objDefinition->getGroupedRepositoryObjectTypes(
			array("cat", "crs", "grp", "fold"));

		$types = array();
		foreach($objtype_groups as $grp => $grpdata)
		{
			$types[] = array(
				"grp" => $grp,
				"title" => $this->lng->txt("objs_".$grp),
				"types" => $grpdata["objs"]);
		}

		foreach ($types as $t)
		{
			
			$type = $t["types"];
			$title = $t["title"];
			$grp = $t["grp"];

			$items = $ilUser->getDesktopItems($type);
			$item_html = array();
			
			if ($this->getCurrentDetailLevel() == 3)
			{
				$rel_header = "th_".$grp;
			}
			
			if (count($items) > 0)
			{
				$tstCount = 0;
				$unsetCount = 0;
				$progressCount = 0;
				$unsetFlag = 0;
				$progressFlag = 0;
				$completedFlag = 0;
				if (strcmp($a_type, "tst") == 0) {
					$items = $this->multiarray_sort($items, "used_tries; title");
					foreach ($items as $tst_item) {
						if (!isset($tst_item["used_tries"])) {
							$unsetCount++;
						}
						elseif ($tst_item["used_tries"] == 0) {
							$progressCount++;
						}
					}
				}
				
				foreach($items as $item)
				{
					// get list gui class for each object type
					if ($cur_obj_type != $item["type"])
					{
						$class = $objDefinition->getClassName($item["type"]);
						$location = $objDefinition->getLocation($item["type"]);
						$full_class = "ilObj".$class."ListGUI";
						include_once($location."/class.".$full_class.".php");
						$item_list_gui = new $full_class();
						$item_list_gui->enableIcon(true);
						$item_list_gui->enableDelete(false);
						$item_list_gui->enableCut(false);
						$item_list_gui->enablePayment(false);
						$item_list_gui->enableLink(false);
						$item_list_gui->enableInfoScreen(false);
						$item_list_gui->setContainerObject($this);
						if ($this->getCurrentDetailLevel() < 3)
						{
							$item_list_gui->enableDescription(false);
							$item_list_gui->enableProperties(false);
							$item_list_gui->enablePreconditions(false);
							$item_list_gui->enableNoticeProperties(false);
						}
						if ($this->getCurrentDetailLevel() < 2)
						{
							$item_list_gui->enableCommands(true, true);
						}
					}
					// render item row
					$ilBench->start("ilPersonalDesktopGUI", "getListHTML");
					
					$html = $item_list_gui->getListItemHTML($item["ref_id"],
					$item["obj_id"], $item["title"], $item["description"]);
					$ilBench->stop("ilPersonalDesktopGUI", "getListHTML");
					if ($html != "")
					{
						// BEGIN WebDAV: Use $item_list_gui to determine icon image type
						$item_html[] = array(
							"html" => $html, 
							"item_ref_id" => $item["ref_id"],
							"item_obj_id" => $item["obj_id"],
							'item_icon_image_type' => (method_exists($item_list_gui, 'getIconImageType')) ?
									$item_list_gui->getIconImageType() :
									$item['type']
							);
						// END WebDAV: Use $item_list_gui to determine icon image type
					}
				}
				
				// output block for resource type
				if (count($item_html) > 0)
				{
					// add a header for each resource type
					if ($this->getCurrentDetailLevel() == 3)
					{
						if ($ilSetting->get("icon_position_in_lists") == "item_rows")
						{
							$this->addHeaderRow($tpl, $grp, false);
						}
						else
						{
							$this->addHeaderRow($tpl, $grp);
						}
						$this->resetRowType();
					}
					
					// content row
					foreach($item_html as $item)
					{
						if ($this->getCurrentDetailLevel() < 3 ||
						$ilSetting->get("icon_position_in_lists") == "item_rows")
						{
							// BEGIN WebDAV: Use $item_list_gui to determine icon image type
								$this->addStandardRow($tpl, $item["html"], $item["item_ref_id"], $item["item_obj_id"], 
									$item['item_icon_image_type'], 
									$rel_header);
							// END WebDAV: Use $item_list_gui to determine icon image type
						}
						else
						{
							$this->addStandardRow($tpl, $item["html"], $item["item_ref_id"], $item["item_obj_id"], "", $rel_header);
						}
						$output = true;
					}
				}
			}
		}
		
		return $output;
	}
	
	/**
	* get selected items per type
	*/
	function getSelectedItemsPerLocation(&$tpl)
	{
		global $ilUser, $rbacsystem, $objDefinition, $ilBench, $ilSetting, $ilObjDataCache;
		
		$output = false;
		
		$items = $ilUser->getDesktopItems();
		$item_html = array();

		if (count($items) > 0)
		{
			// preload object data cache
			$ref_ids = array();
			foreach($items as $item)
			{
				$ref_ids[] = $item["ref_id"];
			}
			reset($items);
			$ilObjDataCache->preloadReferenceCache($ref_ids);
			
			foreach($items as $item)
			{
				//echo "1";
				// get list gui class for each object type
				if ($cur_obj_type != $item["type"])
				{
					$item_list_gui =& $this->getItemListGUI($item["type"]);
					
					$item_list_gui->enableIcon(true);
					$item_list_gui->enableDelete(false);
					$item_list_gui->enableCut(false);
					$item_list_gui->enablePayment(false);
					$item_list_gui->enableLink(false);
					$item_list_gui->enableInfoScreen(false);
					if ($this->getCurrentDetailLevel() < 3)
					{
						//echo "3";
						$item_list_gui->enableDescription(false);
						$item_list_gui->enableProperties(false);
						$item_list_gui->enablePreconditions(false);
					}
					if ($this->getCurrentDetailLevel() < 2)
					{
						$item_list_gui->enableCommands(true, true);
					}
				}
				// render item row
				$ilBench->start("ilPersonalDesktopGUI", "getListHTML");
				
				$html = $item_list_gui->getListItemHTML($item["ref_id"],
				$item["obj_id"], $item["title"], $item["description"]);
				$ilBench->stop("ilPersonalDesktopGUI", "getListHTML");
				if ($html != "")
				{
					// BEGIN WebDAV: Use $item_list_gui to determine icon image type
					$item_html[] = array(
						"html" => $html, 
						"item_ref_id" => $item["ref_id"],
						"item_obj_id" => $item["obj_id"],
						"parent_ref" => $item["parent_ref"],
						"type" => $item["type"],
						'item_icon_image_type' => $item_list_gui->getIconImageType()
						);
					// END WebDAV: Use $item_list_gui to determine icon image type
				}
			}
			
			// output block for resource type
			if (count($item_html) > 0)
			{
				$cur_parent_ref = 0;
				
				// content row
				foreach($item_html as $item)
				{
					// add a parent header row for each new parent
					if ($cur_parent_ref != $item["parent_ref"])
					{
						if ($ilSetting->get("icon_position_in_lists") == "item_rows")
						{
							$this->addParentRow($tpl, $item["parent_ref"], false);
						}
						else
						{
							$this->addParentRow($tpl, $item["parent_ref"]);
						}
						$this->resetRowType();
						$cur_parent_ref = $item["parent_ref"];
					}
					
					// BEGIN WebDAV: Use $item_list_gui to determine icon image type.
					$this->addStandardRow($tpl, $item["html"], $item["item_ref_id"], $item["item_obj_id"], 
						$item['item_icon_image_type'], 
						"th_".$cur_parent_ref);
					// END WebDAV: Use $item_list_gui to determine icon image type.
					$output = true;
				}
			}
		}
		
		return $output;
	}

		function resetRowType()
	{
		$this->cur_row_type = "";
	}
	
	/**
	* returns a new list block template
	*
	* @access	private
	* @return	object		block template
	*/
	function &newBlockTemplate()
	{
		$tpl = new ilTemplate ("tpl.pd_list_block.html", true, true, "Services/PersonalDesktop");
		$this->cur_row_type = "";
		return $tpl;
	}

	/**
	* get item list gui class for type
	*/
	function &getItemListGUI($a_type)
	{
		global $objDefinition;
		//echo "<br>+$a_type+";
		if (!is_object($this->item_list_guis[$a_type]))
		{
			$class = $objDefinition->getClassName($a_type);
			$location = $objDefinition->getLocation($a_type);
			$full_class = "ilObj".$class."ListGUI";
			//echo "<br>-".$location."/class.".$full_class.".php"."-";
			include_once($location."/class.".$full_class.".php");
			$item_list_gui = new $full_class();
			$this->item_list_guis[$a_type] =& $item_list_gui;
		}
		else
		{
			$item_list_gui =& $this->item_list_guis[$a_type];
		}
		return $item_list_gui;
	}
	
	/**
	* adds a header row to a block template
	*
	* @param	object		$a_tpl		block template
	* @param	string		$a_type		object type
	* @access	private
	*/
	function addHeaderRow(&$a_tpl, $a_type, $a_show_image = true)
	{
		global $objDefinition;
		
		$icon = ilUtil::getImagePath("icon_".$a_type.".gif");
		if (!$objDefinition->isPlugin($a_type))
		{
			$title = $this->lng->txt("objs_".$a_type);
		}
		else
		{
			include_once("./Services/Component/classes/class.ilPlugin.php");
			$title =
				ilPlugin::lookupTxt("rep_robj", $a_type, "objs_".$a_type);

		}
		$header_id = "th_".$a_type;

		if ($a_show_image)
		{
			$a_tpl->setCurrentBlock("container_header_row_image");
			$a_tpl->setVariable("HEADER_IMG", $icon);
			$a_tpl->setVariable("HEADER_ALT", $title);
		}
		else
		{
			$a_tpl->setCurrentBlock("container_header_row");
		}
		
		$a_tpl->setVariable("BLOCK_HEADER_CONTENT", $title);
		$a_tpl->setVariable("BLOCK_HEADER_ID", $header_id);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}
	
	/**
	* adds a header row to a block template
	*
	* @param	object		$a_tpl		block template
	* @param	string		$a_type		object type
	* @access	private
	*/
	function addParentRow(&$a_tpl, $a_ref_id, $a_show_image = true)
	{
		global $tree, $ilSetting;
		
		$par_id = ilObject::_lookupObjId($a_ref_id);
		$type = ilObject::_lookupType($par_id);
		if (!in_array($type, array("lm", "dbk", "sahs", "htlm")))
		{
			$icon = ilUtil::getImagePath("icon_".$type.".gif");
		}
		else
		{
			$icon = ilUtil::getImagePath("icon_lm.gif");
		}
		
		// custom icon
		if ($ilSetting->get("custom_icons") &&
		in_array($type, array("cat","grp","crs", "root")))
		{
			require_once("./Services/Container/classes/class.ilContainer.php");
			if (($path = ilContainer::_lookupIconPath($par_id, "small")) != "")
			{
				$icon = $path;
			}
		}
		
		if ($tree->getRootId() != $par_id)
		{
			$title = ilObject::_lookupTitle($par_id);
		}
		else
		{
			$nd = $tree->getNodeData(ROOT_FOLDER_ID);
			$title = $nd["title"];
			if ($title == "ILIAS")
			{
				$title = $this->lng->txt("repository");
			}
		}
		
/*		
		$item_list_gui =& $this->getItemListGUI($type);
		
		$item_list_gui->enableIcon(false);
		$item_list_gui->enableDelete(false);
		$item_list_gui->enableCut(false);
		$item_list_gui->enablePayment(false);
		$item_list_gui->enableLink(false);
		$item_list_gui->enableDescription(false);
		$item_list_gui->enableProperties(false);
		$item_list_gui->enablePreconditions(false);
		$item_list_gui->enablePath(true);
		$item_list_gui->enableCommands(false);
		$html = $item_list_gui->getListItemHTML($a_ref_id,
		$par_id, $title, "");
		
		if ($a_show_image)
		{
			$a_tpl->setCurrentBlock("container_header_row_image");
			$a_tpl->setVariable("HEADER_IMG", $icon);
			$a_tpl->setVariable("HEADER_ALT", $title);
		}
*/
//		else
//		{
			$a_tpl->setCurrentBlock("container_header_row");
//		}

		$a_tpl->setVariable("BLOCK_HEADER_CONTENT", $title);
/*		$a_tpl->setVariable("BLOCK_HEADER_CONTENT", $html);
		$a_tpl->setVariable("BLOCK_HEADER_ID", "th_".$a_ref_id);*/
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}
	
	/**
	* adds a standard row to a block template
	*
	* @param	object		$a_tpl		block template
	* @param	string		$a_html		html code
	* @access	private
	*/
	function addStandardRow(&$a_tpl, $a_html, $a_item_ref_id = "", $a_item_obj_id = "",
	$a_image_type = "", $a_related_header = "")
	{
		global $ilSetting;
		
		$this->cur_row_type = ($this->cur_row_type == "row_type_1")
		? "row_type_2"
		: "row_type_1";
		$a_tpl->touchBlock($this->cur_row_type);
		
		if ($a_image_type != "")
		{
			if (!is_array($a_image_type) && !in_array($a_image_type, array("lm", "dbk", "htlm", "sahs")))
			{
				$icon = ilUtil::getImagePath("icon_".$a_image_type.".gif");
				$title = $this->lng->txt("obj_".$a_image_type);
			}
			else
			{
				$icon = ilUtil::getImagePath("icon_lm.gif");
				$title = $this->lng->txt("learning_resource");
			}
			
			// custom icon
			if ($ilSetting->get("custom_icons") &&
			in_array($a_image_type, array("cat","grp","crs")))
			{
				require_once("./Services/Container/classes/class.ilContainer.php");
				if (($path = ilContainer::_lookupIconPath($a_item_obj_id, "small")) != "")
				{
					$icon = $path;
				}
			}
			
			$a_tpl->setCurrentBlock("block_row_image");
			$a_tpl->setVariable("ROW_IMG", $icon);
			$a_tpl->setVariable("ROW_ALT", $title);
			$a_tpl->parseCurrentBlock();
		}
		else
		{
			$a_tpl->setVariable("ROW_NBSP", "&nbsp;");
		}
		$a_tpl->setCurrentBlock("container_standard_row");
		$a_tpl->setVariable("BLOCK_ROW_CONTENT", $a_html);
		$rel_headers = ($a_related_header != "")
		? "th_selected_items ".$a_related_header
		: "th_selected_items";
		$a_tpl->setVariable("BLOCK_ROW_HEADERS", $rel_headers);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}

	/**
	* Get overview.
	*/
	function getIntroduction()
	{
		global $ilUser, $lng, $ilCtrl, $tree;
		
		switch((int)$this->view)
		{
			case self::VIEW_MY_MEMBERSHIPS:
				$tpl = new ilTemplate('tpl.pd_my_memberships_intro.html', true, true, 'Services/PersonalDesktop');
				$tpl->setVariable('IMG_PD_LARGE', ilUtil::getImagePath('icon_pd_xxl.gif'));
				$tpl->setVariable('TXT_WELCOME', $lng->txt('pd_my_memberships_intro'));
				$tpl->setVariable('TXT_INTRO_1', $lng->txt('pd_my_memberships_intro2'));
				break;
							
			case self::VIEW_MY_OFFERS:			
			default:
				// get repository link
				$nd = $tree->getNodeData(ROOT_FOLDER_ID);
				$title = $nd["title"];
				if ($title == "ILIAS")
				{
					$title = $lng->txt("repository");
				}
				
				$tpl = new ilTemplate("tpl.pd_intro.html", true, true, "Services/PersonalDesktop");
				$tpl->setVariable("IMG_PD_LARGE", ilUtil::getImagePath("icon_pd_xxl.gif"));
				$tpl->setVariable("TXT_WELCOME", $lng->txt("pdesk_intro"));
				$tpl->setVariable("TXT_INTRO_1", sprintf($lng->txt("pdesk_intro2"), $lng->txt("to_desktop")));
				$tpl->setVariable("TXT_INTRO_2", sprintf($lng->txt("pdesk_intro3"),
					'<a href="repository.php?cmd=frameset&getlast=true">'.$title.'</a>'));
				$tpl->setVariable("TXT_INTRO_3", $lng->txt("pdesk_intro4"));
				break;
		}
		
		return $tpl->get();
	}

	/**
	* order desktop items by location
	*/
	function orderPDItemsByLocation()
	{
		global $ilUser, $ilCtrl;
		
		$ilUser->writePref("pd_order_items", "location");
		
		if ($ilCtrl->isAsynch())
		{
			echo $this->getHTML();
			exit;
		}
		else
		{
			$ilCtrl->redirectByClass("ilpersonaldesktopgui", "show");
		}
	}
	
	/**
	* order desktop items by Type
	*/
	function orderPDItemsByType()
	{
		global $ilUser, $ilCtrl;
		
		$ilUser->writePref("pd_order_items", "type");
		
		if ($ilCtrl->isAsynch())
		{
			echo $this->getHTML();
			exit;
		}
		else
		{
			$ilCtrl->redirectByClass("ilpersonaldesktopgui", "show");
		}
	}

}

?>
