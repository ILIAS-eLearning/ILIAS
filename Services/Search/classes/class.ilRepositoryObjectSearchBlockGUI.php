<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Block/classes/class.ilBlockGUI.php';

/**
 * Class ilRepositoryObjectSearchBlockGUI
 * Repository object search 
 * 
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 * @package ServicesSearch
 *
 */
class ilRepositoryObjectSearchBlockGUI extends ilBlockGUI
{
	public static $block_type = "objectsearch";
	public static $st_data;

	
	/**
	 * Constructor
	 * @global type $ilCtrl
	 * @global type $lng
	 */
	public function __construct($a_title)
	{
		global $ilCtrl, $lng;
		
		parent::__construct();
		
		$this->setEnableNumInfo(false);
		
		$this->setTitle($a_title);
		$this->allow_moving = false;
	}
	
	/**
	 * Get block type
	 *
	 * @return	string	Block type.
	 */
	public static function getBlockType()
	{
		return self::$block_type;
	}

	/**
	 * Is this a repository object
	 *
	 * @return	string	Block type.
	 */
	public static function isRepositoryObject()
	{
		return FALSE;
	}

	/**
	 * Get Screen Mode for current command.
	 */
	public static function getScreenMode()
	{
		return IL_SCREEN_SIDE;
	}

	/**
	 * execute command
	 */
	public function executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		switch ($next_class)
		{
			default:
				return $this->$cmd();
		}
	}

	/**
	 * Get bloch HTML code.
	 */
	public function getHTML()
	{
		global $ilCtrl, $lng, $ilUser;

		return parent::getHTML();
	}

	/**
	 * Fill data section
	 */
	public function fillDataSection()
	{
		global $ilCtrl, $lng, $ilAccess;

		$tpl = new ilTemplate("tpl.search_search_block.html", true, true, 'Services/Search');

		$lng->loadLanguageModule('search');
		$tpl->setVariable("TXT_PERFORM", $lng->txt('btn_search'));
		$tpl->setVariable("FORMACTION", $ilCtrl->getFormActionByClass('ilrepositoryobjectsearchgui', 'performSearch'));
		$tpl->setVariable("SEARCH_TERM", ilUtil::prepareFormOutput(ilUtil::stripSlashes($_POST["search_term"])));

		$this->setDataSection($tpl->get());
	}

}
?>