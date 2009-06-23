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

// jump to setup if ILIAS3 is not installed
if(!file_exists(getcwd().'/ilias.ini.php'))
{
    header('Location: ./setup/setup.php');
	exit();
}

require_once 'Services/Init/classes/class.ilInitialisation.php';
$ilInit = new ilInitialisation();
$ilInit->returnBeforeAuth(true);
$ilInit->initILIAS();
$ilInit->initLanguage();

include_once 'Services/JSON/classes/class.ilJsonUtil.php';

global $ilDB;

$ilDB->setLimit(1);
$res = $ilDB->queryF('
	SELECT data, last_remind_ts FROM usr_session 
	WHERE session_id = %s ORDER BY expires DESC',
    array('text'),
    array($_GET['session_id']));
$oRow = $ilDB->fetchObject($res);

$response_data = array('remind' => false);
$currentTime = time();

if(is_object($oRow))
{			    
	$data = $oRow->data;
		
	$expiresTime = null;
	$pattern = "idle\";i:";
	if(($lft_pos = strpos($data, $pattern)) !== false)
	{
		$substr = substr($data, $lft_pos + strlen($pattern));
		$pattern = ";";
		if(($rgt_pos = strpos($substr, $pattern)) !== false)
		{
			$expiresTime = (int)substr($substr, 0, $rgt_pos);
		}
	}
	
	if($expiresTime === null)
	{
		echo ilJsonUtil::encode($response_data);
		exit();
	}
	else
	{			
		$leadTime = $_GET['lead_time'];			
		$expiresTime += $ilClientIniFile->readVariable('session', 'expire');

		if($expiresTime >= $currentTime &&
		   $oRow->last_remind_ts <= $currentTime - $_GET['countDownTime'])
		{
			include_once 'Services/Calendar/classes/class.ilDate.php';
			$date = new ilDateTime(time(),IL_CAL_UNIX);
			$currentTimeTxt = $date->get(IL_CAL_FKT_DATE,'H:i:s', $_GET['timezone']);
			
			$ilDB->manipulateF('
				UPDATE usr_session SET last_remind_ts = %s WHERE session_id = %s',
			    array('integer', 'text'),
			    array($currentTime, $_GET['session_id'])); 
			
			$response_data = array(
				'remind' => true,
				'expiresInTimeX' => ilFormat::_secondsToString($leadTime, true),
				'currentTime' => $currentTimeTxt,
			);	
		}
		
	}	
}

echo ilJsonUtil::encode($response_data);
exit();
?>