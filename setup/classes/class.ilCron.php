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


// include pear
//require_once("DB.php");

/**
* Cron job class
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*/
class ilCron
{
	var $db;
	var $log;

	function ilCron(&$db)
	{
		define('DEBUG',1);
		define('SOCKET_TIMEOUT',5);

		$this->db = $db;
		
		$GLOBALS["ilDB"] = $this->db;
		include_once '../Services/Administration/classes/class.ilSetting.php';
		$this->setting = new ilSetting();

	}

	function initLog($path,$file,$client)
	{
		include_once '../Services/Logging/classes/class.ilLog.php';

		$this->log =& new ilLog($path,$file,$client);

		return true;
	}

	function txt($language,$key,$module = 'common')
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
		return $value ? $value : $key;
	}

	
	function start()
	{
		// add here other checks
		if($this->__readSetting('cron_user_check'))
		{
			$this->__checkUserAccounts();
		}
		if($this->__readSetting('cron_link_check'))
		{
			$this->__checkLinks();
		}
	}

	function __checkUserAccounts()
	{
		$two_weeks_in_seconds = 60 * 60 * 24 * 14;

		$this->log->write('Cron: Start checkUserAccounts()');
		$query = "SELECT * FROM usr_data,usr_pref ".
			"WHERE time_limit_message = '0' ".
			"AND time_limit_unlimited = '0' ".
			"AND time_limit_from < '".time()."' ".
			"AND time_limit_until > '".$two_weeks_in_seconds."' ".
			"AND usr_data.usr_id = usr_pref.usr_id ".
			"AND keyword = 'language'";

		$res = $this->db->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			include_once '../Services/Mail/classes/class.ilMimeMail.php';

			$data['expires'] = $row->time_limit_until;
			$data['email'] = $row->email;
			$data['login'] = $row->login;
			$data['usr_id'] = $row->usr_id;
			$data['language'] = $row->value;
			$data['owner'] = $row->time_limit_owner;

			// Get owner
			$query = "SELECT email FROM usr_data WHERE usr_id = '".$data['owner']."'";
			
			$res2 = $this->db->query($query);
			while($row = $res2->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$from = $row->email;
			}

			// Send mail
			$mail =& new ilMimeMail();
			
			$mail->From($from);
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
		
	}


	function __checkLinks()
	{
		include_once'../classes/class.ilLinkChecker.php';

		$link_checker =& new ilLinkChecker($this->db);
		$link_checker->setMailStatus(true);

		$invalid = $link_checker->checkLinks();
		foreach($link_checker->getLogMessages() as $message)
		{
			$this->log->write($message);
		}

		return true;
	}

	function __readSetting($a_keyword)
	{
		return $this->setting->get($a_keyword);
/*		$query = "SELECT * FROM sett ings ".
			"WHERE keyword = '".$a_keyword."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->value ? $row->value : 0;
		}
		return 0;	*/
	}
}
?>
