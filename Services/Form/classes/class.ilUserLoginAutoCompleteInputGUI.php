<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilTextInputGUI.php");

/**
* This class represents a user login + autocomplete feature fomr input
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilUserLoginAutoCompleteInputGUI extends ilTextInputGUI
{
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title, $a_postvar, $a_class, $a_autocomplete_cmd)
	{
		global $tpl, $ilCtrl;
		
		if (is_object($a_class))
		{
			$a_class = get_class($a_class);
		}
		$a_class = strtolower($a_class);
		
		parent::__construct($a_title, $a_postvar);
		$this->setInputType("logac");
		$tpl->addJavaScript("./Services/User/js/ilUserAutoComplete.js");
		$this->setMaxLength(70);
		$this->setSize(30);
		$dsSchema = array("resultsList" => 'response.results',
			"fields" => array('login', 'firstname', 'lastname'));
		$this->setDataSourceResultFormat("ilUserAutoComplete");
		$this->setDataSource($ilCtrl->getLinkTargetByClass($a_class, $a_autocomplete_cmd));
		$this->setDataSourceSchema($dsSchema);

	}

	/**
	* Static asynchronous default auto complete function.
	*/
	static function echoAutoCompleteList()
	{
		$q = $_REQUEST["query"];
		include_once("./Services/User/classes/class.ilUserAutoComplete.php");
		$list = ilUserAutoComplete::getList($q);
		echo $list;
		exit;
	}
}
