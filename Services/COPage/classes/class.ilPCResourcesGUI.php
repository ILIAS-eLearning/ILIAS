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

/**
 * User Interface for Resources Component Editing
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCResourcesGUI extends ilPageContentGUI
{
    protected \ILIAS\Container\InternalDomainService $container_domain;
    protected ilTree $rep_tree;
    protected ilObjectDefinition $obj_definition;


    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->obj_definition = $DIC["objDefinition"];
        $tree = $DIC->repositoryTree();

        $this->rep_tree = $tree;

        $this->container_domain = $DIC->container()->internal()->domain();

        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function executeCommand(): void
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    public function insert(): void
    {
        $this->edit(true);
    }

    public function edit(bool $a_insert = false): void
    {
        $tpl = $this->tpl;
        $this->displayValidationError();
        $form = $this->initForm($a_insert);
        $html = $form->getHTML();
        $tpl->setContent($html);
    }

    public function initForm(bool $a_insert = false): ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $objDefinition = $this->obj_definition;

        $op_type = null;
        $op_itemgroup = null;

        // edit form
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_insert_resources"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_resources"));
        }

        // count number of existing objects per type and collect item groups
        $ref_id = $this->requested_ref_id;
        $childs = $this->rep_tree->getChilds($ref_id);

        $type_counts = array();
        $item_groups = array();
        foreach ($childs as $c) {
            // see bug #12471
            //echo "<br>-".$c["type"]."-".$objDefinition->getGroupOfObj($c["type"])."-";
            $key = ($objDefinition->getGroupOfObj($c["type"]) != "")
                ? $objDefinition->getGroupOfObj($c["type"])
                : $c["type"];
            $type_counts[$key] = ($type_counts[$key] ?? 0) + 1;
            if ($c["type"] == "itgr") {
                $item_groups[$c["ref_id"]] = $c["title"];
            }
        }

        // radio group for type selection
        $radg = new ilRadioGroupInputGUI($lng->txt("cont_resources"), "res_type");
        $form->addItem($radg);
        if (!$a_insert && $this->content_obj->getMainType() == "ItemGroup") {
            $radg->setValue("itgr");
        } else {
            $radg->setValue("by_type");
        }


        $op_type = new ilRadioOption($lng->txt("cont_resources_of_type"), "by_type", "");
        // all views support typed blocks
        //if ($this->supportsTypeBlocks()) {
        //}

        if ($this->supportsItemGroups() && count($item_groups) > 0) {
            $op_itemgroup = new ilRadioOption($lng->txt("cont_manual_item_group"), "itgr", "");
            $radg->addOption($op_itemgroup);
        }

        $radg->addOption($op_type);

        // type selection
        $type_prop = new ilSelectInputGUI(
            $this->lng->txt("cont_type"),
            "type"
        );
        $obj_id = ilObject::_lookupObjId($this->requested_ref_id);
        $obj_type = ilObject::_lookupType($obj_id);
        $sub_objs = $objDefinition->getGroupedRepositoryObjectTypes($obj_type);
        $types = array();
        foreach ($sub_objs as $k => $so) {
            $cnt = (int) ($type_counts[$k] ?? 0);
            if ($cnt === 0) {
                continue;
            }
            if (!$objDefinition->isPlugin($k)) {
                if ($k != "itgr") {
                    $types[$k] = $this->lng->txt("objs_" . $k) . " (" . $cnt . ")";
                }
            } else {
                $pl = ilObjectPlugin::getPluginObjectByType($k);
                $types[$k] = $pl->txt("objs_" . $k) . " (" . $cnt . ")";
            }
        }
        $type_prop->setOptions($types);
        $selected = ($a_insert)
            ? ""
            : $this->content_obj->getResourceListType();
        $type_prop->setValue($selected);
        $op_type->addSubItem($type_prop);

        if ($this->supportsItemGroups() && count($item_groups) > 0) {
            // item groups
            $options = $item_groups;
            $si = new ilSelectInputGUI($this->lng->txt("obj_itgr"), "itgr");
            $si->setOptions($options);
            $selected = ($a_insert)
                ? ""
                : $this->content_obj->getItemGroupRefId();
            $op_itemgroup->addSubItem($si);
            if ($a_insert) {
                $radg->setValue("itgr");
            }
        }

        // learning objectives
        if ($this->supportsObjectives()) {
            $lng->loadLanguageModule("crs");
            $op_lobj = new ilRadioOption($lng->txt("crs_objectives"), "_lobj", "");
            $radg->addOption($op_lobj);
            if (!$a_insert && $this->content_obj->getResourceListType() === "_lobj") {
                $radg->setValue("_lobj");
            }
        }

        // other
        if ($this->supportsOther() && $this->hasOtherBlock()) {
            $op_other = new ilRadioOption($lng->txt("cont_other_resources"), "_other", "");
            $radg->addOption($op_other);
            if (!$a_insert && $this->content_obj->getResourceListType() === "_other") {
                $radg->setValue("_other");
            }
        }

        // save/cancel buttons
        if ($a_insert) {
            $form->addCommandButton("create_resources", $lng->txt("save"));
            $form->addCommandButton("cancelCreate", $lng->txt("cancel"));
        } else {
            $form->addCommandButton("update_resources", $lng->txt("save"));
            $form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
        }
        return $form;
    }

    public function initCreationForm(): ilPropertyFormGUI
    {
        $form = $this->initForm(true);
        return $form;
    }

    public function initEditingForm(): ilPropertyFormGUI
    {
        $form = $this->initForm(false);
        return $form;
    }

    /**
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    protected function getContainerViewManager(): \ILIAS\Container\Content\ViewManager
    {
        $ref_id = $this->requested_ref_id;
        $container = ilObjectFactory::getInstanceByRefId($ref_id);
        $view_manager = $this->container_domain->content()->view($container);
        return $view_manager;
    }

    protected function supportsItemGroups(): bool
    {
        foreach ($this->getContainerViewManager()->getBlockSequence()->getParts() as $part) {
            if ($part instanceof \ILIAS\Container\Content\ItemGroupBlocks) {
                return true;
            }
        }
        return false;
    }

    protected function supportsOther(): bool
    {
        foreach ($this->getContainerViewManager()->getBlockSequence()->getParts() as $part) {
            if ($part instanceof \ILIAS\Container\Content\OtherBlock) {
                return true;
            }
        }
        return false;
    }

    protected function supportsObjectives(): bool
    {
        foreach ($this->getContainerViewManager()->getBlockSequence()->getParts() as $part) {
            if ($part instanceof \ILIAS\Container\Content\ObjectivesBlock) {
                return true;
            }
        }
        return false;
    }

    protected function supportsTypeBlocks(): bool
    {
        foreach ($this->getContainerViewManager()->getBlockSequence()->getParts() as $part) {
            if ($part instanceof \ILIAS\Container\Content\TypeBlocks) {
                return true;
            }
        }
        return false;
    }

    public function create(): void
    {
        $this->content_obj = new ilPCResources($this->getPage());
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);

        if ($this->request->getString("res_type") === "_other") {
            $this->content_obj->setResourceListType("_other");
        } elseif ($this->request->getString("res_type") === "_lobj") {
            $this->content_obj->setResourceListType("_lobj");
        } elseif ($this->request->getString("res_type") !== "itgr") {
            $this->content_obj->setResourceListType(
                $this->request->getString("type")
            );
        } else {
            $this->content_obj->setItemGroupRefId(
                $this->request->getString("itgr")
            );
        }
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->insert();
        }
    }

    public function update(): void
    {
        if ($this->request->getString("res_type") === "_other") {
            $this->content_obj->setResourceListType("_other");
        } elseif ($this->request->getString("res_type") === "_lobj") {
            $this->content_obj->setResourceListType("_lobj");
        } elseif ($this->request->getString("res_type") !== "itgr") {
            $this->content_obj->setResourceListType(
                $this->request->getString("type")
            );
        } else {
            $this->content_obj->setItemGroupRefId(
                $this->request->getString("itgr")
            );
        }
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->pg_obj->addHierIDs();
            $this->edit();
        }
    }

    protected function hasOtherBlock(): bool
    {
        global $DIC;

        $ref_id = $DIC
            ->copage()
            ->internal()
            ->gui()
            ->pc()
            ->editRequest()
            ->getRefId();
        $item_presentation_manager = $DIC->container()->internal()
                                         ->domain()
                                         ->content()
                                         ->itemPresentation(
                                             \ilObjectFactory::getInstanceByRefId($ref_id),
                                             null,
                                             false
                                         );
        $block_sequence = $item_presentation_manager->getItemBlockSequence();
        foreach ($block_sequence->getBlocks() as $block) {
            if (($block->getBlock() instanceof \ILIAS\Container\Content\OtherBlock)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Insert resources (see also ilContainerContentGUI::determinePageEmbeddedBlocks for presentation)
     */
    public static function insertResourcesIntoPageContent(
        string $a_content
    ): string {
        global $DIC;

        $item_ref_ids = [];

        $lng = $DIC->language();
        $ref_id = $DIC
            ->copage()
            ->internal()
            ->gui()
            ->pc()
            ->editRequest()
            ->getRefId();
        $item_presentation_manager = $DIC->container()->internal()
            ->domain()
            ->content()
            ->itemPresentation(
                \ilObjectFactory::getInstanceByRefId($ref_id),
                null
            );
        $block_sequence = $item_presentation_manager->getItemBlockSequence();

        foreach ($block_sequence->getBlocks() as $block) {
            // render block
            $tpl = new ilTemplate("tpl.resource_block.html", true, true, "Services/COPage");
            $cnt = 0;
            $max = 5;
            if (!($block->getBlock() instanceof \ILIAS\Container\Content\ObjectivesBlock) &&
                count($block->getItemRefIds()) > 0) {
                foreach ($block->getItemRefIds() as $ref_id) {
                    $data = $item_presentation_manager->getRawDataByRefId($ref_id);
                    if ($block->getBlock() instanceof \ILIAS\Container\Content\OtherBlock) {
                        if ($data["type"] === "itgr" || in_array($ref_id, $item_ref_ids)) {
                            continue;
                        }
                    }

                    if ($cnt < $max) {
                        $tpl->setCurrentBlock("row");
                        $tpl->setVariable("IMG", ilUtil::img(ilObject::_getIcon((int) $data["obj_id"], "small")));
                        $tpl->setVariable("TITLE", $data["title"]);
                        $tpl->parseCurrentBlock();
                    }
                    if ($cnt == $max) {
                        $tpl->setCurrentBlock("row");
                        $tpl->setVariable("IMG", ilUtil::img(ilObject::_getIcon((int) $data["obj_id"], "small")));
                        $tpl->setVariable("TITLE", "...");
                        $tpl->parseCurrentBlock();
                    }
                    $cnt++;
                    $item_ref_ids[$ref_id] = $ref_id;
                }
            } elseif (count($block->getObjectiveIds()) > 0) {
                foreach ($block->getObjectiveIds() as $objective_id) {
                    $title = \ilCourseObjective::lookupObjectiveTitle($objective_id);
                    if ($cnt < $max) {
                        $tpl->setCurrentBlock("row");
                        $tpl->setVariable("IMG", ilUtil::img(ilUtil::getImagePath("icon_lobj.svg")));
                        $tpl->setVariable("TITLE", $title);
                        $tpl->parseCurrentBlock();
                    }
                    if ($cnt == $max) {
                        $tpl->setCurrentBlock("row");
                        $tpl->setVariable("IMG", ilUtil::img(ilUtil::getImagePath("icon_lobj.svg")));
                        $tpl->setVariable("TITLE", "...");
                        $tpl->parseCurrentBlock();
                    }
                    $cnt++;
                }
            } else {
                $tpl->setCurrentBlock("row");
                $tpl->setVariable("TITLE", $lng->txt("no_items"));
                $tpl->parseCurrentBlock();
            }
            if ($block->getBlock() instanceof \ILIAS\Container\Content\TypeBlock) {
                $type = $block->getId();
                $tpl->setVariable("HEADER", $lng->txt("objs_" . $type));
                $a_content = str_replace("[list-" . $type . "]", $tpl->get(), $a_content);
            } elseif ($block->getBlock() instanceof \ILIAS\Container\Content\SessionBlock) {
                $type = $block->getId();
                $tpl->setVariable("HEADER", $lng->txt("objs_sess"));
                $a_content = str_replace("[list-" . $type . "]", $tpl->get(), $a_content);
            } elseif ($block->getBlock() instanceof \ILIAS\Container\Content\ItemGroupBlock) {
                $id = $block->getId();
                $tpl->setVariable("HEADER", \ilObject::_lookupTitle(
                    \ilObject::_lookupObjId((int) $id)
                ));
                $a_content = str_replace("[item-group-" . $id . "]", $tpl->get(), $a_content);
            } elseif ($block->getBlock() instanceof \ILIAS\Container\Content\ObjectivesBlock) {
                $id = $block->getId();
                $tpl->setVariable("HEADER", $lng->txt("crs_objectives"));
                $a_content = str_replace("[list-_lobj]", $tpl->get(), $a_content);
            } elseif ($block->getBlock() instanceof \ILIAS\Container\Content\OtherBlock) {
                $id = $block->getId();
                $tpl->setVariable("HEADER", $lng->txt("cont_content"));
                $a_content = str_replace("[list-_other]", $tpl->get(), $a_content);
            }
        }
        return $a_content;
    }
}
