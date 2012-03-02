<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* redirect script for studip-users
*
* @author Arne Schröder <schroeder@data-quest.de>
*
*/

/* ILIAS Version 4.0.2 */

if(file_exists("./ilias.ini.php")){
	require_once("./Services/Init/classes/class.ilIniFile.php");
	$ilIliasIniFile = new ilIniFile("./ilias.ini.php");
	$ilIliasIniFile->read();
	$serverSettings = $ilIliasIniFile->readGroup("server");	
	if ($serverSettings["studip"] != 1)
	{
		echo 'Option "studip" in ilias.ini.php is not enabled. You need to add studip = "1" to the server section.';
		exit();
	}
	
	if (isset($_GET['sess_id']))
	{	
		setcookie('PHPSESSID',$_GET['sess_id']);
		$_COOKIE['PHPSESSID'] = $_GET['sess_id'];
	}
	
	if (isset($_GET['client_id']))
	{	
		setcookie('ilClientId',$_GET['client_id']);
		$_COOKIE['ilClientId'] = $_GET['client_id'];
	}
	
	require_once "./include/inc.header.php";
	
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
					$jump_to = 'ilias.php?baseClass=ilSAHSPresentationGUI&ref_id='.$_GET['ref_id'];
					$redirect = true;
				break;
				case 'htlm':
					$_GET['baseClass'] = 'ilHTLMPresentationGUI'; 
					$jump_to = 'ilias.php';
					break;
				case 'glo':
					$_GET['baseClass'] = 'ilGlossaryPresentationGUI'; 
					$jump_to = 'ilias.php';
				break;
				default:
					unset($jump_to);
			}
		break;
		case 'new':	
			$_POST['new_type'] = $_GET['type'];
			$_POST['cmd']['create'] = 'add';
			$_GET['cmd'] = 'post';
			$_GET[ilCtrl::IL_RTOKEN_NAME] = $ilCtrl->getRequestToken();
			$_GET['baseClass'] = 'ilRepositoryGUI';
			$jump_to = 'ilias.php';
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
						$_GET['baseClass'] = 'ilSAHSEditGUI'; 
						$jump_to = 'ilias.php';
					break;
					case 'htlm':
						$_GET['baseClass'] = 'ilHTLMEditorGUI'; 
						$jump_to = 'ilias.php';
					break;
					case 'glo':
						$_GET['baseClass'] = 'ilGlossaryEditorGUI'; 
						$jump_to = 'ilias.php';
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
	if ($redirect)
	{
		header("Location: ".$jump_to);
		exit();
	}
	elseif(isset($jump_to))
	{
		include($jump_to);
	}
}
?>