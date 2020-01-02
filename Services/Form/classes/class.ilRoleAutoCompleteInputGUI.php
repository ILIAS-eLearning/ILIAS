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
    public function __construct($a_title, $a_postvar, $a_class, $a_autocomplete_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $ilCtrl = $DIC->ctrl();
        
        if (is_object($a_class)) {
            $a_class = get_class($a_class);
        }
        $a_class = strtolower($a_class);
        
        parent::__construct($a_title, $a_postvar);
        $this->setInputType("raci");
        $this->setMaxLength(70);
        $this->setSize(30);
        $this->setDataSource($ilCtrl->getLinkTargetByClass($a_class, $a_autocomplete_cmd, "", true));
    }

    /**
    * Static asynchronous default auto complete function.
    */
    public static function echoAutoCompleteList()
    {
        $q = $_REQUEST["term"];
        include_once("./Services/AccessControl/classes/class.ilRoleAutoComplete.php");
        $list = ilRoleAutoComplete::getList($q);
        echo $list;
        exit;
    }
}
