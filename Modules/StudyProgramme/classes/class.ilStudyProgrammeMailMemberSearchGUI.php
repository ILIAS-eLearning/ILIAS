<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

class ilStudyProgrammeMailMemberSearchGUI
{
    /**
     * @var ilObjGroupGUI|ilObjCourseGUI
     */
    protected $gui;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var array
     */
    protected $assignments;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilLanguage $lng,
        ilAccessHandler $access
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->access = $access;

        $this->lng->loadLanguageModule('mail');
        $this->lng->loadLanguageModule('search');
    }

    public function getAssignments() : array
    {
        return $this->assignments;
    }

    public function setAssignments(array $assignments) : void
    {
        $this->assignments = $assignments;
    }

    public function getBackTarget() : string
    {
        return $this->back_target;
    }

    public function setBackTarget(string $target)
    {
        $this->back_target = $target;
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->ctrl->setReturn($this, '');

        switch ($next_class) {
            default:
                switch ($cmd) {
                    case 'sendMailToSelectedUsers':
                        $this->sendMailToSelectedUsers();
                        break;
                    case 'showSelectableUsers':
                    case 'members':
                        $this->showSelectableUsers();
                        break;
                    case 'cancel':
                        $this->redirectToParent();
                        break;
                    default:
                        throw new Exception('Unknown command ' . $cmd);
                        break;
                }
                break;
        }
    }

    protected function showSelectableUsers() : void
    {
        $this->tpl->loadStandardTemplate();
        $tbl = new ilStudyProgrammeMailMemberSearchTableGUI($this, 'showSelectableUsers');
        $tbl->setData($this->getProcessData());

        $this->tpl->setContent($tbl->getHTML());
    }

    protected function getProcessData() : array
    {
        $data = [];

        foreach ($this->getAssignments() as $assignment) {
            $user_id = $assignment->getUserId();
            $name = ilObjUser::_lookupName($user_id);
            $login = ilObjUser::_lookupLogin($user_id);

            $publicName = '';
            if (in_array(ilObjUser::_lookupPref($user_id, 'public_profile'), array('g', 'y'))) {
                $publicName = $name['lastname'] . ', ' . $name['firstname'];
            }

            $data[$user_id]['user_id'] = $user_id;
            $data[$user_id]['login'] = $login;
            $data[$user_id]['name'] = $publicName;
        }

        return $data;
    }

    protected function sendMailToSelectedUsers() : bool
    {
        if (!isset($_POST['user_ids']) || !count($_POST['user_ids'])) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"));
            $this->showSelectableUsers();
            return false;
        }

        $rcps = array();
        foreach ($_POST['user_ids'] as $usr_id) {
            $rcps[] = ilObjUser::_lookupLogin($usr_id);
        }

        if (!count(array_filter($rcps))) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"));
            $this->showSelectableUsers();
            return false;
        }
        ilMailFormCall::setRecipients($rcps);

        ilUtil::redirect(ilMailFormCall::getRedirectTarget(
            $this,
            'members',
            array(),
            array(
                'type' => 'new',
                'sig' => $this->createMailSignature()
            ),
            $this->generateContextArray()
        ));

        return true;
    }

    protected function generateContextArray() : array
    {
        $context_array = [];
        $ref_id = $this->getObjectRefId();
        $type = ilObject::_lookupType($ref_id, true);
        switch ($type) {
            case 'prg':
                if ($this->access->checkAccess('write', "", $ref_id)) {
                    $context_array = array(
                        ilMailFormCall::CONTEXT_KEY => ilStudyProgrammeMailTemplateContext::ID,
                        'ref_id' => $ref_id,
                        'ts' => time()
                    );
                }
                break;
        }
        return $context_array;
    }

    protected function redirectToParent() : void
    {
        ilUtil::redirect($this->getBackTarget());
    }

    protected function createMailSignature() : string
    {
        $link = chr(13) . chr(10) . chr(13) . chr(10);
        $link .= $this->lng->txt('prg_mail_permanent_link');
        $link .= chr(13) . chr(10) . chr(13) . chr(10);
        include_once 'Services/Link/classes/class.ilLink.php';
        $link .= ilLink::_getLink($this->getObjectRefId());
        return rawurlencode(base64_encode($link));
    }

    protected function getObjectRefId() : int
    {
        $assignment = array_shift($this->getAssignments());
        $obj = $assignment->getStudyProgramme();
        return $obj->getRefId();
    }
}
