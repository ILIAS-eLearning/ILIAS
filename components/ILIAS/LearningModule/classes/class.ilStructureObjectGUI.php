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

use ILIAS\UI\Component\Input\Container\Form;
use ILIAS\LearningModule\Editing\EditSubObjectsGUI;

/**
 * @ilCtrl_Calls ilStructureObjectGUI: ilConditionHandlerGUI, ilObjectMetaDataGUI
 * @ilCtrl_Calls ilStructureObjectGUI: ILIAS\LearningModule\Editing\EditSubObjectsGUI
 */
class ilStructureObjectGUI extends ilLMObjectGUI
{
    protected \ILIAS\LearningModule\InternalDomainService $domain;
    protected \ILIAS\LearningModule\InternalGUIService $gui;
    protected ilPropertyFormGUI $form;
    protected ilConditionHandlerGUI $condHI;
    protected ilObjUser $user;
    protected ilTabsGUI $tabs;
    protected ilLogger $log;
    public ilLMTree $tree;

    public function __construct(
        ilObjLearningModule $a_content_obj,
        ilLMTree $a_tree
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->log = $DIC["ilLog"];
        $this->tpl = $DIC->ui()->mainTemplate();
        parent::__construct($a_content_obj);
        $this->tree = $a_tree;
        $this->gui = $DIC->learningModule()->internal()->gui();
        $this->domain = $DIC->learningModule()->internal()->domain();
    }

    public function setStructureObject(
        ilStructureObject $a_st_object
    ): void {
        $this->obj = $a_st_object;
    }

