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

use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;

class ilObjLearningSequenceSettingsGUI
{
    public const PROP_TITLE = 'title';
    public const PROP_DESC = 'desc';
    public const PROP_ONLINE = 'online';
    public const PROP_AVAIL_FROM = 'start';
    public const PROP_AVAIL_TO = 'end';
    public const PROP_GALLERY = 'gallery';

    public const CMD_EDIT = "settings";
    public const CMD_SAVE = "update";
    public const CMD_CANCEL = "cancel";

    public function __construct(
        protected ilObjLearningSequence $obj,
        protected ilCtrl $ctrl,
        protected ilLanguage $lng,
        protected ilGlobalTemplateInterface $tpl,
        protected ILIAS\Refinery\Factory $refinery,
        protected ILIAS\UI\Factory $ui_factory,
        protected ILIAS\UI\Renderer $renderer,
        protected Psr\Http\Message\ServerRequestInterface $request
    ) {
        $this->lng->loadLanguageModule('content');
        $this->lng->loadLanguageModule('obj');
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd('settings');

        switch ($cmd) {
            case self::CMD_EDIT:
            case self::CMD_SAVE:
            case self::CMD_CANCEL:
                $content = $this->$cmd();
                break;

            default:
                throw new ilException("ilObjLearningSequenceSettingsGUI: Command not supported: $cmd");
        }
        $this->tpl->setContent($content);
    }

    protected function settings(): string
    {
        return $this->renderer->render($this->buildForm(
            $this->obj,
            $this->ctrl->getFormAction($this, self::CMD_SAVE)
        ));
    }

    protected function cancel(): void
    {
        $this->ctrl->redirectByClass(ilObjLearningSequenceGUI::class);
    }

    protected function buildForm(
        ilObjLearningSequence $lso,
        string $submit_action
    ): ILIAS\UI\Component\Input\Container\Form\Standard {
        $if = $this->ui_factory->input();

        $form = $if->container()->form()->standard(
            $submit_action,
            $this->buildFormElements(
                $lso,
                $if
            )
        );

        return $form;
    }

    protected function buildFormElements(
        ilObjLearningSequence $lso,
        ILIAS\UI\Component\Input\Factory $if
    ) {
        $txt = fn($id) => $this->lng->txt($id);
        $settings = $lso->getLSSettings();
        $activation = $lso->getLSActivation();
        $formElements = [];

        // Title & Description
        $title = $if->field()->text($txt("title"))
            ->withRequired(true)
            ->withValue($lso->getTitle());
        $description = $if->field()->text($txt("description"))
            ->withValue($lso->getLongDescription());
        $section_object = $if->field()->section(
            [
                self::PROP_TITLE => $title,
                self::PROP_DESC => $description
            ],
            $txt('lso_edit')
        );
        $formElements['object'] = $section_object;

        // Online status
        $online = $if->field()->checkbox(
            $txt('online'),
            $txt('lso_activation_online_info')
        )->withValue($activation->getIsOnline());
        $online_start = $if->field()->dateTime($txt('from'))
            ->withUseTime(true)
            ->withValue(($activation->getActivationStart()) ? $activation->getActivationStart()->format('Y-m-d H:i') : '');
        $online_end = $if->field()->dateTime($txt('to'))
            ->withUseTime(true)
            ->withValue(($activation->getActivationEnd()) ? $activation->getActivationEnd()->format('Y-m-d H:i') : '');
        $section_online = $if->field()->section(
            [
                self::PROP_ONLINE => $online,
                self::PROP_AVAIL_FROM => $online_start,
                self::PROP_AVAIL_TO => $online_end
            ],
            $txt('lso_settings_availability')
        )->withAdditionalTransformation(
            $this->refinery->custom()->constraint(
                function ($values) {
                    $start = $values[self::PROP_AVAIL_FROM] ?? '';
                    $end = $values[self::PROP_AVAIL_TO] ?? '';
                    if (($start !== '' && $end !== '') && ($end < $start)) {
                        return false;
                    }
                    return true;
                },
                $txt('lso_settings_availability_error')
            )
        );
        $formElements['online'] = $section_online;

        // Member gallery
        $gallery = $if->field()->checkbox($txt("members_gallery"), $txt('lso_show_members_info'))
            ->withValue($settings->getMembersGallery())
            ->withAdditionalTransformation(
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->bool(),
                    $this->refinery->always(false)
                ])
            );
        $section_additional = $if->field()->section(
            [
                self::PROP_GALLERY => $gallery
            ],
            $txt('obj_features')
        );
        $formElements['additional'] = $section_additional;

        // Common properties
        $title_icon = $lso->getObjectProperties()->getPropertyTitleAndIconVisibility()->toForm(
            $this->lng,
            $if->field(),
            $this->refinery
        );
        $header_actions = $lso->getObjectProperties()->getPropertyHeaderActionVisibility()->toForm(
            $this->lng,
            $if->field(),
            $this->refinery
        );
        $image = $lso->getObjectProperties()->getPropertyTileImage()->toForm(
            $this->lng,
            $if->field(),
            $this->refinery
        );
        $section_common = $if->field()->section(
            [
                'icon' => $title_icon,
                'header_actions' => $header_actions,
                'image' => $image
            ],
            $txt('cont_presentation')
        );
        $formElements['common'] = $section_common;

        return $formElements;
    }

    protected function update(): ?string
    {
        $form = $this
            ->buildForm($this->obj, $this->ctrl->getFormAction($this, self::CMD_SAVE))
            ->withRequest($this->request);

        $result = $form->getInputGroup()->getContent();

        if ($result->isOK()) {
            $values = $result->value();
            $lso = $this->obj;

            $lso->setTitle($values['object'][self::PROP_TITLE]);
            $lso->setDescription($values['object'][self::PROP_DESC]);

            $settings = $lso->getLSSettings()
                ->withMembersGallery($values['additional'][self::PROP_GALLERY]);
            $lso->updateSettings($settings);

            $activation = $lso->getLSActivation()
                ->withIsOnline($values['online'][self::PROP_ONLINE])
                ->withActivationStart(null)
                ->withActivationEnd(null);
            if ($values['online'][self::PROP_AVAIL_FROM] !== null) {
                $activation = $activation
                    ->withActivationStart(
                        DateTime::createFromImmutable($values['online'][self::PROP_AVAIL_FROM])
                    );
            }
            if ($values['online'][self::PROP_AVAIL_TO] !== null) {
                $activation = $activation
                    ->withActivationEnd(
                        DateTime::createFromImmutable($values['online'][self::PROP_AVAIL_TO])
                    );
            }
            $lso->updateActivation($activation);

            $status = ilObjLearningSequenceAccess::isOffline($lso->getRefId());
            $lso->getObjectProperties()->storePropertyIsOnline(
                new ilObjectPropertyIsOnline(! $status)
            );

            $lso->getObjectProperties()->storePropertyTitleAndIconVisibility($values['common']['icon']);
            $lso->getObjectProperties()->storePropertyHeaderActionVisibility($values['common']['header_actions']);
            $lso->getObjectProperties()->storePropertyTileImage($values['common']['image']);

            $lso->update();

            $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this);
            return null;
        } else {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("msg_form_save_error"));
            return $this->renderer->render($form);
        }
    }
}
