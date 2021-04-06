<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Page multilinguality GUI class.
 * This could be generalized as an object service in the future.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPageMultiLangGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $single_page_mode = false;

    /**
     * Constructur
     *
     * @param string $a_parent_type parent object type
     * @param int $a_parent_id parent object id
     * @param bool $a_single_page_mode single page mode (page includes ml managing)
     */
    public function __construct($a_parent_type, $a_parent_id, $a_single_page_mode = false)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        //$this->ml = new ilPageMultiLang($a_parent_type, $a_parent_id);

        // object translation
        $this->ot = ilObjectTranslation::getInstance($a_parent_id);
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        
        $next_class = $ilCtrl->getNextClass();
        
        switch ($next_class) {
            default:
                $cmd = $ilCtrl->getCmd("settings");
                if (in_array($cmd, array("settings", "activateMultilinguality", "cancel",
                    "saveMultilingualitySettings", "confirmDeactivateMultiLanguage", "addLanguage",
                    "saveLanguages", "deactivateMultiLang", "confirmRemoveLanguages",
                    "removeLanguages"))) {
                    $this->$cmd();
                }
        }
    }

    /**
     * Get multi lang info
     */
    public function getMultiLangInfo($a_page_lang = "-")
    {
        $lng = $this->lng;
        
        if ($a_page_lang == "") {
            $a_page_lang = "-";
        }
        
        $lng->loadLanguageModule("meta");
        
        $tpl = new ilTemplate("tpl.page_multi_lang_info.html", true, true, "Services/COPage");
        $tpl->setVariable("TXT_MASTER_LANG", $lng->txt("obj_master_lang"));
        $tpl->setVariable("VAL_ML", $lng->txt("meta_l_" . $this->ot->getMasterLanguage()));
        $cl = ($a_page_lang == "-")
            ? $this->ot->getMasterLanguage()
            : $a_page_lang;
        $tpl->setVariable("TXT_CURRENT_LANG", $lng->txt("cont_current_lang"));
        $tpl->setVariable("VAL_CL", $lng->txt("meta_l_" . $cl));
        return $tpl->get();
    }
}
