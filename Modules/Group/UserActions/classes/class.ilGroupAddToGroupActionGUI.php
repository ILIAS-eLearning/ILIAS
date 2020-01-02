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
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        $this->user = $DIC->user();

        $this->lng->loadLanguageModule("grp");
        $this->ctrl->saveParameter($this, array("user_id", "modal_exists"));
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ctrl = $this->ctrl;
        $user = $this->user;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        if ($cmd == "show") {
            include_once("./Modules/Group/UserActions/classes/class.ilGroupUserActionProvider.php");
            $ca = ilGroupUserActionProvider::getCommandAccess($user->getId());
            if (count($ca) == 0) {
                return;
            }
            if (count($ca) == 1) {
                switch (current($ca)) {
                    case "create_grp":
                        $cmd = "selectParent";
                        break;

                    case "manage_members":
                        $cmd = "selectGroup";
                        break;

                    default:
                        return;
                }
            }
        }

        switch ($next_class) {
            default:
                if (in_array($cmd, array("show", "selectGroup", "confirmAddUser", "addUser",
                    "selectParent", "createGroup", "confirmCreateGroupAndAddUser", "createGroupAndAddUser"))) {
                    $ctrl->setParameter($this, "modal_exists", 1);
                    $this->$cmd();
                }
        }
    }

    /**
     * Show
     */
    public function show()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $toolbar = new ilToolbarGUI();

        // button use existing group
        $url1 = $ctrl->getLinkTarget($this, "selectGroup", "", true, false);
        $button1 = $this->ui->factory()->button()->standard($lng->txt("grp_use_existing"), "#")
            ->withOnLoadCode(function ($id) use ($url1) {
                return "$('#$id').on('click', function() {il.Util.ajaxReplaceInner('$url1', 'il_grp_action_modal_content'); return false;})";
            });
        $toolbar->addComponent($button1);

        // button create new group
        $url2 = $ctrl->getLinkTarget($this, "selectParent", "", true, false);
        $button2 = $this->ui->factory()->button()->standard($lng->txt("grp_create_new"), "#")
            ->withOnLoadCode(function ($id) use ($url2) {
                return "$('#$id').on('click', function() {il.Util.ajaxReplaceInner('$url2', 'il_grp_action_modal_content'); return false;})";
            });
        $toolbar->addComponent($button2);

        $this->sendResponse(
            $tpl->getMessageHTML($lng->txt("grp_create_or_use_existing"), "question") .
            $toolbar->getHTML()
        );
    }

    /**
     * Send response
     *
     * @param string $a_content
     */
    public function sendResponse($a_content)
    {
        $lng = $this->lng;

        if ($_GET["modal_exists"] == 1) {
            echo $this->ui->renderer()->renderAsync($this->ui->factory()->legacy($a_content));
        } else {
            $mtpl = new ilTemplate("tpl.grp_add_to_grp_modal_content.html", true, true, "./Modules/Group/UserActions");
            $mtpl->setVariable("CONTENT", $a_content);
            $content = $this->ui->factory()->legacy($mtpl->get());
            $modal = $this->ui->factory()->modal()->roundtrip(
                $lng->txt("grp_add_user_to_group"),
                $content
            )->withOnLoadCode(function ($id) {
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
    public function selectGroup()
    {
        $tree = $this->tree;

        include_once("./Modules/Group/UserActions/classes/class.ilGroupActionTargetExplorerGUI.php");
        $exp = new ilGroupActionTargetExplorerGUI($this, "selectGroup");

        $exp->setClickableType("grp");
        $exp->setTypeWhiteList(array("root", "cat", "crs", "fold", "grp"));
        $exp->setPathOpen((int) $tree->readRootId());

        if (!$exp->handleCommand()) {
            $this->sendResponse($exp->getHTML());
        }

        exit;
    }

    /**
     * Confirm add user to group
     *
     * @param
     * @return
     */
    public function confirmAddUser()
    {
        $ctrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        include_once("./Services/Membership/classes/class.ilParticipants.php");
        include_once './Services/Membership/classes/class.ilParticipants.php';
        $participants = ilParticipants::getInstanceByObjId(ilObject::_lookupObjId((int) $_GET["grp_act_ref_id"]));
        if ($participants->isMember((int) $_GET["user_id"])) {
            $url = $ctrl->getLinkTarget($this, "selectGroup", "", true, false);
            $button = $this->ui->factory()->button()->standard($lng->txt("back"), "#")
                ->withOnLoadCode(function ($id) use ($url) {
                    return "$('#$id').on('click', function() {il.Util.ajaxReplaceInner('$url', 'il_grp_action_modal_content'); return false;})";
                });

            echo
                $tpl->getMessageHTML($lng->txt("grp_user_already_in_group") . "<br>" .
                    $lng->txt("obj_user") . ": " . ilUserUtil::getNamePresentation((int) $_GET["user_id"]) . "<br>" .
                    $lng->txt("obj_grp") . ": " . ilObject::_lookupTitle(ilObject::_lookupObjId($_GET["grp_act_ref_id"])), "failure") .
                $this->ui->renderer()->renderAsync($button);
            exit;
        }


        // button create new group
        $ctrl->setParameter($this, "grp_act_ref_id", $_GET["grp_act_ref_id"]);
        $url = $ctrl->getLinkTarget($this, "addUser", "", true, false);
        $button = $this->ui->factory()->button()->standard($lng->txt("grp_add_user"), "#")
            ->withOnLoadCode(function ($id) use ($url) {
                return "$('#$id').on('click', function() {il.Util.ajaxReplaceInner('$url', 'il_grp_action_modal_content'); return false;})";
            });

        echo
            $tpl->getMessageHTML($lng->txt("grp_sure_add_user_to_group") . "<br>" .
                $lng->txt("obj_user") . ": " . ilUserUtil::getNamePresentation((int) $_GET["user_id"]) . "<br>" .
                $lng->txt("obj_grp") . ": " . ilObject::_lookupTitle(ilObject::_lookupObjId($_GET["grp_act_ref_id"])), "question") .
            $this->ui->renderer()->renderAsync($button);
        exit;
    }

    /**
     * Add user
     *
     * @param
     */
    public function addUser()
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
        echo "<script>setTimeout(function (){ il.Group.UserActions.closeModal();}, 1000);</script>";
        exit;
    }

    /**
     * Select group
     *
     * @param
     * @return
     */
    public function selectParent()
    {
        $tree = $this->tree;
        $lng = $this->lng;
        $tpl = $this->tpl;

        include_once("./Modules/Group/UserActions/classes/class.ilGroupActionTargetExplorerGUI.php");
        $exp = new ilGroupActionTargetExplorerGUI($this, "selectParent", true);

        $exp->setTypeWhiteList(array("root", "cat", "crs"));
        $exp->setPathOpen((int) $tree->readRootId());

        if (!$exp->handleCommand()) {
            $this->sendResponse($tpl->getMessageHTML($lng->txt("grp_no_perm_to_add_create_first"), "info") .
                $exp->getHTML());
        }

        exit;
    }

    /**
     * Create group
     *
     * @param
     * @return
     */
    public function createGroup($form = null)
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        if ($form == null) {
            $form = $this->getGroupCreationForm();
        }
        $this->ctrl->saveParameter($this, "grp_act_par_ref_id");
        $form->setFormAction($this->ctrl->getLinkTarget($this, "confirmCreateGroupAndAddUser", "", true, false));

        echo $tpl->getMessageHTML(str_replace("%1", ilObject::_lookupTitle(ilObject::_lookupObjId($_GET["grp_act_par_ref_id"])), $lng->txt("grp_create_new_grp_in")), "info") .
            $form->getHTML();
        exit;
    }

    protected function getGroupCreationForm()
    {
        $lng = $this->lng;

        $group_gui = new ilObjGroupGUI("", 0, true);
        $group_gui->setCreationMode(true);
        // workaround for bug #22748 (which is triggered, if a didactic template for groups exist which is limited to a rep node)
        $ref_id = $_GET["ref_id"];
        $_GET["ref_id"] = $_GET["grp_act_par_ref_id"];
        $form = $group_gui->initForm("create", true);
        $_GET["ref_id"] = $ref_id;
        $form->clearCommandButtons();
        $form->addCommandButton("save", $lng->txt("grp_next"));
        $form->setShowTopButtons(false);
        return $form;
    }

    /**
     * Save group
     *
     * @param
     * @return
     */
    public function confirmCreateGroupAndAddUser()
    {
        $ctrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $form = $this->getGroupCreationForm();
        if (!$form->checkInput()) {
            $this->createGroup($form);
            return;
        }

        $this->ctrl->saveParameter($this, "grp_act_par_ref_id");
        $form->setFormAction($this->ctrl->getLinkTarget($this, "createGroupAndAddUser", "", true, false));
        $form->setValuesByPost();

        $button = $this->ui->factory()->button()->standard($lng->txt("grp_create_and_add_user"), "#")
            ->withOnLoadCode(function ($id) {
                return "$('#$id').on('click', function(e) {il.Group.UserActions.createGroup(e);})";
            });

        echo
            $tpl->getMessageHTML($lng->txt("grp_sure_create_group_add_user") . "<br>" .
                $lng->txt("obj_user") . ": " . ilUserUtil::getNamePresentation($_GET["user_id"]) . "<br>" .
                $lng->txt("obj_grp") . ": " . $_POST["title"], "question") .
            "<div class='ilNoDisplay'>" . $form->getHTML() . "</div>" .
            "<div class='ilRight'>" . $this->ui->renderer()->renderAsync($button) . "</div>";

        exit;
    }

    /**
     * Create group and add user
     *
     * @param
     * @return
     */
    public function createGroupAndAddUser()
    {
        $lng = $this->lng;
        $tpl = $this->tpl;

        $user_id = (int) $_GET["user_id"];
        $form = $this->getGroupCreationForm();

        $form->checkInput();

        $group_gui = new ilObjGroupGUI("", 0, true);

        // create instance
        include_once("./Modules/Group/classes/class.ilObjGroup.php");
        $newObj = new ilObjGroup();
        $newObj->setType("grp");
        $newObj->setTitle($form->getInput("title"));
        $newObj->setDescription($form->getInput("desc"));
        $newObj->create();

        $group_gui->putObjectInTree($newObj, (int) $_GET["grp_act_par_ref_id"]);

        // apply didactic template?
        $dtpl = $group_gui->getDidacticTemplateVar("dtpl");
        if ($dtpl) {
            $newObj->applyDidacticTemplate($dtpl);
        }

        $group_gui->afterSave($newObj, false);


        include_once './Services/Membership/classes/class.ilParticipants.php';
        $participants = ilParticipants::getInstanceByObjId($newObj->getId());

        $participants->add($user_id, IL_GRP_MEMBER);

        include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
        $participants->sendNotification(
            ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER,
            $user_id
        );


        include_once("./Services/Link/classes/class.ilLink.php");
        $url = ilLink::_getLink($newObj->getRefId());
        echo $tpl->getMessageHTML($lng->txt("grp_created_and_user_been_added"), "success");
        echo "<script>setTimeout(function (){ window.location.replace('$url');}, 1500);</script>";
        exit;
    }
}
