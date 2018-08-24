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

		include_once "Services/jQuery/classes/class.iljQueryUtil.php";
		iljQueryUtil::initjQuery();
		iljQueryUtil::initjQueryUI();
		$this->tpl = new ilTemplate('tpl.main_menu_search.html',true,true,'Services/Search');
		
		if ($ilUser->getId() != ANONYMOUS_USER_ID)
		{
			if(ilSearchSettings::getInstance()->isLuceneUserSearchEnabled() or (int) $_GET['ref_id'])
			{
				$this->tpl->setCurrentBlock("position");
				$this->tpl->setVariable('TXT_GLOBALLY', $lng->txt("search_globally"));
				$this->tpl->setVariable('ROOT_ID', ROOT_FOLDER_ID);
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("position_hid");
				$this->tpl->setVariable('ROOT_ID_HID', ROOT_FOLDER_ID);
				$this->tpl->parseCurrentBlock();
			}
			if((int) $_GET['ref_id'])
			{
				$this->tpl->setCurrentBlock('position_rep');
				$this->tpl->setVariable('TXT_CURRENT_POSITION', $lng->txt("search_at_current_position"));
				$this->tpl->setVariable('REF_ID', (int) $_GET["ref_id"]);
				$this->tpl->parseCurrentBlock();
			}
		}

		if($ilUser->getId() != ANONYMOUS_USER_ID && ilSearchSettings::getInstance()->isLuceneUserSearchEnabled())
		{
			$this->tpl->setCurrentBlock('usr_search');
			$this->tpl->setVariable('TXT_USR_SEARCH',$this->lng->txt('search_users'));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable('FORMACTION','ilias.php?baseClass=ilSearchController&cmd=post'.
			'&rtoken='.$ilCtrl->getRequestToken().'&fallbackCmd=remoteSearch');
		$this->tpl->setVariable('BTN_SEARCH',$this->lng->txt('search'));
		
		// $this->tpl->setVariable('ID_AUTOCOMPLETE', "mm_sr_auto");
		$this->tpl->setVariable('AC_DATASOURCE', "ilias.php?baseClass=ilSearchController&cmd=autoComplete");
		
		$this->tpl->setVariable('IMG_MM_SEARCH', ilUtil::img(ilUtil::getImagePath("icon_seas.svg"),
			$lng->txt("search")));
		
		if ($ilUser->getId() != ANONYMOUS_USER_ID)
		{
			$this->tpl->setVariable('HREF_SEARCH_LINK', "ilias.php?baseClass=ilSearchController");
			$this->tpl->setVariable('TXT_SEARCH_LINK', $lng->txt("last_search_result"));						
		}
		
		// #10555 - we need the overlay for the autocomplete which is always active
		$this->tpl->setVariable('TXT_SEARCH', $lng->txt("search"));
		include_once("./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");
		$ov = new ilOverlayGUI("mm_search_menu");
		//$ov->setTrigger("main_menu_search", "none",
		//	"main_menu_search", "tr", "br");
		//$ov->setAnchor("main_menu_search", "tr", "br");
		$ov->setAutoHide(false);
		$ov->add();
		
		return $this->tpl->get();
	} 
}
?>
