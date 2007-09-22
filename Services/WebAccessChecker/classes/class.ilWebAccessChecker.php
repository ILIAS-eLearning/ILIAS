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
			$this->mimetype = ilObjMediaObject::getMimeType($this->file);
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
		global $ilLog;
		
		// extract the object id (currently only for learning modules)
		$pos1 = strpos($this->subpath, "lm_data/lm_") + 11;
		$pos2 = strpos($this->subpath, "/", $pos1);
		if ($pos1 === false or $pos2 === false)
		{
			$this->errorcode = 404;
			$this->errortext = $this->lng->txt("url_not_found");
			return false;
		}
		$obj_id = substr($this->subpath, $pos1, $pos2-$pos1);
		if (!is_numeric($obj_id))
		{
			$this->errorcode = 404;
			$this->errortext = $this->lng->txt("obj_not_found");
			return false;
		}

		// look in cache, if already checked
		if (is_array($this->checked_list))
		{
			if (in_array($obj_id, $this->checked_list))
			{
//				return true;
			}
		}

		// find the object references
		$obj_type = ilObject::_lookupType($obj_id);
		$ref_ids  = ilObject::_getAllReferences($obj_id);
		if (!$ref_ids)
		{
			$this->errorcode = 403;
			$this->errortext = $this->lng->txt("permission_denied");
			return false;
		}

		// check, if one of the references is readable
		$readable = false;

		foreach($ref_ids as $ref_id)
		{
		  	if ($this->ilAccess->checkAccess("read", "view", $ref_id, $obj_type, $obj_id))
			{
				$readable = true;
				break;
			}
		}
		if ($readable)
		{
			//add object to cache
			$this->checked_list[] = $obj_id;
			return true;
		}
		else
		{
			$this->errorcode = 403;
			$this->errortext = $this->lng->txt("permission_denied");
			return false;
		}
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
		if ($this->getDisposition() == "attachment")
		{
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

			ilUtil::readFile( $this->file);
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
			case 403:
				header("HTTP/1.0: 403 Forbidden");
				break;
			case 404:
				header("HTTP/1.0: 404 Not Found");
				break;
		}
		exit($this->errortext);
	}
}
?>