    public function getType(): string
    {
        return "st";
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case 'ilobjectmetadatagui':

                $this->setTabs();

                $md_gui = new ilObjectMetaDataGUI($this->content_object, $this->obj->getType(), $this->obj->getId());
                $md_gui->addMDObserver($this->obj, 'MDUpdateListener', 'General');
                $md_gui->addMDObserver($this->obj, 'MDUpdateListener', 'Educational'); // #9510
                $this->ctrl->forwardCommand($md_gui);
                break;

            case "ilconditionhandlergui":
                $ilTabs = $this->tabs;

                $this->setTabs();
                $this->initConditionHandlerInterface();
                $this->ctrl->forwardCommand($this->condHI);
                $ilTabs->setTabActive('preconditions');
                break;

            case strtolower(EditSubObjectsGUI::class):
                $this->setTabs();
                $this->tabs->activateTab("sub_pages");
                if ($this->request->getSubType() === "pg") {
                    $this->addSubTabs("sub_pages");
                    $table_title = $this->lng->txt("cont_pages");
                } else {
                    $this->addSubTabs("sub_chapters");
                    $table_title = $this->lng->txt("cont_subchapters");
                }
                $gui = $this->gui->editing()->editSubObjectsGUI(
                    $this->request->getSubType(),
                    $this->content_object,
                    $table_title
                );
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if ($cmd == 'listConditions') {
                    $this->setTabs();
                    $this->initConditionHandlerInterface();
                    $this->condHI->executeCommand();
                } elseif (($cmd == "create") && ($this->requested_new_type == "pg")) {
                    $this->setTabs();
                    $pg_gui = new ilLMPageObjectGUI($this->content_object);
                    $pg_gui->executeCommand();
                } else {
                    $this->$cmd();
                }
                break;
        }
    }

    public function create(): void
    {
        if ($this->requested_obj_id != 0) {
            $this->setTabs();
        }
        parent::create();
    }

    public function edit(): void
    {
        $this->view();
    }

    public function view(): void
    {
        $this->ctrl->redirectByClass(EditSubObjectsGUI::class, "editPages");
    }


    public function initConditionHandlerInterface(): void
    {
        $this->condHI = new ilConditionHandlerGUI();
        $this->condHI->setBackButtons(array());
        $this->condHI->setAutomaticValidation(false);
        $this->condHI->setTargetType("st");
        $this->condHI->setTargetRefId($this->content_object->getRefId());
        $this->condHI->setTargetId($this->obj->getId());
        $this->condHI->setTargetTitle($this->obj->getTitle());
    }


    /**
     * cancel creation of new page or chapter
     */
    public function cancel(): void
    {
        if ($this->requested_obj_id != 0) {
            if ($this->requested_new_type == "pg") {
                $this->ctrl->redirect($this, "view");
            } else {
                $this->ctrl->redirect($this, "subchap");
            }
        }
    }

    public function setTabs(): void
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;

        // subelements
        $this->ctrl->setParameterByClass(static::class, "sub_type", "pg");
        $ilTabs->addTab(
            "sub_pages",
            $lng->txt("cont_content"),
            $this->ctrl->getLinkTargetByClass(EditSubObjectsGUI::class)
        );

        // preconditions
        $ilTabs->addTab(
            "preconditions",
            $lng->txt("preconditions"),
            $this->ctrl->getLinkTarget($this, 'listConditions')
        );

        // metadata
        $mdgui = new ilObjectMetaDataGUI($this->content_object, $this->obj->getType(), $this->obj->getId());
        $mdtab = $mdgui->getTab();
        if ($mdtab) {
            $ilTabs->addTab(
                "meta_data",
                $lng->txt("meta_data"),
                $mdtab
            );
        }

        $this->tpl->setTitleIcon(ilUtil::getImagePath("standard/icon_st.svg"));
        $this->tpl->setTitle(
            $this->lng->txt($this->obj->getType()) . ": " . $this->obj->getTitle()
        );

        // presentation view
        $ilTabs->addNonTabbedLink(
            "pres_mode",
            $lng->txt("cont_presentation_view"),
            ILIAS_HTTP_PATH . "/goto.php?target=st_" . $this->obj->getId()
        );
    }

    protected function addSubTabs($active = "")
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;

        // content -> pages
        $this->ctrl->setParameterByClass(static::class, "sub_type", "pg");
        $ilTabs->addSubTab(
            "sub_pages",
            $lng->txt("cont_pages"),
            $this->ctrl->getLinkTargetByClass(EditSubObjectsGUI::class)
        );

        // chapters
        $this->ctrl->setParameterByClass(static::class, "sub_type", "st");
        $ilTabs->addSubTab(
            "sub_chapters",
            $lng->txt("cont_subchapters"),
            $this->ctrl->getLinkTargetByClass(EditSubObjectsGUI::class)
        );
        $ilTabs->activateSubTab($active);
    }

    /**
     * @throws ilPermissionException
     */
    public static function _goto(
        string $a_target,
        int $a_target_ref_id = 0
    ): void {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $ctrl = $DIC->ctrl();

        // determine learning object
        $lm_id = ilLMObject::_lookupContObjID($a_target);

        // get all references
        $ref_ids = ilObject::_getAllReferences($lm_id);

        // always try passed ref id first
        if (in_array($a_target_ref_id, $ref_ids)) {
            $ref_ids = array_merge(array($a_target_ref_id), $ref_ids);
        }

        // check read permissions
        foreach ($ref_ids as $ref_id) {
            // Permission check
            if ($ilAccess->checkAccess("read", "", $ref_id)) {
                $ctrl->setParameterByClass("ilLMPresentationGUI", "obj_id", $a_target);
                $ctrl->setParameterByClass("ilLMPresentationGUI", "ref_id", $ref_id);
                $ctrl->redirectByClass("ilLMPresentationGUI", "");
            }
        }

        if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle($lm_id)
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }

        throw new ilPermissionException($lng->txt("msg_no_perm_read_lm"));
    }


    ////
    //// Pages layout
    ////

    /**
     * Set layout for multipl pages
     */
    public function setPageLayout(): void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ids = $this->request->getIds();
        if (count($ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "showHierarchy");
        }

        $this->initSetPageLayoutForm();

        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Init set page layout form.
     */
    public function initSetPageLayoutForm(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->form = new ilPropertyFormGUI();

        $ids = $this->request->getIds();
        foreach ($ids as $id) {
            $hi = new ilHiddenInputGUI("id[]");
            $hi->setValue($id);
            $this->form->addItem($hi);
        }
        $layout = ilObjContentObjectGUI::getLayoutOption(
            $lng->txt("cont_layout"),
            "layout",
            $this->content_object->getLayout()
        );

        $this->form->addItem($layout);

        $this->form->addCommandButton("savePageLayout", $lng->txt("save"));
        $this->form->addCommandButton("showHierarchy", $lng->txt("cancel"));

        $this->form->setTitle($lng->txt("cont_set_layout"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Save page layout
     */
    public function savePageLayout(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ids = $this->request->getIds();
        $layout = $this->request->getLayout();
        foreach ($ids as $id) {
            ilLMPageObject::writeLayout(
                $id,
                $layout,
                $this->content_object
            );
        }
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "showHierarchy");
    }

    public function editMasterLanguage(): void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, "transl", "-");
        $ilCtrl->redirect($this, "showHierarchy");
    }

    public function switchToLanguage(): void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, "transl", $this->requested_totransl);
        $ilCtrl->redirect($this, "showHierarchy");
    }
}
