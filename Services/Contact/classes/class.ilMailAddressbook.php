<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* @author Nadia Krzywon
* @version $Id$
*
*/
class ilMailAddressbook
{
	private $user_id = null;

	public function __construct()
	{
		global $ilUser;

		$this->user_id = $ilUser->getId();
	}
	
	public function getAddressbookAsync($search)
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
			WHERE abook.user_id = '.$ilDB->quote($this->user_id, 'integer').'
			AND ( '. $ilDB->like('abook.login', 'text', $search).' 
			OR '. $ilDB->like('abook.firstname', 'text', $search).' 
			OR '. $ilDB->like('abook.lastname', 'text', $search).' 
			)';

		$query_res = $ilDB->query($query);
		
		while ($row = $query_res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$tmp = new stdClass();
			$tmp->login = $row->login;
			$tmp->firstname = $row->firstname;
			$tmp->lastname = $row->lastname;
			$result->response->results[] = $tmp;
		}
		$result->response->total = count($result->response->results);
		
		return $result;
	}
}
?>
