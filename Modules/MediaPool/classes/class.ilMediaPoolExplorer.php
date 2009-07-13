<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("classes/class.ilExplorer.php");

/*
* Explorer for Media Pools
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesMediaPool
*/
class ilMediaPoolExplorer extends ilExplorer
{

	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $root_id;
	var $media_pool;
	var $output;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilMediaPoolExplorer($a_target, &$a_media_pool)
	{
		parent::ilExplorer($a_target);

		$this->tree =& $a_media_pool->getTree();
		$this->root_id = $this->tree->readRootId();
		$this->media_pool =& $a_media_pool;
		$this->order_column = "";
		$this->setSessionExpandVariable("mepexpand");
		$this->checkPermissions(false);
		$this->setPostSort(false);
	}


	/**
	* overwritten method from base class
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader(&$tpl, $a_obj_id,$a_option)
	{
		global $lng, $ilias;

		//$tpl = new ilTemplate("tpl.tree.html", true, true);

		$tpl->setCurrentBlock("icon");
		$tpl->setVariable("ICON_IMAGE" , ilUtil::getImagePath("icon_mep_s.gif"));
		$tpl->setVariable("TXT_ALT_IMG",
			ilUtil::shortenText($this->media_pool->getTitle(), $this->textwidth, true));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("link");
		$tpl->setVariable("TITLE", ilUtil::shortenText($this->media_pool->getTitle(), $this->textwidth, true));
		$tpl->setVariable("LINK_TARGET", $this->target);
		$tpl->setVariable("TARGET", " target=\"".$this->frame_target."\"");
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("element");
		$tpl->parseCurrentBlock();

		//$this->output[] = $tpl->get();
	}

}
?>
