<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

/**
* User interface for media player. Wraps flash mp3 player and similar tools.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesMediaObjects
*/
class ilMediaPlayerGUI
{
	var $file;
	var $displayHeight;
	var $mimeType;
	static $nr = 1;

	function __construct()
	{
	}

	/**
	* Set File.
	*
	* @param	string	$a_file	File
	*/
	function setFile($a_file)
	{
		$this->file = $a_file;
	}

	/**
	* Get File.
	*
	* @return	string	File
	*/
	function getFile()
	{
		return $this->file;
	}

	/**
	 * set display height
	 *
	 * @param int $dHeight
	 */
	function setDisplayHeight ($dHeight) {
		$this->displayHeight = $dHeight;
	}
	
	/**
	 * return display height of player.
	 *
	 * @return int
	 */
	function getDisplayHeight () {
		return $this->displayHeight;
	}


	function setMimeType ($value) {
	    $this->mimeType = $value;
	}

	/**
	* Get Html for MP3 Player
	*/
	function getMp3PlayerHtml()
	{
		global $tpl;
		require_once 'Services/MediaObjects/classes/class.ilObjMediaObject.php';
		$mimeType = $this->mimeType == "" ? ilObjMediaObject::getMimeType(basename($this->getFile())) : $this->mimeType;

		if (strpos($mimeType,"flv") === false 
		 && strpos($mimeType,"audio/mpeg") === false
		 && strpos($mimeType,"image/png") === false
		 && strpos($mimeType,"image/gif") === false)		
		{
   			$html = '<embed src="'.$this->getFile().'" '.
   					'type="'.$mimeType.'" '.
   					'autoplay="false" autostart="false" '.
   					'width="320" height="240" scale="tofit" ></embed>';
   			return $html;
		}
		
		$tpl->addJavaScript("./Services/MediaObjects/flash_flv_player/swfobject.js");		
		$mp_tpl = new ilTemplate("tpl.flv_player.html", true, true, "Services/MediaObjects");
		$mp_tpl->setVariable("FILE", $this->getFile());
		$mp_tpl->setVariable("PLAYER_NR", self::$nr);
		$mp_tpl->setVariable("DISPLAY_HEIGHT", "240");
		$mp_tpl->setVariable("DISPLAY_WIDTH", "320");
		self::$nr++;
		
		return $mp_tpl->get();
	}
}
?>
