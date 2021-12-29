<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");

/**
* Class ilMediaPoolPage GUI class
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilMediaPoolPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
* @ilCtrl_Calls ilMediaPoolPageGUI: ilPublicUserProfileGUI, ilObjectMetaDataGUI
*
* @ingroup ModulesMediaPool
*/
class ilMediaPoolPageGUI extends ilPageObjectGUI
{
    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilObjMediaPoolGUI
     */
    protected $pool_gui = null;

    /**
     * @var ilObjMediaPool
     */
    protected $pool = null;

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
        $tpl = $DIC["tpl"];

        parent::__construct("mep", $a_id, $a_old_nr, $a_prevent_get_id, $a_lang);

        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $this->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(0));

        $this->setEditPreview(true);
    }

    /**
     * Set pool gui
     * @param ilObjMediaPoolGUI $pool_gui
     */
    public function setPoolGUI(ilObjMediaPoolGUI $pool_gui)
    {
        $this->pool_gui = $pool_gui;
        $this->pool = $pool_gui->object;

        $this->getMediaPoolPage()->setPool($this->pool);

        $this->activateMetaDataEditor(
            $this->pool,
            "mpg",
            $this->getId(),
            $this->getMediaPoolPage(),
            "MDUpdateListener"
        );
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
     * @return ilMediaPoolPage
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

        $ilDB = $DIC->database();

        include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
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
        $ilCtrl = $this->ctrl;

        // get raw page content is used for including into other pages
        if (!$this->getRawPageContent()) {
            include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
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
        $this->setMediaPoolPageTabs();
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
     * Create new content snippet
     */
    public function createMediaPoolPage()
    {
        $tpl = $this->tpl;

        $form = $this->initMediaPoolPageForm("create");
        $tpl->setContent($form->getHTML());
        $this->tabs->clearTargets();
    }

    /**
     * Edit media pool page
     */
    public function editMediaPoolPage()
    {
        $tpl = $this->tpl;
        $form = $this->initMediaPoolPageForm("edit");
        $this->getMediaPoolPageValues($form);
        $tpl->setContent($form->getHTML());
    }

    /**
     * Save media pool page
     */
    public function saveMediaPoolPage()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = $this->initMediaPoolPageForm("create");
        if ($form->checkInput()) {
            // create media pool item
            include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
            $item = new ilMediaPoolItem();
            $item->setTitle($_POST["title"]);
            $item->setType("pg");
            $item->create();

            if ($item->getId() > 0) {
                // put in tree
                $tree = $this->pool->getTree();
                $parent = $_GET["mepitem_id"] > 0
                    ? $_GET["mepitem_id"]
                    : $tree->getRootId();
                $this->pool->insertInTree($item->getId(), $parent);

                // create page
                include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
                $page = new ilMediaPoolPage();
                $page->setId($item->getId());
                $page->setParentId($this->pool->getId());
                $page->create();
                $page->createMetaData($this->pool->getId());

                $ilCtrl->setParameterByClass("ilmediapoolpagegui", "mepitem_id", $item->getId());
                $ilCtrl->redirectByClass("ilmediapoolpagegui", "edit");
            }
            $ilCtrl->returnToParent($this);
        }

        $form->setValuesByPost();
        $tpl->setContent($form->getHtml());
    }

    /**
     * Update media pool page
     */
    public function updateMediaPoolPage()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        $form = $this->initMediaPoolPageForm("edit");
        if ($form->checkInput()) {
            $item = new ilMediaPoolItem($_GET["mepitem_id"]);
            $item->setTitle($_POST["title"]);
            $item->update();
            $this->getMediaPoolPage()->updateMetaData();
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editMediaPoolPage");
        }

        $form->setValuesByPost();
        $tpl->setContent($form->getHtml());
    }

    /**
     * Init page form.
     *
     * @param        int        $a_mode        Edit Mode
     */
    public function initMediaPoolPageForm($a_mode = "edit")
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(128);
        $ti->setRequired(true);
        $form->addItem($ti);

        // save and cancel commands
        if ($a_mode == "create") {
            $form->addCommandButton("saveMediaPoolPage", $lng->txt("save"));
            $form->addCommandButton("cancelSave", $lng->txt("cancel"));
            $form->setTitle($lng->txt("mep_new_content_snippet"));
        } else {
            $form->addCommandButton("updateMediaPoolPage", $lng->txt("save"));
            $form->setTitle($lng->txt("mep_edit_content_snippet"));
        }

        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    /**
     *
     * @param
     */
    protected function cancelSave()
    {
        $ctrl = $this->ctrl;
        $ctrl->returnToParent($this);
    }

    /**
     * Get current values for media pool page from
     */
    public function getMediaPoolPageValues($form)
    {
        $values = array();

        include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
        $values["title"] = ilMediaPoolItem::lookupTitle($_GET["mepitem_id"]);

        $form->setValuesByArray($values);
    }

    /**
     * Set media pool page tabs
     */
    public function setMediaPoolPageTabs()
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ilTabs->addTarget(
            "cont_usage",
            $ilCtrl->getLinkTarget($this, "showMediaPoolPageUsages"),
            array("showMediaPoolPageUsages", "showAllMediaPoolPageUsages"),
            get_class($this)
        );
        $ilTabs->addTarget(
            "settings",
            $ilCtrl->getLinkTarget($this, "editMediaPoolPage"),
            "editMediaPoolPage",
            get_class($this)
        );
        $ilCtrl->setParameter($this, "mepitem_id", $this->pool->getPoolTree()->getParentId($_GET["mepitem_id"]));
        $ilTabs->setBackTarget($lng->txt("mep_folder"), $ilCtrl->getParentReturn($this));
        $ilCtrl->setParameter($this, "mepitem_id", $_GET["mepitem_id"]);
    }

    /**
     * List usages of the contnet snippet
     */
    public function showAllMediaPoolPageUsages()
    {
        $this->showMediaPoolPageUsages(true);
    }


    /**
     * List usages of the contnet snippet
     */
    public function showMediaPoolPageUsages($a_all = false)
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $tpl = $this->tpl;

        $ilTabs->clearTargets();

        $ilTabs->addSubTab(
            "current_usages",
            $lng->txt("cont_current_usages"),
            $ilCtrl->getLinkTarget($this, "showMediaPoolPageUsages")
        );

        $ilTabs->addSubTab(
            "all_usages",
            $lng->txt("cont_all_usages"),
            $ilCtrl->getLinkTarget($this, "showAllMediaPoolPageUsages")
        );

        if ($a_all) {
            $ilTabs->activateSubTab("all_usages");
            $cmd = "showAllMediaPoolPageUsages";
        } else {
            $ilTabs->activateSubTab("current_usages");
            $cmd = "showMediaPoolPageUsages";
        }


        //$mep_page_gui = $this->getMediaPoolPageGUI((int) $_GET["mepitem_id"], $_GET["old_nr"]);
        $this->getTabs();

        include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
        $page = new ilMediaPoolPage((int) $_GET["mepitem_id"]);

        include_once("./Modules/MediaPool/classes/class.ilMediaPoolPageUsagesTableGUI.php");
        $table = new ilMediaPoolPageUsagesTableGUI($this, $cmd, $page, $a_all);

        $tpl->setContent($table->getHTML());
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
