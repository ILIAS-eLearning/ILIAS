<?php
/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use GuzzleHttp\Psr7\ServerRequest;
use ILIAS\UI\Component\Input;
use ILIAS\UI\Component\MessageBox;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Renderer;
use ILIAS\Data;
use ILIAS\Refinery;

class ilIndividualAssessmentMemberGUI extends AbstractCtrlAwareUploadHandler
{
    const CMD_VIEW = 'view';
    const CMD_EDIT = 'edit';
    const CMD_UPDATE = 'update';
    const CMD_FINALIZE = 'finalize';
    const CMD_FINALIZE_CONFIRMATION = 'finalizeConfirmation';
    const CMD_AMEND = 'amend';
    const CMD_SAVE_AMEND = "saveAmend";
    const CMD_DOWNLOAD_FILE = "downloadFile";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilGlobalPageTemplate
     */
    protected $tpl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var Input\Factory
     */
    protected $input_factory;

    /**
     * @var MessageBox\Factory
     */
    protected $messagebox_factory;

    /**
     * @var MessageBox\Factory
     */
    protected $button_factory;

    /**
     * @var Refinery\Factory
     */
    protected $refinery_factory;

    /**
     * @var Data\Factory
     */
    protected $data_factory;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var ServerRequest
     */
    protected $request;

    /**
     * @var ilObjIndividualAssessment
     */
    protected $object;

    /**
     * @var ilIndividualAssessmentMembersGUI
     */
    protected $parent_gui;

    /**
     * @var ilIndividualAssessmentAccessHandler
     */
    protected $iass_access;

    /**
     * @var ilIndividualAssessmentPrimitiveInternalNotificator
     */
    protected $notificator;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilErrorHandling
     */
    protected $error_object;

