<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
