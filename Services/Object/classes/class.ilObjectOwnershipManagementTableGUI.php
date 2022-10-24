<?php

declare(strict_types=1);

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

use ILIAS\UI\Component\Symbol\Icon\Standard;

/**
 * Table for object role permissions
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjectOwnershipManagementTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilTree $tree;
    protected ilObjectDefinition $obj_definition;
    protected \ILIAS\UI\Factory $factory;
    protected \ILIAS\UI\Renderer $renderer;

    protected int $user_id;

    public function __construct(?object $parent_obj, string $parent_cmd, int $user_id, array $data = null)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->obj_definition = $DIC["objDefinition"];
        $this->renderer = $DIC->ui()->renderer();
        $this->factory = $DIC->ui()->factory();

        $this->user_id = $user_id;
        $this->setId('objownmgmt'); // #16373

        parent::__construct($parent_obj, $parent_cmd);

        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("path"), "path");
        $this->addColumn($this->lng->txt("action"));

        $this->setFormAction($this->ctrl->getFormAction($parent_obj, $parent_cmd));
        $this->setRowTemplate("tpl.obj_ownership_row.html", "Services/Object");
        $this->setDisableFilterHiding();

        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");

        $this->initItems($data);
    }

    protected function initItems(?array $data): void
    {
        $process_arr = [];
        $is_admin = false;
        $a_type = "";
        if (!is_null($data) && sizeof($data)) {
            if (!$this->user_id) {
                $is_admin = $this->access->checkAccess("visible", "", SYSTEM_FOLDER_ID);
            }

            foreach ($data as $id => $item) {
                // workspace objects won't have references
                $refs = ilObject::_getAllReferences($id);
                if ($refs) {
                    foreach ($refs as $ref_id) {
                        // objects in trash are hidden
                        if (!$this->tree->isDeleted($ref_id)) {
                            if ($this->user_id) {
                                $readable = $this->access->checkAccessOfUser(
                                    $this->user_id,
                                    "read",
                                    "",
                                    $ref_id,
                                    $a_type
                                );
                            } else {
                                $readable = $is_admin;
                            }

                            $process_arr[$ref_id] = [
                                "obj_id" => $id,
                                "ref_id" => $ref_id,
                                "type" => ilObject::_lookupType($id),
                                "title" => $item,
                                "path" => $this->buildPath($ref_id),
                                "readable" => $readable
                            ];
                        }
                    }
                }
            }
        }

        $this->setData($process_arr);
    }

    protected function fillRow(array $set): void
    {
        $icon = $this->factory->symbol()->icon()->standard($set["type"], $set["title"], Standard::MEDIUM);
        $this->tpl->setVariable("ICON", $this->renderer->render($icon));

        $this->tpl->setVariable("TITLE", $set["title"]);
        $this->tpl->setVariable("PATH", $set["path"]);

        if ($set["readable"]) {
            $this->tpl->setCurrentBlock("actions");
            $this->tpl->setVariable("ACTIONS", $this->buildActions($set["ref_id"], $set["type"]));
            $this->tpl->parseCurrentBlock();
        }
    }

    protected function buildActions(int $ref_id, string $type): string
    {
        $agui = new ilAdvancedSelectionListGUI();
        $agui->setId($this->id . "-" . $ref_id);
        $agui->setListTitle($this->lng->txt("actions"));

        $this->ctrl->setParameter($this->parent_obj, "ownid", $ref_id);

        $agui->addItem(
            $this->lng->txt("show"),
            "",
            ilLink::_getLink($ref_id, $type),
            "",
            "",
            "_blank"
        );

        $agui->addItem(
            $this->lng->txt("move"),
            "",
            $this->ctrl->getLinkTarget($this->parent_obj, "move")
        );

        $agui->addItem(
            $this->lng->txt("change_owner"),
            "",
            $this->ctrl->getLinkTarget($this->parent_obj, "changeOwner")
        );

        if (!in_array($type, array("crsr", "catr", "grpr")) && $this->obj_definition->allowExport($type)) {
            $agui->addItem(
                $this->lng->txt("export"),
                "",
                $this->ctrl->getLinkTarget($this->parent_obj, "export")
            );
        }

        $agui->addItem(
            $this->lng->txt("delete"),
            "",
            $this->ctrl->getLinkTarget($this->parent_obj, "delete")
        );

        $this->ctrl->setParameter($this->parent_obj, "ownid", "");

        return $agui->getHTML();
    }

    protected function buildPath(int $ref_id): string
    {
        $path = "...";
        $counter = 0;
        $path_full = $this->tree->getPathFull($ref_id);
        foreach ($path_full as $data) {
            if (++$counter < (count($path_full) - 2)) {
                continue;
            }
            if ($ref_id != $data['ref_id']) {
                $path .= " &raquo; " . $data['title'];
            }
        }

        return $path;
    }
}
