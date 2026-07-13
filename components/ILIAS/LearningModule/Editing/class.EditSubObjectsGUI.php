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
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\ILIASObject\Properties\Translations\CachedRepository as TranslationsRepository;
use ILIAS\Repository\Table\TableAdapterGUI;

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
        $this->gui->initFetch();
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
                    "switchToLanguage", "editMasterLanguage",
                    "savePageAfter", "savePageBefore",
                    "saveChapterAfter", "saveChapterBefore",
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
        return $this->gui->editing()->subObjectTableBuilder(
            $this->table_title,
            $this->lm_id,
            $this->sub_type,
            $this,
            "list"
        )->getTable();
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

        if ($this->getTable()->handleCommand()) {
            return;
        }

        $retrieval = $this->domain->subObjectRetrieval(
            $this->lm_id,
            $this->sub_type,
            $this->sub_obj_id,
            $this->lang
        );

        $ml_head = \ilObjLearningModuleGUI::getMultiLangHeader($this->lm_id, $this);

        if ($this->sub_type === "st") {
            $modal = $this->gui->modal(
                $lng->txt("lm_insert_chapter")
            )->form($this->getAddPageForm("saveChapterAfter"))->getAsyncTriggerButtonComponents(
                $lng->txt("lm_insert_chapter"),
                $this->gui->ctrl()->getLinkTargetByClass(self::class, "insertChapterAfter"),
                false
            );
            $this->gui->toolbar()->addComponent($modal["button"]);
            $this->gui->toolbar()->addComponent($modal["modal"]);
            /*
            $this->gui->button(
                $lng->txt("lm_insert_chapter"),
                $ctrl->getLinkTargetByClass(self::class, "insertFirstChapter")
            )->toToolbar();*/
            if ($user->clipboardHasObjectsOfType("st")) {
                $this->gui->button(
                    $lng->txt("lm_insert_chapter_clip"),
                    $ctrl->getLinkTargetByClass(self::class, "insertChapterClip")
                )->toToolbar();
            }
        } else {
            $modal = $this->gui->modal(
                $lng->txt("lm_insert_page")
            )->form($this->getAddPageForm("savePageAfter"))->getAsyncTriggerButtonComponents(
                $lng->txt("lm_insert_page"),
                $this->gui->ctrl()->getLinkTargetByClass(self::class, "insertPageAfter"),
                false
            );
            $this->gui->toolbar()->addComponent($modal["button"]);
            $this->gui->toolbar()->addComponent($modal["modal"]);
            /*$this->gui->button(
                $lng->txt("lm_insert_page"),
                $ctrl->getLinkTargetByClass(self::class, "insertFirstPage")
            )->toToolbar();*/
            if ($user->clipboardHasObjectsOfType("pg")) {
                $this->gui->button(
                    $lng->txt("lm_insert_page_clip"),
                    $ctrl->getLinkTargetByClass(self::class, "insertPageClip")
                )->toToolbar();
            }
        }
        $table = $this->getTable();

        $main_tpl->setContent($ml_head . $table->render());
        $main_tpl->addOnloadCode("window.setTimeout(() => { il.repository.core.trigger('il-lm-editor-tree'); }, 500);");
    }

    protected function getCurrentParentId(): int
    {
        $parent = $this->sub_obj_id;
        if ($parent === 0) {
            $parent = $this->lm_tree->readRootId();
        }
        return $parent;
    }

    public function insertChapterClipBefore(): void
    {
        $parent = $this->getCurrentParentId();
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
    public function insertPageAfter(int $id = 0): void
    {
        $lng = $this->domain->lng();
        $this->gui->ctrl()->setParameterByClass(
            self::class,
            "target_id",
            $id
        );
        $this->gui->clearAsnyOnloadCode();
        $modal = $this->gui->modal($lng->txt("lm_insert_page"))->form($this->getAddPageForm("savePageAfter"));
        $modal->send();
    }

    public function savePageAfter(): void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $lng = $this->domain->lng();
        $target_id = $this->request->getTargetId();
        $this->insertPage(
            $this->sub_obj_id,
            $target_id,
            $this->getTitlesFromForm(),
            $this->getLayoutIdFromForm()
        );
        $mt->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
        $this->gui->ctrl()->redirect($this, "list");
    }

    public function insertPageBefore(int $id): void
    {
        $lng = $this->domain->lng();
        $this->gui->ctrl()->setParameterByClass(
            self::class,
            "target_id",
            $id
        );
        $this->gui->clearAsnyOnloadCode();
        $modal = $this->gui->modal($lng->txt("lm_insert_page"))->form($this->getAddPageForm("savePageBefore"));
        $modal->send();
    }

    public function savePageBefore(): void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $lng = $this->domain->lng();
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
            $before_target,
            $this->getTitlesFromForm(),
            $this->getLayoutIdFromForm()
        );
        $mt->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
        $this->gui->ctrl()->redirect($this, "list");
    }

    protected function insertPage(
        int $parent_id = 0,
        int $target = 0,
        array $titles = [],
        int $layout_id = 0
    ): void {
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();

        $page = new \ilLMPageObject($this->lm);
        $page->setType("pg");
        $page->setTitle($lng->txt("cont_new_page"));
        $page->setLMId($this->lm_id);
        $page->create(false, false, $layout_id);
        \ilLMObject::putInTree($page, $parent_id, $target);

        if (count($titles) > 0) {
            \ilLMObject::saveTitle($page->getId(), $titles["-"]);

            $ot = $this->domain->translation($this->lm->getId());
            if ($ot->getContentTranslationActivated()) {
                foreach ($ot->getLanguages() as $lang) {
                    $code = $lang->getLanguageCode();
                    if ($code === $ot->getBaseLanguage()) {
                        continue;
                    }
                    \ilLMObject::saveTitle($page->getId(), $titles[$code], $code);
                }
            }
        }

        $ctrl->redirect($this, "list");
    }

    public function insertFirstChapter(): void
    {
        $this->insertChapter(
            $this->sub_obj_id
        );
    }

    public function insertChapterAfter(int $id = 0): void
    {
        $lng = $this->domain->lng();
        $this->gui->ctrl()->setParameterByClass(
            self::class,
            "target_id",
            $id
        );
        $this->gui->clearAsnyOnloadCode();
        $modal = $this->gui->modal($lng->txt("lm_insert_chapter"))->form($this->getEditTitleForm(0, "saveChapterAfter"));
        $modal->send();
    }

    public function saveChapterAfter(): void
    {
        $target_id = $this->request->getTargetId();
        $this->insertChapter(
            $this->sub_obj_id,
            $target_id,
            $this->getTitlesFromForm()
        );
    }

    public function insertChapterBefore(int $id): void
    {
        $lng = $this->domain->lng();
        $this->gui->ctrl()->setParameterByClass(
            self::class,
            "target_id",
            $id
        );
        $this->gui->clearAsnyOnloadCode();
        $modal = $this->gui->modal($lng->txt("lm_insert_chapter"))->form($this->getEditTitleForm(0, "saveChapterBefore"));
        $modal->send();
    }

    public function saveChapterBefore(): void
    {
        $parent = $this->getCurrentParentId();
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
            $before_target,
            $this->getTitlesFromForm()
        );
    }

    protected function insertChapter(
        int $parent_id = 0,
        int $target = \ilTree::POS_LAST_NODE,
        array $titles = []
    ): void {
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $chap = new \ilStructureObject($this->lm);
        $chap->setType("st");
        $chap->setTitle($lng->txt("cont_new_chap"));
        $chap->setLMId($this->lm_id);
        $chap->create();
        \ilLMObject::putInTree($chap, $parent_id, $target);

        if (count($titles) > 0) {
            \ilLMObject::saveTitle($chap->getId(), $titles["-"]);

            $ot = $this->domain->translation($this->lm->getId());
            if ($ot->getContentTranslationActivated()) {
                foreach ($ot->getLanguages() as $lang) {
                    $code = $lang->getLanguageCode();
                    if ($code === $ot->getBaseLanguage()) {
                        continue;
                    }
                    \ilLMObject::saveTitle($chap->getId(), $titles[$code], $code);
                }
            }
        }

        $ctrl->redirect($this, "list");
    }

    protected function getAddPageForm($cmd): FormAdapterGUI
    {
        $this->domain->lng()->loadLanguageModule("copg");
        $form = $this->getEditTitleForm(0, $cmd);
        $arr_templates = \ilPageLayout::activeLayouts(\ilPageLayout::MODULE_LM);
        if (count($arr_templates) > 0) {
            $form = $form->optional("use_template", $this->domain->lng()->txt("copg_use_template"));
            $form = \ilPageLayoutGUI::addTemplateSelection((string) \ilPageLayout::MODULE_LM, $form);
            $form = $form->end();
        }
        return $form;
    }

    protected function getEditTitleForm(int $id, $cmd = "saveTitle"): FormAdapterGUI
    {
        $lng = $this->domain->lng();
        $this->gui->ctrl()->setParameterByClass(self::class, "edit_id", $id);
        $ot = $this->domain->translation($this->lm->getId());
        $ml = "";
        if ($ot->getContentTranslationActivated()) {
            $ml = " (" . $lng->txt("meta_l_" . $ot->getBaseLanguage()) . ")";
        }

        $form = $this
            ->gui
            ->form([self::class], $cmd)
            ->text("title", $lng->txt('title') . $ml, "", ilLMObject::_lookupTitle($id), 200);
        if ($ot->getContentTranslationActivated()) {
            foreach ($ot->getLanguages() as $lang) {
                $code = $lang->getLanguageCode();
                if ($code === $ot->getBaseLanguage()) {
                    continue;
                }
                $lmobjtrans = new \ilLMObjTranslation($id, $code);
                $title = $lmobjtrans->getTitle();
                $form = $form->text(
                    "title_" . $code,
                    $lng->txt('title') . " (" . $lng->txt("meta_l_" . $code) . ")",
                    "",
                    $title,
                    200
                );
            }
        }
        return $form;
    }

    public function editTitle(int $id): void
    {
        $lng = $this->domain->lng();
        $this->gui->clearAsnyOnloadCode();
        $modal = $this->gui->modal($lng->txt("cont_edit_title"))->form($this->getEditTitleForm($id));
        $modal->send();
    }

    public function getTitlesFromForm(): array
    {
        $titles = [];
        $form = $this->getEditTitleForm($this->request->getEditId());
        if ($form->isValid()) {
            $titles["-"] = $form->getData("title");

            $ot = $this->domain->translation($this->lm->getId());
            if ($ot->getContentTranslationActivated()) {
                foreach ($ot->getLanguages() as $lang) {
                    $code = $lang->getLanguageCode();
                    if ($code === $ot->getBaseLanguage()) {
                        continue;
                    }
                    $titles[$code] = $form->getData("title_" . $code);
                }
            }
        }
        return $titles;
    }

    public function getLayoutIdFromForm(): int
    {
        $form = $this->getAddPageForm("");
        if ($form->isValid()) {
            if ($form->getData("use_template")) {
                return (int) $form->getData("template_id");
            }
        }
        return 0;
    }

    public function saveTitle(): void
    {
        $mt = $this->gui->mainTemplate();
        $lng = $this->domain->lng();
        $form = $this->getEditTitleForm($this->request->getEditId());
        if ($form->isValid()) {
            \ilLMObject::saveTitle($this->request->getEditId(), $form->getData("title"));

            $ot = $this->lm->getObjectProperties()->getPropertyTranslations();
            if ($ot->getContentTranslationActivated()) {
                foreach ($ot->getLanguages() as $lang) {
                    $code = $lang->getLanguageCode();
                    if ($code === $ot->getBaseLanguage()) {
                        continue;
                    }
                    \ilLMObject::saveTitle($this->request->getEditId(), $form->getData("title_" . $code), $code);
                }
            }
        }
        $mt->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
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

    /*

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

    */
}
