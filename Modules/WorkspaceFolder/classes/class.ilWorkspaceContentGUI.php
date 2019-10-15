<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Workspace content renderer
 *
 * @author @leifos.de
 * @ingroup
 */
class ilWorkspaceContentGUI
{
	/**
	 * @var int
	 */
	protected $current_node;

	/**
	 * @var bool
	 */
	protected $admin;

	/**
	 * @var object
	 */
	protected $access_handler;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilObjUser 
	 */
	protected $user;

	/**
	 * @var ilObjectDefinition
	 */
	protected $obj_definition;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilWorkspaceFolderSorting
	 */
	protected $folder_sorting;

	/**
	 * Constructor
	 */
	public function __construct($object_gui, int $node_id, bool $admin, $access_handler,
				\ILIAS\DI\UIServices $ui, ilLanguage $lng, ilObjUser $user,
				ilObjectDefinition $obj_definition, ilCtrl $ctrl, ilWorkspaceFolderUserSettings $user_folder_settings)
	{
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

	/**
	 * Render
	 */
	public function render()
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
		$parent_id = $tree->getParentId($this->object_gui->ref_id);
		$parent_effective = ($parent_id > 0)
			? $this->user_folder_settings->getEffectiveSortation($parent_id)
			: 0;
		$selected = $this->user_folder_settings->getSortation($this->object_gui->object->getId());
		$sort_options = $this->folder_sorting->getOptionsByType($this->object_gui->object->getType(), $selected, $parent_effective);
		$sortation = $this->ui->factory()->viewControl()->sortation($sort_options)
			->withTargetURL($this->ctrl->getLinkTarget($this->object_gui, "setSortation"), 'sortation')
			->withLabel($this->lng->txt("wfld_sortation"));


		if ($first)
		{
			return "";
		}

		$leg = $this->ui->factory()->legacy($html);

		$panel = $this->ui->factory()->panel()->standard($this->lng->txt("content"), [$leg]);

		if (method_exists($panel, "withViewControls")) {
            $panel = $panel->withViewControls(array($sortation));
        }



		return $this->ui->renderer()->render($panel);
	}

	/**
	 *
	 */
	protected function getItems()
	{
		$user = $this->user;

		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		$tree = new ilWorkspaceTree($user->getId());
		$nodes = $tree->getChilds($this->current_node, "title");

		if(sizeof($nodes))
		{
			include_once("./Services/Object/classes/class.ilObjectListGUIPreloader.php");
			$preloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_WORKSPACE);
			foreach($nodes as $node)
			{
				$preloader->addItem($node["obj_id"], $node["type"]);
			}
			$preloader->preload();
			unset($preloader);
		}

		$this->shared_objects = $this->access_handler->getObjectsIShare();

		$nodes = $this->folder_sorting->sortNodes($nodes, $this->user_folder_settings->getEffectiveSortation($this->object_gui->ref_id));

		return $nodes;
	}

	/**
	 * Get item HTML
	 *
	 * @param
	 * @return
	 */
	protected function getItemHTML($node)
	{
		$objDefinition = $this->obj_definition;
		$ilCtrl = $this->ctrl;

		$class = $objDefinition->getClassName($node["type"]);
		$location = $objDefinition->getLocation($node["type"]);
		$full_class = "ilObj".$class."ListGUI";

		include_once($location."/class.".$full_class.".php");
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
		// $item_list_gui->setSeparateCommands(true);

		$item_list_gui->enableNotes(true);
		$item_list_gui->enableCopy($objDefinition->allowCopy($node["type"]));

		if($node["type"] == "file")
		{
			$item_list_gui->enableRepositoryTransfer(true);
		}

		$item_list_gui->setContainerObject($this->object_gui);

		if(in_array($node["type"], array("file", "blog")))
		{
			// add "share" link
			$ilCtrl->setParameterByClass("ilworkspaceaccessgui", "wsp_id", $node["wsp_id"]);
			$share_link = $ilCtrl->getLinkTargetByClass(array("ilObj".$class."GUI", "ilworkspaceaccessgui"), "share");
			$item_list_gui->addCustomCommand($share_link, "wsp_permissions");

			// show "shared" status
			if(in_array($node["obj_id"], $this->shared_objects))
			{
				$item_list_gui->addCustomProperty($this->lng->txt("status"), $this->lng->txt("wsp_status_shared"), true, true);
			}
		}

		$html = $item_list_gui->getListItemHTML($node["wsp_id"], $node["obj_id"],
			$node["title"], $node["description"]);

		return $html;
	}


}