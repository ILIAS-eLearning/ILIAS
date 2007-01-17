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
* class for editing lm menu
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMMenuEditor
{
	function ilLMMenuEditor()
	{
		global $ilDB;

		$this->db =& $ilDB;
		$this->link_type = "extern";
		$this->link_ref_id = null;
	}

	function setObjId($a_obj_id)
	{
		$this->lm_id = $a_obj_id;
	}

	function getObjId()
	{
		return $this->lm_id;
	}

	function setEntryId($a_id)
	{
		$this->entry_id = $a_id;
	}

	function getEntryId()
	{
		return $this->entry_id;
	}

	function setLinkType($a_link_type)
	{
		$this->link_type = $a_link_type;	
	}
	
	function getLinkType()
	{
		return $this->link_type;
	}
	
	function setTitle($a_title)
	{
		$this->title = $a_title;	
	}

	function getTitle()
	{
		return $this->title;
	}
	
	function setTarget($a_target)
	{
		$this->target = $a_target;	
	}
	
	function getTarget()
	{
		return $this->target;
	}
	
	function setLinkRefId($a_link_ref_id)
	{
		$this->link_ref_id = $a_link_ref_id;
	}

	function getLinkRefId()
	{
		return $this->link_ref_id;
	}

	function create()
	{
		$q = "INSERT INTO lm_menu (lm_id,link_type,title,target,link_ref_id) ".
			 "VALUES ".
			 "(".
			 $this->db->quote($this->getObjId()).",".
			 $this->db->quote($this->getLinkType()).",".
 			 $this->db->quote($this->getTitle()).",".
 			 $this->db->quote($this->getTarget()).",".
			 $this->db->quote($this->getLinkRefId()).")";
		$r = $this->db->query($q);
		
		return true;
	}
	
	function getMenuEntries($a_only_active = false)
	{
		global $ilDB;
		
		$entries = array();
		
		if ($a_only_active === true)
		{
			$and = " AND active = 'y'";
		}
		
		$q = "SELECT * FROM lm_menu ".
			 "WHERE lm_id = ".$ilDB->quote($this->lm_id).
			 $and;
			 
		$r = $this->db->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$entries[] = array('id'		=> $row->id,
							   'title'	=> $row->title,
							   'link'	=> $row->target,
							   'type'	=> $row->link_type,
							   'ref_id'	=> $row->link_ref_id,
							   'active'	=> $row->active
							   );
		}

		return $entries;
	}
	
	/**
	 * delete menu entry
	 * 
	 */
	function delete($a_id)
	{
		if (!$a_id)
		{
			return false;
		}
		
		$q = "DELETE FROM lm_menu WHERE id = ".$this->db->quote($a_id);
		$this->db->query($q);
		
		return true;
	}
	
	/**
	 * update menu entry
	 * 
	 */
	function update()
	{
		global $ilDB;
		
		$q = "UPDATE lm_menu SET ".
			" link_type = ".$ilDB->quote($this->getLinkType()).",".
			" title = ".$ilDB->quote($this->getTitle()).",".
			" target = ".$ilDB->quote($this->getTarget()).",".
			" link_ref_id = ".$ilDB->quote($this->getLinkRefId()).
			" WHERE id = '".$this->getEntryId()."'";
		$r = $this->db->query($q);
		
		return true;
	}
	
	function readEntry($a_id)
	{
		if (!$a_id)
		{
			return false;
		}
		
		$q = "SELECT * FROM lm_menu WHERE id = ".$this->db->quote($a_id);
		$r = $this->db->query($q);

		$row = $this->db->getRow($q,DB_FETCHMODE_OBJECT);
		
		$this->setTitle($row->title);
		$this->setTarget($row->target);
		$this->setLinkType($row->link_type);
		$this->setLinkRefId($row->link_ref_id);
		$this->setEntryid($a_id);
	}
	
	/**
	 * update active status of all menu entries of lm
	 * @param	array	entry ids
	 * 
	 */
	function updateActiveStatus($a_entries)
	{
		if (!is_array($a_entries))
		{
			return false;
		}
		
		// update active status
		$q = "UPDATE lm_menu SET " .
			 "active = CASE " .
			 "WHEN id IN (".implode(',',$a_entries).") " .
			 "THEN 'y' ".
			 "ELSE 'n' ".
			 "END " .
			 "WHERE lm_id = ".$this->lm_id;
		$this->db->query($q);
	}
	
