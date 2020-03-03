<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
namespace LTI;

use LTI\ilTemplate as ilTemplate;

include_once("./Services/UICore/lib/html-it/IT.php");
include_once("./Services/UICore/lib/html-it/ITX.php");
require_once("./Services/UICore/classes/class.ilTemplate.php");

/**
* special template class to simplify handling of ITX/PEAR
* @author	Stefan Schneider <schneider@hrz.uni-marburg.de>
* @version	$Id$
*/
class ilTemplate extends \ilTemplate
{
    public function __construct(
        $file,
        $flag1,
        $flag2,
        $in_module = false,
        $vars = "DEFAULT",
        $plugin = false,
        $a_use_cache = false
    ) {
        parent::__construct(
            $file,
            $flag1,
            $flag2,
            $in_module,
            $vars,
            $plugin,
            $a_use_cache
        );
    }
    
    public function getMainMenu()
    {
        global $ilMainMenu;
        //$ilMainMenu->setLoginTargetPar($this->getLoginTargetPar());
        $this->main_menu = $ilMainMenu->getHTML();
        $this->main_menu_spacer = $ilMainMenu->getSpacerClass();
    }
}