    public function __construct(
        ilCtrl $ctrl,
        ilLanguage $lng,
        ilGlobalPageTemplate $tpl,
        ilObjUser $user,
        Input\Factory $input_factory,
        MessageBox\Factory $messagebox_factory,
        Button\Factory $button_factory,
        Refinery\Factory $refinery_factory,
        Data\Factory $data_factory,
        Renderer $renderer,
        ServerRequest $request,
        ilIndividualAssessmentPrimitiveInternalNotificator $notificator,
        ilToolbarGUI $toolbar,
        ilObjIndividualAssessment $object,
        ilErrorHandling $error_object
    ) {
        parent::__construct();

        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->user = $user;
        $this->input_factory = $input_factory;
        $this->messagebox_factory = $messagebox_factory;
        $this->button_factory = $button_factory;
        $this->refinery_factory = $refinery_factory;
        $this->data_factory = $data_factory;
        $this->renderer = $renderer;
        $this->request = $request;
        $this->notificator = $notificator;
        $this->toolbar = $toolbar;
        $this->object = $object;
        $this->error_object = $error_object;
    }

    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_VIEW:
            case self::CMD_UPDATE:
            case self::CMD_EDIT:
            case self::CMD_FINALIZE:
            case self::CMD_FINALIZE_CONFIRMATION:
            case self::CMD_AMEND:
            case self::CMD_SAVE_AMEND:
            case self::CMD_DOWNLOAD_FILE:
                $this->$cmd();
                break;
            case AbstractCtrlAwareUploadHandler::CMD_UPLOAD:
            case AbstractCtrlAwareUploadHandler::CMD_REMOVE:
            case AbstractCtrlAwareUploadHandler::CMD_INFO:
                parent::executeCommand();
                break;
            default:
                throw new LogicException("Unknown command $cmd");
        }
    }

    protected function view()
    {
        if (!$this->mayBeViewed()) {
            $this->handleAccessViolation();
            return;
        }
        $form = $this->buildForm('', false);
        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function edit()
    {
        if (!$this->mayBeEdited()) {
            $this->handleAccessViolation();
            return;
        }

        $this->setToolbar();
        $form = $this->buildForm($this->getFormActionForCommand(self::CMD_UPDATE), true);
        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function update()
    {
        $form = $this
            ->buildForm($this->getFormActionForCommand(self::CMD_UPDATE), true)
            ->withRequest($this->request)
        ;

        /** @var ilIndividualAssessmentUserGrading $grading */
        $grading = $form->getData();
        if (is_null($grading)) {
            $this->tpl->setContent($this->renderer->render($form));
            return;
        }

        if ($grading->getFile() == '') {
            $storage = $this->getUserFileStorage();
            $storage->deleteCurrentFile();
        }

        if ($grading->isFinalized()) {
            $not_finalized_grading = $grading->withFinalized(false);
            $this->saveMember($not_finalized_grading);
            $this->finalizeConfirmation();
            return;
        }

        $this->saveMember($grading);

        if ($this->getObject()->isActiveLP()) {
            ilIndividualAssessmentLPInterface::updateLPStatusOfMember($this->getMember());
        }

        ilUtil::sendSuccess($this->lng->txt('iass_membership_saved'), true);
        $this->redirect(self::CMD_EDIT);

    }

    protected function amend()
    {
        if (!$this->mayBeAmended()) {
            $this->handleAccessViolation();
            return;
        }

        $this->setToolbar();
        $form = $this->buildForm($this->getFormActionForCommand(self::CMD_SAVE_AMEND), true, true);
        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function getFormActionForCommand(string $cmd) : string
    {
        $this->ctrl->setParameterByClass(self::class, 'usr_id', $this->getExaminee()->getId());
        $action = $this->ctrl->getFormAction($this, $cmd);
        $this->ctrl->clearParameterByClass(self::class, 'usr_id');

        return $action;
    }

    protected function downloadFile()
    {
        $path = $this->getUserFileStorage()->getFilePath();
        $file_name = $this->getMember()->fileName();
        ilUtil::deliverFile($path, $file_name);
    }

    protected function saveAmend()
    {
        if (!$this->mayBeAmended()) {
            $this->handleAccessViolation();
            return;
        }

        $form = $this
            ->buildForm($this->ctrl->getFormAction($this, self::CMD_AMEND), true, true)
            ->withRequest($this->request)
        ;

        $grading = $form->getData();

        if (!is_null($grading)) {
            if ($grading->getFile() == '') {
                $storage = $this->getUserFileStorage();
                $storage->deleteCurrentFile();
            }

            $this->saveMember($grading, true, true);

            if ($this->getObject()->isActiveLP()) {
                ilIndividualAssessmentLPInterface::updateLPStatusOfMember($this->getMember());
            }

            ilUtil::sendSuccess($this->lng->txt('iass_amend_saved'), true);
            $this->redirect(self::CMD_AMEND);
        }
    }

    protected function buildForm(
        string $form_action,
        bool $may_be_edited,
        bool $amend = false
    ) : ILIAS\UI\Component\Input\Container\Form\Form {
        $section = $this->getMember()->getGrading()->toFormInput(
            $this->input_factory->field(),
            $this->data_factory,
            $this->lng,
            $this->refinery_factory,
            $this->getPossibleLPStates(),
            $may_be_edited,
            (bool) $this->getObject()->getSettings()->isEventTimePlaceRequired(),
            $amend,
            $this
        );

        $form = $this->input_factory->container()->form()->standard($form_action, [$section]);
        $form = $form->withAdditionalTransformation(
            $this->refinery_factory->custom()->transformation(
                function ($values) use ($amend) {
                    return array_shift($values);
                }
            )
        );
        return $form;
    }

    protected function finalize() : void
    {
        if (!$this->mayBeEdited()) {
            $this->handleAccessViolation();
            return;
        }

        $member = $this->getMember();
        if (!$member->mayBeFinalized()) {
            ilUtil::sendFailure($this->lng->txt('iass_may_not_finalize'), true);
            $this->redirect('edit');
            return;
        }

        try {
            $grading = $member->getGrading()->withFinalized(true);
            $member = $member->withGrading($grading);
            $this->getObject()->membersStorage()->updateMember($member);
        } catch (ilIndividualAssessmentException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->redirect('edit');
            return;
        }

        if ($this->object->isActiveLP()) {
            ilIndividualAssessmentLPInterface::updateLPStatusOfMember($member);
        }

        try {
            $member->maybeSendNotification($this->notificator);
        } catch (ilIndividualAssessmentException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->redirect('edit');
            return;
        }

        ilUtil::sendSuccess($this->lng->txt('iass_membership_finalized'), true);
        $this->redirect('view');
    }

    protected function finalizeConfirmation()
    {
        if (!$this->mayBeEdited()) {
            $this->handleAccessViolation();
            return;
        }

        $message = $this->lng->txt('iass_finalize_user_qst');
        $this->ctrl->setParameterByClass(self::class, 'usr_id', $this->getExaminee()->getId());
        $finalize = $this->ctrl->getFormActionByClass(self::class, self::CMD_FINALIZE);
        $cancel = $this->ctrl->getFormActionByClass(self::class, self::CMD_EDIT);
        $this->ctrl->clearParameterByClass(self::class, 'usr_id');

        $buttons = [
            $this->button_factory->standard($this->lng->txt('iass_confirm_finalize'), $finalize),
            $this->button_factory->standard($this->lng->txt('iass_cancel'), $cancel)
        ];

        $message_box = $this->messagebox_factory->confirmation($message)->withButtons($buttons);

        $this->tpl->setContent($this->renderer->render($message_box));
    }

    protected function saveMember(
        ilIndividualAssessmentUserGrading $grading,
        bool $keep_examiner = false,
        bool $amend = false
    ) : void {
        $member = $this->getMember()
            ->withGrading($grading)
        ;

        if ($amend) {
            $member = $member->withChangerId($this->user->getId());
        }

        if (!$keep_examiner) {
            $member = $member->withExaminerId($this->user->getId());
        }
        $this->getObject()->membersStorage()->updateMember($member);
    }

    protected function getPossibleLPStates() : array
    {
        return [
            ilIndividualAssessmentMembers::LP_IN_PROGRESS => $this->lng->txt('iass_status_pending'),
            ilIndividualAssessmentMembers::LP_COMPLETED => $this->lng->txt('iass_status_completed'),
            ilIndividualAssessmentMembers::LP_FAILED => $this->lng->txt('iass_status_failed')
        ];
    }

    protected function getUploadResult() : HandlerResult
    {
        $this->upload->process();
        $array = $this->upload->getResults();
        $result = end($array);

        if ($result instanceof UploadResult && $result->isOK()) {
            $identifier = $this->uploadFile($result);
            $status = HandlerResult::STATUS_OK;
            $message = 'Upload ok';
        } else {
            $status = HandlerResult::STATUS_FAILED;
            $identifier = '';
            $message = $result->getStatus()->getMessage();
        }

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }

    protected function getRemoveResult(string $identifier) : HandlerResult
    {
        $status = HandlerResult::STATUS_FAILED;
        $message = $this->lng->txt('iass_file_deleted');

        if ($this->getFileName() == $identifier) {
            $this->deleteFile();
            $member = $this->getMember();
            $grading = $member->getGrading()->withFile(null);
            $member = $member->withGrading($grading);
            $this->getObject()->membersStorage()->updateMember($member);
            $status = HandlerResult::STATUS_OK;
            $message = 'File Deleted';
        }

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }

    /**
     * @inheritDoc
     */
    protected function getInfoResult(string $identifier) : FileInfoResult
    {
        $filename = $this->getFileName();
        if ($filename != $identifier) {
            throw new LogicException("Wrong filename $identifier");
        }

        $file_size = filesize($this->getFilePath());
        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(),
            $identifier,
            $filename,
            $file_size,
            pathinfo($filename, PATHINFO_EXTENSION)
        );
    }

    /**
     * @inheritDoc
     */
    public function getInfoForExistingFiles(array $file_ids) : array
    {
        $name = $this->getFileName();

        $ids = array_filter($file_ids, function ($id) {
            if ($id == "") {
                return false;
            }
            return true;
        });

        if (is_null($name) || count($ids) === 0) {
            return [];
        }

        if (!in_array($name, $file_ids)) {
            throw new LogicException("Wrong filename " . $this->getFileName());
        }

        return [
            new BasicFileInfoResult(
                $this->getFileIdentifierParameterName(),
                "identifier",
                $name,
                64,
                ''
            )
        ];
    }

    /**
     * @inheritDoc
     */
    public function getFileIdentifierParameterName() : string
    {
        return 'iass';
    }

    /**
     * @inheritDoc
     */
    public function getUploadURL() : string
    {
        $this->ctrl->setParameter($this, 'usr_id', $this->getExaminee()->getId());
        $link = $this->ctrl->getLinkTarget($this, self::CMD_UPLOAD);
        $this->ctrl->setParameter($this, 'usr_id', null);

        return $link;
    }

    /**
     * @inheritDoc
     */
    public function getExistingFileInfoURL() : string
    {
        $this->ctrl->setParameter($this, 'usr_id', $this->getExaminee()->getId());
        $link = $this->ctrl->getLinkTarget($this, self::CMD_INFO);
        $this->ctrl->setParameter($this, 'usr_id', null);

        return $link;
    }

    /**
     * @inheritDoc
     */
    public function getFileRemovalURL() : string
    {
        $this->ctrl->setParameter($this, 'usr_id', $this->getExaminee()->getId());
        $this->ctrl->setParameter($this, $this->getFileIdentifierParameterName(), $this->getFileName());
        $link = $this->ctrl->getLinkTarget($this, self::CMD_REMOVE);
        $this->ctrl->setParameter($this, 'usr_id', null);
        $this->ctrl->setParameter($this, $this->getFileIdentifierParameterName(), null);

        return $link;
    }

    protected function uploadFile(UploadResult $result) : string
    {
        $storage = $this->getUserFileStorage();
        $storage->create();
        $storage->deleteCurrentFile();
        $storage->uploadFile($result);

        return $storage->getFileName();
    }

    protected function deleteFile()
    {
        $storage = $this->getUserFileStorage();
        $storage->deleteCurrentFile();
    }

    protected function getFileName() : ?string
    {
        $path = $this->getFilePath();
        if (is_null($path)) {
            return null;
        }

        return end(explode('/', $path));
    }

    protected function getFilePath() : ?string
    {
        $storage = $this->getUserFileStorage();
        if ($storage->isEmpty()) {
            return null;
        }

        return $storage->getFilePath();
    }

    protected function redirect(string $cmd) : void
    {
        $this->ctrl->setParameterByClass(self::class, 'usr_id', $this->getExaminee()->getId());
        $this->ctrl->redirect($this, $cmd);
    }

    public function setObject(ilObjIndividualAssessment $object) : void
    {
        $this->object = $object;
    }

    protected function getObject() : ilObjIndividualAssessment
    {
        return $this->object;
    }

    public function setParentGUI(ilIndividualAssessmentMembersGUI $parent_gui) : void
    {
        $this->parent_gui = $parent_gui;
    }

    public function getParentGUI() : ilIndividualAssessmentMembersGUI
    {
        return $this->parent_gui;
    }

    protected function getAccessHandler() : ilIndividualAssessmentAccessHandler
    {
        if (is_null($this->iass_access)) {
            $this->iass_access = $this->getObject()->accessHandler();
        }
        return $this->iass_access;
    }

    protected function getExaminee()
    {
        return new ilObjUser($_GET['usr_id']);
    }

    protected function getUserFileStorage() : ilIndividualAssessmentFileStorage
    {
        $storage = $this->getObject()->getFileStorage();
        $storage->setUserId($this->getExaminee()->getId());
        return $storage;
    }

    protected function getMember() : ilIndividualAssessmentMember
    {
        return $this->getObject()->membersStorage()->loadMember(
            $this->getObject(),
            $this->getExaminee()
        );
    }

    protected function setToolbar()
    {
        $member = $this->getMember();
        if ($member->fileName() != '') {
            $btn = ilLinkButton::getInstance();
            $btn->setCaption('download_assessment_paper');
            $this->ctrl->setParameter($this, 'usr_id', $this->getExaminee()->getId());
            $btn->setUrl($this->ctrl->getLinkTarget($this, self::CMD_DOWNLOAD_FILE, false, true));
            $this->ctrl->setParameter($this, 'usr_id', null);
            $this->toolbar->addButtonInstance($btn);
        }
    }

    protected function mayBeEdited() : bool
    {
        return $this->getAccessHandler()->isSystemAdmin() || (!$this->isFinalized() && $this->userMayGrade());
    }

    protected function mayBeViewed() : bool
    {
        return
            $this->getAccessHandler()->isSystemAdmin() ||
            ($this->isFinalized() && ($this->userMayGrade() || $this->userMayView()))
            ;
    }

    protected function mayBeAmended() : bool
    {
        return $this->getAccessHandler()->isSystemAdmin() || ($this->isFinalized() && $this->userMayAmend());
    }

    protected function userMayGrade() : bool
    {
        return
            $this->getAccessHandler()->isSystemAdmin() ||
            (!$this->targetWasEditedByOtherUser($this->getMember()) && $this->getAccessHandler()->mayGradeUser())
            ;
    }

    protected function userMayView() : bool
    {
        return $this->getAccessHandler()->isSystemAdmin() || $this->getAccessHandler()->mayViewUser();
    }

    protected function userMayAmend() : bool
    {
        return $this->getAccessHandler()->isSystemAdmin() || $this->getAccessHandler()->mayAmendGradeUser();
    }

    protected function targetWasEditedByOtherUser(ilIndividualAssessmentMember $member) : bool
    {
        return
            (int) $member->examinerId() !== (int) $this->user->getId() &&
            0 !== (int) $member->examinerId()
            ;
    }

    protected function isFinalized() : bool
    {
        return $this->getMember()->finalized();
    }

    public function handleAccessViolation()
    {
        $this->error_object->raiseError($this->txt("msg_no_perm_read"), $this->error_object->WARNING);
    }
}
