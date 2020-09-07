<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Wiki page template gui class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesWiki
 */
class ilWikiPageTemplateGUI
{
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $wiki_gui;
    protected $ctrl;
    protected $tpl;

    /**
     * Constructor
     *
     * @param ilObjWikiGUI $a_wiki_gui wiki gui object
     */
    public function __construct(ilObjWikiGUI $a_wiki_gui)
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $tpl = $DIC["tpl"];
        $ilToolbar = $DIC->toolbar();
        $lng = $DIC->language();

        $this->wiki_gui = $a_wiki_gui;
        $this->wiki = $this->wiki_gui->object;
        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->toolbar = $ilToolbar;
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $nc = $this->ctrl->getNextClass();

        switch ($nc) {
            default:
                $cmd = $this->ctrl->getCmd("listTemplates");
                if (in_array($cmd, array("listTemplates", "add", "remove", "saveTemplateSettings", "addPageTemplateFromPageAction", "removePageTemplateFromPageAction"))) {
                    $this->$cmd();
                }
                break;
        }
    }

    /**
     * List templates
     */
    public function listTemplates()
    {
        // list pages
        include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
        $pages = ilWikiPage::getAllWikiPages($this->wiki->getId());
        $options = array("" => $this->lng->txt("please_select"));
        foreach ($pages as $p) {
            //if (!in_array($p["id"], $ipages_ids))
            //{
            $options[$p["id"]] = ilUtil::shortenText($p["title"], 60, true);
            //}
        }

        $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
        $this->toolbar->setOpenFormTag(true);
        $this->toolbar->setCloseFormTag(false);

        if (count($options) > 0) {
            include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
            $si = new ilSelectInputGUI($this->lng->txt("wiki_pages"), "templ_page_id");
            $si->setOptions($options);
            $this->toolbar->addInputItem($si);
            $this->toolbar->addFormButton($this->lng->txt("wiki_add_template"), "add");
            $this->toolbar->addSeparator();
        }

        // empty page as template?
        include_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
        $cb = new ilCheckboxInputGUI($this->lng->txt("wiki_empty_page_template"), "empty_page_templ");
        $cb->setChecked($this->wiki->getEmptyPageTemplate());
        $this->toolbar->addInputItem($cb, true);
        $this->toolbar->addFormButton($this->lng->txt("save"), "saveTemplateSettings");


        include_once("./Modules/Wiki/classes/class.ilWikiPageTemplatesTableGUI.php");
        $tab = new ilWikiPageTemplatesTableGUI($this, "listTemplates", $this->wiki->getId());
        $tab->setOpenFormTag(false);
        $tab->setCloseFormTag(true);
        $this->tpl->setContent($tab->getHTML());
    }

    /**
     * Add page as template page
     */
    public function add()
    {
        include_once("./Modules/Wiki/classes/class.ilWikiPageTemplate.php");
        $wpt = new ilWikiPageTemplate($this->wiki->getId());
        $wpt->save((int) $_POST["templ_page_id"]);
        ilUtil::sendSuccess($this->lng->txt("wiki_template_added"), true);
        $this->ctrl->redirect($this, "listTemplates");
    }

    /**
     * Remove
     */
    public function remove()
    {
        include_once("./Modules/Wiki/classes/class.ilWikiPageTemplate.php");
        $wpt = new ilWikiPageTemplate($this->wiki->getId());

        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $id) {
                $wpt->remove((int) $id);
            }
            ilUtil::sendSuccess($this->lng->txt("wiki_template_status_removed"), true);
        }

        $this->ctrl->redirect($this, "listTemplates");
    }

    /**
     * Save template settings
     */
    public function saveTemplateSettings()
    {
        if (is_array($_POST["all_ids"])) {
            include_once("./Modules/Wiki/classes/class.ilWikiPageTemplate.php");
            foreach ($_POST["all_ids"] as $id) {
                $wpt = new ilWikiPageTemplate($this->wiki->getId());
                $wpt->save((int) $id, (int) $_POST["new_pages"][$id], (int) $_POST["add_to_page"][$id]);
            }
        }

        $this->wiki->setEmptyPageTemplate((int) $_POST["empty_page_templ"]);
        $this->wiki->update();

        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "listTemplates");
    }
    
    
    //
    // PAGE ACTIONS
    //
    
    public function removePageTemplateFromPageAction()
    {
        $page_id = (int) $_GET["wpg_id"];
        if ($page_id) {
            include_once("./Modules/Wiki/classes/class.ilWikiPageTemplate.php");
            $wpt = new ilWikiPageTemplate($this->wiki->getId());
            $wpt->remove($page_id);
            ilUtil::sendSuccess($this->lng->txt("wiki_template_status_removed"), true);
        }
        
        $this->ctrl->redirect($this, "listTemplates");
    }
    
    public function addPageTemplateFromPageAction()
    {
        $page_id = (int) $_GET["wpg_id"];
        if ($page_id) {
            include_once("./Modules/Wiki/classes/class.ilWikiPageTemplate.php");
            $wpt = new ilWikiPageTemplate($this->wiki->getId());
            $wpt->save($page_id);
            ilUtil::sendSuccess($this->lng->txt("wiki_template_added"), true);
        }
        
        $this->ctrl->redirect($this, "listTemplates");
    }
}
