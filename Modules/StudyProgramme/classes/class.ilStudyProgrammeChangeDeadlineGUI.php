<?php

declare(strict_types=1);

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

use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Component\Input\Factory;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Renderer;

class ilStudyProgrammeChangeDeadlineGUI
{
    private const CMD_SHOW_DEADLINE_CONFIG = "showDeadlineConfig";
    private const CMD_CHANGE_DEADLINE = "changeDeadline";
    private const PROP_DEADLINE = "deadline";

    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilAccess $access;
    protected ilObjUser $user;
    protected Factory $input_factory;
    protected Renderer $renderer;
    protected Psr\Http\Message\ServerRequestInterface $request;
    protected ILIAS\Refinery\Factory $refinery_factory;
    protected ILIAS\Data\Factory $data_factory;
    protected ilPRGMessagePrinter $messages;

    protected ?string $back_target = null;
    protected array $progress_ids = [];
    protected ?int $ref_id = null;
    protected ?ilObjStudyProgramme $object = null;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilLanguage $lng,
        ilAccess $access,
        ilObjUser $user,
        Factory $input_factory,
        Renderer $renderer,
        Psr\Http\Message\ServerRequestInterface $request,
        ILIAS\Refinery\Factory $refinery_factory,
        ILIAS\Data\Factory $data_factory,
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

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW_DEADLINE_CONFIG:
                $this->showDeadlineConfig();
                break;
            case self::CMD_CHANGE_DEADLINE:
                $this->changeDeadline();
                break;
            case 'cancel':
                $this->redirectToParent();
                break;
            default:
                throw new Exception('Unknown command ' . $cmd);
        }
    }

    protected function showDeadlineConfig(): void
    {
        $this->tpl->loadStandardTemplate();
        $this->ctrl->setParameter($this, 'prgrs_ids', implode(',', $this->getProgressIds()));
        $action = $this->ctrl->getFormAction(
            $this,
            self::CMD_CHANGE_DEADLINE
        );
        $this->ctrl->clearParameters($this);

        $form = $this->buildForm($this->getObject(), $action);

        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function buildForm(ilObjStudyProgramme $prg, string $submit_action): Standard
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

    protected function getDeadlineSubForm(ilObjStudyProgramme $prg): Input
    {
        $ff = $this->input_factory->field();
        $txt = function ($id) {
            return $this->lng->txt($id);
        };

        $option = ilObjStudyProgrammeSettingsGUI::OPT_NO_DEADLINE;
        $deadline_date = $prg->getSettings()->getDeadlineSettings()->getDeadlineDate();
        $format = $this->data_factory->dateFormat()->germanShort();
        $deadline_date_sub_form = $ff
            ->dateTime('', $txt('prg_deadline_date_desc'))
            ->withFormat($format)
        ;

        if ($deadline_date !== null) {
            $deadline_date_sub_form = $deadline_date_sub_form->withValue(
                $deadline_date->format($format->toString())
            );
            $option = ilObjStudyProgrammeSettingsGUI::OPT_DEADLINE_DATE;
        }

        $sg = $ff->switchableGroup(
            [
                ilObjStudyProgrammeSettingsGUI::OPT_NO_DEADLINE =>
                    $ff->group([], $txt('prg_no_deadline')),
                ilObjStudyProgrammeSettingsGUI::OPT_DEADLINE_DATE =>
                    $ff->group([$deadline_date_sub_form], $txt('prg_deadline_date'))
            ],
            ''
        );

        return $sg->withValue($option);
    }

    protected function buildFormElements(
        $ff,
        Closure $txt,
        ilObjStudyProgramme $prg
    ): array {
        return [
            $ff->section(
                [
                    ilObjStudyProgrammeSettingsGUI::PROP_DEADLINE => $this->getDeadlineSubForm($prg)
                ],
                $txt("prg_deadline_settings"),
                ""
            )
        ];
    }

    protected function changeDeadline(): void
    {
        $form = $this
            ->buildForm($this->getObject(), $this->ctrl->getFormAction($this, "changeDeadline"))
            ->withRequest($this->request);

        $result = $form->getInputGroup()->getContent();

        $msg_collection = $this->messages->getMessageCollection('msg_change_deadline_date');

        if ($result->isOK()) {
            $values = $result->value();
            $programme = $this->getObject();
            $acting_usr_id = $this->user->getId();

            $deadline_data = $values[0][self::PROP_DEADLINE];
            $deadline_type = $deadline_data[0];
            $deadline = null;
            if ($deadline_type === ilObjStudyProgrammeSettingsGUI::OPT_DEADLINE_DATE) {
                $deadline = DateTimeImmutable::createFromFormat(
                    'd.m.Y',
                    array_shift($deadline_data[1])
                );

                if (!$deadline) {
                    $this->tpl->setOnScreenMessage("failure", $this->lng->txt('error_updating_deadline'), true);
                    $this->ctrl->redirectByClass(self::class, self::CMD_SHOW_DEADLINE_CONFIG);
                }
            }

            foreach ($this->getProgressIds() as $progress_id) {
                $programme->changeProgressDeadline($progress_id, $acting_usr_id, $msg_collection, $deadline);
            }

            $this->messages->showMessages($msg_collection);
            $this->ctrl->redirectByClass('ilObjStudyProgrammeMembersGUI', 'view');
        }

        $this->tpl->setOnScreenMessage("failure", $this->lng->txt('error_updating_deadline'), true);
        $this->ctrl->redirectByClass(self::class, self::CMD_SHOW_DEADLINE_CONFIG);
    }

    protected function getBackTarget(): ?string
    {
        return $this->back_target;
    }

    public function setBackTarget(string $target): void
    {
        $this->back_target = $target;
    }

    protected function getProgressIds(): array
    {
        return $this->progress_ids;
    }

    public function setProgressIds(array $progress_ids): void
    {
        $this->progress_ids = array_map('intval', $progress_ids);
    }

    protected function getRefId(): ?int
    {
        return $this->ref_id;
    }

    public function setRefId(int $ref_id): void
    {
        $this->ref_id = $ref_id;
    }

    protected function getObject(): ilObjStudyProgramme
    {
        $ref_id = $this->getRefId();
        if (is_null($ref_id)) {
            throw new LogicException("Can't create object. No ref_id given.");
        }

        if ($this->object === null) {
            $this->object = ilObjStudyProgramme::getInstanceByRefId($ref_id);
        }
        return $this->object;
    }

    protected function redirectToParent(): void
    {
        $back_target = $this->getBackTarget();
        if (is_null($back_target)) {
            throw new LogicException("Can't redirect. No back target given.");
        }

        $this->ctrl->redirectToURL($back_target);
    }
}
