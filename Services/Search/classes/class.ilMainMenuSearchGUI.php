<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
 * Add a search box to main menu
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 *
 * @ingroup ServicesSearch
 */
class ilMainMenuSearchGUI
{
	protected $tpl = null;
	protected $lng = null;
	
	private $ref_id = ROOT_FOLDER_ID;
	private $obj_id = 0;
	private $type = '';
	private $isContainer = true;
	
	/**
	 * Constructor
	 * @access public
	 */
	public function __construct()
	{
		global $lng,$objDefinition,$tree;
		
		$this->lng = $lng;
		
		if(isset($_GET['ref_id']))
		{
			$this->ref_id = (int )$_GET['ref_id'];
		}
		$this->obj_id = ilObject::_lookupObjId($this->ref_id);
		$this->type = ilObject::_lookupType($this->obj_id);

		$lng->loadLanguageModule("search");
		
		/*
		if(!$objDefinition->isContainer($this->type))
		{
			$this->isContainer = false;
			$parent_id = $tree->getParentId($this->ref_id);
			$this->obj_id = ilObject::_lookupObjId($parent_id);
			$this->type = ilObject::_lookupType($this->obj_id);
		}
		*/
	}
	
	public function getHTML()
	{
		global $ilCtrl, $tpl, $lng, $ilUser;
		
		if(!$this->isContainer)
		{
			#return '';
		}
		if($_GET['baseClass'] == 'ilSearchController')
		{
//			return '';
		}
		
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initAutocomplete();
		$this->tpl = new ilTemplate('tpl.main_menu_search.html',true,true,'Services/Search');
		$this->tpl->setVariable('FORMACTION','ilias.php?baseClass=ilSearchController&cmd=post'.
			'&rtoken='.$ilCtrl->getRequestToken().'&fallbackCmd=remoteSearch');
		$this->tpl->setVariable('BTN_SEARCH',$this->lng->txt('search'));
		$this->tpl->setVariable('CONT_REF_ID',ROOT_FOLDER_ID);
		$this->tpl->setVariable('ID_AUTOCOMPLETE', "mm_sr_auto");
		$this->tpl->setVariable('YUI_DATASOURCE', "ilias.php?baseClass=ilSearchController&cmd=autoComplete");
		
		$this->tpl->setVariable('IMG_MM_SEARCH', ilUtil::img(ilUtil::getImagePath("icon_seas_s.gif")));
		
		// search link menu
		//$this->tpl->setVariable('ARROW', ilUtil::getImagePath("mm_down_arrow_dark.gif"));
		//$this->tpl->setVariable('SRC_ICON', ilUtil::getImagePath("icon_seas_s.gif"));
		//$this->tpl->setVariable('TXT_LAST_SEARCH', " > ".$lng->txt("last_search_result"));
		//$this->tpl->setVariable('HREF_LAST_SEARCH', "ilias.php?baseClass=ilSearchController");
		
		if ($ilUser->getId() != ANONYMOUS_USER_ID)
		{
			include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
			$list = new ilGroupedListGUI();
			$list->addEntry($lng->txt("last_search_result"), "ilias.php?baseClass=ilSearchController");
			$this->tpl->setVariable('SEARCH_LINK_MENU', $list->getHTML());
			$this->tpl->setVariable('TXT_SEARCH', $lng->txt("search"));
			include_once("./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");
			$ov = new ilOverlayGUI("mm_search_menu");
			//$ov->setTrigger("main_menu_search", "none",
			//	"main_menu_search", "tr", "br");
			//$ov->setAnchor("main_menu_search", "tr", "br");
			$ov->setAutoHide(false);
			$ov->add();
		}
		
		return $this->tpl->get();
	} 
}
?>
