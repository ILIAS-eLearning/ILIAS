<?php
/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * Copyright (c) 2005-2007 Alfred Kohnert.
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * You must not remove this notice, or any other, from this software.
 */

/**
 * PRELIMINARY EDITION 
 * This is work in progress and therefore incomplete and buggy ... 
 *  
 * Business class for demonstration of current state of ILIAS SCORM 2004 
 * 
 * For security reasons this is not connected to ILIAS database
 * but uses a small fake database in slite2 format.
 * Waits on finishing other sub tasks before being connected to ILIAS.
 * 
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id: $
 * @copyright: (c) 2005-2007 Alfred Kohnert
 *
 * Frontend for demonstration of current state of ILIAS SCORM 2004 
 *  
 */ 
 

require_once('classes/phpext.php');
require_once('classes/ilSCORM13DB.php');

define('USR_ID', 50);
define('ilSCORM13_FOLDER', dirname(__FILE__) . '/packages');
define('PACKGAGE_BASE', 'sco.php/packages/' . $_REQUEST['packageId'] . '/');


ilSCORM13DB::init(
	'sqlite2:data/slite2.db',
	'sqlite'
);	

function get_player()
{
	header('Content-Type: text/html; charset=UTF-8');
	include('templates/tpl/player.tpl');
}

function get_lang()
{
	header('Content-Type: text/javascript; charset=UTF-8');
	include('scripts/lang.js');
}

function get_cp()
{
	header('Content-Type: text/javascript; charset=UTF-8');
	$packageData = ilSCORM13DB::getRecord(
		'cp_package', 
		'obj_id', 
		$_REQUEST['packageId']
	);
	print('var Package = ' . $packageData['jsdata'] . ';'); 
	print('Package.base = "' . PACKGAGE_BASE . '";'); 
}

function get_cmi()
{
	//require_once('classes/ilSCORM13Player.php');
	header('Content-Type: text/javascript; charset=UTF-8');
	$cmiData = ilSCORM13DB::query('SELECT * FROM cmi_node, cp_node WHERE 
		cmi_node.user_id=? AND cp_node.slm_id=? ORDER BY cp_node.cp_node_id',
		array(USR_ID, $_REQUEST['packageId'])
	);
	die('var Userdata = ' . json_encode($cmiData)); 
}


$cmd = strtolower($_SERVER['REQUEST_METHOD'] . '_' . $_REQUEST['call']); 

if (is_callable($cmd)) $cmd();
else die($cmd);


?>
