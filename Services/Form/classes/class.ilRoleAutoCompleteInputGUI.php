<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilTextInputGUI.php");

/**
* This class represents a role + autocomplete feature form input
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilRoleAutoCompleteInputGUI extends ilTextInputGUI
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
		$this->setInputType("raci");
		$tpl->addJavaScript("./Services/AccessControl/js/ilRoleAutoComplete.js");
		$this->setMaxLength(70);
		$this->setSize(30);
		$dsSchema = array("resultsList" => 'response.results',
			"fields" => array('role', 'container'));
		$this->setDataSourceResultFormat("ilRoleAutoComplete");
		$this->setDataSource($ilCtrl->getLinkTargetByClass($a_class, $a_autocomplete_cmd));
		$this->setDataSourceSchema($dsSchema);

	}

	/**
	* Static asynchronous default auto complete function.
	*/
	static function echoAutoCompleteList()
	{
		$q = $_REQUEST["query"];
		include_once("./Services/AccessControl/classes/class.ilRoleAutoComplete.php");
		$list = ilRoleAutoComplete::getList($q);
		echo $list;
		exit;
	}
}
