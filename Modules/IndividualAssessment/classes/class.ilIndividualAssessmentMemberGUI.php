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
use ILIAS\UI\Component\Link;
use ILIAS\UI\Renderer;
use ILIAS\Data;
use ILIAS\Refinery;
use ILIAS\ResourceStorage\Services as IRSS;

class ilIndividualAssessmentMemberGUI extends AbstractCtrlAwareUploadHandler
{
    public const CMD_VIEW = 'view';
    public const CMD_EDIT = 'edit';
    public const CMD_UPDATE = 'update';
    public const CMD_FINALIZE = 'finalize';
    public const CMD_FINALIZE_CONFIRMATION = 'finalizeConfirmation';
    public const CMD_AMEND = 'amend';
    public const CMD_SAVE_AMEND = "saveAmend";
    public const CMD_DOWNLOAD_FILE = "downloadFile";

    protected ?ilIndividualAssessmentAccessHandler $iass_access = null;

    public function __construct(
        protected ilCtrl $ctrl,
        protected ilLanguage $lng,
        protected ilGlobalPageTemplate $tpl,
        protected ilObjUser $user,
        protected Input\Factory $input_factory,
        protected MessageBox\Factory $messagebox_factory,
        protected Button\Factory $button_factory,
        protected Link\Factory $link_factory,
        protected Refinery\Factory $refinery_factory,
        protected Data\Factory $data_factory,
        protected Renderer $renderer,
        protected ServerRequest $request,
        protected ilIndividualAssessmentPrimitiveInternalNotificator $notificator,
        protected ilToolbarGUI $toolbar,
        protected ilObjIndividualAssessment $object,
        protected ilErrorHandling $error_object,
        protected ILIAS\Refinery\Factory $refinery,
        protected ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper,
        protected ilIndividualAssessmentDateFormatter $date_formatter,
        protected IRSS $irss,
        protected ilIndividualAssessmentGradingStakeholder $stakeholder,
    ) {
        parent::__construct();
    }

    public function executeCommand(): void
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

    protected function view(): void
    {
        if (!$this->mayBeViewed()) {
            $this->handleAccessViolation();
            return;
        }
        $form = $this->buildForm('', false);
        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function edit(): void
    {
        if (!$this->mayBeEdited()) {
            $this->handleAccessViolation();
            return;
        }

        $this->setToolbar();
        $form = $this->buildForm($this->getFormActionForCommand(self::CMD_UPDATE), true);
        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function update(): void
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

        $this->tpl->setOnScreenMessage("success", $this->lng->txt('iass_membership_saved'), true);
        $this->ctrl->redirectByClass(ilIndividualAssessmentMembersGUI::class, 'view');
    }

    protected function amend(): void
    {
        if (!$this->mayBeAmended()) {
            $this->handleAccessViolation();
            return;
        }

        $this->setToolbar();
        $form = $this->buildForm($this->getFormActionForCommand(self::CMD_SAVE_AMEND), true, true);
        $form->withSubmitLabel($this->lng->txt("save_amend"));
        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function getFormActionForCommand(string $cmd): string
    {
        $this->ctrl->setParameterByClass(self::class, 'usr_id', $this->getExaminee()->getId());
        $action = $this->ctrl->getFormAction($this, $cmd);
        $this->ctrl->clearParameterByClass(self::class, 'usr_id');

        return $action;
    }

    protected function downloadFile(): void
    {
        $identifier = $this->getMember()->getGrading()->getFile();
        $resource_id = $this->irss->manage()->find($identifier);
        if($resource_id) {
            $this->irss->consume()->download($resource_id)->run();
        }
    }

    protected function saveAmend(): void
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
            $this->saveMember($grading, true, true);

            if ($this->getObject()->isActiveLP()) {
                ilIndividualAssessmentLPInterface::updateLPStatusOfMember($this->getMember());
            }

            $this->tpl->setOnScreenMessage("success", $this->lng->txt('iass_amend_saved'), true);
            $this->ctrl->redirectByClass(ilIndividualAssessmentMembersGUI::class, 'view');
        }
    }

