<?php

declare(strict_types=1);

use ILIAS\DI\UIServices;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Add user to group from awareness tool
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ModulesGroup
 */
class ilGroupAddToGroupActionGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected UIServices $ui;
    protected ilLanguage $lng;
    protected ilTree $tree;
    protected ilObjUser $user;


    protected GlobalHttpState $http;
    protected Factory $refinery;



    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        $this->user = $DIC->user();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();


        $this->lng->loadLanguageModule("grp");
        $this->ctrl->saveParameter($this, array("user_id", "modal_exists"));
    }

    protected function initGroupRefIdFromQuery(): int
    {
        $ref_id = 0;
        if ($this->http->wrapper()->query()->has('grp_act_par_ref_id')) {
            $ref_id = $this->http->wrapper()->query()->retrieve(
                'grp_act_par_ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return $ref_id;
    }

    protected function initUserIdFromQuery(): int
    {
        $user_id = 0;
        if ($this->http->wrapper()->query()->has('user_id')) {
            $user_id = $this->http->wrapper()->query()->retrieve(
                'user_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return $user_id;
    }

    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;
        $user = $this->user;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        if ($cmd == "show") {
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
    public function show(): void
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $toolbar = new ilToolbarGUI();

        // button use existing group
        $url1 = $ctrl->getLinkTarget($this, "selectGroup", "", true);
        $button1 = $this->ui->factory()->button()->standard($lng->txt("grp_use_existing"), "#")
            ->withOnLoadCode(function ($id) use ($url1) {
                return "$('#$id').on('click', function() {il.Util.ajaxReplaceInner('$url1', 'il_grp_action_modal_content'); return false;})";
            });
        $toolbar->addComponent($button1);

        // button create new group
        $url2 = $ctrl->getLinkTarget($this, "selectParent", "", true);
        $button2 = $this->ui->factory()->button()->standard($lng->txt("grp_create_new"), "#")
            ->withOnLoadCode(function ($id) use ($url2) {
                return "$('#$id').on('click', function() {il.Util.ajaxReplaceInner('$url2', 'il_grp_action_modal_content'); return false;})";
            });
        $toolbar->addComponent($button2);

        $this->sendResponse(
            ilUtil::getSystemMessageHTML($lng->txt("grp_create_or_use_existing"), "question") .
            $toolbar->getHTML()
        );
    }

    public function sendResponse(string $a_content): void
    {
        $lng = $this->lng;

        $modal_exists = false;
        if ($this->http->wrapper()->query()->has('modal_exists')) {
            $modal_exists = (bool) $this->http->wrapper()->query()->retrieve(
                'modal_exists',
                $this->refinery->kindlyTo()->int()
            );
        }
        if ($modal_exists) {
            echo $this->ui->renderer()->renderAsync($this->ui->factory()->legacy($a_content));
        } else {
            $mtpl = new ilTemplate("tpl.grp_add_to_grp_modal_content.html", true, true, "./Modules/Group/UserActions");
            $mtpl->setVariable("CONTENT", $a_content);
            $content = $this->ui->factory()->legacy($mtpl->get());
            $modal = $this->ui->factory()->modal()->roundtrip(
                $lng->txt("grp_add_user_to_group"),
                $content
            )->withOnLoadCode(function ($id) {
                return "il.UI.modal.showModal('$id', {'ajaxRenderUrl':'','keyboard':true}, {id: '$id'});";
            });
            echo $this->ui->renderer()->renderAsync($modal);
        }
        exit;
    }


    public function selectGroup(): void
    {
        $tree = $this->tree;

        $exp = new ilGroupActionTargetExplorerGUI($this, "selectGroup");

        $exp->setClickableType("grp");
        $exp->setTypeWhiteList(array("root", "cat", "crs", "fold", "grp"));
        $exp->setPathOpen($tree->readRootId());

        if (!$exp->handleCommand()) {
            $this->sendResponse($exp->getHTML());
        }
        exit;
    }

    public function confirmAddUser(): void
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $ref_id = $this->initGroupRefIdFromQuery();
        $user_id = $this->initUserIdFromQuery();

        $participants = ilParticipants::getInstanceByObjId(ilObject::_lookupObjId($ref_id));
        if ($participants->isMember($user_id)) {
            $url = $ctrl->getLinkTarget($this, "selectGroup", "", true);
            $button = $this->ui->factory()->button()->standard($lng->txt("back"), "#")
                ->withOnLoadCode(function ($id) use ($url) {
                    return "$('#$id').on('click', function() {il.Util.ajaxReplaceInner('$url', 'il_grp_action_modal_content'); return false;})";
                });

            echo
                ilUtil::getSystemMessageHTML($lng->txt("grp_user_already_in_group") . "<br>" .
                    $lng->txt("obj_user") . ": " . ilUserUtil::getNamePresentation($user_id) . "<br>" .
                    $lng->txt("obj_grp") . ": " . ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id)), "failure") .
                $this->ui->renderer()->renderAsync($button);
            exit;
        }


        // button create new group
        $ctrl->setParameter($this, "grp_act_ref_id", $ref_id);
        $url = $ctrl->getLinkTarget($this, "addUser", "", true);
        $button = $this->ui->factory()->button()->standard($lng->txt("grp_add_user"), "#")
            ->withOnLoadCode(function ($id) use ($url) {
                return "$('#$id').on('click', function() {il.Util.ajaxReplaceInner('$url', 'il_grp_action_modal_content'); return false;})";
            });

        echo
            ilUtil::getSystemMessageHTML($lng->txt("grp_sure_add_user_to_group") . "<br>" .
                $lng->txt("obj_user") . ": " . ilUserUtil::getNamePresentation($user_id) . "<br>" .
                $lng->txt("obj_grp") . ": " . ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id)), "question") .
            $this->ui->renderer()->renderAsync($button);
        exit;
    }

    public function addUser(): void
    {
        $lng = $this->lng;

        $ref_id = $this->initGroupRefIdFromQuery();
        $user_id = $this->initUserIdFromQuery();

        // @todo: check permission

        $group = new ilObjGroup($ref_id);
        $participants = ilParticipants::getInstanceByObjId($group->getId());

        $participants->add($user_id, ilParticipants::IL_GRP_MEMBER);

        $participants->sendNotification(
            ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER,
            $user_id
        );

        echo ilUtil::getSystemMessageHTML($lng->txt("grp_user_been_added"), "success");
        echo "<script>setTimeout(function (){ il.Group.UserActions.closeModal();}, 1000);</script>";
        exit;
    }

    public function selectParent(): void
    {
        $tree = $this->tree;
        $lng = $this->lng;

        $exp = new ilGroupActionTargetExplorerGUI($this, "selectParent", true);

        $exp->setTypeWhiteList(array("root", "cat", "crs"));
        $exp->setPathOpen($tree->readRootId());

        if (!$exp->handleCommand()) {
            $this->sendResponse(ilUtil::getSystemMessageHTML($lng->txt("grp_no_perm_to_add_create_first")) .
                $exp->getHTML());
        }

        exit;
    }

    public function createGroup($form = null): void
    {
        $lng = $this->lng;

        $ref_id = $this->initGroupRefIdFromQuery();

        if ($form == null) {
            $form = $this->getGroupCreationForm();
        }
        $this->ctrl->saveParameter($this, "grp_act_par_ref_id");
        $form->setFormAction($this->ctrl->getLinkTarget($this, "confirmCreateGroupAndAddUser", "", true));

        echo ilUtil::getSystemMessageHTML(str_replace("%1", ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id)), $lng->txt("grp_create_new_grp_in")), "info") .
            $form->getHTML();
        exit;
    }


    protected function getGroupCreationForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;

        $ref_id = $this->initGroupRefIdFromQuery();

        $group_gui = new ilObjGroupGUI("", 0, true);
        $group_gui->setCreationMode();
        $form = $group_gui->initForm("create", true);
        $form->clearCommandButtons();
        $form->addCommandButton("save", $lng->txt("grp_next"));
        $form->setShowTopButtons(false);
        return $form;
    }

    public function confirmCreateGroupAndAddUser(): void
    {
        $lng = $this->lng;

        $user_id = $this->initUserIdFromQuery();
        $title = '';
        if ($this->http->wrapper()->post()->has('title')) {
            $title = $this->http->wrapper()->post()->retrieve(
                'title',
                $this->refinery->kindlyTo()->string()
            );
        }
        $form = $this->getGroupCreationForm();



        if (!$form->checkInput()) {
            $this->createGroup($form);
            return;
        }

        $this->ctrl->saveParameter($this, "grp_act_par_ref_id");
        $form->setFormAction($this->ctrl->getLinkTarget($this, "createGroupAndAddUser", "", true));
        $form->setValuesByPost();

        $button = $this->ui->factory()->button()->standard($lng->txt("grp_create_and_add_user"), "#")
            ->withOnLoadCode(function ($id) {
                return "$('#$id').on('click', function(e) {il.Group.UserActions.createGroup(e);})";
            });

        echo
            ilUtil::getSystemMessageHTML($lng->txt("grp_sure_create_group_add_user") . "<br>" .
                $lng->txt("obj_user") . ": " . ilUserUtil::getNamePresentation($user_id) . "<br>" .
                $lng->txt("obj_grp") . ": " . $title, "question") .
            "<div class='ilNoDisplay'>" . $form->getHTML() . "</div>" .
            "<div class='ilRight'>" . $this->ui->renderer()->renderAsync($button) . "</div>";

        exit;
    }

    public function createGroupAndAddUser(): void
    {
        $lng = $this->lng;

        $user_id = $this->initUserIdFromQuery();
        $ref_id = $this->initGroupRefIdFromQuery();
        $form = $this->getGroupCreationForm();

        $form->checkInput();

        $group_gui = new ilObjGroupGUI("", 0, true);

        // create instance
        $newObj = new ilObjGroup();
        $newObj->setType("grp");
        $newObj->setTitle($form->getInput("title"));
        $newObj->setDescription($form->getInput("desc"));
        $newObj->create();

        $group_gui->putObjectInTree($newObj, $ref_id);

        // apply didactic template?
        $dtpl = $group_gui->getDidacticTemplateVar("dtpl");
        if ($dtpl) {
            $newObj->applyDidacticTemplate($dtpl);
        }

        $group_gui->afterSave($newObj, false);


        $participants = ilParticipants::getInstanceByObjId($newObj->getId());

        $participants->add($user_id, ilParticipants::IL_GRP_MEMBER);

        $participants->sendNotification(
            ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER,
            $user_id
        );


        $url = ilLink::_getLink($newObj->getRefId());
        echo ilUtil::getSystemMessageHTML($lng->txt("grp_created_and_user_been_added"), "success");
        echo "<script>setTimeout(function (){ window.location.replace('$url');}, 1500);</script>";
        exit;
    }
}
