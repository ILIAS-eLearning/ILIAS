<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

// Prevent a general redirect to the login screen for anonymous users.
// This should only be done if access is not granted to anonymous.
// (see ilInitialisation::InitILIAS() for details)
$_GET["baseClass"] = "ilStartUpGUI";

// Define a pseudo module to get a correct ILIAS_HTTP_PATH 
// (needed for links on the error page).
// "data" is assumed to be the ILIAS_WEB_DIR
// (see ilInitialisation::buildHTTPPath() for details)
define("ILIAS_MODULE", substr($_SERVER['PHP_SELF'],
					   strpos($_SERVER['PHP_SELF'], "/data/") + 6));

// Define the cookie path to prevent a different session created for web access
// (see ilInitialisation::setCookieParams() for details)
$GLOBALS['COOKIE_PATH'] = substr($_SERVER['PHP_SELF'], 0,
						  strpos($_SERVER['PHP_SELF'], "/data/"));

// Now the ILIAS header can be included
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
		global $ilUser, $ilAccess, $lng, $ilLog;

		$this->lng =& $lng;
		$this->ilAccess =& $ilAccess;
		$this->params = array();

		// set the anonymous user if no user is set
		if (!$_SESSION["AccountId"])
		{
	        $_SESSION["AccountId"] = ANONYMOUS_USER_ID;
			$ilUser->setId(ANONYMOUS_USER_ID);
			$ilUser->read();
		}

		// get the requested file and its type
		$uri = parse_url($_SERVER["REQUEST_URI"]);
		parse_str($uri["query"], $this->params);

		$pattern = ILIAS_WEB_DIR . "/" . CLIENT_ID;
		$this->subpath = urldecode(substr($uri["path"], strpos($uri["path"], $pattern)));
		$this->file = realpath(ILIAS_ABSOLUTE_PATH . "/". $this->subpath);
		
		// build url path for virtual function
		$this->virtual_path = str_replace($pattern, "virtual-" . $pattern, $uri["path"]);
		
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
	public function checkAccess()
	{
		global $ilLog, $ilUser, $ilObjDataCache;

		// an error already occurred at class initialisation
		if ($this->errorcode)
		{
	        return false;
	    }

		// check for type by subdirectory
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
		{
			$this->errorcode = 404;
			$this->errortext = $this->lng->txt("obj_not_found");
			return false;
		}
			
		switch($type)
		{
			case 'lm':
				if ($this->checkAccessLM($obj_id, 'lm'))
				{
					return true;
				}
				break;

			case 'mob':
				$usages = ilObjMediaObject::lookupUsages($obj_id);
				foreach($usages as $usage)
				{
	                //echo $usage;

					$oid = ilObjMediaObject::getParentObjectIdForUsage($usage, true);
					switch($usage['type'])
					{
						case 'lm:pg':
							if ($oid > 0)
							{
								if ($this->checkAccessLM($oid, 'lm', $usage['id']))
								{
									return true;
								}
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

						case 'qpl:pg':
						case 'qpl:html':
							// test questions
							if ($this->checkAccessTestQuestion($oid, $usage['id']))
							{
								return true;
							}
							break;

						case 'gdf:pg':
							// special check for glossary terms
							if ($this->checkAccessGlossaryTerm($oid, $usage['id']))
							{
	                            return true;
							}
							break;

						default:
							// standard object check
							if ($this->checkAccessObject($oid))
							{
								return true;
							}
							break;
					}
				}
				break;
		}

		// none of the checks above gives access
		$this->errorcode = 403;
		$this->errortext = $this->lng->txt('msg_no_perm_read');
		return false;
	}
	
	private function checkAccessLM($obj_id, $obj_type, $page = 0)
	{
	    global $lng;

		//if (!$page)
		//{
			$ref_ids  = ilObject::_getAllReferences($obj_id);
			foreach($ref_ids as $ref_id)
			{
				if ($this->ilAccess->checkAccess("read", "", $ref_id))
				{
					return true;
				}
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
	* Check access rights for an object by its object id
	*
	* @param    int     	object id
	* @return   boolean     access given (true/false)
	*/
	private function checkAccessObject($obj_id, $obj_type = '')
	{
	    global $ilAccess;

		if (!$obj_type)
		{
			$obj_type = ilObject::_lookupType($obj_id);
		}
		$ref_ids  = ilObject::_getAllReferences($obj_id);
		foreach($ref_ids as $ref_id)
		{
			if ($ilAccess->checkAccess("read", "view", $ref_id, $obj_type, $obj_id))
			{
				return true;
			}
		}
		return false;
	}

	
	/**
	* Check access rights for a test question
	* This checks also tests with random selection of questions
	*
	* @param    int     	object id (question pool or test)
	* @param    int         usage id (not yet used)
	* @return   boolean     access given (true/false)
	*/
	private function checkAccessTestQuestion($obj_id, $usage_id = 0)
	{
	    global $ilAccess;

		// give access if direct usage is readable
	    if ($this->checkAccessObject($obj_id))
		{
	        return true;
	    }

		$obj_type = ilObject::_lookupType($obj_id);
		if ($obj_type == 'qpl')
		{
			// give access if question pool is used by readable test
			// for random selection of questions
			include_once('./Modules/Test/classes/class.ilObjTestAccess.php');
			$tests = ilObjTestAccess::_getRandomTestsForQuestionPool($obj_id);
			foreach ($tests as $test_id)
			{
	            if ($this->checkAccessObject($test_id, 'tst'))
				{
	                return true;
	            }
			}
		}
		return false;
	}


	/**
	* Check access rights for glossary terms
	* This checks also learning modules linking the term
	*
	* @param    int     	object id (glossary)
	* @param    int         page id (definition)
	* @return   boolean     access given (true/false)
	*/
	private function checkAccessGlossaryTerm($obj_id, $page_id)
	{
        // give access if glossary is readable
	    if ($this->checkAccessObject($obj_id))
		{
	    	return true;
	    }

		include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
		include_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
		$term_id = ilGlossaryDefinition::_lookupTermId($page_id);

		include_once('./Services/COPage/classes/class.ilInternalLink.php');
		$sources = ilInternalLink::_getSourcesOfTarget('git',$term_id, 0);

		if ($sources)
		{
			foreach ($sources as $src)
			{
				switch ($src['type'])
				{
	                // Give access if term is linked by a learning module with read access.
					// The term including media is shown by the learning module presentation!
	                case 'lm:pg':
						include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
						$src_obj_id = ilLMObject::_lookupContObjID($src['id']);
						if ($this->checkAccessObject($src_obj_id, 'lm'))
						{
	                        return true;
						}
						break;

					// Don't yet give access if the term is linked by another glossary
					// The link will lead to the origin glossary which is already checked
					/*
					case 'gdf:pg':
						$src_term_id = ilGlossaryDefinition::_lookupTermId($src['id']);
						$src_obj_id = ilGlossaryTerm::_lookGlossaryID($src_term_id);
 						if ($this->checkAccessObject($src_obj_id, 'glo'))
						{
	                        return true;
						}
						break;
					*/
				}
			}
		}
	}


	/**
	* Set the delivery mode for the file
	* @param    string      "inline" or "attachment"
	* @access	public
	*/
	public function setDisposition($a_disposition = "inline")
	{
		$this->disposition = $a_disposition;
	}

	/**
	* Get the delivery mode for the file
	* @return   string      "inline" or "attachment"
	* @access	public
	*/
	public function getDisposition()
	{
		return $this->disposition;
	}

	
	/**
	* Send the requested file as if directly delivered from the web server
	* @access	public
	*/
	public function sendFile()
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
	* Send the requested file by apache web server via virtual function
	*
	* The ILIAS "data" directory must have a "virtual-data" symbolic link
	* Access to "virtual-data" should be protected by "Allow from env=ILIAS_CHECKED"
	* The auto-generated headers should be unset by Apache for the WebAccessChecker directory
	*
	* @access	public
	*/
	public function sendFileVirtual()
	{
		header('Last-Modified: '. date ("D, j M Y H:i:s", filemtime($this->file)). " GMT");
		header('ETag: "'. md5(filemtime($this->file).filesize($this->file)).'"');
		header('Accept-Ranges: bytes');
		header("Content-Length: ".(string)(filesize($this->file)));
		header("Content-Type: " . $this->mimetype);

		apache_setenv('ILIAS_CHECKED','1');
		virtual($this->virtual_path);
		exit;
	}
	
	
	/**
	* Send an error response for the requested file
	* @access	public
	*/
	public function sendError()
	{
		global $ilSetting, $ilUser, $tpl, $lng, $tree;

		switch ($this->errorcode)
		{
			case 404:
				header("HTTP/1.0 404 Not Found");
				break;
			case 403:
			default:
				header("HTTP/1.0 403 Forbidden");
				break;
		}

		// set the page base to the ILIAS directory
		// to get correct references for images and css files
		$tpl->setCurrentBlock("HeadBaseTag");
		$tpl->setVariable('BASE', ILIAS_HTTP_PATH . '/error.php');
		$tpl->parseCurrentBlock();
        $tpl->addBlockFile("CONTENT", "content", "tpl.error.html");

		// Check if user is logged in
		$anonymous = ($ilUser->getId() == ANONYMOUS_USER_ID);

		if ($anonymous)
		{
			// Provide a link to the login screen for anonymous users

			$tpl->SetCurrentBlock("ErrorLink");
			$tpl->SetVariable("TXT_LINK", $lng->txt('login'));
			$tpl->SetVariable("LINK", ILIAS_HTTP_PATH. '/login.php?cmd=force_login&client_id='.CLIENT_ID);
			$tpl->ParseCurrentBlock();
		}
		else
		{
			// Provide a link to the repository for authentified users

			$nd = $tree->getNodeData(ROOT_FOLDER_ID);
			$txt = $nd['title'] == 'ILIAS' ? $lng->txt('repository') : $nd['title'];

			$tpl->SetCurrentBlock("ErrorLink");
			$tpl->SetVariable("TXT_LINK", $txt);
			$tpl->SetVariable("LINK", ILIAS_HTTP_PATH. '/repository.php?client_id='.CLIENT_ID);
			$tpl->ParseCurrentBlock();
		}

		$tpl->setCurrentBlock("content");
		$tpl->setVariable("ERROR_MESSAGE",($this->errortext));
		$tpl->setVariable("SRC_IMAGE", ilUtil::getImagePath("mess_failure.gif"));
		$tpl->parseCurrentBlock();

		$tpl->show();
		exit;
	}
	
	/**
	* Get the mime type of the requested file requested file
	* @param    string      default type
	* @return   string      mime type
	* @access	public
	*/
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
		{
			$mime = ilObjMediaObject::getMimeType($this->file);
		}
		
		$this->mimetype = $mime ? $mime : $default;
	}
	
	
}
?>
