<?php
/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * You must not remove this notice, or any other, from this software.
 *  
 * PRELIMINARY EDITION 
 * This is work in progress and therefore incomplete and buggy ... 
 *  
 * Content-Type: application/x-httpd-php; charset=ISO-8859-1
 * 
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
 * @copyright: (c) 2007 Alfred Kohnert
 *  
 * Business class for demonstration of current state of ILIAS SCORM 2004 
 * 
 */ 
 





function get_player()
{
	require_once('classes/ilSCORM13PlayerBridge.php');
	$player = new ilSCORM13PlayerBridge("");
	$player->getPlayer();
}


function get_cp()
{
	require_once('classes/ilSCORM13PlayerBridge.php');
	$player = new ilSCORM13PlayerBridge("");
	$player->getCPData();
}

function get_cmi()
{
	require_once('classes/ilSCORM13PlayerBridge.php');
	$player = new ilSCORM13PlayerBridge("");
	$player->fetchCMIData();
}

function post_cmi()
{
	require_once('classes/ilSCORM13PlayerBridge.php');
	$player = new ilSCORM13PlayerBridge("");
	$player->persistCMIData();
}

function get_test()
{
	$d = '{"package":[],"node":[[0,0,0,0,1,0,0,0,false,0,false,0,0,null,2,0,null,null,null,0,"credit",0,null,null,null,"Joe Student",null,0,0,null,0,null,0,0,null,null,null,"Mon Apr 09 2007 19:11:19 GMT+0200","PT0H0M0S",null]],"comment":[],"correct_response":[],"interaction":[],"objective":[]}';
	$d = '{"package":[],"node":[[0,0,0,0,1,0,null,0,0,false,0,false,0,0,null,"$2",0,null,null,3,0,"credit",0,null,null,null,"Joe Student",null,0,0,null,0,null,0,0,null,null,null,"Mon Apr 09 2007 19:56:36 GMT+0200","PT0H0M0S",null]],"comment":[],"correct_response":[],"interaction":[],"objective":[]}';

	//$d = json_decode($d);
	require_once('classes/ilSCORM13PlayerBridge.php');
	$player = new ilSCORM13PlayerBridge("");
	$player->persistCMIData($d);
		
}


$call = $_REQUEST['call'];
$path = $_SERVER['PATH_INFO'];

if ($call) 
{
	$cmd = strtolower($_SERVER['REQUEST_METHOD'] . '_' . $call); 
	if (is_callable($cmd)) 
	{
		$cmd();
	}
}
elseif ($path)
{
	require_once('classes/ilSCORM13PlayerBridge.php');
	$player = new ilSCORM13PlayerBridge("");
	$player->readFile($path);
}

?>
