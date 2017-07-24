<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Add user to group from awareness tool
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ModulesGroup
 */
class ilGroupAddToGroupActionGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilTree
	 */
	protected $tree;

	/**
	 * Constructor
	 *
	 * @param
	 */
	function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->tpl = $DIC["tpl"];
		$this->ui = $DIC->ui();
		$this->lng = $DIC->language();
		$this->tree = $DIC->repositoryTree();

		$this->lng->loadLanguageModule("grp");
		$this->ctrl->saveParameter($this, "user_id");
	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$ctrl = $this->ctrl;

		$next_class = $ctrl->getNextClass($this);
		$cmd = $ctrl->getCmd("show");

		switch ($next_class)
		{
			default:
				if (in_array($cmd, array("show", "createGroup", "selectGroup", "confirmAddUser", "addUser")))
				{
					$this->$cmd();
				}
		}
	}

	/**
	 * Show
	 */
	function show()
	{
		$tpl = $this->tpl;
		$lng = $this->lng;
		$ctrl = $this->ctrl;

		$toolbar = new ilToolbarGUI();

		// button use existing group
		$url1 = $ctrl->getLinkTarget($this, "selectGroup", "", true, false);
		$button1 = $this->ui->factory()->button()->standard($lng->txt("grp_use_existing"), "#")
			->withOnLoadCode(function ($id) use ($url1) {
				return "$('#$id').on('click', function() {il.Util.ajaxReplaceInner('$url1', 'il_grp_action_modal_content');})";
			});
		$toolbar->addComponent($button1);

		// button create new group
		$url2 = $ctrl->getLinkTarget($this, "createGroup", "", true, false);
		$button2 = $this->ui->factory()->button()->standard($lng->txt("grp_create_new"), "#")
			->withOnLoadCode(function ($id) use ($url2) {
				return "$('#$id').on('click', function() {il.Util.ajaxReplaceInner('$url2', 'il_grp_action_modal_content');})";
			});
		$toolbar->addComponent($button2);

		$this->sendResponse(
			$tpl->getMessageHTML($lng->txt("grp_create_or_use_existing"), "question").
			$toolbar->getHTML()
		);
	}

	/**
	 * Send response
	 *
	 * @param string $a_content
	 */
	function sendResponse($a_content)
	{
		$lng = $this->lng;

		if ($_GET["modal_exists"] == 1)
		{
			echo $this->ui->renderer()->renderAsync($this->ui->factory()->legacy($a_content));
		}
		else
		{
			$mtpl = new ilTemplate("tpl.grp_add_to_grp_modal_content.html", true, true, "./Modules/Group/UserActions");
			$mtpl->setVariable("CONTENT", $a_content);
			$content = $this->ui->factory()->legacy($mtpl->get());
			$modal = $this->ui->factory()->modal()->roundtrip(
				$lng->txt("grp_add_user_to_group"), $content)->withOnLoadCode(function($id) {
				return "il.UI.modal.showModal('$id', {'ajaxRenderUrl':'','keyboard':true});";
			});
			echo $this->ui->renderer()->renderAsync($modal);
		}
		exit;
	}


	/**
	 * Select group
	 *
	 * @param
	 * @return
	 */
	function selectGroup()
	{
		$tree = $this->tree;

		include_once("./Modules/Group/UserActions/classes/class.ilGroupActionTargetExplorerGUI.php");
		$exp = new ilGroupActionTargetExplorerGUI($this, "selectGroup");

		$exp->setClickableType("grp");
		$exp->setTypeWhiteList(array("root", "cat", "crs", "fold", "grp"));
		$exp->setPathOpen((int) $tree->readRootId());

		if (!$exp->handleCommand())
		{
			echo $exp->getHTML();
		}

		exit;
	}

	/**
	 * Create group
	 *
	 * @param
	 * @return
	 */
	function createGroup()
	{
		echo "Create Group";
		exit;
	}

	/**
	 * Confirm add user to group
	 *
	 * @param
	 * @return
	 */
	function confirmAddUser()
	{
		$ctrl = $this->ctrl;
		$tpl = $this->tpl;
		$lng = $this->lng;

		// button create new group
		$ctrl->setParameter($this, "grp_act_ref_id", $_GET["grp_act_ref_id"]);
		$url = $ctrl->getLinkTarget($this, "addUser", "", true, false);
		$button = $this->ui->factory()->button()->standard($lng->txt("grp_add_user"), "#")
			->withOnLoadCode(function ($id) use ($url) {
				return "$('#$id').on('click', function() {il.Util.ajaxReplaceInner('$url', 'il_grp_action_modal_content');})";
			});

		echo
			$tpl->getMessageHTML($lng->txt("grp_sure_add_user_to_group")."<br>".
				$lng->txt("obj_user").": ".ilUserUtil::getNamePresentation($_GET["user_id"])."<br>".
				$lng->txt("obj_grp").": ".ilObject::_lookupTitle(ilObject::_lookupObjId($_GET["grp_act_ref_id"])) , "question").
			$this->ui->renderer()->renderAsync($button);
		exit;
	}

	/**
	 * Add user
	 *
	 * @param
	 */
	function addUser()
	{
		$tpl = $this->tpl;
		$lng = $this->lng;

		$user_id = (int) $_GET["user_id"];

		// @todo: check permission

		include_once("./Modules/Group/classes/class.ilObjGroup.php");
		$group = new ilObjGroup((int) $_GET["grp_act_ref_id"]);

		include_once './Services/Membership/classes/class.ilParticipants.php';
		$participants = ilParticipants::getInstanceByObjId($group->getId());

		$participants->add($user_id, IL_GRP_MEMBER);

		include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
		$participants->sendNotification(
			ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER,
			$user_id
		);

		echo $tpl->getMessageHTML($lng->txt("grp_user_been_added"), "success");
		exit;
	}


}

?>