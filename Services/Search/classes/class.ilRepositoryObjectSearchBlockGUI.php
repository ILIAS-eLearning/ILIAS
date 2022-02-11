<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilRepositoryObjectSearchBlockGUI
 * Repository object search
 *
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @package ServicesSearch
 *
 */
class ilRepositoryObjectSearchBlockGUI extends ilBlockGUI
{
    public static string $block_type = "objectsearch";
    public static $st_data;


    public function __construct(string $a_title)
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
    public static function getScreenMode() : string
    {
        return IL_SCREEN_SIDE;
    }

    public function executeCommand() : void
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd("getHTML");

        switch ($next_class) {
            default:
                $this->$cmd();
        }
    }

    public function getHTML() : string
    {
        return parent::getHTML();
    }

    public function fillDataSection() : void
    {
        $this->setDataSection($this->getLegacyContent());
    }

    //
    // New rendering
    //

    protected $new_rendering = true;


    /**
     * @inheritdoc
     */
    protected function getLegacyContent() : string
    {

        $tpl = new ilTemplate("tpl.search_search_block.html", true, true, 'Services/Search');

        $this->lng->loadLanguageModule('search');
        $tpl->setVariable("TXT_SEARCH_INPUT_LABEL", $this->lng->txt('search_field'));
        $tpl->setVariable("TXT_PERFORM", $this->lng->txt('btn_search'));
        $tpl->setVariable("FORMACTION", $this->ctrl->getFormActionByClass('ilrepositoryobjectsearchgui', 'performSearch'));
        $tpl->setVariable("SEARCH_TERM",
            ilLegacyFormElementsUtil::prepareFormOutput(ilUtil::stripSlashes($_POST["search_term"]))
        );

        return $tpl->get();
    }
}
