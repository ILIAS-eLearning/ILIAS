<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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

/** @defgroup ServicesInfoScreen Services/InfoScreen
 */

/**
* Class ilInfoScreenGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilInfoScreenGUI.php 17080 2008-07-30 08:56:48Z smeyer $
*
* @ilCtrl_Calls ilPermanentLinkGUI: ilNoteGUI, ilFeedbackGUI, ilColumnGUI, ilPublicUserProfileGUI
*
* @ingroup ServicesInfoScreen
*/
class ilPermanentLinkGUI
{
	/**
	* Example: type = "wiki", id (ref_id) = "234", append = "_Start_Page"
	*/
	function __construct($a_type, $a_id, $a_append = "")
	{
		$this->setType($a_type);
		$this->setId($a_id);
		$this->setAppend($a_append);
		$this->setIncludePermanentLinkText(true);
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
	* Get HTML for link
	*/
	function getHTML()
	{
		global $lng;
		
		$tpl = new ilTemplate("tpl.permanent_link.html", true, true,
			"Services/PermanentLink");
		
		include_once('classes/class.ilLink.php');
		$href = ilLink::_getStaticLink($this->getId(), $this->getType(),
			true, $this->getAppend());
			
		// delicous link
		$d_set = new ilSetting("delicious");
		if ($d_set->get("add_info_links") == "1")
		{
			$tpl->setCurrentBlock("delicious");
			$lng->loadLanguageModule("delic");
			$tpl->setVariable("DEL_LINK", urlencode($href));
			$tpl->setVariable("IMG_DEL", ilUtil::getImagePath("icon_delicious_s.gif"));
			$tpl->setVariable("TXT_DEL", $lng->txt("delic_add_to_delicious"));
			$tpl->parseCurrentBlock();
		}
		
		if ($this->getIncludePermanentLinkText())
		{
			$tpl->setVariable("TXT_PERMA", $lng->txt("perma_link").": ");
		}
		$tpl->setVariable("LINK", $href);
		
		return $tpl->get();
	}
}

?>
