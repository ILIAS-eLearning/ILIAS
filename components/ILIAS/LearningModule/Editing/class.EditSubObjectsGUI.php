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

declare(strict_types=1);

namespace ILIAS\LearningModule\Editing;

use ILIAS\LearningModule\InternalDomainService;
use ILIAS\LearningModule\InternalGUIService;
use ILIAS\Repository\Form\FormAdapterGUI;
use ilLMObject;
use ILIAS\LearningModule\Table\TableAdapterGUI;
use ILIAS\UI\Component\Input\Container\Form\Standard;

class EditSubObjectsGUI
{
    protected string $lang;
    protected array $page_layouts;
    protected EditingGUIRequest $request;
    protected int $lm_id;
    protected \ilLMTree $lm_tree;
    protected int $sub_obj_id;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected string $sub_type,
        protected \ilObjLearningModule $lm,
        protected string $table_title
    ) {
        $this->sub_obj_id = $this->gui->editing()->request()->getObjId();
        $this->gui->ctrl()->saveParameterByClass(self::class, "sub_type");
        $this->lm_id = $lm->getId();
        $this->lm_tree = $this->domain->lmTree($this->lm_id);
        $this->request = $this->gui->editing()->request();
        $this->page_layouts = \ilPageLayout::activeLayouts(
            \ilPageLayout::MODULE_LM
        );
        $this->lang = $this->request->getTranslation();
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();
        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("list");

        switch ($next_class) {
            default:
                if (in_array($cmd, [
                    "list", "tableCommand", "editPages",
                    "insertChapterAfter", "insertChapterBefore", "insertFirstChapter",
                    "insertPageAfter", "insertPageBefore", "insertFirstPage",
                    "editTitle", "saveTitle", "saveOrder",
                    "confirmedDelete", "delete", "cancelDelete",
                    "insertPageClip", "insertPageClipBefore", "insertPageClipAfter",
                    "insertChapterClip", "insertChapterClipBefore", "insertChapterClipAfter",
                    "activatePages",
                    "insertLayoutBefore", "insertLayoutAfter", "insertPageFromLayout",
                    "switchToLanguage", "editMasterLanguage"
                ])) {
                    $this->$cmd();
                }
        }
    }

    protected function editPages(): void
    {
        $this->gui->ctrl()->setParameterByClass(self::class, "sub_type", "pg");
        $this->gui->ctrl()->redirectByClass(static::class, "list");
    }

    protected function getTable(): TableAdapterGUI
    {
        return $this->gui->editing()->subObjectTableGUI(
            $this->table_title,
            $this->lm_id,
            $this->sub_type,
            $this
        );
    }

    public function tableCommand(): void
    {
        $this->getTable()->handleCommand();
    }

    public function switchToLanguage(): void
    {
        $ctrl = $this->gui->ctrl();
        $ctrl->setParameter($this, "transl", $this->request->getToTranslation());
        $ctrl->redirect($this, "list");
    }

    public function editMasterLanguage(): void
    {
        $ctrl = $this->gui->ctrl();
        $ctrl->setParameter($this, "transl", "-");
        $ctrl->redirect($this, "list");
    }


    protected function list(): void
    {
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $main_tpl = $this->gui->mainTemplate();
        $user = $this->domain->user();

        $retrieval = $this->domain->subObjectRetrieval(
            $this->lm_id,
            $this->sub_type,
            $this->sub_obj_id,
            $this->lang
        );

        $ml_head = \ilObjLearningModuleGUI::getMultiLangHeader($this->lm_id, $this);

        if ($retrieval->count() === 0) {
            if ($this->sub_type === "st") {
                $this->gui->button(
                    $lng->txt("lm_insert_chapter"),
                    $ctrl->getLinkTargetByClass(self::class, "insertFirstChapter")
                )->toToolbar();
                if ($user->clipboardHasObjectsOfType("st")) {
                    $this->gui->button(
                        $lng->txt("lm_insert_chapter_clip"),
                        $ctrl->getLinkTargetByClass(self::class, "insertChapterClip")
                    )->toToolbar();
                }
            } else {
                $this->gui->button(
                    $lng->txt("lm_insert_page"),
                    $ctrl->getLinkTargetByClass(self::class, "insertFirstPage")
                )->toToolbar();
                if ($user->clipboardHasObjectsOfType("pg")) {
                    $this->gui->button(
                        $lng->txt("lm_insert_page_clip"),
                        $ctrl->getLinkTargetByClass(self::class, "insertPageClip")
                    )->toToolbar();
                }
            }
        }
        $table = $this->getTable();

        $main_tpl->setContent($ml_head . $table->render());
        $main_tpl->addOnloadCode("window.setTimeout(() => { il.repository.core.trigger('il-lm-editor-tree'); }, 500);");
    }

    public function insertChapterClipBefore(): void
    {
        $parent = $this->sub_obj_id;
        $target_id = $this->request->getTargetId();
        $before_target = \ilTree::POS_FIRST_NODE;
        foreach ($this->lm_tree->getChilds($parent) as $node) {
            if ((int) $node["obj_id"] !== $target_id) {
                $before_target = (int) $node["obj_id"];
            } else {
                break;
            }
        }
        $this->insertChapterClip(
            $before_target
        );
    }

    public function insertChapterClipAfter(): void
    {
        $this->insertChapterClip(
            $this->request->getTargetId()
        );
    }

    public function insertChapterClip(
        $target = \ilTree::POS_LAST_NODE
    ): void {
        $user = $this->domain->user();
        $ctrl = $this->gui->ctrl();
        $parent_id = $this->request->getObjId();

        // copy and paste
        $chapters = $user->getClipboardObjects("st", true);
        $copied_nodes = array();

        foreach ($chapters as $chap) {
            $cid = ilLMObject::pasteTree(
                $this->lm,
                $chap["id"],
                $parent_id,
                (int) $target,
                (string) ($chap["insert_time"] ?? ""),
                $copied_nodes,
                (\ilEditClipboard::getAction() == "copy")
            );
            $target = $cid;
        }
        ilLMObject::updateInternalLinks($copied_nodes);

        if (\ilEditClipboard::getAction() == "cut") {
            $user->clipboardDeleteObjectsOfType("pg");
            $user->clipboardDeleteObjectsOfType("st");
            \ilEditClipboard::clear();
        }

        $this->lm->checkTree();
        $ctrl->redirect($this, "list");
    }

    public function insertPageClipBefore(): void
    {
        $parent = $this->sub_obj_id;
        $target_id = $this->request->getTargetId();
        $before_target = \ilTree::POS_FIRST_NODE;
        foreach ($this->lm_tree->getChildsByType($parent, "pg") as $node) {
            if ((int) $node["obj_id"] !== $target_id) {
                $before_target = (int) $node["obj_id"];
            } else {
                break;
            }
        }
        $this->insertPageClip(
            $before_target
        );
    }

    public function insertPageClipAfter(): void
    {
        $this->insertPageClip(
            $this->request->getTargetId()
        );
    }

    public function insertPageClip(
        int $target = 0
    ): void {
        $user = $this->domain->user();
        $ctrl = $this->gui->ctrl();

        $parent_id = $this->request->getObjId();

        // cut and paste
        $pages = $user->getClipboardObjects("pg");
        $copied_nodes = array();
        foreach ($pages as $pg) {
            $cid = ilLMObject::pasteTree(
                $this->lm,
                $pg["id"],
                $parent_id,
                $target,
                (string) ($pg["insert_time"] ?? ""),
                $copied_nodes,
                (\ilEditClipboard::getAction() == "copy")
            );
            $target = $cid;
        }
        \ilLMObject::updateInternalLinks($copied_nodes);

        if (\ilEditClipboard::getAction() == "cut") {
            $user->clipboardDeleteObjectsOfType("pg");
            $user->clipboardDeleteObjectsOfType("st");
            \ilEditClipboard::clear();
        }

        $ctrl->redirect($this, "list");
    }

    public function insertFirstPage(): void
    {
        $this->insertPage(
            $this->sub_obj_id
        );
    }
    public function insertPageAfter(): void
    {
        $target_id = $this->request->getTargetId();
        $this->insertPage(
            $this->sub_obj_id,
            $target_id
        );
    }

    public function insertPageBefore(): void
    {
        $parent = $this->sub_obj_id;
        $target_id = $this->request->getTargetId();
        $before_target = \ilTree::POS_FIRST_NODE;
        foreach ($this->lm_tree->getChildsByType($parent, "pg") as $node) {
            if ((int) $node["obj_id"] !== $target_id) {
                $before_target = (int) $node["obj_id"];
            } else {
                break;
            }
        }
        $this->insertPage(
            $parent,
            $before_target
        );
    }

    protected function insertPage(
        int $parent_id = 0,
        int $target = \ilTree::POS_LAST_NODE
    ): void {
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();

        $chap = new \ilLMPageObject($this->lm);
        $chap->setType("pg");
        $chap->setTitle($lng->txt("cont_new_page"));
        $chap->setLMId($this->lm_id);
        $chap->create();
        \ilLMObject::putInTree($chap, $parent_id, $target);

        /*
        if ($parent_id === $this->lm_tree->readRootId()) {
            $ctrl->setParameterByClass(static::class, "obj_id", 0);
        } else {
            $ctrl->setParameterByClass(static::class, "obj_id", $parent_id);
        }*/

        $ctrl->redirect($this, "list");
    }

    public function insertFirstChapter(): void
    {
        $this->insertChapter(
            $this->sub_obj_id
        );
    }
    public function insertChapterAfter(): void
    {
        $target_id = $this->request->getTargetId();
        $this->insertChapter(
            $this->sub_obj_id,
            $target_id
        );
    }

    public function insertChapterBefore(): void
    {
        $parent = $this->sub_obj_id;
        if ($parent === 0) {
            $parent = $this->lm_tree->getRootId();
        }
        $target_id = $this->request->getTargetId();
        $before_target = \ilTree::POS_FIRST_NODE;
        foreach ($this->lm_tree->getChilds($parent) as $node) {
            if ((int) $node["obj_id"] !== $target_id) {
                $before_target = (int) $node["obj_id"];
            } else {
                break;
            }
        }
        $this->insertChapter(
            $parent,
            $before_target
        );
    }

    protected function insertChapter(
        int $parent_id = 0,
        int $target = \ilTree::POS_LAST_NODE
    ): void {
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $chap = new \ilStructureObject($this->lm);
        $chap->setType("st");
        $chap->setTitle($lng->txt("cont_new_chap"));
        $chap->setLMId($this->lm_id);
        $chap->create();
        \ilLMObject::putInTree($chap, $parent_id, $target);

        /*
        if ($parent_id === $this->lm_tree->readRootId()) {
            $ctrl->setParameterByClass(static::class, "obj_id", 0);
        } else {
            $ctrl->setParameterByClass(static::class, "obj_id", $parent_id);
        }*/

        $ctrl->redirect($this, "list");
    }

    protected function getEditTitleForm(int $id): FormAdapterGUI
    {
        $lng = $this->domain->lng();
        $this->gui->ctrl()->setParameterByClass(self::class, "edit_id", $id);
        $ot = \ilObjectTranslation::getInstance($this->lm->getId());
        $ml = "";
        if ($ot->getContentActivated()) {
            $ml = " (" . $lng->txt("meta_l_" . $ot->getMasterLanguage()) . ")";
        }

        $form = $this
            ->gui
            ->form(self::class, "saveTitle")
            ->text("title", $lng->txt('title') . $ml, "", ilLMObject::_lookupTitle($id));
        if ($ot->getContentActivated()) {
            foreach ($ot->getLanguages() as $lang) {
                $code = $lang->getLanguageCode();
                if ($code === $ot->getMasterLanguage()) {
                    continue;
                }
                $lmobjtrans = new \ilLMObjTranslation($id, $code);
                $title = $lmobjtrans->getTitle();
                $form = $form->text(
                    "title_" . $code,
                    $lng->txt('title') . " (" . $lng->txt("meta_l_" . $code) . ")",
                    "",
                    $title
                );
            }
        }
        return $form;
    }

    public function editTitle(int $id): void
    {
        $modal = $this->gui->modal()->form($this->getEditTitleForm($id));
        $modal->send();
    }

    public function saveTitle(): void
    {
        $mt = $this->gui->mainTemplate();
        $lng = $this->domain->lng();
        $form = $this->getEditTitleForm($this->request->getEditId());
        if ($form->isValid()) {
            \ilLMObject::saveTitle($this->request->getEditId(), $form->getData("title"));

            $ot = \ilObjectTranslation::getInstance($this->lm->getId());
            if ($ot->getContentActivated()) {
                foreach ($ot->getLanguages() as $lang) {
                    $code = $lang->getLanguageCode();
                    if ($code === $ot->getMasterLanguage()) {
                        continue;
                    }
                    \ilLMObject::saveTitle($this->request->getEditId(), $form->getData("title_" . $code), $code);
                }
            }
        }
        $mt->setContent("success", $lng->txt("msg_obj_modified"), true);
        $this->gui->ctrl()->redirect($this, "list");
    }

    public function saveOrder(): void
    {
        $mt = $this->gui->mainTemplate();
        $lng = $this->domain->lng();
        $tree = $this->domain->lmTree($this->lm_id);
        $table = $this->getTable();
        $data = $table->getData();
        $parent = ($this->sub_obj_id > 0)
            ? $this->sub_obj_id
            : $tree->readRootId();
        if (!is_array($data)) {
            return;
        }

        // note: moveTree has a bug and does not use the last parameter
        // target will always be "last node"
        // since all chapters must follow all pages
        // we can simple call moveTree in the correct order for the chapters
        // but if we order the pages, we must append all chapters to the data first
        if ($this->sub_type === "pg") {
            foreach ($tree->getChilds($parent) as $child) {
                if ($child["type"] == "st") {
                    $data[] = $child["child"];
                }
            }
        }
        foreach ($data as $id) {
            $tree->moveTree((int) $id, $parent);
        }
        $mt->setContent("success", $lng->txt("msg_obj_modified"), true);
        $this->gui->ctrl()->redirect($this, "list");
    }

    /**
     * confirm deletion screen for page object and structure object deletion
     * @param int $a_parent_subobj_id id of parent object (structure object)
     *								  of the objects, that should be deleted
     *								  (or no parent object id for top level)
     */
    public function delete(array $ids): void
    {
        $a_parent_subobj_id = $this->sub_obj_id;
        $mt = $this->gui->mainTemplate();
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();

        if (count($ids) == 0) {
            $mt->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $this->cancelDelete();
        }

        if (count($ids) == 1 && $ids[0] == \ilTree::POS_FIRST_NODE) {
            $mt->setOnScreenMessage('failure', $lng->txt("cont_select_item"), true);
            $this->cancelDelete();
        }

        $form_action = $ctrl->getFormActionByClass(self::class);

        // display confirmation message
        $cgui = new \ilConfirmationGUI();
        $cgui->setFormAction($form_action);
        $cgui->setHeaderText($lng->txt("info_delete_sure"));
        $cgui->setCancel($lng->txt("cancel"), "cancelDelete");
        $cgui->setConfirm($lng->txt("confirm"), "confirmedDelete");

        foreach ($ids as $id) {
            if ($id != \ilTree::POS_FIRST_NODE) {
                $obj = new \ilLMObject($this->lm, $id);
                $caption = $obj->getTitle();

                $cgui->addItem("id[]", (string) $id, $caption);
            }
        }

        $mt->setContent($cgui->getHTML());
    }

    public function cancelDelete(): void
    {
        $this->gui->ctrl()->redirect($this, "list");
    }

    /**
     * delete page object or structure objects
     *
     * @param	int		$a_parent_subobj_id		id of parent object (structure object)
     *											of the objects, that should be deleted
     *											(or no parent object id for top level)
     */
    public function confirmedDelete(int $a_parent_subobj_id = 0): void
    {
        $tree = $this->domain->lmTree($this->lm_id);
        $ids = $this->request->getIds();
        $mt = $this->gui->mainTemplate();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();

        // check number of objects
        if (count($ids) == 0) {
            $mt->setOnScreenMessage('failure', $lng->txt("no_checkbox"));
            $ctrl->redirect($this, "list");
        }

        // delete all selected objects
        foreach ($ids as $id) {
            if ($id != \ilTree::POS_FIRST_NODE) {
                $obj = \ilLMObjectFactory::getInstance($this->lm, $id, false);
                $node_data = $tree->getNodeData($id);
                if (is_object($obj)) {
                    $obj->setLMId($this->lm->getId());
                    $obj->delete();
                }
                if ($tree->isInTree($id)) {
                    $tree->deleteTree($node_data);
                }
            }
        }

        // check the tree
        $this->lm->checkTree();

        // feedback
        $mt->setOnScreenMessage('success', $lng->txt("info_deleted"), true);
        $ctrl->redirect($this, "list");
    }

    /**
     * Copy items to clipboard, then cut them from the current tree
     */
    public function cutItems(array $ids): void
    {
        $ctrl = $this->gui->ctrl();
        $mt = $this->gui->mainTemplate();
        $lng = $this->domain->lng();

        $items = $ids;
        if (count($items) == 0) {
            $mt->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $ctrl->redirect($this, "list");
        }

        $todel = array();			// delete IDs < 0 (needed for non-js editing)
        foreach ($items as $k => $item) {
            if ($item < 0) {
                $todel[] = $k;
            }
        }
        foreach ($todel as $k) {
            unset($items[$k]);
        }

        \ilLMObject::clipboardCut($this->lm_id, $items);
        \ilEditClipboard::setAction("cut");
        $mt->setOnScreenMessage('info', $lng->txt("cont_selected_items_have_been_cut"), true);

        $ctrl->redirect($this, "list");
    }

    /**
     * Copy items to clipboard
     */
    public function copyItems($ids): void
    {

        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        $mt = $this->gui->mainTemplate();

        $items = $ids;
        if (count($items) == 0) {
            $mt->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $ctrl->redirect($this, "list");
        }

        $todel = array();				// delete IDs < 0 (needed for non-js editing)
        foreach ($items as $k => $item) {
            if ($item < 0) {
                $todel[] = $k;
            }
        }
        foreach ($todel as $k) {
            unset($items[$k]);
        }

        \ilLMObject::clipboardCopy($this->lm_id, $items);
        \ilEditClipboard::setAction("copy");

        $mt->setOnScreenMessage('info', $lng->txt("cont_selected_items_have_been_copied"), true);
        $ctrl->redirect($this, "list");
    }

    public function activatePages(array $ids): void
    {
        $ctrl = $this->gui->ctrl();
        $mt = $this->gui->mainTemplate();
        $lng = $this->domain->lng();
        $lm_tree = $this->domain->lmTree($this->lm_id);

        $ids = $ids;
        if (count($ids) > 0) {
            $act_items = array();
            // get all "top" ids, i.e. remove ids, that have a selected parent
            foreach ($ids as $id) {
                $path = $lm_tree->getPathId($id);
                $take = true;
                foreach ($path as $path_id) {
                    if ($path_id != $id && in_array($path_id, $ids)) {
                        $take = false;
                    }
                }
                if ($take) {
                    $act_items[] = $id;
                }
            }


            foreach ($act_items as $id) {
                $childs = $lm_tree->getChilds($id);
                foreach ($childs as $child) {
                    if (ilLMObject::_lookupType($child["child"]) == "pg") {
                        $act = \ilLMPage::_lookupActive(
                            $child["child"],
                            $this->lm->getType()
                        );
                        \ilLMPage::_writeActive(
                            $child["child"],
                            $this->lm->getType(),
                            !$act
                        );
                    }
                }
                if (ilLMObject::_lookupType($id) == "pg") {
                    $act = \ilLMPage::_lookupActive(
                        $id,
                        $this->lm->getType()
                    );
                    \ilLMPage::_writeActive(
                        $id,
                        $this->lm->getType(),
                        !$act
                    );
                }
            }
        } else {
            $mt->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
        }

        $ctrl->redirect($this, "list");
    }

    public function insertLayoutBefore(): void
    {
        $this->insertLayout(true);
    }

    public function insertLayoutAfter(): void
    {
        $this->insertLayout();
    }

    public function insertLayout(bool $before = false): void
    {
        $ctrl = $this->gui->ctrl();
        $ui = $this->gui->ui();
        $mt = $this->gui->mainTemplate();
        if ($before) {
            $ctrl->setParameterByClass(self::class, "before", "1");
        }
        $ctrl->saveParameterByClass(self::class, ["obj_id", "target_id"]);
        $form = $this->initInsertTemplateForm();
        $mt->setContent($ui->renderer()->render($form) . \ilLMPageObjectGUI::getLayoutCssFix());
    }

    public function initInsertTemplateForm(): Standard
    {
        $ui = $this->gui->ui();
        $f = $ui->factory();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();

        $fields["title"] = $f->input()->field()->text($lng->txt("title"), "");
        $ts = \ilPageLayoutGUI::getTemplateSelection((string) \ilPageLayout::MODULE_LM);
        if (!is_null($ts)) {
            $fields["layout_id"] = $ts;
        }

        // section
        $section1 = $f->input()->field()->section($fields, $lng->txt("cont_insert_pagelayout"));

        $form_action = $ctrl->getLinkTarget($this, "insertPageFromLayout");
        return $f->input()->container()->form()->standard($form_action, ["sec" => $section1]);
    }

    public function insertPageFromLayout(): void
    {
        global $DIC;

        $ctrl = $this->gui->ctrl();
        $mt = $this->gui->mainTemplate();
        $lng = $this->domain->lng();


        $parent = $this->sub_obj_id;
        $target_id = $this->request->getTargetId();

        $first_child = false;
        if ($this->request->getBefore()) {
            $before_target = \ilTree::POS_FIRST_NODE;
            $first_child = true;
            foreach ($this->lm_tree->getChildsByType($parent, "pg") as $node) {
                if ((int) $node["obj_id"] !== $target_id) {
                    $before_target = (int) $node["obj_id"];
                    $first_child = false;
                } else {
                    break;
                }
            }
            $target_id = $before_target;
        }

        $form = $this->initInsertTemplateForm();
        $form = $form->withRequest($DIC->http()->request());
        $data = $form->getData();
        $layout_id = $data["sec"]["layout_id"];
        $page_ids = \ilLMPageObject::insertPagesFromTemplate(
            $this->lm->getId(),
            1,
            $target_id,
            $first_child,
            (int) $layout_id,
            $data["sec"]["title"]
        );

        $mt->setOnScreenMessage("success", $lng->txt("lm_page_added"), true);

        $ctrl->redirect($this, "list");
    }
}
