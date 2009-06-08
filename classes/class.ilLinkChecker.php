<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* class for checking external links in page objects
* Normally used in Cron jobs, but should be extensible for use in learning modules. In this case set second parameter of 
* contructor = false, and use setPageObjectId() 
*
* @author Stefan Meyer <smeyer@databay.de>
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


	function ilLinkChecker(&$db,$a_validate_all = true)
	{
		global $ilDB;

		define('DEBUG',1);
		define('SOCKET_TIMEOUT',5);

		$this->db =& $db;

		// SET GLOBAL DB HANDLER FOR STATIC METHODS OTHER CLASSES
		$ilDB =& $db;

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
		global $ilDB;
		
		$query = "SELECT * FROM link_check ".
			"WHERE obj_id = ".$ilDB->quote($this->getObjId(),'integer')." ";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$invalid[] = array('page_id' => $row->page_id,
							   'url'	 => $row->url);
		}

		return $invalid ? $invalid : array();
	}

	function getLastCheckTimestamp()
	{
		global $ilDB;		
		
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
		global $ilDB;
		
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
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
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
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
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

	function checkPear()
	{
		if(!@include_once('HTTP/Request.php'))
		{
			return false;
		}
		return true;
	}
		

	// PRIVATE
	function __txt($language,$key,$module = 'common')
	{
		global $ilDB;
		
		include_once './Services/Language/classes/class.ilLanguage.php';
		return ilLanguage::_lookupEntry($language, $module, $key);
	}

	function __fetchUserData($a_usr_id)
	{
		global $ilDB;
		
		$query = "SELECT email FROM usr_data WHERE usr_id = ".$ilDB->quote($a_usr_id)."";

		$row = $this->db->getRow($query,DB_FETCHMODE_OBJECT);

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
		global $ilDB;
		
		$query = "SELECT title FROM object_data ".
			"WHERE obj_id = ".$ilDB->quote($a_lm_obj_id ,'integer')." ";

		$row = $this->db->getRow($query,DB_FETCHMODE_OBJECT);

		return $row->title;
	}

	function __sendMail()
	{
		global $ilUser;


		if(!count($notify = $this->__getNotifyLinks()))
		{
			// Nothing to do
			return true;
		}
		if(!$this->getMailStatus())
		{
			return true;
		}

		include_once './classes/class.ilLinkCheckNotify.php';
		
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
						$body .= $this->__txt($tmp_user->getLanguage(),'obj_webr');
						break;

					case 'lm':
					default:
						$body .= $this->__txt($tmp_user->getLanguage(),'lo');
						break;
				}
						
				$body .= ': ';
				$body .= $this->__getTitle($obj_id)."\r\n";

				// Print all invalid
				foreach($notify[$obj_id] as $data)
				{
					$body .= $data['url']."\r\n";
				}
				$body .= "\r\n";
			}
			if($counter)
			{
				include_once "Services/Mail/classes/class.ilFormatMail.php";
				
				$umail = new ilFormatMail($tmp_user->getId());
				$subject = $this->__txt($tmp_user->getLanguage(),'link_check_subject');

				$umail->sendMail($tmp_user->getLogin(),"","",$subject,$body,array(),array("normal"));
				$this->__appendLogMessage('LinkChecker: Sent mail to '.$tmp_user->getEmail());
			}

		}
				

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
		include_once 'Modules/WebResource/classes/class.ilLinkResourceItems.php';

		$link_res_obj = new ilLinkResourceItems($this->getObjId());

		foreach($check_links = $link_res_obj->getCheckItems($this->getCheckPeriod()) as $item_data)
		{
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

		

	function __validateLinks($a_links)
	{
		if(!@include_once('HTTP/Request.php'))
		{
			$this->__appendLogMessage('LinkChecker: Pear HTTP_Request is not installed. Aborting');

			return array();
		}

		foreach($a_links as $link)
		{
			if(gethostbyname($link['host']) == $link['host'])
			{
				$invalid[] = $link;
				continue;
			}

			if($link['scheme'] !== 'http' and $link['scheme'] !== 'https')
			{
				continue;
			}
			$req =& new HTTP_Request($link['complete']);
			$req->sendRequest();

			switch($req->getResponseCode())
			{
				// EVERYTHING OK
				case '200':
					// In the moment 301 will be handled as ok
				case '301':
				case '302':
					break;

				default:
					$link['http_status_code'] = $req->getResponseCode();
					$invalid[] = $link;
					break;
			}
		}
		return $invalid ? $invalid : array();
	}

	function __getObjIdByPageId($a_page_id)
	{
		$query = "SELECT lm_id FROM lm_data ".
			"WHERE obj_id = '".$a_page_id."'";

		$row = $this->db->getRow($query,DB_FETCHMODE_OBJECT);

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
		global $ilDB;
		
		if($this->getMailStatus())
		{
			$this->__checkNotify();
		}
		$this->__clearDBData();


		foreach($this->getInvalidLinks() as $link)
		{
			$query = "INSERT INTO link_check (obj_id,page_id,url,parent_type,http_status_code,last_check) ".
				"VALUES ( ".
				$ilDB->quote($link['obj_id'],'integer').", ".
				$ilDB->quote($link['page_id'],'integer').", ".
				$ilDB->quote(substr($link['complete'],0,255),'text').", ".
				$ilDB->quote($link['type'],'text').", ".
				$ilDB->quote($link['http_status_code'],'integer').", ".
				$ilDB->quote(time(),'integer')." ".
				")";
			$res = $ilDB->manipulate($query);
		}
	}

	function __checkNotify()
	{
		global $ilDB;
		
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
		global $ilDB;
		
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
