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
* class for checking external links in page objects
* Normally used in Cron jobs, but should be extensible for use in learning modules. In this case set second parameter of 
* contructor = false, and use setPageObjectId() 
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* @package application
*/
class ilLinkChecker
{
	var $db = null;
	var $log_messages = array();
	var $invalid_links = array();

	var $validate_all = true;
	var $page_id = 0;


	function ilLinkChecker(&$db,$a_validate_all = true)
	{
		define('DEBUG',1);
		define('SOCKET_TIMEOUT',5);

		$this->db =& $db;
		$this->validate_all = $a_validate_all;
	}

	function setPageObjectId($a_page_id)
	{
		return $this->page_id = $a_page_id;
	}
	function getPageId()
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
	
	function checkLinks()
	{
		$pages = array();

		$this->__clearLogMessages();
		$this->__clearInvalidLinks();
		$this->__appendLogMessage('LinkChecker: Start checkLinks()');

		if(!$this->getValidateAll() and !$this->getPageId())
		{
			echo "ilLinkChecker::checkLinks() No Page id given";

			return false;
		}
		elseif(!$this->getValidateAll() and $this->getPageId())
		{
			$query = "SELECT * FROM page_object ".
				"WHERE page_id = '".$this->getPageId()."' ".
				"AND parent_type = 'lm'";

			$row = $this->db->getRow($query,DB_FETCHMODE_OBJECT);
			$pages[] = array('page_id' => $row->page_id,
							 'content' => $row->content,
							 'type'	 => $row->parent_type);
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

		return $this->getInvalidLinks();
	}

	// PRIVATE
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

		$req =& new HTTP_Request('');

		foreach($a_links as $link)
		{
			if($link['scheme'] !== 'http')
			{
				continue;
			}

			$req->setURL($link['complete']);
			$req->sendRequest();

			switch($req->getResponseCode())
			{
				case '200':
					; // EVERYTHING OK
					break;

				default:
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

	function __saveInDB()
	{
		$this->__clearDBData();

		foreach($this->getInvalidLinks() as $link)
		{
			$query = "INSERT INTO link_check ".
				"SET page_id = '".$link['page_id']."', ".
				"obj_id = '".$link['obj_id']."', ".
				"url = '".substr($link['complete'],0,255)."', ".
				"parent_type = '".$link['type']."'";

			$this->db->query($query);
		}
	}

	function __clearDBData()
	{
		$query = "DELETE FROM link_check";

		$this->db->query($query);

		return true;
	}
}
?>
