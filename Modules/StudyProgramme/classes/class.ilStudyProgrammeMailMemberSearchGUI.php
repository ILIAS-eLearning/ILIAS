<?php declare(strict_types=1);

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

class ilStudyProgrammeMailMemberSearchGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilAccessHandler $access;
    protected ILIAS\HTTP\Wrapper\WrapperFactory $http_wrapper;
    protected ILIAS\Refinery\Factory $refinery;

    protected array $assignments = [];
    private ?string $back_target = null;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilLanguage $lng,
        ilAccessHandler $access,
        ILIAS\HTTP\Wrapper\WrapperFactory $http_wrapper,
        ILIAS\Refinery\Factory $refinery
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->access = $access;
        $this->http_wrapper = $http_wrapper;
        $this->refinery = $refinery;

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
        $cmd = $this->ctrl->getCmd();

        $this->ctrl->setReturn($this, '');

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
    }

    protected function showSelectableUsers() : void
    {
        $this->tpl->loadStandardTemplate();
        $tbl = new ilStudyProgrammeMailMemberSearchTableGUI($this, $this->getRootPrgObjId(), 'showSelectableUsers');
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
        $user_ids = [];
        if ($this->http_wrapper->post()->has("user_ids")) {
            $user_ids = $this->http_wrapper->post()->retrieve(
                "user_ids",
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }

        if (!count($user_ids)) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("no_checkbox"));
            $this->showSelectableUsers();
            return false;
        }

        $rcps = array();
        foreach ($user_ids as $usr_id) {
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

    protected function getRootPrgObjId() : int
    {
        $assignments = $this->getAssignments();
        $assignment = array_shift($assignments);
        return (int) $assignment->getRootId();
    }
}
