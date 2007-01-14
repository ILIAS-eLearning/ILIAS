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
 */

/**
 * PRELIMINARY EDITION 
 * This is work in progress and therefore incomplete and buggy ... 
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
	$packageId = intval($_REQUEST['packageId']);
	$packageData = ilSCORM13DB::getRecord(
		'cp_package', 
		'obj_id', 
		$packageId
	);
	print('var Package = ' . $packageData['jsdata'] . ';'); 
	print('Package.base = "' . str_replace('{packageId}', $packageId, IL_OP_PACKAGE_BASE) . '";'); 
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
