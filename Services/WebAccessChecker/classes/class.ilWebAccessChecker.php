<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

// Prevent a general redirect to the login screen for anonymous users.
// The checker will show an error page with login link instead
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

// Determine the ILIAS client from the web path
// This is needed because a session cookie may not yet exist
// (see ilINITIALISATION::determineClient() for details)
$client_start = strpos($_SERVER['PHP_SELF'], "/data/") + 6;
$client_end = strpos($_SERVER['PHP_SELF'], "/", $client_start);
$_GET['client_id'] = substr($_SERVER['PHP_SELF'], $client_start, $client_end - $client_start);

// Remember if the initial session was empty
// Then a new session record should not be written
// (see ilSession::_writeData for details)
$GLOBALS['WEB_ACCESS_WITHOUT_SESSION'] = (session_id() == "");

include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_WEB_ACCESS_CHECK);

// Now the ILIAS header can be included
require_once "./include/inc.header.php";
require_once "./Services/Utilities/classes/class.ilUtil.php";
require_once "./Services/Object/classes/class.ilObject.php";
require_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";


/**
* Class ilWebAccessChecker
*
* Checks the access rights of a directly requested content file.
* Called from an alias or rewrite rule
* - determines the related learning module and checks the permission
* - either delivers the accessed file (without redirect)
* - or shows an error screen (if too less rights)
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
	* @var string	(inline, attachment or virtual)
	* @access private
	*/
	var $disposition = "inline";

	/**
	 * Check the ip address if no session cookie is given
	 * @var boolean
	 * @access private
	 */
	var $check_ip = false;

	
	/**
	 * User ids to check access for
	 * These may be more than one if ip address is checked
	 * Access is granted if one user has access
	 * @var array
	 * @access private
	 */
	var $check_users = array();
	
	/**
	 * Send the mime type with the delivery
	 * @var boolan
	 * @access private
	 */
	var $send_mimetype = true;
	

	/**
	 * The mimetype to be sent
	 * will be determined if null
	 * @var string
	 * @access private
	 */
	var $mimetype = null;
	
	
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
		
		// get the requested file and its type
		$uri = parse_url($_SERVER["REQUEST_URI"]);
		parse_str($uri["query"], $this->params);

		$pattern = ILIAS_WEB_DIR . "/" . CLIENT_ID;
		$this->subpath = urldecode(substr($uri["path"], strpos($uri["path"], $pattern)));
		$this->file = realpath(ILIAS_ABSOLUTE_PATH . "/". $this->subpath);
		
		// build url path for virtual function
		$this->virtual_path = str_replace($pattern, "virtual-" . $pattern, $uri["path"]);

		
		// set the parameters provided with the checker call
		if (isset($_GET['disposition']))
		{
			$this->setDisposition($_GET['disposition']);
		}
		if (isset($_GET['check_ip']))
		{
			$this->setCheckIp($_GET['check_ip']);	
		}
		if (isset($_GET['send_mimetype']))
		{
			$this->setSendMimetype($_GET['send_mimetype']);
		}		
		
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
		echo "disposition:         ". $this->disposition. "\n";
		echo "ckeck_ip:            ". $this->check_ip. "\n";
		echo "send_mimetype:       ". $this->send_mimetype. "\n";
		echo "</pre>";
		echo phpinfo();
		exit;
		*/
		
		if (!file_exists($this->file))
		{
			$this->errorcode = 404;
			$this->errortext = $this->lng->txt("url_not_found");
			return false;
		}
	}

	/**
	 * Determine the current user(s)
	 */
	public function determineUser()
	{
		global $ilUser;
		
	    // a valid user session is found 
	    if ($_SESSION["AccountId"])
	    {
	    	$this->check_users = array($_SESSION["AccountId"]);	
	    	return;
	    }	   

	    // no session cookie was delivered
	    // user identification by ip address is allowed
	    elseif ($GLOBALS['WEB_ACCESS_WITHOUT_SESSION'] and $this->getCheckIp())
	    {
	    	$this->check_users = ilSession::_getUsersWithIp($_SERVER['REMOTE_ADDR']);
	    	
	    	if (count($this->check_users) == 0)
	    	{
	    		// no user was found for the ip address
	    		$this->check_users = array(ANONYMOUS_USER_ID);

	    		$_SESSION["AccountId"] = ANONYMOUS_USER_ID;
				$ilUser->setId(ANONYMOUS_USER_ID);
				$ilUser->read();	
	    	}
	    	elseif (count($this->check_users) == 1)
	    	{
	    		// exactly one user is found with an active session
	    		$_SESSION["AccountId"] = current($this->check_users);
				$ilUser->setId(current($this->check_users));
				$ilUser->read();	
	    	}
	    	else
	    	{
	    		// more than one user found for the ip address
	    		// take the anonymous user for the session
	    		$_SESSION["AccountId"] = ANONYMOUS_USER_ID;
				$ilUser->setId(ANONYMOUS_USER_ID);
				$ilUser->read();	
	    	}
	    	return;
	    }
	    
	    // take the anonymous user as fallback
	    else
	    {
	    	$this->check_users = array(ANONYMOUS_USER_ID);
	    	
    		$_SESSION["AccountId"] = ANONYMOUS_USER_ID;
			$ilUser->setId(ANONYMOUS_USER_ID);
			$ilUser->read();	
	    	
			return;
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

	    // do this here because ip based checking may be set after construction
	    $this->determineUser();
	    	    
		// check for type by subdirectory
		$pos1 = strpos($this->subpath, "lm_data/lm_") + 11;
		$pos2 = strpos($this->subpath, "mobs/mm_") + 8;
		$pos3 = strpos($this->subpath, "usr_images/") + 11;
		
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
		// trying to access a user image
		elseif ($pos3 > 11)
		{
			$type = 'user_image';
			// user images may be: 
			// upload_123pic, upload_123
			// usr_123.jpg, usr_123_small.jpg, usr_123_xsmall.jpg, usr_123_xxsmall.jpg
			$seperator = strpos($this->subpath, '_', $pos3);
			$obj_id = (int) substr($this->subpath, $seperator + 1);
		}
		
		if (!$obj_id || $type == 'none')
		{
			$this->errorcode = 404;
			$this->errortext = $this->lng->txt("obj_not_found");
			return false;
		}
			
		switch($type)
		{
			// SCORM or HTML learning module
			case 'lm':
				if ($this->checkAccessObject($obj_id))
				{
					return true;
				}
				break;

			// media object	
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
								{
									return true;
								}
							}
							break;
						case 'news':
							// media objects in news (media casts)
							include_once("./Modules/MediaCast/classes/class.ilObjMediaCastAccess.php");
							include_once("./Services/News/classes/class.ilNewsItem.php");
						
							if ($this->checkAccessObject($oid, 'mcst'))
							{
								return true;
							}
							elseif (ilObjMediaCastAccess::_lookupPublicFiles($oid) && ilNewsItem::_lookupVisibility($usage["id"]) == NEWS_PUBLIC)
							{
								return true;
							}
							break;
							
							
						case 'frm~:html':
							// $oid = userid
							foreach ($this->check_users as $user_id)
							{
								if ($ilObjDataCache->lookupType($oid) == 'usr' && $oid == $user_id)
								{
									return true;
								}
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
							
						case 'sahs:pg':
							// check for scorm pages
							if ($this->checkAccessObject($oid, 'sahs'))
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

			// image in user profile	
			case 'user_image':
				if ($this->checkAccessUserImage($obj_id))
				{
					return true;
				}
				break;
		}

		// none of the checks above gives access
		$this->errorcode = 403;
		$this->errortext = $this->lng->txt('msg_no_perm_read');
		return false;
	}
	
	/**
	 * check access for ILIAS learning modules
	 * (obsolete, if checking of page conditions is not activated!)
	 * 
	 * @param int 		object id
	 * @param string 	object type
	 * @param int 		page id
	 */
	private function checkAccessLM($obj_id, $obj_type, $page = 0)
	{
	    global $lng;

		//if (!$page)
		//{
			$ref_ids  = ilObject::_getAllReferences($obj_id);
			foreach($ref_ids as $ref_id)
			{
				foreach ($this->check_users as $user_id)
				{
					if ($this->ilAccess->checkAccessOfUser($user_id, "read", "view", $ref_id, $obj_id, $obj_type))
					{
						return true;
					}
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
			foreach ($this->check_users as $user_id)
			{				
				if ($ilAccess->checkAccessOfUser($user_id, "read", "view", $ref_id, $obj_type, $obj_id))
				{
					return true;
				}
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
	* Check access rights for user images
	* 
	* Due to privacy this will be checked for a truly identified user
	* (IP based checking is not recommended user images)
	*
	* @param    int     	usr_id
	* @return   boolean     access given (true/false)
	*/
	private function checkAccessUserImage($usr_id)
	{
		global $ilUser, $ilSetting;
		
		// check if own image is viewed
		if ($usr_id == $ilUser->getId())
		{
			return true;			
		}

		// check if image is in the public profile
		$public_upload = ilObjUser::_lookupPref($usr_id, 'public_upload');
		if ($public_upload != 'y')
		{
			return false;
		}
		
		// check the publication status of the profile
		$public_profile = ilObjUser::_lookupPref($usr_id, 'public_profile');

		if ($public_profile == 'g' 
			and $ilSetting->get('enable_global_profiles')
			and $ilSetting->get('pub_section'))
		{
			// globally public
			return true;
		}
		elseif (($public_profile == 'y' or $public_profile == 'g')
			and $ilUser->getId() != ANONYMOUS_USER_ID)
		{
			// public for logged in users
			return true;
		}
		else
		{
			// not public
			return false;
		}
	}


	/**
	* Set the delivery mode for the file
	* @param    string       "inline", "attachment" or "virtual"
	* @access	public
	*/
	public function setDisposition($a_disposition)
	{
		if (in_array(strtolower($a_disposition), array('inline','attachment','virtual')))
		{
			$this->disposition = strtolower($a_disposition);
		}
		else
		{
			$this->disposition = 'inline';
		}
	}

	/**
	* Get the delivery mode for the file
	* @return   string      "inline", "attachment" or "virtual"
	* @access	public
	*/
	public function getDisposition()
	{
		return $this->disposition;
	}

	/**
	 * Set the sending of the mime type
	 * @param	string	(boolean switch or mimetype)			
	 * @access	public
	 */
	public function setSendMimetype($a_send_mimetype)
	{
		if (in_array(strtolower($a_send_mimetype), array('','0','off','false')))
		{
			$this->mimetype = null;
			$this->send_mimetype = false;
		}
		elseif (in_array(strtolower($a_send_mimetype), array('1','on','true')))
		{
			$this->mimetype = null;
			$this->send_mimetype = true;
		}
		else
		{
			$this->mimetype = $a_send_mimetype;
			$this->send_mimetype = true;
		}
	}
	
	/**
	 * Get if mimetype should be sent for a virtual delivery
	 * @return	boolean
	 */
	public function getSendMimetype()
	{
		return $this->send_mimetype;
	}

	
	/**
	 * Set the checking of the IP address if no valid session is found
	 * @param	boolean	
	 * @access	public
	 */
	public function setCheckIp($a_check_ip)
	{
		if (in_array(strtolower($a_check_ip), array('','0','off','false')))
		{
			$this->check_ip = false;
		}
		elseif (in_array(strtolower($a_check_ip), array('1','on','true')))
		{
			$this->check_ip	= true;
		}
	}
	
	/**
	 * Set the checking of the IP address of no valid session is found
	 * @return	boolean
	 */
	public function getCheckIp()
	{
		return $this->check_ip;
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
		
		
		// delivery via apache virtual function
		if ($this->getDisposition() == "virtual")
		{
			$this->sendFileVirtual();
			exit;
		}
		// delivery for download dialogue
		elseif ($this->getDisposition() == "attachment")
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
		// inline delivery
		else
		{
			if (!isset($_SERVER["HTTPS"]))
			{
				header("Cache-Control: no-cache, must-revalidate");
				header("Pragma: no-cache");
			}

			if ($this->getSendMimetype())
			{
				header("Content-Type: " . $this->getMimeType());
			}
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
				if ($this->getSendMimetype())
				{
					header("Content-Type: " . $this->getMimeType());
				}
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
		if ($this->getSendMimetype())
		{
			header("Content-Type: " . $this->getMimeType());
		}

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
			$tpl->SetVariable("TXT_LINK", $lng->txt('login_to_ilias'));
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
			$tpl->SetVariable("LINK", ILIAS_HTTP_PATH. '/ilias.php?baseClass=ilRepositoryGUI&amp;client_id='.CLIENT_ID);
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
	* Get the mime type of the requested file
	* @param    string      default type
	* @return   string      mime type
	* @access	public
	*/
	public function getMimeType($default = 'application/octet-stream')
	{
		// take a previously set mimetype
		if (isset($this->mimetype))
		{
			return $this->mimetype;
		}
		
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
		
		// set and return the mime type
		$this->mimetype = $mime ? $mime : $default;
		return $this->mimetype;
	}
}
?>
