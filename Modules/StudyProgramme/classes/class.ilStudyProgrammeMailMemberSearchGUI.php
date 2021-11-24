<?php declare(strict_types=1);

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

class ilStudyProgrammeMailMemberSearchGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilAccessHandler $access;

    protected array $assignments = [];
    private ?string $back_target = null;

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

    public function getBackTarget() : ?string
    {
        return $this->back_target;
    }

    public function setBackTarget(string $target) : void
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

    protected function getPRGMembersGUI() : ilObjStudyProgrammeMembersGUI
    {
        return ilStudyProgrammeDIC::dic()['ilObjStudyProgrammeMembersGUI'];
    }

    protected function sendMailToSelectedUsers() : bool
    {
        if (!isset($_POST['user_ids']) || !count($_POST['user_ids'])) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("no_checkbox"));
            $this->showSelectableUsers();
            return false;
        }

        $rcps = array();
        foreach ($_POST['user_ids'] as $usr_id) {
            $rcps[] = ilObjUser::_lookupLogin($usr_id);
        }

        if (!count(array_filter($rcps))) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("no_checkbox"));
            $this->showSelectableUsers();
            return false;
        }
        ilMailFormCall::setRecipients($rcps);

        $members_gui = $this->getPRGMembersGUI();
        $this->ctrl->redirectToURL(ilMailFormCall::getRedirectTarget(
            $members_gui,
            'view',
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
        $ref_id = $this->getRootPrgRefId();
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
        $back_target = $this->getBackTarget();
        if (is_null($back_target)) {
            throw new LogicException("Can't redirect. No back target given.");
        }

        $this->ctrl->redirectToURL($back_target);
    }

    protected function createMailSignature() : string
    {
        $link = chr(13) . chr(10) . chr(13) . chr(10);
        $link .= $this->lng->txt('prg_mail_permanent_link');
        $link .= chr(13) . chr(10) . chr(13) . chr(10);
        $link .= ilLink::_getLink($this->getRootPrgRefId());
        return rawurlencode(base64_encode($link));
    }

    protected function getRootPrgRefId() : int
    {
        $assignments = $this->getAssignments();
        $assignment = array_shift($assignments);
        return ilObjStudyProgramme::getRefIdFor($assignment->getRootId());
    }
}
