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

include_once("./Services/Rating/classes/class.ilRating.php");

/**
* Class ilRatingGUI. User interface class for rating.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesRating
*/
class ilRatingGUI
{
	function __construct()
	{
		global $lng;
		
		$lng->loadLanguageModule("rating");
	}
	
		/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		
		switch($next_class)
		{
			default:
				return $this->$cmd();
				break;
		}
	}

	/**
	* Set Object.
	*
	* @param	int			$a_obj_id			Object ID
	* @param	string		$a_obj_type			Object Type
	* @param	int			$a_sub_obj_id		Subobject ID
	* @param	string		$a_sub_obj_type		Subobject Type
	*/
	function setObject($a_obj_id, $a_obj_type, $a_sub_obj_id = 0, $a_sub_obj_type = "")
	{
		global $ilUser;

		$this->obj_id = $a_obj_id;
		$this->obj_type = $a_obj_type;
		$this->sub_obj_id = $a_sub_obj_id;
		$this->sub_obj_type = $a_sub_obj_type;
		
		//$this->setSaveCmd("saveTags");
		$this->setUserId($ilUser->getId());
		//$this->setInputFieldName("il_tags");
	}
	
	/**
	* Set User ID.
	*
	* @param	int	$a_userid	User ID
	*/
	function setUserId($a_userid)
	{
		$this->userid = $a_userid;
	}

	/**
	* Get User ID.
	*
	* @return	int	User ID
	*/
	function getUserId()
	{
		return $this->userid;
	}

	/**
	* Get HTML for rating of an object (and a user)
	*/
	function getHTML()
	{
		global $lng, $ilCtrl;
		
		$ttpl = new ilTemplate("tpl.rating_input.html", true, true, "Services/Rating");
		$rating = ilRating::getRatingForUserAndObject($this->obj_id, $this->obj_type,
			$this->sub_obj_id, $this->sub_obj_type, $this->getUserId());
		
		for($i = 1; $i <= 5; $i++)
		{
			$ttpl->setCurrentBlock("rating_link");
			$ilCtrl->setParameter($this, "rating", $i);
			$ttpl->setVariable("HREF_RATING", $ilCtrl->getLinkTarget($this, "saveRating"));
			if ($rating >= $i)
			{
				$ttpl->setVariable("SRC_ICON",
					ilUtil::getImagePath("icon_rate_on.gif"));
			}
			else
			{
				$ttpl->setVariable("SRC_ICON",
					ilUtil::getImagePath("icon_rate_off.gif"));
			}
			$ttpl->setVariable("ALT_ICON", "(".$i."/5)");
			$ttpl->parseCurrentBlock();
		}
			
		$ttpl->setVariable("TXT_YOUR_RATING", $lng->txt("rating_your_rating"));
		if ($rating > 0)
		{
			$ttpl->setVariable("VAL_RATING", "(".$rating."/5)");
		}
		
		return $ttpl->get();
	}
	
	/**
	* Save Rating
	*/
	function saveRating()
	{
		ilRating::writeRatingForUserAndObject($this->obj_id, $this->obj_type,
			$this->sub_obj_id, $this->sub_obj_type, $this->getUserId(),
			ilUtil::stripSlashes($_GET["rating"]));
	}
}

?>
