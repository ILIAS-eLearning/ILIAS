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

use ILIAS\LearningModule\Editing\EditingGUIRequest;

/**
 * Base class for ilStructureObjects and ilPageObjects (see ILIAS DTD)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMObjectGUI
{
    protected int $requested_ref_id;
    public ilGlobalTemplateInterface $tpl;
    public ilLanguage $lng;
    public ilLMObject $obj;
    public ilCtrl $ctrl;
    public ilObjLearningModule $content_object;
    public array $actions;

    protected \ILIAS\DI\UIServices $ui;
    protected int $requested_obj_id;
    protected string $requested_totransl = "";
    protected string $requested_transl = "";
    protected string $requested_target;
    protected string $requested_new_type;
    protected array $target_frame;
    protected EditingGUIRequest $request;

    /**
    * constructor
    *
    */
    public function __construct(ilObjLearningModule $a_content_obj)
    {
        global $DIC;

        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->content_object = $a_content_obj;
        $this->ui = $DIC->ui();
        $this->request = $DIC
            ->learningModule()
            ->internal()
            ->gui()
            ->editing()
            ->request();
        $req = $this->request;

        $this->requested_ref_id = $req->getRefId();
        $this->requested_obj_id = $req->getObjId();
        $this->requested_transl = $req->getTranslation();
        $this->requested_totransl = $req->getToTranslation();
        $this->requested_target = $req->getTarget();
        $this->requested_new_type = $req->getNewType();
    }


    /**
     * @param ?array $a_actions action array (key = action key, value = action language string)
     */
    public function setActions(?array $a_actions = null): void
    {
        if (is_array($a_actions)) {
            foreach ($a_actions as $name => $lng) {
                $this->actions[$name] = array("name" => $name, "lng" => $lng);
            }
        } else {
            $this->actions = [];
        }
    }


    /**
     * get target frame for command (command is method name without "Object", e.g. "perm")
     * @param	string		$a_cmd			command
     * @param	string		$a_target_frame	default target frame (is returned, if no special
     *										target frame was set)
     */
    public function getTargetFrame(
        string $a_cmd,
        string $a_target_frame = ""
    ): string {
        if ($this->target_frame[$a_cmd] != "") {
            return $this->target_frame[$a_cmd];
        } elseif (!empty($a_target_frame)) {
            return "target=\"" . $a_target_frame . "\"";
        } else {
            return "";
        }
    }

    /**
     * structure / page object creation form
     */
    public function create(): void
    {
        $form = $this->getCreateForm();
        $this->tpl->setContent($form->getHTML());
    }

    public function getCreateForm(): ilPropertyFormGUI
    {
        $new_type = $this->requested_new_type;
        $this->ctrl->setParameter($this, "new_type", $new_type);
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "save"));
        $form->setTitle($this->lng->txt($new_type . "_new"));

        $title = new ilTextInputGUI($this->lng->txt("title"), "title");
        $form->addItem($title);

        $desc = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $form->addItem($desc);

        $form->addCommandButton("save", $this->lng->txt($new_type . "_add"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        return $form;
    }


    /**
     * put this object into content object tree
     */
    public function putInTree(?int $target = 0): void
    {
        if ($target == 0) {
            $target = $this->requested_target;
        }
        $tree = new ilTree($this->content_object->getId());
        $tree->setTableNames('lm_tree', 'lm_data');
        $tree->setTreeTablePK("lm_id");

        $parent_id = ($this->requested_obj_id > 0)
            ? $this->requested_obj_id
            : $tree->getRootId();

        if ($target == "") {
            // determine last child of current type
            $childs = $tree->getChildsByType($parent_id, $this->obj->getType());
            if (count($childs) == 0) {
                $target = ilTree::POS_FIRST_NODE;
            } else {
                $target = $childs[count($childs) - 1]["obj_id"];
            }
        }
        if (!$tree->isInTree($this->obj->getId())) {
            $tree->insertNode($this->obj->getId(), $parent_id, $target);
        }
    }


    /**
     * Confirm deletion screen (delete page or structure objects)
     */
    public function delete(): void
    {
        $this->setTabs();

        $cont_obj_gui = new ilObjLearningModuleGUI(
            "",
            $this->content_object->getRefId(),
            true,
            false
        );
        $cont_obj_gui->delete($this->obj->getId());
    }


    /**
     * cancel deletion of page/structure objects
     */
    public function cancelDelete(): void
    {
        ilSession::clear("saved_post");
        $this->ctrl->redirect($this, $this->request->getBackCmd());
    }


    /**
     * page and structure object deletion
     */
    public function confirmedDelete(): void
    {
        $cont_obj_gui = new ilObjLearningModuleGUI(
            "",
            $this->content_object->getRefId(),
            true,
            false
        );
        $cont_obj_gui->confirmedDelete($this->obj->getId());
        $this->ctrl->redirect($this, $this->request->getBackCmd());
    }

    /**
     * show possible action (form buttons)
     */
    public function showActions(array $a_actions): void
    {
        $d = [];
        foreach ($a_actions as $name => $lng) {
            $d[$name] = array("name" => $name, "lng" => $lng);
        }

        $operations = $d;
        if (count($operations) > 0) {
            foreach ($operations as $val) {
                $this->tpl->setCurrentBlock("operation_btn");
                $this->tpl->setVariable("BTN_NAME", $val["name"]);
                $this->tpl->setVariable("BTN_VALUE", $this->lng->txt($val["lng"]));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("operation");
            $this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * check the content object tree
     */
    public function checkTree(): void
    {
        $this->content_object->checkTree();
    }
}
