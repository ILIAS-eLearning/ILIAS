<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesPermanentLink Services/PermanentLink
 */

/**
* Class for permanent links
*
* @version $Id$
*
* @ilCtrl_Calls ilPermanentLinkGUI: ilNoteGUI, ilColumnGUI, ilPublicUserProfileGUI
*
* @ingroup ServicesPermanentLink
*/
class ilPermanentLinkGUI
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilObjectDataCache
	 */
	protected $obj_data_cache;

	protected $align_center = true;
	
	/**
	* Example: type = "wiki", id (ref_id) = "234", append = "_Start_Page"
	*/
	function __construct($a_type, $a_id, $a_append = "", $a_target = "")
	{
		global $DIC;

		$this->lng = $DIC->language();
		$this->ctrl = $DIC->ctrl();
		$this->obj_data_cache = $DIC["ilObjDataCache"];
		$this->setType($a_type);
		$this->setId($a_id);
		$this->setAppend($a_append);
		$this->setIncludePermanentLinkText(true);
		$this->setTarget($a_target);
	}
	
	/**
	* Set Include permanent link text.
	*
	* @param	boolean	$a_includepermanentlinktext	Include permanent link text
	*/
	function setIncludePermanentLinkText($a_includepermanentlinktext)
	{
		$this->includepermanentlinktext = $a_includepermanentlinktext;
	}

	/**
	* Get Include permanent link text.
	*
	* @return	boolean	Include permanent link text
	*/
	function getIncludePermanentLinkText()
	{
		return $this->includepermanentlinktext;
	}

	/**
	* Set Type.
	*
	* @param	string	$a_type	Type
	*/
	function setType($a_type)
	{
		$this->type = $a_type;
	}

	/**
	* Get Type.
	*
	* @return	string	Type
	*/
	function getType()
	{
		return $this->type;
	}

	/**
	* Set Id.
	*
	* @param	string	$a_id	Id
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	* Get Id.
	*
	* @return	string	Id
	*/
	function getId()
	{
		return $this->id;
	}

	/**
	* Set Append.
	*
	* @param	string	$a_append	Append
	*/
	function setAppend($a_append)
	{
		$this->append = $a_append;
	}

	/**
	* Get Append.
	*
	* @return	string	Append
	*/
	function getAppend()
	{
		return $this->append;
	}

	/**
	* Set Target.
	*
	* @param	string	$a_target	Target
	*/
	function setTarget($a_target)
	{
		$this->target = $a_target;
	}

	/**
	* Get Target.
	*
	* @return	string	Target
	*/
	function getTarget()
	{
		return $this->target;
	}

	/**
	 * Set title
	 *
	 * @param	string	title
	 */
	function setTitle($a_val)
	{
		$this->title = $a_val;
	}
	
	/**
	 * Get title
	 *
	 * @return	string	title
	 */
	function getTitle()
	{
		return $this->title;
	}
	
	/**
	 * Set center alignment
	 *
	 * @param	boolean	align the link at center
	 */
	function setAlignCenter($a_val)
	{
		$this->align_center = $a_val;
	}
	
	/**
	 * Get center alignment
	 *
	 * @return	boolean	align the link at center
	 */
	function getAlignCenter()
	{
		return $this->align_center;
	}

	/**
	* Get HTML for link
	*/
	function getHTML()
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		$ilObjDataCache = $this->obj_data_cache;
		
		$tpl = new ilTemplate("tpl.permanent_link.html", true, true,
			"Services/PermanentLink");
		
		include_once('./Services/Link/classes/class.ilLink.php');
		$href = ilLink::_getStaticLink($this->getId(), $this->getType(),
			true, $this->getAppend());
		if ($this->getIncludePermanentLinkText())
		{
			$tpl->setVariable("TXT_PERMA", $lng->txt("perma_link").":");
		}

		$title = '';
		
		// fetch default title for bookmark

		if ($this->getTitle() != "")
		{
			$title = $this->getTitle();
		}
		else if(is_numeric($this->getId()))
		{
			$obj_id = $ilObjDataCache->lookupObjId($this->getId());
			$title = $ilObjDataCache->lookupTitle($obj_id);
		}
		#if (!$title)
		#	$bookmark->setTitle("untitled");

		$tpl->setVariable("TXT_BOOKMARK_DEFAULT", $title);

		$tpl->setVariable("LINK", $href);
		
		if ($this->getAlignCenter())
		{
			$tpl->setVariable("ALIGN", "center");
		}
		else
		{
			$tpl->setVariable("ALIGN", "left");
		}
		
		if ($this->getTarget() != "")
		{
			$tpl->setVariable("TARGET", 'target="'.$this->getTarget().'"');
		}

		$bm_html = self::getBookmarksSelectionList($title, $href);
		if($bm_html)
		{
			$tpl->setVariable('SELECTION_LIST', $bm_html);
		}

		return $tpl->get();
	}
	
	/**
	 * @return string
	 */
	protected static function getBookmarksSelectionList($title, $href)
	{
		require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setId('socialbm_actions_' . md5(uniqid(rand(), true)));

		$html = '';

		if(!$GLOBALS['DIC']['ilUser']->isAnonymous() && !$GLOBALS['DIC']['ilSetting']->get('disable_bookmarks'))
		{
			$linktpl = 'ilias.php?cmd=redirect&baseClass=ilPersonalDesktopGUI&redirectClass=ilbookmarkadministrationgui&redirectCmd=newFormBookmark&param_bmf_id=1&param_return_to=true&param_bm_title=' . urlencode(urlencode($title)) . '&param_bm_link=' . urlencode(urlencode($href)) . "&param_return_to_url=" . urlencode(urlencode($_SERVER['REQUEST_URI']));
			$current_selection_list->addItem($GLOBALS['DIC']['lng']->txt("bm_add_to_ilias"), '', $linktpl, '' , $GLOBALS['DIC']['lng']->txt('bm_add_to_ilias'), '_top');
			$html = $current_selection_list->getHTML();
		}

		return $html;
	}
}
