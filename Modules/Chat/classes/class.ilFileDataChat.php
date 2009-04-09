<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* This class handles all operations on files for the exercise object
*  
* @author	Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
*/
require_once("classes/class.ilFileData.php");
				
class ilFileDataChat extends ilFileData
{
	var $chat_obj;

	/**
	 * path of tmp_chat directory
	* @var string path
	* @access private
	*/
	var $chat_path;

	/**
	* Constructor
	* call base constructors
	* checks if directory is writable and sets the optional obj_id
	* @param integereger obj_id
	* @access	public
	*/
	public function __construct(&$chat_obj)
	{

		parent::ilFileData();
		
		$this->chat_obj =& $chat_obj;
		$this->chat_path = parent::getPath()."/chat";

		if(!$this->__checkPath())
		{
 			$this->__createDirectory($this->chat_path);
		}
		$this->__deleteOld();
		
		if(@is_dir($this->chat_path."/chatrooms_".$_SESSION["AccountId"]))
		{
			ilUtil::delDir($this->chat_path."/chatrooms_".$_SESSION["AccountId"]);
		}
		$this->__createDirectory($this->chat_path."/chatrooms_".$_SESSION["AccountId"]);
	}

	public function addFile($filename,$data)
	{
		$fp = @fopen($this->chat_path."/chatrooms_".$_SESSION["AccountId"]."/".$filename,"w+");

		fwrite($fp,$data);
		fclose($fp);

		return $this->chat_path."/chatrooms_".$_SESSION["AccountId"];
	}

	public function zip()
	{
		ilUtil::zip($this->chat_path."/chatrooms_".$_SESSION["AccountId"],
					$this->chat_path."/ilias_chat.zip");

		return $this->chat_path."/ilias_chat.zip";
	}

	public function getChatPath()
	{
		return $this->chat_path;
	}

	// DESTUCTOR CALLED BY PEAR BASE CLASS
	public function _ilFileDataChat()
	{
		ilUtil::delDir($this->chat_path);
	}

	private function __checkPath()
	{
		if(!file_exists($this->getChatPath()))
		{
			return false;
		}
		return true;
	}

	private function __createDirectory($a_path)
	{
		return ilUtil::makeDir($a_path);
	}

	private function __deleteOld()
	{
		if(is_dir($this->getChatPath()))
		{
			$dp = opendir($this->getChatPath());
			while(($file = readdir($dp)) !== false)
			{
				if($file != '.' and $file != '..')
				{
					if(filectime($this->getChatPath()."/".$file) < (time() - 60*60*24))
					{
						ilUtil::delDir($this->getChatPath()."/".$file);
					}
				}
			}
			closedir($dp);
		}
	}
}
?>