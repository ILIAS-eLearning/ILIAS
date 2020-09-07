<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Block/classes/class.ilBlockGUI.php';

/**
 * Class ilRepositoryObjectSearchBlockGUI
 * Repository object search
 *
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 * @package ServicesSearch
 *
 */
class ilRepositoryObjectSearchBlockGUI extends ilBlockGUI
{
    public static $block_type = "objectsearch";
    public static $st_data;

    
    /**
     * Constructor
     * @global type $ilCtrl
     * @global type $lng
     */
    public function __construct($a_title)
    {
        parent::__construct();
        
        $this->setEnableNumInfo(false);
        
        $this->setTitle($a_title);
        $this->allow_moving = false;
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }

    /**
     * Get Screen Mode for current command.
     */
    public static function getScreenMode()
    {
        return IL_SCREEN_SIDE;
    }

    /**
     * execute command
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd("getHTML");

        switch ($next_class) {
            default:
                return $this->$cmd();
        }
    }

    /**
     * Get bloch HTML code.
     */
    public function getHTML()
    {
        return parent::getHTML();
    }

    /**
     * Fill data section
     */
    public function fillDataSection()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $tpl = new ilTemplate("tpl.search_search_block.html", true, true, 'Services/Search');

        $lng->loadLanguageModule('search');
        $tpl->setVariable("TXT_PERFORM", $lng->txt('btn_search'));
        $tpl->setVariable("FORMACTION", $ilCtrl->getFormActionByClass('ilrepositoryobjectsearchgui', 'performSearch'));
        $tpl->setVariable("SEARCH_TERM", ilUtil::prepareFormOutput(ilUtil::stripSlashes($_POST["search_term"])));

        $this->setDataSection($tpl->get());
    }
}
