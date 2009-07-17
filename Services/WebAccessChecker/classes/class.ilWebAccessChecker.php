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
require_once "./include/inc.header.php";
require_once "./Services/Utilities/classes/class.ilUtil.php";
require_once "./classes/class.ilObject.php";
require_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";

/**
* Class ilWebAccessChecker
*
* Checks the access rights of a directly requested content file.
* Called from a redirection script or from an include to a content page.
* - determines the related learning module and checks the permission
* - either delivers the accessed file (without redirect)
* - or redirects to the login screen (if not logged in)
* - or prints an error message (if too less rights)
*
* @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id$
*
*/
class ilWebAccessChecker
{
	var $lng;
	var $ilAccess;
	var $checked_list;
	
	/**
	* relative file path from ilias directory (without leading /)
	* @var string
	* @access private
	*/
	var $subpath;

	/**
	* absolute path in file system
	* @var string
	* @access private
	*/
	var $file;

	/**
	* params provided with the query
	* @var array
	* @access private
	*/
	var $params;


	/**
	* Content-Disposition for file delivery
	* @var string
	* @access private
	*/
	var $disposition = "inline";


	/**
	* determined mime type
	* @var string
	* @access private
	*/
	var $mimetype;      

	/**
	* errorcode for sendError
	* @var integer
	* @access private
	*/
	var $errorcode;

	    
	/**
	* errortext for sendError
	* @var integer
	* @access private
	*/
	var $errortext;


	/**
	* Constructor
	* @access	public
	*/
	function ilWebAccessChecker()
	{
		global $ilAccess, $lng, $ilLog;

		$this->lng =& $lng;
		$this->ilAccess =& $ilAccess;
		$this->checked_list = & $_SESSION["WebAccessChecked"];
		$this->params = array();

		// get the requested file and its type
		$uri = parse_url($_SERVER["REQUEST_URI"]);
		parse_str($uri["query"], $this->params);

		$pattern = ILIAS_WEB_DIR . "/" . CLIENT_ID;
		$this->subpath = urldecode(substr($uri["path"], strpos($uri["path"], $pattern)));
		$this->file = realpath(ILIAS_ABSOLUTE_PATH . "/". $this->subpath);
		
		/* debugging
		echo "<pre>";
		echo "REQUEST_URI:         ". $_SERVER["REQUEST_URI"]. "\n";
		echo "Parsed URI:          ". $uri["path"]. "\n";
		echo "DOCUMENT_ROOT:       ". $_SERVER["DOCUMENT_ROOT"]. "\n";
		echo "PHP_SELF:            ". $_SERVER["PHP_SELF"]. "\n";
		echo "SCRIPT_NAME:         ". $_SERVER["SCRIPT_NAME"]. "\n";
		echo "SCRIPT_FILENAME:     ". $_SERVER["SCRIPT_FILENAME"]. "\n";
		echo "PATH_TRANSLATED:     ". $_SERVER["PATH_TRANSLATED"]. "\n";
		echo "ILIAS_WEB_DIR:       ". ILIAS_WEB_DIR. "\n";
		echo "ILIAS_HTTP_PATH:     ". ILIAS_HTTP_PATH. "\n";
		echo "ILIAS_ABSOLUTE_PATH: ". ILIAS_ABSOLUTE_PATH. "\n";
		echo "CLIENT_ID:           ". CLIENT_ID. "\n";
		echo "CLIENT_WEB_DIR:      ". CLIENT_WEB_DIR. "\n";
		echo "subpath:             ". $this->subpath. "\n";
		echo "file:                ". $this->file. "\n";
		echo "</pre>";
		exit;
		*/

		if (file_exists($this->file))
		{
			//$this->mimetype = ilObjMediaObject::getMimeType($this->file);
			$this->mimetype = $this->getMimeType();
		}
		else
		{
			$this->errorcode = 404;
			$this->errortext = $this->lng->txt("url_not_found");
			return false;
		}
	}

