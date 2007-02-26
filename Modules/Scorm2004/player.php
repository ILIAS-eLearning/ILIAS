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
 


	// common constants classes, initalization, php core extension, etc. 
require_once('common.php');


function get_player()
{
	require_once('classes/ilSCORM13Player.php');
	$player = new ilSCORM13Player();
	$player->getPlayer();
}


function get_cp()
{
	require_once('classes/ilSCORM13Player.php');
	$player = new ilSCORM13Player();
	$player->getCPData();
}

function get_cmi()
{
	require_once('classes/ilSCORM13Player.php');
	$player = new ilSCORM13Player();
	$player->fetchCMIData();
}

function post_cmi()
{
	require_once('classes/ilSCORM13Player.php');
	$player = new ilSCORM13Player();
	$player->persistCMIData();
}

$cmd = strtolower($_SERVER['REQUEST_METHOD'] . '_' . $_REQUEST['call']); 

if (is_callable($cmd)) $cmd();
else die($cmd);


?>
