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
* 
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/

class ilCronCheckUserAccounts
{
	function ilCronCheckUserAccounts()
	{
		global $ilLog,$ilDB;

		$this->log =& $ilLog;
		$this->db =& $ilDB;
	}

	function check()
	{
		$two_weeks_in_seconds = 60 * 60 * 24 * 14;

		$this->log->write('Cron: Start ilCronCheckUserAccounts::check()');

		$query = "SELECT * FROM usr_data,usr_pref ".
			"WHERE time_limit_message = '0' ".
			"AND time_limit_unlimited = '0' ".
			"AND time_limit_from < '".time()."' ".
			"AND time_limit_until > '".$two_weeks_in_seconds."' ".
			"AND usr_data.usr_id = usr_pref.usr_id ".
			"AND keyword = 'language'";

		$res = $this->db->query($query);

		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			include_once 'Services/Mail/classes/class.ilMimeMail.php';

			$data['expires'] = $row->time_limit_until;
			$data['email'] = $row->email;
			$data['login'] = $row->login;
			$data['usr_id'] = $row->usr_id;
			$data['language'] = $row->value;
			$data['owner'] = $row->time_limit_owner;

			// Send mail
			$mail =& new ilMimeMail();
			
			$mail->From('noreply');
			$mail->To($data['email']);
			$mail->Subject($this->txt($data['language'],'account_expires_subject'));
			$mail->Body($this->txt($data['language'],'account_expires_body')." ".strftime('%Y-%m-%d %R',$data['expires']));
			$mail->send();

			// set status 'mail sent'
			$query = "UPDATE usr_data SET time_limit_message = '1' WHERE usr_id = '".$data['usr_id']."'";
			$this->db->query($query);
			
			// Send log message
			$this->log->write('Cron: (checkUserAccounts()) sent message to '.$data['login'].'.');


		}

		$this->log->write('Cron: End ilCronCheckUserAccounts::check()');
	}
	function txt($language,$key,$module = 'common')
	{
		$query = "SELECT value FROM lng_data ".
			"WHERE module = '".$module."' ".
			"AND identifier = '".$key."' ".
			"AND lang_key = '".$language."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$value = $row->value;
		}
		return $value ? $value : $key;
	}
}




?>
