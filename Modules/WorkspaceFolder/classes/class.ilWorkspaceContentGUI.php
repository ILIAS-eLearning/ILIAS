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
 * Workspace content renderer
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWorkspaceContentGUI
{
    private object $object_gui;
    private ilWorkspaceFolderUserSettings $user_folder_settings;
    private array $shared_objects;
    protected int $current_node;
    protected bool $admin;
    protected ilWorkspaceAccessHandler $access_handler;
    protected \ILIAS\DI\UIServices $ui;
    protected ilLanguage $lng;
    protected ilObjUser $user;
    protected ilObjectDefinition $obj_definition;
    protected ilCtrl $ctrl;
    protected ?ilWorkspaceFolderSorting $folder_sorting = null;

    public function __construct(
        object $object_gui,
        int $node_id,
        bool $admin,
        ilWorkspaceAccessHandler $access_handler,
        \ILIAS\DI\UIServices $ui,
        ilLanguage $lng,
        ilObjUser $user,
        ilObjectDefinition $obj_definition,
        ilCtrl $ctrl,
        ilWorkspaceFolderUserSettings $user_folder_settings
    ) {
        $this->current_node = $node_id;
        $this->admin = $admin;
        $this->access_handler = $access_handler;
        $this->object_gui = $object_gui;
        $this->ui = $ui;
        $this->lng = $lng;
        $this->user = $user;
        $this->obj_definition = $obj_definition;
        $this->ctrl = $ctrl;
        $this->user_folder_settings = $user_folder_settings;

        $this->folder_sorting = new ilWorkspaceFolderSorting();
    }

    public function render() : string
    {
        $html = "";
        $first = true;
        foreach ($this->getItems() as $i) {
            if ($first) {
                $first = false;
            } else {
                $html .= $this->ui->renderer()->render($this->ui->factory()->divider()->horizontal());
            }
            $html .= $this->getItemHTML($i);
        }

        if ($this->admin) {
            $tpl = new ilTemplate("tpl.admin_container.html", true, true, "Modules/WorkspaceFolder");
            $tpl->setVariable("ITEMS", $html);
            $tpl->setVariable("TXT_SELECT_ALL", $this->lng->txt("select_all"));
            $html = $tpl->get();
        }


        // output sortation
        $tree = new ilWorkspaceTree($this->user->getId());
        $parent_id = $tree->getParentId($this->object_gui->getRefId());
        $parent_effective = ($parent_id > 0)
            ? $this->user_folder_settings->getEffectiveSortation($parent_id)
            : 0;
        $selected = $this->user_folder_settings->getSortation($this->object_gui->getObject()->getId());
        $sort_options = $this->folder_sorting->getOptionsByType($this->object_gui->getObject()->getType(), $selected, $parent_effective);
        $sortation = $this->ui->factory()->viewControl()->sortation($sort_options)
            ->withTargetURL($this->ctrl->getLinkTarget($this->object_gui, "setSortation"), 'sortation')
            ->withLabel($this->lng->txt("wfld_sortation"));


        if ($first) {
            return "";
        }

        $leg = $this->ui->factory()->legacy($html);

        $panel = $this->ui->factory()->panel()->standard($this->lng->txt("content"), [$leg]);

        if (method_exists($panel, "withViewControls")) {
            $panel = $panel->withViewControls(array($sortation));
        }



        return $this->ui->renderer()->render($panel);
    }

    protected function getItems() : array
    {
        $user = $this->user;

        $tree = new ilWorkspaceTree($user->getId());
        $nodes = $tree->getChilds($this->current_node, "title");

        if (sizeof($nodes)) {
            $preloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_WORKSPACE);
            foreach ($nodes as $node) {
                $preloader->addItem($node["obj_id"], $node["type"]);
            }
            $preloader->preload();
            unset($preloader);
        }

        $this->shared_objects = $this->access_handler->getObjectsIShare();

        $nodes = $this->folder_sorting->sortNodes($nodes, $this->user_folder_settings->getEffectiveSortation($this->object_gui->getRefId()));

        return $nodes;
    }

    protected function getItemHTML(array $node) : string
    {
        $objDefinition = $this->obj_definition;
        $ilCtrl = $this->ctrl;
        //bug ilCertificateVerificationClassMap in 6 beta was cmiv instead of cmxv
        if ($node["type"] == "cmiv") {
            return "";
        }

        $class = $objDefinition->getClassName($node["type"]);
        $full_class = "ilObj" . $class . "ListGUI";

        $item_list_gui = new $full_class(ilObjectListGUI::CONTEXT_WORKSPACE);

        $item_list_gui->setDetailsLevel(ilObjectListGUI::DETAILS_ALL);
        $item_list_gui->enableDelete(true);
        $item_list_gui->enableCut(true);
        $item_list_gui->enableSubscribe(false);
        $item_list_gui->enableLink(false);
        $item_list_gui->enablePath(false);
        $item_list_gui->enableLinkedPath(false);
        $item_list_gui->enableSearchFragments(true);
        $item_list_gui->enableRelevance(false);
        $item_list_gui->enableIcon(true);
        $item_list_gui->enableTimings(false);
        $item_list_gui->enableCheckbox($this->admin);

        $item_list_gui->enableNotes(true);
        $item_list_gui->enableCopy($objDefinition->allowCopy($node["type"]));

        if ($node["type"] == "file") {
            $item_list_gui->enableRepositoryTransfer(true);
        }

        $item_list_gui->setContainerObject($this->object_gui);

        if (in_array($node["type"], array("file", "blog"))) {
            // add "share" link
            $ilCtrl->setParameterByClass("ilworkspaceaccessgui", "wsp_id", $node["wsp_id"]);
            $share_link = $ilCtrl->getLinkTargetByClass(array("ilObj" . $class . "GUI", "ilworkspaceaccessgui"), "share");
            $item_list_gui->addCustomCommand($share_link, "wsp_permissions");

            // show "shared" status
            if (in_array($node["obj_id"], $this->shared_objects)) {
                $item_list_gui->addCustomProperty($this->lng->txt("status"), $this->lng->txt("wsp_status_shared"), true, true);
            }
        }

        $html = $item_list_gui->getListItemHTML(
            $node["wsp_id"],
            $node["obj_id"],
            (string) $node["title"],
            (string) $node["description"]
        );

        return $html;
    }
}