	/**
	* Check access rights of the requested file
	* @access	public
	*/
	function checkAccess()
	{
		global $ilLog, $ilUser, $ilObjDataCache;
		$pos1 = strpos($this->subpath, "lm_data/lm_") + 11;
		$pos2 = strpos($this->subpath, "mobs/mm_") + 8;
		
		$obj_id = 0;
		$type = 'none';
		// trying to access data within a learning module folder
		if ($pos1 > 11)
		{
			$type = 'lm';
			$seperator = strpos($this->subpath, '/', $pos1);
			$obj_id = substr($this->subpath, $pos1, ($seperator > 0 ? $seperator : strlen($this->subpath))-$pos1);
		}
		//trying to access media data
		else if ($pos2 > 8)
		{
			$type = 'mob';
			$seperator = strpos($this->subpath, '/', $pos2);
			$obj_id = substr($this->subpath, $pos2, ($seperator > 0 ? $seperator : strlen($this->subpath))-$pos2);
		}
		
		if (!$obj_id || $type == 'none')
			return false;
			
		switch($type)
		{
			case 'lm':
				return $this->checkAccessLM($obj_id, 'lm');
				break;
			case 'mob':
				$usages = ilObjMediaObject::lookupUsages($obj_id);
				foreach($usages as $usage)
				{
					$oid = ilObjMediaObject::getParentObjectIdForUsage($usage, true);
					switch($usage['type'])
					{
						case 'lm:pg':
							if ($oid > 0)
							{
								if ($this->checkAccessLM($oid, 'lm', $usage['id']))
									return true;
							}
							break;
						case 'news':
							// media objects in news (media casts)

							include_once("./Modules/MediaCast/classes/class.ilObjMediaCastAccess.php");
							include_once("./Services/News/classes/class.ilNewsItem.php");
						
							if (ilObjMediaCastAccess::_lookupPublicFiles($oid) && ilNewsItem::_lookupVisibility($usage["id"]) == NEWS_PUBLIC)
							{
								return true;
							}
							break;
						case 'frm~:html':
							// $oid = userid
							if ($ilObjDataCache->lookupType($oid) == 'usr' && $oid == $ilUser->getId())
							{
								return true;
							}
							break;
						default:
							$ref_ids  = ilObject::_getAllReferences($oid);
							$obj_type = ilObject::_lookupType($oid);
							foreach($ref_ids as $ref_id)
							{
								if ($this->ilAccess->checkAccess("read", "view", $ref_id, $obj_type, $oid))
									return true;
							}
							break;
					}
				}
				break;
		}
	}
	
	private function checkAccessLM($obj_id, $obj_type, $page = 0)
	{
		//if (!$page)
		//{
			$ref_ids  = ilObject::_getAllReferences($obj_id);
			foreach($ref_ids as $ref_id)
			{
				if ($this->ilAccess->checkAccess("read", "", $ref_id))
					return true;
			}
			return false;
		//}	
		//else
		//{
		//	$ref_ids  = ilObject::_getAllReferences($obj_id);
		//	foreach($ref_ids as $ref_id)
		//	{
		//		if ($this->ilAccess->checkAccess("read", "", $ref_id))
		//		{
		//			require_once 'Modules/LearningModule/classes/class.ilObjLearningModule.php'; 
		//			$lm = new ilObjLearningModule($obj_id,false);
		//			if ($lm->_checkPreconditionsOfPage($ref_id, $obj_id, $page))
		//				return true;
		//		}
		//	}
		//	return false;
		//}
	}
	
	/**
	* Set the delivery mode for the file
	* @param    string      "inline" or "attachment"
	* @access	public
	*/
	function setDisposition($a_disposition = "inline")
	{
		$this->disposition = $a_disposition;
	}

	/**
	* Get the delivery mode for the file
	* @return   string      "inline" or "attachment"
	* @access	public
	*/
	function getDisposition()
	{
		return $this->disposition;
	}

	
	/**
	* Send the requested file as if directly delivered from the web server
	* @access	public
	*/
	function sendFile()
	{
		//$system_use_xsendfile = true;
		$xsendfile_available = false;
		
		//if (function_exists('apache_get_modules'))
		//{
		//	$modules = apache_get_modules();
		//	$xsendfile_available = in_array('mod_xsendfile', $modules);
		//}
		
		//$xsendfile_available = $system_use_xsendfile & $xsendfile_available;
		
		if ($this->getDisposition() == "attachment")
		{
			if ($xsendfile_available)
			{
				header('x-sendfile: ' . $this->file);
				header("Content-Type: application/octet-stream");
			}
			else
				ilUtil::deliverFile($this->file, basename($this->file));
			exit;
		}
		else
		{
			if (!isset($_SERVER["HTTPS"]))
			{
				header("Cache-Control: no-cache, must-revalidate");
				header("Pragma: no-cache");
			}

			header("Content-Type: " . $this->mimetype);
			header("Content-Length: ".(string)(filesize($this->file)));
			
			if (isset($_SERVER["HTTPS"]))
			{
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
			}

			header("Connection: close");

			if ($xsendfile_available)
			{
				header('x-sendfile: ' . $this->file);
				header("Content-Type: " . $this->mimetype);
			}
			else
			{
				ilUtil::readFile( $this->file);
			}
			
			exit;
		}
	}
	
	/**
	* Send an error response for the requested file
	* @access	public
	*/
	function sendError()
	{
		switch ($this->errorcode)
		{
			case 404:
				header("HTTP/1.0: 404 Not Found");
				break;
			case 403:
			default:
				header("HTTP/1.0: 403 Forbidden");
				break;
		}
		exit($this->errortext);
	}
	
	public function getMimeType($default = 'application/octet-stream')
	{
		$mime = '';
		if (extension_loaded('Fileinfo'))
		{
			$finfo = finfo_open(FILEINFO_MIME);
			$mime = finfo_file($finfo, $this->file);
			finfo_close($finfo);
			if ($pos = strpos($mime, ' '))
			{
				$mime = substr($mime, 0, $pos);
			}
		}
		else
			$mime = ilObjMediaObject::getMimeType($this->file);
		
		$this->mimetype = $mime ? $mime : $default;
	}
	
	
}
?>
