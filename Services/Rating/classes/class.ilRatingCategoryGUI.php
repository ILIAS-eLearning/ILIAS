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

use Psr\Http\Message\RequestInterface;

/**
 * Class ilRatingCategoryGUI. User interface class for rating categories.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilRatingCategoryGUI
{
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected int $parent_id; // [int]
    protected $export_callback; // [string|array]
    protected ?string $export_subobj_title = null;
    protected int $requested_cat_id;
    protected RequestInterface $request;
    protected int $cat_id;

    /**
     * ilRatingCategoryGUI constructor.
     * @param int         $a_parent_id
     * @param ?mixed  $a_export_callback
     * @param ?string $a_export_subobj_title
     */
    public function __construct(
        int $a_parent_id,
        $a_export_callback = null,
        string $a_export_subobj_title = null
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->request = $DIC->http()->request();
        $lng = $DIC->language();

        $this->parent_id = $a_parent_id;
        $this->export_callback = $a_export_callback;
        $this->export_subobj_title = $a_export_subobj_title;

        $lng->loadLanguageModule("rating");

        $params = $this->request->getQueryParams();
        $body = $this->request->getParsedBody();
        $this->requested_cat_id = (int) ($body["cat_id"] ?? ($params["cat_id"] ?? 0));

        if ($this->requested_cat_id) {
            $cat = new ilRatingCategory($this->requested_cat_id);
            if ($cat->getParentId() == $this->parent_id) {
                $this->cat_id = $cat->getId();
            }
        }
    }

    /**
     * execute command
     */
    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd("listCategories");

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    protected function listCategories(): void
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilToolbar->addButton(
            $lng->txt("rating_add_category"),
            $ilCtrl->getLinkTarget($this, "add")
        );

        $ilToolbar->addSeparator();

        $ilToolbar->addButton(
            $lng->txt("export"),
            $ilCtrl->getLinkTarget($this, "export")
        );

        $table = new ilRatingCategoryTableGUI($this, "listCategories", $this->parent_id);
        $tpl->setContent($table->getHTML());
    }


    protected function initCategoryForm(int $a_id = null): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($ilCtrl->getFormAction($this, "save"));
        $form->setTitle($lng->txt("rating_category_" . ($a_id ? "edit" : "create")));

        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        // description
        $ta = new ilTextAreaInputGUI($lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $form->addItem($ta);

        if (!$a_id) {
            $form->addCommandButton("save", $lng->txt("rating_category_add"));
        } else {
            $cat = new ilRatingCategory($a_id);
            $ti->setValue($cat->getTitle());
            $ta->setValue($cat->getDescription());

            $form->addCommandButton("update", $lng->txt("rating_category_update"));
        }
        $form->addCommandButton("listCategories", $lng->txt("cancel"));

        return $form;
    }

    protected function add(ilPropertyFormGUI $a_form = null): void
    {
        $tpl = $this->tpl;

        if (!$a_form) {
            $a_form = $this->initCategoryForm();
        }

        $tpl->setContent($a_form->getHTML());
    }

    protected function save(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $form = $this->initCategoryForm();
        if ($form->checkInput()) {
            $cat = new ilRatingCategory();
            $cat->setParentId($this->parent_id);
            $cat->setTitle($form->getInput("title"));
            $cat->setDescription($form->getInput("desc"));
            $cat->save();

            $this->tpl->setOnScreenMessage('success', $lng->txt("rating_category_created"));
            $ilCtrl->redirect($this, "listCategories");
        }

        $form->setValuesByPost();
        $this->add($form);
    }

    protected function edit(ilPropertyFormGUI $a_form = null): void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, "cat_id", $this->cat_id);

        if (!$a_form) {
            $a_form = $this->initCategoryForm($this->cat_id);
        }

        $tpl->setContent($a_form->getHTML());
    }

    protected function update(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $form = $this->initCategoryForm($this->cat_id);
        if ($form->checkInput()) {
            $cat = new ilRatingCategory($this->cat_id);
            $cat->setTitle($form->getInput("title"));
            $cat->setDescription($form->getInput("desc"));
            $cat->update();

            $this->tpl->setOnScreenMessage('success', $lng->txt("rating_category_updated"));
            $ilCtrl->redirect($this, "listCategories");
        }

        $form->setValuesByPost();
        $this->add($form);
    }

    protected function updateOrder(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $body = $this->request->getParsedBody();
        $order = $body["pos"];
        asort($order);

        $cnt = 0;
        foreach ($order as $id => $pos) {
            $cat = new ilRatingCategory($id);
            if ($cat->getParentId() == $this->parent_id) {
                $cnt += 10;
                $cat->setPosition($cnt);
                $cat->update();
            }
        }

        $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "listCategories");
    }

    protected function confirmDelete(): void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (!$this->cat_id) {
            $this->listCategories();
            return;
        }

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($lng->txt("rating_category_delete_sure") . "<br/>" .
            $lng->txt("info_delete_warning_no_trash"));

        $cgui->setFormAction($ilCtrl->getFormAction($this));
        $cgui->setCancel($lng->txt("cancel"), "listCategories");
        $cgui->setConfirm($lng->txt("confirm"), "delete");

        $cat = new ilRatingCategory($this->cat_id);
        $cgui->addItem("cat_id", $this->cat_id, $cat->getTitle());

        $tpl->setContent($cgui->getHTML());
    }

    protected function delete(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if ($this->cat_id) {
            ilRatingCategory::delete($this->cat_id);
            $this->tpl->setOnScreenMessage('success', $lng->txt("rating_category_deleted"), true);
        }

        // fix order
        $cnt = 0;
        foreach (ilRatingCategory::getAllForObject($this->parent_id) as $item) {
            $cnt += 10;

            $cat = new ilRatingCategory($item["id"]);
            $cat->setPosition($cnt);
            $cat->update();
        }

        $ilCtrl->redirect($this, "listCategories");
    }

    protected function export(): void
    {
        $lng = $this->lng;

        $excel = new ilExcel();
        $excel->addSheet($lng->txt("rating_categories"));

        // restrict to currently active (probably not needed - see delete())
        $active = array();
        foreach (ilRatingCategory::getAllForObject($this->parent_id) as $item) {
            $active[$item["id"]] = $item["title"];
        }

        // title row
        $row = 1;
        $excel->setCell($row, 0, $this->export_subobj_title . " (" . $lng->txt("id") . ")");
        $excel->setCell($row, 1, $this->export_subobj_title);
        $excel->setCell($row, 2, $lng->txt("rating_export_category") . " (" . $lng->txt("id") . ")");
        $excel->setCell($row, 3, $lng->txt("rating_export_category"));
        $excel->setCell($row, 4, $lng->txt("rating_export_date"));
        $excel->setCell($row, 5, $lng->txt("rating_export_rating"));
        $excel->setBold("A1:F1");

        // content rows
        foreach (ilRating::getExportData($this->parent_id, ilObject::_lookupType($this->parent_id), array_keys($active)) as $item) {
            // overall rating?
            if (!$item["sub_obj_id"]) {
                continue;
            }

            $row++;

            $sub_obj_title = $item["sub_obj_type"];
            if ($this->export_callback) {
                $sub_obj_title = call_user_func($this->export_callback, $item["sub_obj_id"], $item["sub_obj_type"]);
            }

            $excel->setCell($row, 0, (int) $item["sub_obj_id"]);
            $excel->setCell($row, 1, $sub_obj_title);
            $excel->setCell($row, 2, (int) $item["category_id"]);
            $excel->setCell($row, 3, $active[$item["category_id"]] ?? "");
            $excel->setCell($row, 4, new ilDateTime($item["tstamp"] ?? null, IL_CAL_UNIX));
            $excel->setCell($row, 5, $item["rating"] ?? "");
        }

        $excel->sendToClient(ilObject::_lookupTitle($this->parent_id));
    }
}
