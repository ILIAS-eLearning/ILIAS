<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilMediaPoolPage GUI class
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilMediaPoolPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilMediaPoolPageGUI: ilPublicUserProfileGUI
 */
class ilMediaPoolPageGUI extends ilPageObjectGUI
{
    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
    * Constructor
    */
    public function __construct($a_id = 0, $a_old_nr = 0, $a_prevent_get_id = false, $a_lang = "")
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->lng = $DIC->language();
        
        parent::__construct("mep", $a_id, $a_old_nr, $a_prevent_get_id, $a_lang);

        $this->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(0));

        $this->setEditPreview(true);
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                return parent::executeCommand();
        }
    }

    /**
    * Set Media Pool Page Object.
    *
    * @param	object	$a_media_pool_page	Media Pool Page Object
    */
    public function setMediaPoolPage($a_media_pool_page)
    {
        $this->setPageObject($a_media_pool_page);
    }

    /**
    * Get Media Pool Page Object.
    *
    * @return	object	Media Pool Page Object
    */
    public function getMediaPoolPage()
    {
        return $this->getPageObject();
    }

    /**
    * Get media pool page gui for id and title
    */
    public static function getGUIForTitle($a_media_pool_id, $a_title, $a_old_nr = 0)
    {
        global $DIC;

        $id = ilMediaPoolPage::getPageIdForTitle($a_media_pool_id, $a_title);
        $page_gui = new ilMediaPoolPageGUI($id, $a_old_nr);
        
        return $page_gui;
    }
    
    /**
    * View media pool page.
    */
    public function preview()
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $lng = $this->lng;
        
        return parent::preview();
    }
    
    /**
     * Show page
     */
    public function showPage($a_no_title = false)
    {
        $tpl = $this->tpl;

        // get raw page content is used for including into other pages
        if (!$this->getRawPageContent()) {
            $tpl->setCurrentBlock("ContentStyle");
            $tpl->setVariable(
                "LOCATION_CONTENT_STYLESHEET",
                ilObjStyleSheet::getContentStylePath(0)
            );
            $tpl->parseCurrentBlock();
        }

        $this->setTemplateOutput(false);
        if (!$a_no_title) {
            $this->setPresentationTitle(ilMediaPoolItem::lookupTitle($this->getMediaPoolPage()->getId()));
        }
        $output = parent::showPage();
        
        return $output;
    }

    public function getTabs($a_activate = "")
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        parent::getTabs($a_activate);
    }
    
    /**
     * Get raw content
     *
     * @param
     * @return
     */
    public function getRawContent()
    {
        $this->setRawPageContent(true);
        $this->setLinkXML("");
        return $this->showPage(true);
    }

    /**
     * Set template
     * @param ilTemplate
     */
    public function setTemplate($tpl)
    {
        $this->tpl = $tpl;
    }
}
