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
        protected Psr\Http\Message\ServerRequestInterface $request,
        protected \ilObjUser $user,
        protected \ILIAS\Data\Factory $data_factory
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
        $shift_trafo = $this->refinery->custom()->transformation(
            static fn(array $v) => current($v)
        );
        $date_format = $this->data_factory->dateFormat()->withTime24(
            $this->user->getDateFormat()
        );
        $props = $lso->getObjectProperties();
        $ref_props = $lso->getObjectReferenceProperties();
        $settings = $lso->getLSSettings();

        $formElements = [];
        $formElements['object'] = $if->field()->section(
            [
                $props->getPropertyTitleAndDescription()->toForm(
                    $this->lng,
                    $if->field(),
                    $this->refinery
                )
            ],
            $txt('lso_edit')
        )->withAdditionalTransformation($shift_trafo);

        $formElements['online'] = $if->field()->section(
            [
                $props->getPropertyIsOnline()
                    ->toForm(
                        $this->lng,
                        $if->field(),
                        $this->refinery
                    ),
                $ref_props->getPropertyAvailabilityPeriod()
                    ->toForm(
                        $this->lng,
                        $if->field(),
                        $this->refinery,
                        [
                            'user_time_zone' => $this->user->getTimeZone(),
                            'user_date_format' => $date_format
                        ]
                    )
            ],
            $txt('lso_settings_availability')
        );

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
        $title_icon = $props->getPropertyTitleAndIconVisibility()->toForm(
            $this->lng,
            $if->field(),
            $this->refinery
        );
        $header_actions = $props->getPropertyHeaderActionVisibility()->toForm(
            $this->lng,
            $if->field(),
            $this->refinery
        );
        $custom_icon = $props->getPropertyIcon()->toForm(
            $this->lng,
            $if->field(),
            $this->refinery
        );
        $image = $props->getPropertyTileImage()->toForm(
            $this->lng,
            $if->field(),
            $this->refinery
        );
        $section_common = $if->field()->section(
            array_filter([
                'icon' => $title_icon,
                'header_actions' => $header_actions,
                'custom_icon' => $custom_icon,
                'image' => $image
            ]),
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

        $data = $form->getData();
        if ($data === null) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("msg_form_save_error"));
            return $this->renderer->render($form);
        }

        $lso = $this->obj;
        $obj_props = $lso->getObjectProperties();

        $obj_props->storePropertyTitleAndDescription($data['object']);
        list($online, $availability) = $data['online'];
        $obj_props->storePropertyIsOnline($online);
        $lso->storeAvailabilityPeriod($availability);

        $settings = $lso->getLSSettings()
            ->withMembersGallery($data['additional'][self::PROP_GALLERY]);
        $lso->updateSettings($settings);

        $obj_props->storePropertyTitleAndIconVisibility($data['common']['icon']);
        $obj_props->storePropertyHeaderActionVisibility($data['common']['header_actions']);
        if (array_key_exists('custom_icon', $data['common'])) {
            $obj_props->storePropertyIcon($data['common']['custom_icon']);
        }
        $obj_props->storePropertyTileImage($data['common']['image']);

        $lso->update();

        $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this);
        return null;
    }
}
