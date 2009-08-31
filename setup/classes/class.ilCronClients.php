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
* Cron job class
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*/

include_once 'PEAR.php';
include_once 'MDB2.php';

class ilCronClients extends PEAR
{
	var $fp;

	// PRIVATE CONTRUCTOR
	function ilCronClients()
	{
		define('INI_FILE_PATH','../ilias.ini.php');
		define('CRON_DEBUG',1);

		$this->__createLock();
		$this->__readClients();
		register_shutdown_function(array($this,'__ilCronClients'));
	}


	function &_getInstance()
	{
		if(ilCronClients::_lockExists())
		{
			die('Instance already created');
		}
		return new ilCronClients();
	}

	function __createLock()
	{
		$this->fp = @fopen('cron.lock','wb');
		fwrite($this->fp,(string) time(),strlen((string) time()));

		return true;
	}

	function _lockExists()
	{
		if(@file_exists('cron.lock'))
		{
			$fp = fopen('cron.lock','r');

			(int) $timest = fread($fp,filesize('cron.lock'));
			
			if(!CRON_DEBUG and ($timest > time() - 60 * 60 * 12))
			{
				return true;
			}
			unlink('cron.lock');
		}
		return false;
	}

	function __readClients()
	{
		include_once './classes/class.ilIniFile.php';

		$ini_file_obj =& new ilIniFile(INI_FILE_PATH);

		$ini_file_obj->read();

		$this->log['enabled'] = $ini_file_obj->readVariable('log','enabled');
		$this->log['path'] = $ini_file_obj->readVariable('log','path');
		$this->log['file'] = $ini_file_obj->readVariable('log','file');

		$this->web_enabled = $ini_file_obj->readVariable('cron','web_enabled');
		$this->web_pass = $ini_file_obj->readVariable('cron','web_pass');
		$this->__checkAccess();


		$this->client_data = $ini_file_obj->readGroup('clients');
		unset($ini_file_obj);

		// open client.ini.php

		// set path to directory where clients reside

		$this->client_ini = array();
		$dp = opendir('../'.$this->client_data['path']);
		while(($file = readdir($dp)) !== false)
		{
			if($file == '.' or $file == '..' or $file == 'CVS')
			{
				continue;
			}
			if(@file_exists('../'.$this->client_data['path'].'/'.$file.'/'.$this->client_data['inifile']))
			{
				$tmp_data['path'] = '../'.$this->client_data['path'].'/'.$file.'/'.$this->client_data['inifile'];
				$tmp_data['name'] = $file;

				$this->client_ini[] = $tmp_data;
				unset($tmp_data);
			}
		}

		$this->__startChecks();
	}

	function __startChecks()
	{
		foreach($this->client_ini as $client_data)
		{
			include_once './classes/class.ilIniFile.php';

			$ini_file_obj =& new ilIniFile($client_data['path']);

			$ini_file_obj->read();
			$this->db_data = $ini_file_obj->readGroup('db');

			$this->__readFileDBVersion();

			if($this->__openDb())
			{
				include_once './setup/classes/class.ilCron.php';
				
				$cron_obj =& new ilCron($this->db);
				if($this->log['enabled'])
				{
					$cron_obj->initLog($this->log['path'],$this->log['file'],$client_data['name']);
				}
				
				if($this->__checkDBVersion())
				{
					$cron_obj->start();
				}
				else
				{
					include_once './Services/Logging/classes/class.ilLog.php';
					
					$log =& new ilLog($this->log['path'],$this->log['file']);
					$log->write('Cron: Database not up to date. Aborting');
				}
				$this->db->disconnect();
			}
		}
	}

	function __openDb()
	{
		$dsn = $this->db_data['type']."://".
			$this->db_data['user'].":".
			$this->db_data['pass']."@".
			$this->db_data['host']."/".
			$this->db_data['name'];

		$this->db = MDB2::connect($dsn,true);

		if (MDB2::isError($this->db))
		{
			return false;
		}
		return true;
	}

	function __checkDBVersion()
	{
		$GLOBALS["ilDB"] = $this->db;
		include_once './Services/Administration/classes/class.ilSetting.php';
		$setting = new ilSetting();

		/*$query = "SELECT value FROM sett ings ".
			"WHERE keyword = 'db_version'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$db_version = $row->value;
		}*/
		$db_version = $setting->get("db_version");

		return $db_version == $this->file_version;
	}

	function __readFileDBVersion()
	{
		$this->db_version = 99999;

		// GET FILE VERSION
		if(!$content = file('./setup/sql/dbupdate_02.php'))
		{
			echo 'Cannot open ./setup/sql/dbupdate_02.php';
			return false;
		}
		foreach($content as $row)
		{
			if(preg_match('/^<#([0-9]+)>/',$row,$matches))
			{
				$this->file_version = $matches[1];
			}
		}
	}

	function __checkAccess()
	{
		if($_SERVER['REQUEST_URI'])
		{
			if(!$this->web_enabled or ($_GET['web_pass'] !== $this->web_pass))
			{
				if($this->log['enabled'])
				{
					include_once './Services/Logging/classes/class.ilLog.php';

					$this->log =& new ilLog($this->log['path'],$this->log['file']);

					$this->log->write('Cron: __checkAccess() failed');
					
					exit;
				}
			}
		}
		return true;
	}



	function __ilCronClients()
	{
		fclose($this->fp);
	}
}
		
