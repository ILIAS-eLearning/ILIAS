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

use ILIAS\Repository\Trash\TrashGUIRequest;

/**
 * Repository GUI Utilities
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilRepositoryTrashGUI: ilPropertyFormGUI
 */
class ilRepositoryTrashGUI
{
    protected ilLanguage $lng;
    protected ilSetting $settings;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjectDefinition $obj_definition;
    protected ilAccessHandler $access;
    protected ilTree $tree;
    protected ?ilLogger $logger = null;
    protected TrashGUIRequest $request;
    protected object $parent_gui;
    protected string $parent_cmd;

    public function __construct(
        ilObjectGUI $a_parent_gui,
        string $a_parent_cmd = ""
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->obj_definition = $DIC["objDefinition"];
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->parent_gui = $a_parent_gui;
        $this->parent_cmd = $a_parent_cmd;

        $this->logger = $DIC->logger()->rep();
        $this->request = $DIC->repository()
            ->internal()
            ->gui()
            ->trash()
            ->request();
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            case "ilpropertyformgui":
                $form = $this->initFormTrashTargetLocation();
                $this->ctrl->forwardCommand($form);
                break;

            default:
                $cmd = $this->ctrl->getCmd('cancel');
                $this->$cmd();
                break;
        }
    }

    protected function cancel(): void
    {
        $this->ctrl->returnToParent($this);
    }

    public function restoreToNewLocation(
        ilPropertyFormGUI $form = null
    ): void {
        $this->lng->loadLanguageModule('rep');

        $trash_ids = $this->request->getTrashIds();

        $this->ctrl->setParameter($this, 'trash_ids', implode(',', $trash_ids));

        if (!count($trash_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->returnToParent($this);
        }

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormTrashTargetLocation();
        }
        $this->tpl->setOnScreenMessage('info', $this->lng->txt('rep_target_location_info'));
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    public function doRestoreToNewLocation(): void
    {
        $trash_ids = $this->request->getTrashIds();

        $form = $this->initFormTrashTargetLocation();
        if (!$form->checkInput() && count($trash_ids)) {
            $this->lng->loadLanguageModule('search');
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('search_no_selection'), true);
            $this->ctrl->returnToParent($this);
        }

        try {
            ilRepUtil::restoreObjects($form->getInput('target_id'), $trash_ids);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_undeleted'), true);
            $this->ctrl->returnToParent($this);
        } catch (ilRepositoryException $e) {
            $this->tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            $this->ctrl->returnToParent($this);
        }
    }

    protected function initFormTrashTargetLocation(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $target = new ilRepositorySelector2InputGUI(
            $this->lng->txt('rep_target_location'),
            'target_id',
            false
        );
        $target->setRequired(true);

        $explorer = new ilRepositorySelectorExplorerGUI(
            [
                ilAdministrationGUI::class,
                get_class($this->parent_gui),
                self::class,
                ilPropertyFormGUI::class,
                ilFormPropertyDispatchGUI::class,
                ilRepositorySelector2InputGUI::class
            ],
            'handleExplorerCommand',
            $target,
            'root_id',
            'rep_exp_sel_' . $target->getPostVar()
        );
        $explorer->setSelectMode($target->getPostVar() . "_sel", false);
        $explorer->setRootId(ROOT_FOLDER_ID);
        $explorer->setTypeWhiteList(['root','cat','crs','grp','fold']);
        $target->setExplorerGUI($explorer);

        $form->addItem($target);
        $form->addCommandButton('doRestoreToNewLocation', $this->lng->txt('btn_undelete'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        return $form;
    }

    public function showDeleteConfirmation(
        ?array $a_ids,
        bool $a_supress_message = false
    ): bool {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $objDefinition = $this->obj_definition;

        if (!is_array($a_ids) || count($a_ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            return false;
        }

        // Remove duplicate entries
        $a_ids = array_unique($a_ids);

        $cgui = new ilConfirmationGUI();

        if (!$a_supress_message) {
            $msg = $lng->txt("info_delete_sure");

            if (!$ilSetting->get('enable_trash')) {
                $msg .= "<br/>" . $lng->txt("info_delete_warning_no_trash");
            }

            $cgui->setHeaderText($msg);
        }
        $cgui->setFormAction($ilCtrl->getFormAction($this->parent_gui));
        $cgui->setCancel($lng->txt("cancel"), "cancelDelete");
        $cgui->setConfirm($lng->txt("confirm"), "confirmedDelete");

        $form_name = "cgui_" . md5(uniqid('', true));
        $cgui->setFormName($form_name);

        $deps = [];
        foreach ($a_ids as $ref_id) {
            $obj_id = ilObject::_lookupObjId($ref_id);
            $type = ilObject::_lookupType($obj_id);
            $title = call_user_func([ilObjectFactory::getClassByType($type), '_lookupTitle'], $obj_id);
            $alt = ($objDefinition->isPlugin($type))
                ? $lng->txt("icon") . " " . ilObjectPlugin::lookupTxtById($type, "obj_" . $type)
                : $lng->txt("icon") . " " . $lng->txt("obj_" . $type);

            $title .= $this->handleMultiReferences($obj_id, $ref_id, $form_name);

            $cgui->addItem(
                "id[]",
                $ref_id,
                $title,
                ilObject::_getIcon($obj_id, "small", $type),
                $alt
            );

            ilObject::collectDeletionDependencies($deps, $ref_id, $obj_id, $type);
        }
        $deps_html = "";

        if (is_array($deps) && count($deps) > 0) {
            $tab = new ilRepDependenciesTableGUI($deps);
            $deps_html = "<br/><br/>" . $tab->getHTML();
        }

        $tpl->setContent($cgui->getHTML() . $deps_html);
        return true;
    }

    // Build sub-item list for multiple references
    public function handleMultiReferences(
        int $a_obj_id,
        int $a_ref_id,
        string $a_form_name
    ): string {
        $lng = $this->lng;
        $ilAccess = $this->access;
        $tree = $this->tree;

        // process

        $all_refs = ilObject::_getAllReferences($a_obj_id);
        if (count($all_refs) > 1) {
            $lng->loadLanguageModule("rep");

            $may_delete_any = 0;
            $counter = 0;
            $items = [];
            foreach ($all_refs as $mref_id) {
                // not the already selected reference, no refs from trash
                if ($mref_id != $a_ref_id && !$tree->isDeleted($mref_id)) {
                    if ($ilAccess->checkAccess("read", "", $mref_id)) {
                        $may_delete = false;
                        if ($ilAccess->checkAccess("delete", "", $mref_id)) {
                            $may_delete = true;
                            $may_delete_any++;
                        }

                        $path = $this->buildPath([$mref_id]);
                        $items[] = [
                            "id" => $mref_id,
                            "path" => array_shift($path),
                            "delete" => $may_delete
                        ];
                    } else {
                        $counter++;
                    }
                }
            }


            // render

            $tpl = new ilTemplate("tpl.rep_multi_ref.html", true, true, "Services/Repository/Trash");

            $tpl->setVariable("TXT_INTRO", $lng->txt("rep_multiple_reference_deletion_intro"));

            if ($may_delete_any) {
                $tpl->setVariable("TXT_INSTRUCTION", $lng->txt("rep_multiple_reference_deletion_instruction"));
            }

            if ($items) {
                $var_name = "mref_id[]";

                foreach ($items as $item) {
                    if ($item["delete"]) {
                        $tpl->setCurrentBlock("cbox");
                        $tpl->setVariable("ITEM_NAME", $var_name);
                        $tpl->setVariable("ITEM_VALUE", $item["id"]);
                    } else {
                        $tpl->setCurrentBlock("item_info");
                        $tpl->setVariable("TXT_ITEM_INFO", $lng->txt("rep_no_permission_to_delete"));
                    }
                    $tpl->parseCurrentBlock();

                    $tpl->setCurrentBlock("item");
                    $tpl->setVariable("ITEM_TITLE", $item["path"]);
                    $tpl->parseCurrentBlock();
                }

                if ($may_delete_any > 1) {
                    $tpl->setCurrentBlock("cbox");
                    $tpl->setVariable("ITEM_NAME", "sall_" . $a_ref_id);
                    $tpl->setVariable("ITEM_VALUE", "");
                    $tpl->setVariable("ITEM_ADD", " onclick=\"il.Util.setChecked('" .
                        $a_form_name . "', '" . $var_name . "', document." . $a_form_name .
                        ".sall_" . $a_ref_id . ".checked)\"");
                    $tpl->parseCurrentBlock();

                    $tpl->setCurrentBlock("item");
                    $tpl->setVariable("ITEM_TITLE", $lng->txt("select_all"));
                    $tpl->parseCurrentBlock();
                }
            }

            if ($counter) {
                $tpl->setCurrentBlock("add_info");
                $tpl->setVariable(
                    "TXT_ADDITIONAL_INFO",
                    sprintf($lng->txt("rep_object_references_cannot_be_read"), $counter)
                );
                $tpl->parseCurrentBlock();
            }

            return $tpl->get();
        }
        return "";
    }

    public function showTrashTable(
        int $a_ref_id
    ): void {
        $tpl = $this->tpl;
        $tree = $this->tree;
        $lng = $this->lng;

        $objects = $tree->getSavedNodeData($a_ref_id);

        if (count($objects) === 0) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("msg_trash_empty"));
            return;
        }
        $ttab = new ilTrashTableGUI($this->parent_gui, "trash", $a_ref_id);
        $ttab->setData($objects);

        $tpl->setContent($ttab->getHTML());
    }

    /**
     * Restore objects from trash
     * @param int   $a_cur_ref_id
     * @param int[] $a_ref_ids array of ref ids to be restored
     * @return bool
     */
    public function restoreObjects(
        int $a_cur_ref_id,
        array $a_ref_ids
    ): bool {
        $lng = $this->lng;
        $lng->loadLanguageModule('rep');

        if (!is_array($a_ref_ids) || count($a_ref_ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            return false;
        }

        $tree_trash_queries = new ilTreeTrashQueries();
        if ($tree_trash_queries->isTrashedTrash($a_ref_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('rep_failure_trashed_trash'), true);
            return false;
        }
        try {

            // find parent foreach node
            $by_location = [];
            foreach ($a_ref_ids as $deleted_node_id) {
                $target_id = $tree_trash_queries->findRepositoryLocationForDeletedNode($deleted_node_id);
                if ($target_id) {
                    $by_location[$target_id][] = $deleted_node_id;
                }
            }
            foreach ($by_location as $target_id => $deleted_node_ids) {
                ilRepUtil::restoreObjects($target_id, $deleted_node_ids);
            }
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_undeleted"), true);
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            return false;
        }
        return true;
    }

    public function deleteObjects(
        int $a_cur_ref_id,
        array $a_ref_ids
    ): void {
        $ilSetting = $this->settings;
        $lng = $this->lng;

        if (!is_array($a_ref_ids) || count($a_ref_ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
        } else {
            try {
                ilRepUtil::deleteObjects($a_cur_ref_id, $a_ref_ids);
                if ($ilSetting->get('enable_trash')) {
                    $this->tpl->setOnScreenMessage('success', $lng->txt("info_deleted"), true);
                } else {
                    $this->tpl->setOnScreenMessage('success', $lng->txt("msg_removed"), true);
                }
            } catch (Exception $e) {
                //$this->tpl->setOnScreenMessage('failure', $e->getMessage(), true);
                // alex: I outcommented this, since it makes tracking down errors impossible
                // we need a call stack at least in the logs
                throw $e;
            }
        }
    }

    public function removeObjectsFromSystem(
        array $a_ref_ids,
        bool $a_from_recovery_folder = false
    ): bool {
        $lng = $this->lng;

        if (!is_array($a_ref_ids) || count($a_ref_ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            return false;
        }

        try {
            ilRepUtil::removeObjectsFromSystem($a_ref_ids, $a_from_recovery_folder);
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_removed"), true);
        } catch (Exception $e) {
            // alex: I outcommented this, since it makes tracking down errors impossible
            // we need a call stack at least in the logs
            //$this->tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            throw $e;
            return false;
        }

        return true;
    }

    /**
     * Build path with deep-link
     */
    protected function buildPath(array $ref_ids): array
    {
        $tree = $this->tree;

        if (!count($ref_ids)) {
            return [];
        }

        $result = [];
        foreach ($ref_ids as $ref_id) {
            $path = "";
            $path_full = $tree->getPathFull($ref_id);
            foreach ($path_full as $idx => $data) {
                if ($idx) {
                    $path .= " &raquo; ";
                }
                if ((int) $ref_id !== (int) $data['ref_id']) {
                    $path .= $data['title'];
                } else {
                    $path .= ('<a target="_top" href="' .
                              ilLink::_getLink($data['ref_id'], $data['type']) . '">' .
                              $data['title'] . '</a>');
                }
            }

            $result[] = $path;
        }
        return $result;
    }

    /**
     * Confirmation for trash
     *
     * @param array $a_ids ref_ids
     */
    public function confirmRemoveFromSystemObject(
        array $a_ids
    ): void {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $objDefinition = $this->obj_definition;
        $tpl = $this->tpl;

        if (!is_array($a_ids)) {
            $a_ids = [$a_ids];
        }

        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($ilCtrl->getFormAction($this->parent_gui));
        $cgui->setCancel($lng->txt("cancel"), "trash");
        $cgui->setConfirm($lng->txt("confirm"), "removeFromSystem");
        $cgui->setFormName("trash_confirmation");
        $cgui->setHeaderText($lng->txt("info_delete_sure"));

        foreach ($a_ids as $id) {
            $obj_id = ilObject::_lookupObjId($id);
            $type = ilObject::_lookupType($obj_id);
            $title = call_user_func([ilObjectFactory::getClassByType($type), '_lookupTitle'], $obj_id);
            $alt = ($objDefinition->isPlugin($type))
                ? $lng->txt("icon") . " " . ilObjectPlugin::lookupTxtById($type, "obj_" . $type)
                : $lng->txt("icon") . " " . $lng->txt("obj_" . $type);

            $cgui->addItem(
                "trash_id[]",
                $id,
                $title,
                ilObject::_getIcon($obj_id, "small", $type),
                $alt
            );
        }

        $tpl->setContent($cgui->getHTML());
    }
}
