<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\MediaPool;

/**
 * Class ilMediaPoolPage GUI class
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilMediaPoolPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilMediaPoolPageGUI: ilPublicUserProfileGUI, ilObjectMetaDataGUI
 */
class ilMediaPoolPageGUI extends ilPageObjectGUI
{
    protected \ILIAS\Style\Content\GUIService $cs_gui;
    protected MediaPool\StandardGUIRequest $mep_request;
    protected ilTabsGUI $tabs;
    protected ?ilObjMediaPoolGUI $pool_gui = null;
    protected ?ilObjMediaPool $pool = null;

    public function __construct(
        int $a_id = 0,
        int $a_old_nr = 0,
        bool $a_prevent_get_id = false,
        string $a_lang = ""
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->lng = $DIC->language();

        if (in_array($this->ctrl->getCmd(), ["createMediaPoolPage", "saveMediaPoolPage"])) {
            $a_id = 0;
        }

        parent::__construct("mep", $a_id, $a_old_nr, $a_prevent_get_id, $a_lang);

        $cs = $DIC->contentStyle()
            ->domain()
            ->styleForObjId($this->getPageObject()->getParentId());
        $this->setStyleId($cs->getEffectiveStyleId());
        $this->cs_gui = $DIC->contentStyle()->gui();

        $this->setEditPreview(true);
        $this->mep_request = $DIC->mediaPool()
            ->internal()
            ->gui()
            ->standardRequest();
    }

    public function setMediaPoolPage(
        ilMediaPoolPage $a_media_pool_page
    ): void {
        $this->setPageObject($a_media_pool_page);
    }

    public function getMediaPoolPage(): ilMediaPoolPage
    {
        /** @var ilMediaPoolPage $p */
        $p = $this->getPageObject();
        return $p;
    }

    public function setPoolGUI(ilObjMediaPoolGUI $pool_gui): void
    {
        $this->pool_gui = $pool_gui;
        /** @var ilObjMediaPool $pool */
        $pool = $pool_gui->getObject();
        $this->pool = $pool;

        $this->getMediaPoolPage()->setPool($this->pool);

        $this->activateMetaDataEditor(
            $this->pool,
            "mpg",
            $this->getId(),
            $this->getMediaPoolPage(),
            "MDUpdateListener"
        );
    }

    public function showPage(
        bool $a_no_title = false
    ): string {
        $tpl = $this->tpl;

        // get raw page content is used for including into other pages
        if (!$this->getRawPageContent()) {
            $this->cs_gui->addCss($tpl, $this->requested_ref_id);
        }

        $this->setTemplateOutput(false);
        if (!$a_no_title) {
            $this->setPresentationTitle(ilMediaPoolItem::lookupTitle($this->getMediaPoolPage()->getId()));
        }

        return parent::showPage();
    }

    public function getTabs(string $a_activate = ""): void
    {
        parent::getTabs($a_activate);
        $this->setMediaPoolPageTabs();
    }

    public function getRawContent(): string
    {
        $this->setRawPageContent(true);
        $this->setLinkXml("");
        return $this->showPage(true);
    }

    public function setTemplate(ilGlobalTemplateInterface $tpl): void
    {
        $this->tpl = $tpl;
    }

    public function createMediaPoolPage(): void
    {
        $tpl = $this->tpl;

        $form = $this->initMediaPoolPageForm("create");
        $tpl->setContent($form->getHTML());
        $this->tabs->clearTargets();
    }

    public function editMediaPoolPage(): void
    {
        $tpl = $this->tpl;
        $form = $this->initMediaPoolPageForm("edit");
        $this->getMediaPoolPageValues($form);
        $tpl->setContent($form->getHTML());
    }

    public function saveMediaPoolPage(): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = $this->initMediaPoolPageForm("create");
        if ($form->checkInput()) {
            // create media pool item
            $item = new ilMediaPoolItem();
            $item->setTitle($form->getInput("title"));
            $item->setType("pg");
            $item->create();

            if ($item->getId() > 0) {
                // put in tree
                $tree = $this->pool->getTree();
                $parent = $this->mep_request->getItemId() > 0
                    ? $this->mep_request->getItemId()
                    : $tree->getRootId();
                $this->pool->insertInTree($item->getId(), $parent);

                // create page
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

    public function updateMediaPoolPage(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        $form = $this->initMediaPoolPageForm("edit");
        if ($form->checkInput()) {
            $item = new ilMediaPoolItem($this->mep_request->getItemId());
            $item->setTitle($form->getInput("title"));
            $item->update();
            $this->getMediaPoolPage()->updateMetaData();
            $tpl->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editMediaPoolPage");
        }

        $form->setValuesByPost();
        $tpl->setContent($form->getHtml());
    }

    public function initMediaPoolPageForm(string $a_mode = "edit"): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();

        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(128);
        $ti->setRequired(true);
        $form->addItem($ti);

        // save and cancel commands
        if ($a_mode === "create") {
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

    protected function cancelSave(): void
    {
        $ctrl = $this->ctrl;
        $ctrl->returnToParent($this);
    }

    public function getMediaPoolPageValues(ilPropertyFormGUI $form): void
    {
        $values = array();

        $values["title"] = ilMediaPoolItem::lookupTitle($this->mep_request->getItemId());

        $form->setValuesByArray($values);
    }

    public function setMediaPoolPageTabs(): void
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
        $ilCtrl->setParameter($this, "mepitem_id", $this->pool->getPoolTree()->getParentId($this->mep_request->getItemId()));
        $ilTabs->setBackTarget($lng->txt("mep_folder"), $ilCtrl->getParentReturn($this));
        $ilCtrl->setParameter($this, "mepitem_id", $this->mep_request->getItemId());
    }

    public function showAllMediaPoolPageUsages(): void
    {
        $this->showMediaPoolPageUsages(true);
    }


    /**
     * List usages of the contnet snippet
     */
    public function showMediaPoolPageUsages(bool $a_all = false): void
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

        $this->getTabs();
        $page = new ilMediaPoolPage($this->mep_request->getItemId());
        $table = new ilMediaPoolPageUsagesTableGUI($this, $cmd, $page, $a_all);

        $tpl->setContent($table->getHTML());
    }
}
