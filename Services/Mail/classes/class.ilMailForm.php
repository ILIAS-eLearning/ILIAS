<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* @author Nadia Krzywon
* @version $Id$
*/
class ilMailForm
{
	private $allow_smtp = null;
	private $user_id = null;
	
	public function __construct()
	{
		global $ilUser, $rbacsystem;
		
		$this->allow_smtp = $rbacsystem->checkAccess('smtp_mail', MAIL_SETTINGS_ID);
		$this->user_id = $ilUser->getId();
		
	}

	public function getRecipientAsync($a_search)
	{
		global $ilDB;
		
		$ilDB->setLimit(0,20);

		$query =
			'SELECT DISTINCT
				abook.login login,
				abook.firstname firstname,
				abook.lastname lastname,
				"addressbook" type
			FROM addressbook abook
			WHERE abook.user_id = '.$ilDB->quote($this->user_id,'integer') .' 
			AND abook.login IS NOT NULL
			AND ('. $ilDB->like('abook.login', 'text', $a_search).' 
					OR '. $ilDB->like('abook.firstname', 'text', $a_search).' 
					OR '. $ilDB->like('abook.lastname', 'text', $a_search).' 
			)
			UNION
			SELECT DISTINCT
				abook.email login,
				abook.firstname firstname,
				abook.lastname lastname,
				"addressbook" type
			FROM addressbook abook
			WHERE 1='.($this->allow_smtp ? 1 : 0).'
			AND abook.user_id = '.$ilDB->quote($this->user_id,'integer') .' 
			AND abook.login IS NULL
			AND ('. $ilDB->like('abook.email', 'text', $a_search).' 
					OR '. $ilDB->like('abook.firstname', 'text', $a_search).' 
					OR '. $ilDB->like('abook.lastname', 'text', $a_search).' 
			)			
			UNION
			SELECT DISTINCT
				mail.rcp_to login,
				"" firstname,
				"" lastname,
				"mail" type
			FROM mail
			WHERE '. $ilDB->like('mail.rcp_to', 'text', $a_search).' 
				AND sender_id ='.$ilDB->quote($this->user_id,'integer');
				
		$query_res = $ilDB->query($query);

		$setMap = array();
		$i = 0;
		while ($row = $query_res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if ($i > 20)
				break;
			if (isset($setMap[$row->login]))
				continue;
			$parts = array();
			if (strpos($row->login, ',') || strpos($row->login, ';'))
			{
				$parts = split("[ ]*[;,][ ]*", trim($row->login));
				foreach($parts as $part)
				{
					$tmp = new stdClass();
					$tmp->login = $part;
					$i++;
					$setMap[$part] = 1;
				}
			}
			else
			{
				$tmp = new stdClass();
				$tmp->login = $row->login;
				if ($row->public_profile == 'y' || $row->type = 'addressbook')
				{
					$tmp->firstname = $row->firstname;
					$tmp->lastname = $row->lastname;
				}
				$result->response->results[] = $tmp;
				$i++;
				$setMap[$row->login] = 1;
			}
		}
		$result->response->total = count($result->response->results);

		return $result;
	}
}
?>