/*
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
		$query = "SELECT * FROM link_check ".
			"WHERE obj_id = '".$this->getObjId()."'";

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
		if($this->getValidateAll())
		{
			$query = "SELECT MAX(last_check) as last_check FROM link_check ";
		}
		else
		{
			$query = "SELECT MAX(last_check) as last_check FROM link_check ".
				"WHERE obj_id = '".$this->getObjId()."'";
		}
		$row = $this->db->getRow($query,DB_FETCHMODE_OBJECT);

		return $row->last_check ? $row->last_check : 0;
	}

	
	function checkLinks()
	{
		$pages = array();

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
				"WHERE parent_id = '".$this->getObjId()."' ".
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
		$query = "SELECT value FROM lng_data ".
			"WHERE module = '".$module."' ".
			"AND identifier = '".$key."' ".
			"AND lang_key = '".$language."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$value = $row->value;
		}
		if(!$value)
		{
			$query = "SELECT value FROM lng_data ".
				"WHERE module = '".$module."' ".
				"AND identifier = '".$key."' ".
				"AND lang_key = 'en'";
			
			$res = $this->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$value = $row->value;
			}
		}
		return $value ? $value : '-'.$key.'-';
	}

	function __fetchUserData($a_usr_id)
	{
		$query = "SELECT email FROM usr_data WHERE usr_id = '".$a_usr_id."'";

		$row = $this->db->getRow($query,DB_FETCHMODE_OBJECT);

		$data['email'] = $row->email;

		$query = "SELECT * FROM usr_pref ".
			"WHERE usr_id = '".$a_usr_id."' ".
			"AND keyword = 'language'";

		$row = $this->db->getRow($query,DB_FETCHMODE_OBJECT);

		$data['lang'] = $row->value;

		return $data;
	}

	function __getTitle($a_lm_obj_id)
	{
		$query = "SELECT title FROM object_data ".
			"WHERE obj_id = '".$a_lm_obj_id."'";

		$row = $this->db->getRow($query,DB_FETCHMODE_OBJECT);

		return $row->title;
	}

	function __sendMail()
	{
		if(!count($notify = $this->__getNotifyLinks()))
		{
			// Nothing to do
			return true;
		}
		if($this->getMailStatus())
		{
			// get all users who want to be notified
			include_once '../classes/class.ilLinkCheckNotify.php';
			
			foreach(ilLinkCheckNotify::_getAllNotifiers($this->db) as $usr_id => $obj_ids)
			{
				// Get usr_data (default language, email)
				$usr_data = $this->__fetchUserData($usr_id);

				include_once '../classes/class.ilMimeMail.php';
				
				$mail =& new ilMimeMail();
				
				$mail->From('noreply');
				$mail->To($usr_data['email']);
				$mail->Subject($this->__txt($usr_data['lang'],'link_check_subject'));

				$body = $this->__txt($usr_data['lang'],'link_check_body_top')."\r\n";

				$counter = 0;
				foreach($obj_ids as $obj_id)
				{
					if(!isset($notify[$obj_id]))
					{
						continue;
					}
					++$counter;

					$body .= $this->__txt($usr_data['lang'],'lo');
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
					$mail->Body($body);
					$mail->Send();
					$this->__appendLogMessage('LinkChecker: Sent mail to '.$usr_data['email']);

				}
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
				$url_data = parse_url($matches[1][$i]);
				
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

	function __validateLinks($a_links)
	{
		if(!@include_once('HTTP/Request.php'))
		{
			$this->__appendLogMessage('LinkChecker: Pear HTTP_Request is not installed. Aborting');

			return array();
		}

		foreach($a_links as $link)
		{
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
		if($this->getMailStatus())
		{
			$this->__checkNotify();
		}
		$this->__clearDBData();


		foreach($this->getInvalidLinks() as $link)
		{

			$query = "INSERT INTO link_check ".
				"SET page_id = '".$link['page_id']."', ".
				"obj_id = '".$link['obj_id']."', ".
				"url = '".substr($link['complete'],0,255)."', ".
				"parent_type = '".$link['type']."', ".
				"http_status_code = '".$link['http_status_code']."', ".
				"last_check = '".time()."'";


			$res = $this->db->query($query);
		}

		// delete old values
		
	}

	function __checkNotify()
	{
		foreach($this->getInvalidLinks() as $link)
		{
			$query = "SELECT * FROM link_check ".
				"WHERE page_id = '".$link['page_id']."' ".
				"AND url = '".substr($link['complete'],0,255)."'";
			$res = $this->db->query($query);
			
			if(!$res->numRows())
			{
				$this->notify["$link[obj_id]"][] = array('page_id' => $link['page_id'],
														 'url'	   => $link['complete']);
			}
		}
	}


	function __clearDBData()
	{
		if($this->getValidateAll())
		{
			$query = "DELETE FROM link_check";
		}
		else
		{
			$query = "DELETE FROM link_check ".
				"WHERE obj_id = '".$this->getObjId()."'";
		}

		$this->db->query($query);

		return true;
	}
	*/
}
?>