    protected function buildForm(
        string $form_action,
        bool $may_be_edited,
        bool $amend = false
    ): ILIAS\UI\Component\Input\Container\Form\Form {
        $section = $this->getMember()->getGrading()->toFormInput(
            $this->input_factory->field(),
            $this->data_factory,
            $this->lng,
            $this->refinery_factory,
            $this,
            $this->user->getDateFormat(),
            $this->getPossibleLPStates(),
            $may_be_edited,
            $this->getObject()->getSettings()->isEventTimePlaceRequired(),
            $this->getObject()->getSettings()->isFileRequired(),
            $amend
        );

        $form = $this->input_factory->container()->form()->standard($form_action, [$section]);
        return $form->withAdditionalTransformation(
            $this->refinery_factory->custom()->transformation(
                function ($values) use ($amend) {
                    return array_shift($values);
                }
            )
        );
    }

    protected function finalize(): void
    {
        if (!$this->mayBeEdited()) {
            $this->handleAccessViolation();
            return;
        }

        $member = $this->getMember();
        if (!$member->mayBeFinalized()) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt('iass_may_not_finalize'), true);
            $this->redirect('edit');
            return;
        }

        try {
            $grading = $member->getGrading()->withFinalized(true);
            $member = $member->withGrading($grading);
            $this->getObject()->membersStorage()->updateMember($member);
        } catch (ilIndividualAssessmentException $e) {
            $this->tpl->setOnScreenMessage("failure", $e->getMessage(), true);
            $this->redirect('edit');
            return;
        }

        if ($this->object->isActiveLP()) {
            ilIndividualAssessmentLPInterface::updateLPStatusOfMember($member);
        }

        try {
            $member->maybeSendNotification($this->notificator);
            $this->ctrl->redirectByClass(ilIndividualAssessmentMembersGUI::class, 'view');
        } catch (ilIndividualAssessmentException $e) {
            $this->tpl->setOnScreenMessage("failure", $e->getMessage(), true);
            $this->redirect('edit');
            return;
        }

        $this->tpl->setOnScreenMessage("success", $this->lng->txt('iass_membership_finalized'), true);
        $this->redirect('view');
    }

    protected function finalizeConfirmation(): void
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
    ): void {
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

    protected function getPossibleLPStates(): array
    {
        return [
            ilIndividualAssessmentMembers::LP_IN_PROGRESS => $this->lng->txt('iass_status_pending'),
            ilIndividualAssessmentMembers::LP_COMPLETED => $this->lng->txt('iass_status_completed'),
            ilIndividualAssessmentMembers::LP_FAILED => $this->lng->txt('iass_status_failed')
        ];
    }

    protected function getUploadResult(): HandlerResult
    {
        $this->upload->process();
        $array = $this->upload->getResults();
        $result = end($array);

        if ($result instanceof UploadResult && $result->isOK()) {

            $resource_id = $this->irss->manage()->upload($result, $this->stakeholder);
            $this->irss->manage()->publish($resource_id);

            $identifier = $resource_id->serialize();
            $status = HandlerResult::STATUS_OK;
            $message = 'Upload ok';
        } else {
            $status = HandlerResult::STATUS_FAILED;
            $identifier = '';
            $message = $result->getStatus()->getMessage();
        }

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }

    protected function getRemoveResult(string $identifier): HandlerResult
    {
        $resource_id = $this->irss->manage()->find($identifier);
        if($resource_id) {
            $this->irss->manage()->remove($resource_id, $this->stakeholder);
            $status = HandlerResult::STATUS_OK;
            $message = $this->lng->txt('iass_file_deleted');
        } else {
            $status = HandlerResult::STATUS_FAILED;
            $identifier = '';
            $message = 'no resource to delete';
        }
        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }

    public function getInfoResult(string $identifier): ?FileInfoResult
    {
        $resource_id = $this->irss->manage()->find($identifier);
        if(! $resource_id) {
            return null;
        }
        $resource = $this->irss->manage()->getResource($resource_id);
        $info = $resource->getCurrentRevision()->getInformation();

        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(),
            $identifier,
            $info->getTitle(),
            $info->getSize(),
            $info->getMimeType()
        );
    }

    public function getInfoForExistingFiles(array $file_ids): array
    {
        $file_ids = array_filter($file_ids, fn($id) => $id !== "");
        return array_map(function ($id) {
            $resource_id = $this->irss->manage()->find($identifier);
            if(! $resource_id) {
                return null;
            }
            $resource = $this->irss->manage()->getResource($resource_id);
            $info = $resource->getCurrentRevision()->getInformation();
            return new BasicFileInfoResult(
                $this->getFileIdentifierParameterName(),
                $id,
                $info->getTitle(),
                $info->getSize(),
                $info->getMimeType()
            );
        }, $file_ids);
    }

    public function getFileIdentifierParameterName(): string
    {
        return 'iass';
    }

    public function getUploadURL(): string
    {
        $this->ctrl->setParameter($this, 'usr_id', $this->getExaminee()->getId());
        $link = $this->ctrl->getLinkTarget($this, self::CMD_UPLOAD);
        $this->ctrl->setParameter($this, 'usr_id', null);

        return $link;
    }

    public function getExistingFileInfoURL(): string
    {
        $this->ctrl->setParameter($this, 'usr_id', $this->getExaminee()->getId());
        $link = $this->ctrl->getLinkTarget($this, self::CMD_INFO);
        $this->ctrl->setParameter($this, 'usr_id', null);

        return $link;
    }

    protected function redirect(string $cmd): void
    {
        $this->ctrl->setParameterByClass(self::class, 'usr_id', $this->getExaminee()->getId());
        $this->ctrl->redirect($this, $cmd);
    }

    public function setObject(ilObjIndividualAssessment $object): void
    {
        $this->object = $object;
    }

    protected function getObject(): ilObjIndividualAssessment
    {
        return $this->object;
    }

    public function setParentGUI(ilIndividualAssessmentMembersGUI $parent_gui): void
    {
        $this->parent_gui = $parent_gui;
    }

    public function getParentGUI(): ilIndividualAssessmentMembersGUI
    {
        return $this->parent_gui;
    }

    protected function getAccessHandler(): ilIndividualAssessmentAccessHandler
    {
        if (is_null($this->iass_access)) {
            $this->iass_access = $this->getObject()->accessHandler();
        }
        return $this->iass_access;
    }

    protected function getExaminee(): ilObjUser
    {
        return new ilObjUser($this->request_wrapper->retrieve('usr_id', $this->refinery->kindlyTo()->int()));
    }

    protected function getMember(): ilIndividualAssessmentMember
    {
        return $this->getObject()->membersStorage()->loadMember(
            $this->getObject(),
            $this->getExaminee()
        );
    }

    protected function setToolbar(): void
    {
        $member = $this->getMember();
        if ($member->fileName() != '') {
            $this->ctrl->setParameter($this, 'usr_id', $this->getExaminee()->getId());
            $url = $this->ctrl->getLinkTarget($this, self::CMD_DOWNLOAD_FILE, "", false, true);
            $this->ctrl->setParameter($this, 'usr_id', null);
            $btn = $this->link_factory->standard(
                $this->lng->txt('download_assessment_paper'),
                $url
            );
            $this->toolbar->addComponent($btn);
        }
    }

    protected function mayBeEdited(): bool
    {
        return $this->getAccessHandler()->isSystemAdmin() || (!$this->isFinalized() && $this->userMayGrade());
    }

    protected function mayBeViewed(): bool
    {
        return
            $this->getAccessHandler()->isSystemAdmin() ||
            ($this->isFinalized() && ($this->userMayGrade() || $this->userMayView()))
        ;
    }

    protected function mayBeAmended(): bool
    {
        return $this->getAccessHandler()->isSystemAdmin() || ($this->isFinalized() && $this->userMayAmend());
    }

    protected function userMayGrade(): bool
    {
        return
            $this->getAccessHandler()->isSystemAdmin() ||
            (!$this->targetWasEditedByOtherUser($this->getMember()) && $this->getAccessHandler()->mayGradeUser($this->getMember()->id()))
        ;
    }

    protected function userMayView(): bool
    {
        return $this->getAccessHandler()->mayViewUser($this->getMember()->id());
    }

    protected function userMayAmend(): bool
    {
        return $this->getAccessHandler()->mayAmendAllUsers();
    }

    protected function targetWasEditedByOtherUser(ilIndividualAssessmentMember $member): bool
    {
        return
            (int) $member->examinerId() !== $this->user->getId() &&
            0 !== (int) $member->examinerId()
        ;
    }

    protected function isFinalized(): bool
    {
        return $this->getMember()->finalized();
    }

    public function handleAccessViolation(): void
    {
        $this->error_object->raiseError($this->lng->txt("msg_no_perm_read"), $this->error_object->WARNING);
    }
}
