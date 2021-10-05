<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Input\Factory;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Renderer;

class ilStudyProgrammeChangeExpireDateGUI
{
    const CMD_SHOW_EXPIRE_DATE_CONFIG = "showExpireDateConfig";
    const CMD_CHANGE_EXPIRE_DATE = "changeExpireDate";
    const PROP_VALIDITY_OF_QUALIFICATION = "validity_qualification";

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
     * @var ilAccess
     */
    protected $access;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var string
     */
    protected $back_target;

    /**
     * @var Factory
     */
    protected $input_factory;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var ServerRequest
     */
    protected $request;

    /**
     * @var \ILIAS\Refinery\Factory
     */
    protected $refinery_factory;

    /**
     * @var \ILIAS\Data\Factory
     */
    protected $data_factory;

    /**
     * @var array
     */
    protected $progress_ids;

    /**
     * @var int
     */
    protected $ref_id;

    /**
     * @var ilObjStudyProgramme
     */
    protected $object;

    /**
     * @var ilPRGMessages
     */
    protected $messages;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilLanguage $lng,
        ilAccess $access,
        ilObjUser $user,
        Factory $input_factory,
        Renderer $renderer,
        ServerRequest $request,
        \ILIAS\Refinery\Factory $refinery_factory,
        \ILIAS\Data\Factory $data_factory,
        ilPRGMessagePrinter $messages
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->access = $access;
        $this->user = $user;
        $this->input_factory = $input_factory;
        $this->renderer = $renderer;
        $this->request = $request;
        $this->refinery_factory = $refinery_factory;
        $this->data_factory = $data_factory;
        $this->messages = $messages;
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                switch ($cmd) {
                    case self::CMD_SHOW_EXPIRE_DATE_CONFIG:
                        $this->showExpireDateConfig();
                        break;
                    case self::CMD_CHANGE_EXPIRE_DATE:
                        $this->changeExpireDate();
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

    protected function showExpireDateConfig() : void
    {
        $this->tpl->loadStandardTemplate();
        $this->ctrl->setParameter($this, 'prgrs_ids', implode(',', $this->getProgressIds()));
        $action = $this->ctrl->getFormAction(
            $this,
            self::CMD_CHANGE_EXPIRE_DATE
        );
        $this->ctrl->clearParameters($this);

        $form = $this->buildForm($this->getObject(), $action);

        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function buildForm(ilObjStudyProgramme $prg, string $submit_action) : Standard
    {
        $ff = $this->input_factory->field();
        $txt = function ($id) {
            return $this->lng->txt($id);
        };

        return $this->input_factory->container()->form()->standard(
            $submit_action,
            $this->buildFormElements(
                $ff,
                $txt,
                $prg
            )
        );
    }

    protected function getValidityOfQualificationSubForm(ilObjStudyProgramme $prg) : Input
    {
        $ff = $this->input_factory->field();
        $txt = function ($id) {
            return $this->lng->txt($id);
        };

        $option = ilObjStudyProgrammeSettingsGUI::OPT_NO_VALIDITY_OF_QUALIFICATION;
        $format = $this->data_factory->dateFormat()->germanShort();
        $vq_date_sub_form = $ff
            ->dateTime('', $txt('validity_qualification_date_desc'))
            ->withFormat($format);
        $date = $prg->getSettings()->getValidityOfQualificationSettings()->getQualificationDate();
        if ($date !== null) {
            $vq_date_sub_form = $vq_date_sub_form->withValue($date->format($format->toString()));
            $option = ilObjStudyProgrammeSettingsGUI::OPT_VALIDITY_OF_QUALIFICATION_DATE;
        }

        $sg = $ff->switchableGroup(
            [
                ilObjStudyProgrammeSettingsGUI::OPT_NO_VALIDITY_OF_QUALIFICATION =>
                    $ff->group([], $txt('prg_no_validity_qualification')),
                ilObjStudyProgrammeSettingsGUI::OPT_VALIDITY_OF_QUALIFICATION_DATE =>
                    $ff->group([$vq_date_sub_form], $txt('validity_qualification_date'))
            ],
            ''
        );
        return $sg->withValue($option);
    }

    protected function buildFormElements(
        \ILIAS\UI\Component\Input\Field\Factory $ff,
        Closure $txt,
        ilObjStudyProgramme $prg
    ) : array {
        return [
            $ff->section(
                [
                    ilObjStudyProgrammeSettingsGUI::PROP_VALIDITY_OF_QUALIFICATION => $this->getValidityOfQualificationSubForm($prg)
                ],
                $txt("prg_validity_of_qualification"),
                ""
            )
        ];
    }

    protected function changeExpireDate() : void
    {
        $form = $this
            ->buildForm($this->getObject(), $this->ctrl->getFormAction($this, "changeExpireDate"))
            ->withRequest($this->request);

        $result = $form->getInputGroup()->getContent();

        $msg_collection = $this->messages->getMessageCollection('msg_change_expire_date');

        if ($result->isOK()) {
            $values = $result->value();
            $programme = $this->getObject();
            $acting_usr_id = $this->user->getId();

            $vq_data = $values[0][self::PROP_VALIDITY_OF_QUALIFICATION];
            $vq_type = $vq_data[0];
            $validity = null;
            if ($vq_type === ilObjStudyProgrammeSettingsGUI::OPT_VALIDITY_OF_QUALIFICATION_DATE) {
                $validity = DateTimeImmutable::createFromFormat('d.m.Y', array_shift($vq_data[1]));
                if (!$validity) {
                    ilUtil::sendFailure($this->lng->txt('error_updating_expire_date'), true);
                    $this->ctrl->redirectByClass(self::class, self::CMD_SHOW_EXPIRE_DATE_CONFIG);
                }
            }
            foreach ($this->getProgressIds() as $progress_id) {
                $programme->changeProgressValidityDate($progress_id, $acting_usr_id, $msg_collection, $validity, );
            }

            $this->messages->showMessages($msg_collection);
            $this->ctrl->redirectByClass('ilObjStudyProgrammeMembersGUI', 'view');
        }
        ilUtil::sendFailure($this->lng->txt('error_updating_expire_date'), true);
        $this->ctrl->redirectByClass(self::class, self::CMD_SHOW_EXPIRE_DATE_CONFIG);
    }

    protected function getBackTarget() : string
    {
        return $this->back_target;
    }

    public function setBackTarget(string $target) : void
    {
        $this->back_target = $target;
    }

    protected function getProgressIds() : array
    {
        return $this->progress_ids;
    }

    public function setProgressIds(array $progress_ids) : void
    {
        $this->progress_ids = array_map('intval', $progress_ids);
    }

    protected function getRefId() : int
    {
        return $this->ref_id;
    }

    public function setRefId(int $ref_id) : void
    {
        $this->ref_id = $ref_id;
    }

    protected function getObject() : ilObjStudyProgramme
    {
        if ($this->object === null) {
            $this->object = ilObjStudyProgramme::getInstanceByRefId($this->getRefId());
        }
        return $this->object;
    }

    protected function redirectToParent() : void
    {
        ilUtil::redirect($this->getBackTarget());
    }
}
