<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 2005 ILIAS open source, University of Cologne            |
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
* redirect script for studip-users
*
* @author Arne Schröder <schroeder@data-quest.de>
*
* @package studip-interface
*/

/* ILIAS Version 3.7.0stable */

$jump_to = 'index.php';

// redirect to specified page
$redirect = false;
switch($_GET['target'])
{
	case 'start': 
		switch($_GET['type'])
		{
			case 'lm':
				$_GET['baseClass'] = 'ilLMPresentationGUI'; 
				$jump_to = 'ilias.php';
			break;
			case 'tst':
				$_GET['cmd'] = 'infoScreen';
				$_GET['baseClass'] = 'ilObjTestGUI'; 
				$jump_to = 'ilias.php';
			break;
			case 'sahs':
				$_GET['baseClass'] = 'ilSAHSPresentationGUI'; 
				$jump_to = 'ilias.php';
			break;
			case 'htlm':
				$jump_to = 'content/fblm_presentation.php?ref_id='.$_GET['ref_id'];
				$redirect = true;
				break;
			default:
				unset($jump_to);
		}
	break;
	case 'new':	
		$_POST['new_type'] = $_GET['type'];
		$_POST['cmd']['create'] = 'add';
		$_GET['cmd'] = 'post';
		$jump_to = 'repository.php';
	break;
	case 'edit':
		switch($_GET['type'])
			{
				case 'lm':
					$_GET['baseClass'] = 'ilLMEditorGUI'; 
					$jump_to = 'ilias.php';
				break;
				case 'tst':
					$_GET['cmd'] = '';
					$_GET['baseClass'] = 'ilObjTestGUI'; 
					$jump_to = 'ilias.php';
				break;
				case 'sahs':
					$jump_to = 'content/sahs_edit.php?ref_id='.$_GET['ref_id'];
					$redirect = true;
				break;
				case 'htlm':
					$jump_to = 'content/fblm_edit.php?ref_id='.$_GET['ref_id'];
					$redirect = true;
				break;
				default:
					unset($jump_to);
			}
	break;
	case 'login':
	break;
	default:
	unset($jump_to);
}

if (isset($_GET['sess_id']))
{	
	setcookie('PHPSESSID',$_GET['sess_id']);
	$_COOKIE['PHPSESSID'] = $_GET['sess_id'];
} else {
	unset($jump_to);
}

if (isset($_GET['client_id']))
{	
	setcookie('ilClientId',$_GET['client_id']);
	$_COOKIE['ilClientId'] = $_GET['client_id'];
} else {
	unset($jump_to);
}

if ($redirect)
{
	header("Location: ".$jump_to);
	exit();
}
elseif(isset($jump_to)) 
	include($jump_to);
?>