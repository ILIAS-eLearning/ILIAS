<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* class for checking external links in page objects
* Normally used in Cron jobs, but should be extensible for use in learning modules. In this case set second parameter of 
* contructor = false, and use setPageObjectId() 
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*/
class ilLinkChecker
{
	var $db = null;
	var $log_messages = array();
	var $invalid_links = array();

	var $validate_all = true;
	var $mail_status = false;
	var $page_id = 0;


	public function __construct($db,$a_validate_all = true)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];

		define('DEBUG',1);
		define('SOCKET_TIMEOUT',5);
		define('MAX_REDIRECTS',5);

		$this->db = $db;

		// SET GLOBAL DB HANDLER FOR STATIC METHODS OTHER CLASSES
		$ilDB = $db;

		$this->validate_all = $a_validate_all;
	}

	function setCheckPeriod($a_period)
	{
		$this->period = $a_period;
	}
	function getCheckPeriod()
	{
		return $this->period;
	}

	function setMailStatus($a_status)
	{
		$this->mail_status = (bool) $a_status;
	}
	function getMailStatus()
	{
		return (bool) $this->mail_status;
	}

	function __setType($a_type)
	{
		$this->type = $a_type;
	}
	function __getType()
	{
		return $this->type;
	}

	function setObjId($a_page_id)
	{
		return $this->page_id = $a_page_id;
	}
	function getObjId()
	{
		return $this->page_id;
	}

	function getValidateAll()
	{
		return $this->validate_all ? true : false;
	}

	function getLogMessages()
	{
		return $this->log_messages ? $this->log_messages : array();
	}

	function getInvalidLinks()
	{
		return $this->invalid_links ? $this->invalid_links : array();
	}

	function getInvalidLinksFromDB()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		$query = "SELECT * FROM link_check ".
			"WHERE obj_id = ".$ilDB->quote($this->getObjId(),'integer')." ";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$invalid[] = array('page_id' => $row->page_id,
							   'url'	 => $row->url);
		}

		return $invalid ? $invalid : array();
	}

	function getLastCheckTimestamp()
	{
		global $DIC;		

		$ilDB = $DIC['ilDB'];
		
		if($this->getValidateAll())
		{
			$query = "SELECT MAX(last_check) last_check FROM link_check ";
		}
		else
		{
			$query = "SELECT MAX(last_check) last_check FROM link_check ".
				"WHERE obj_id = ".$ilDB->quote($this->getObjId(),'integer')." ";
		}
		$res = $ilDB->query($query);
		$row = $ilDB->fetchObject($res);

		return $row->last_check ? $row->last_check : 0;
	}

	function checkWebResourceLinks()
	{
		$pages = array();

		$this->__setType('webr');
		$this->__clearLogMessages();
		$this->__clearInvalidLinks();
		$this->__appendLogMessage('LinkChecker: Start checkLinks()');

		if(count($invalid = $this->__validateLinks($this->__getWebResourceLinks())))
		{
			foreach($invalid as $invalid_item)
			{
				$this->__appendLogMessage('LinkChecker: found invalid link: '.$invalid_item['complete']);
				$this->__appendInvalidLink($invalid_item);
			}
		}
		
		$this->__appendLogMessage('LinkChecker: End checkLinks()');
		$this->__saveInDB();
		
		$this->__sendMail();
		
		return $this->getInvalidLinks();
	}
	
	function checkLinks()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		$pages = array();

		$this->__setType('lm');
		$this->__clearLogMessages();
		$this->__clearInvalidLinks();
		$this->__appendLogMessage('LinkChecker: Start checkLinks()');

		if(!$this->getValidateAll() and !$this->getObjId())
		{
			echo "ilLinkChecker::checkLinks() No Page id given";

			return false;
		}
		elseif(!$this->getValidateAll() and $this->getObjId())
		{
			$query = "SELECT * FROM page_object ".
				"WHERE parent_id = ".$ilDB->quote($this->getObjId())." ".
				"AND parent_type = 'lm'";

			$res = $this->db->query($query);
			while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
			{
				$pages[] = array('page_id' => $row->page_id,
								 'content' => $row->content,
								 'type'	 => $row->parent_type);
			}
		}
		elseif($this->getValidateAll())
		{
			$query = "SELECT * FROM page_object ".
				"WHERE parent_type = 'lm'";

			$res = $this->db->query($query);
			while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
			{
				$pages[] = array('page_id' => $row->page_id,
								 'content' => $row->content,
								 'type'	 => $row->parent_type);
			}
		}

		// VALIDATE
		foreach($pages as $page)
		{
			if(count($invalid = $this->__validateLinks($this->__getLinks($page))))
			{
				foreach($invalid as $invalid_item)
				{
					$this->__appendLogMessage('LinkChecker: found invalid link: '.$invalid_item['complete']);
					$this->__appendInvalidLink($invalid_item);
				}
			}
		}
		
		$this->__appendLogMessage('LinkChecker: End checkLinks()');
		$this->__saveInDB();

		$this->__sendMail();

		return $this->getInvalidLinks();
	}

	// PRIVATE
	function __txt($language,$key,$module = 'common')
	{
		return ilLanguage::_lookupEntry($language, $module, $key);
	}

	function __fetchUserData($a_usr_id)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];

		$r = $this->db->query("SELECT email FROM usr_data WHERE usr_id = ".$ilDB->quote($a_usr_id));

		$row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

		$data['email'] = $row->email;

		$set = $ilDB->query("SELECT * FROM usr_pref ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id, "integer")." ".
			"AND keyword = ".$ilDB->quote('language', "text"));

		$row = $ilDB->fetchObject($set);

		$data['lang'] = $row->value;

		return $data;
	}

	function __getTitle($a_lm_obj_id)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];

		$r = $this->db->query("SELECT title FROM object_data ".
			"WHERE obj_id = ".$ilDB->quote($a_lm_obj_id ,'integer')." ");

		$row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

		return $row->title;
	}

	function __sendMail()
	{
		global $DIC;

		$ilUser = $DIC['ilUser'];


		if(!count($notify = $this->__getNotifyLinks()))
		{
			// Nothing to do
			return true;
		}
		if(!$this->getMailStatus())
		{
			return true;
		}


		$body = "";
		$obj_name = "";

		foreach(ilLinkCheckNotify::_getAllNotifiers($this->db) as $usr_id => $obj_ids)
		{
			if(!is_object($tmp_user =& ilObjectFactory::getInstanceByObjId($usr_id,false)))
			{
				$this->__appendLogMessage('LinkChecker: Cannot find user with id: '.$usr_id);
				continue;
			}

			$counter = 0;
			foreach($obj_ids as $obj_id)
			{
				if(!isset($notify[$obj_id]))
				{
					continue;
				}
				++$counter;

				switch($this->__getType())
				{
					case 'webr':
						$obj_name = $this->__txt($tmp_user->getLanguage(),'obj_webr');
						break;
					case 'lm':
					default:
						$obj_name = $this->__txt($tmp_user->getLanguage(),'lo');
						break;
				}
				$body .= $obj_name.': '.$this->__getTitle($obj_id)."\r\n";
				$body .= $this->__txt($tmp_user->getLanguage(),'link_check_perma_link', "mail"). ": " .
					$this->createPermanentLink($obj_id, $usr_id, $this->__getType())." \r\n";
				$body .= $this->__txt($tmp_user->getLanguage(),"link_check_affected_links", "mail"). ":\r\n";

				// Print all invalid
				foreach($notify[$obj_id] as $data)
				{
					$body .= $data['url']."\r\n";
				}
				$body .= "\r\n";
			}
			if($counter)
			{

				$ntf = new ilSystemNotification();
				$ntf->setLangModules(array("mail", "common"));
				$ntf->setSubjectLangId("link_check_subject");
				$ntf->setIntroductionLangId("link_check_introduction");
				$ntf->setReasonLangId("link_check_reason");
				$ntf->addAdditionalInfo("additional_info", $body,true);
				$ntf->sendMail(array($tmp_user->getId()));

				$this->__appendLogMessage('LinkChecker: Sent mail to '.$tmp_user->getEmail());
			}
			$body = "";
		}
	}

	/**
	 * creates a permanent link
	 * @param $a_obj_id
	 * @param $a_usr_id
	 * @param $a_obj_type
	 * @return string goto link
	 */
	protected function createPermanentLink($a_obj_id, $a_usr_id, $a_obj_type)
	{
		global $DIC;

		$ilAccess = $DIC['ilAccess'];
		$ref_ids = ilObject::_getAllReferences($a_obj_id);
		$ref_id = null;

		foreach((array) $ref_ids as $id)
		{
			if($ilAccess->checkAccessOfUser($a_usr_id, "read", "", $id, $a_obj_type, $a_obj_id))
			{
				$ref_id = $id;
			}
		}

		if($ref_id === null)
		{
			return false;
		}


		return ilLink::_getLink($ref_id, $a_obj_type);
	}

	function __getNotifyLinks()
	{
		return $this->notify ? $this->notify : array();
	}


	function __clearInvalidLinks()
	{
		$this->invalid_links = array();
	}
	function __appendInvalidLink($a_link)
	{
		$this->invalid_links[] = $a_link;
	}
					

	function __appendLogMessage($a_string)
	{
		$this->log_messages[] = $a_string;
	}
	function __clearLogMessages()
	{
		return $this->log_messages = array();
	}

	function __getLinks($a_page)
	{
		$matches = array();

		$pattern_complete = '/\<ExtLink Href="([^"]*)"\>/';
		if(preg_match_all($pattern_complete,$a_page['content'],$matches))
		{
			for($i = 0;$i < count($matches[0]); ++$i)
			{
				$url_data = @parse_url($matches[1][$i]);
				// continue if mailto link
				if($url_data['scheme'] == 'mailto')
				{
					continue;
				}
				
				// PUH, HTTP_REQUEST needs a beginning http://
				if(!$url_data['scheme'])
				{
					$matches[1][$i] = 'http://'.$matches[1][$i];
				}

				$lm_id = $this->__getObjIdByPageId($a_page['page_id']);
				$link[] = array('page_id'  => $a_page['page_id'],
								'obj_id'   => $lm_id,
								'type'	   => $a_page['type'],
								'complete' => $matches[1][$i],
								'scheme'   => isset($url_data['scheme']) ? $url_data['scheme'] : 'http',
								'host'	   => isset($url_data['host']) ? $url_data['host'] : $url_data['path']);
			}
		}

		return $link ? $link : array();
	}

	function __getWebResourceLinks()
	{
		global $DIC;

		$objDefinition = $DIC['objDefinition'];
		


		$link_res_obj = new ilLinkResourceItems($this->getObjId());

		foreach($check_links = $link_res_obj->getCheckItems($this->getCheckPeriod()) as $item_data)
		{
			// #10091 - internal
			if(strpos($item_data['target'], '|'))
			{				
				$parts = explode('|', $item_data['target']);
				if(sizeof($parts) == 2 &&
					is_numeric($parts[1]) &&
					$objDefinition->isAllowedInRepository($parts[0]))
				{										
					$link[] = array('page_id'  => $item_data['link_id'],
								'obj_id'   => $this->getObjId(),
								'type'	   => 'webr',
								'complete' => $item_data['target'],
								'scheme'   => 'internal',
								'obj_type' => $parts[0],
								'ref_id'   => $parts[1]);			
					continue;
				}					
			}
			
			// external			
			$url_data = @parse_url($item_data['target']);

			// PUH, HTTP_REQUEST needs a beginning http://
			if(!$url_data['scheme'])
			{
				$item_data['target'] = 'http://'.$item_data['target'];
			}

			$link[] = array('page_id'  => $item_data['link_id'],
						'obj_id'   => $this->getObjId(),
						'type'	   => 'webr',
						'complete' => $item_data['target'],
						'scheme'   => isset($url_data['scheme']) ? $url_data['scheme'] : 'http',
						'host'	   => isset($url_data['host']) ? $url_data['host'] : $url_data['path']);										
		}
		return $link ? $link : array();
	}


	/**
	 *
	 * $a_links Format:
	 * Array (
	 * 	[1] => Array (
	 * 		['scheme'] => intern/http/https,
	 * 		['ref_id'] => ILIAS ref ID,
	 * 		['obj_type'] => ILIAS object type,
	 * 		['complete'] => link to check,
	 * 	),
	 * 	[2]=> ...
	 * )
	 *
	 * @param array $a_links Format:
	 * @return array Returns all invalid links! Format like $a_links with additional error information ['http_status_code'] and ['curl_errno']
	 */
	function __validateLinks($a_links)
	{
		global $DIC;

		$tree = $DIC['tree'];
		if(!ilCurlConnection::_isCurlExtensionLoaded())
		{
			$this->__appendLogMessage('LinkChecker: Pear HTTP_Request is not installed. Aborting');
			ilLoggerFactory::getLogger('lchk')->error('LinkChecker: Curl extension is not loeaded. Aborting');
			return array();
		}
		$invalid = array();

		foreach($a_links as $link)
		{
			// #10091 - internal
			if($link['scheme'] == 'internal')
			{				
				$obj_id = ilObject::_lookupObjId($link['ref_id']);
				if(!$obj_id || 
					ilObject::_lookupType($obj_id) != $link['obj_type'] ||
					$tree->isDeleted($link['ref_id']))
				{					
					$invalid[] = $link;
				}					
			}
			// external
			else
			{
				//ilLoggerFactory::getLogger('lchk')->debug('Check: '.$link['complete']);

				if($link['scheme'] !== 'http' and $link['scheme'] !== 'https')
				{
					ilLoggerFactory::getLogger('lchk')->error('LinkChecker: Unkown link sheme "' . $link['scheme'] . '". Continue check');
					continue;
				}

				$curl = null;
				$http_code = 0;
				$c_error_no = 0;
				try
				{
					$curl = new ilCurlConnection($link['complete']);
					$curl->init();

					if(ilProxySettings::_getInstance()->isActive())
					{
						$curl->setOpt(CURLOPT_HTTPPROXYTUNNEL,true );
						$curl->setOpt(CURLOPT_PROXY, ilProxySettings::_getInstance()->getHost());
						$curl->setOpt(CURLOPT_PROXYPORT, ilProxySettings::_getInstance()->getPort());
					}

					$curl->setOpt( CURLOPT_HEADER, 1);
					$curl->setOpt(CURLOPT_RETURNTRANSFER, 1);
					$curl->setOpt(CURLOPT_CONNECTTIMEOUT ,SOCKET_TIMEOUT);
					$curl->setOpt(CURLOPT_FOLLOWLOCATION, 1);
					$curl->setOpt(CURLOPT_MAXREDIRS ,MAX_REDIRECTS);
					$curl->exec();
					$headers = $curl->getInfo();
					$http_code  = $headers['http_code'];
				}
				catch(ilCurlConnectionException $e)
				{
					$c_error_no = $e->getCode();
					ilLoggerFactory::getLogger('lchk')->error('LinkChecker: No valid http code received. Curl error ('.$e->getCode().'): ' . $e->getMessage());
				}
				finally
				{
					if ($curl != null)
					{
						$curl->close();
					}
				}

				switch($http_code)
				{
					// EVERYTHING OK
					case '200':
						// In the moment 301 will be handled as ok
					case '301':
					case '302':
						break;
					default:
						$link['http_status_code'] = $http_code;
						if($http_code == 0 && $c_error_no != 0)
						{
							$link['curl_errno'] = $c_error_no;
						}
						$invalid[] = $link;
						break;
				}
			}
		}
		return $invalid;
	}

	function __getObjIdByPageId($a_page_id)
	{
		$res = $this->db->query( "SELECT lm_id FROM lm_data ".
			"WHERE obj_id = '".$a_page_id."'");

		$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

		return $row->lm_id ? $row->lm_id : 0;
	}

	function __isInvalid($a_page_id, $a_url)
	{
		foreach($this->getInvalidLinks() as $link)
		{
			if($link['page_id'] == $a_page_id and
			   substr($link['complete'],0,255) == $a_url)
			{
				return true;
			}
		}
		return false;
	}

	function __saveInDB()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		if($this->getMailStatus())
		{
			$this->__checkNotify();
		}
		$this->__clearDBData();


		foreach($this->getInvalidLinks() as $link)
		{
			$id = $ilDB->nextId('link_check');

			$query = "INSERT INTO link_check (id, obj_id,page_id,url,parent_type,http_status_code,last_check) ".
				"VALUES ( ".
				$ilDB->quote($id, "integer").",".
				$ilDB->quote($link['obj_id'],'integer').", ".
				$ilDB->quote($link['page_id'],'integer').", ".
				$ilDB->quote(substr($link['complete'],0,255),'text').", ".
				$ilDB->quote($link['type'],'text').", ".
				$ilDB->quote($link['http_status_code'] ? $link['http_status_code'] : 0,'integer').", ".
				$ilDB->quote(time(),'integer')." ".
				")";
			$res = $ilDB->manipulate($query);
		}
	}

	function __checkNotify()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		foreach($this->getInvalidLinks() as $link)
		{
			$query = "SELECT * FROM link_check ".
				"WHERE page_id = ".$ilDB->quote($link['page_id'],'integer')." ".
				"AND url = ".$ilDB->quote(substr($link['complete'],0,255),'text')." ";
			$res = $ilDB->query($query);
						
			if(!$res->numRows())
			{
				$this->notify[$link["obj_id"]][] = array('page_id' => $link['page_id'],
														 'url'	   => $link['complete']);
			}
		}
	}


	function __clearDBData()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		if($this->getValidateAll())
		{
			$query = "DELETE FROM link_check";
		}
		else
		{
			$query = "DELETE FROM link_check ".
				"WHERE obj_id = ".$ilDB->quote($this->getObjId(),'integer');
		}
		$res = $ilDB->manipulate($query);

		return true;
	}
}
?>
