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

use ILIAS\ILIASObject\Properties\CoreProperties\TitleAndDescription;
use ILIAS\ILIASObject\Properties\ObjectReferenceProperties\AvailabilityPeriod\AvailabilityPeriod;

class ilObjLearningSequenceSettingsGUI
{
    public const PROP_TITLE = 'title';
    public const PROP_DESC = 'desc';
    public const PROP_ONLINE = 'online';
    public const PROP_AVAIL_FROM = 'start';
    public const PROP_AVAIL_TO = 'end';
    public const PROP_GALLERY = 'gallery';
    public const PROP_LSO_MODE = 'lso_mode';

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
    ): array {
        $txt = fn($id) => $this->lng->txt($id);
        $date_format = $this->data_factory->dateFormat()->withTime24(
            $this->user->getDateFormat()
        );
        $props = $lso->getObjectProperties();
        $ref_props = $lso->getObjectReferenceProperties();
        $settings = $lso->getLSSettings();

        // LSO Mode
        $lso_mode = $if->field()->radio($txt('lso_mode'), $txt('lso_mode_info'))
            ->withOption(
                (string) ilLearningSequenceSettings::MODE_LINEAR,
                $txt('lso_mode_linear'),
                $txt('lso_mode_linear_info')
            )
            ->withOption(
                (string) ilLearningSequenceSettings::MODE_ADAPTIVE,
                $txt('lso_mode_adaptive'),
                $txt('lso_mode_adaptive_info')
            )
            ->withByline($txt('lso_mode_byline'))
            ->withValue((string) $settings->getLSOMod())
            ->withAdditionalTransformation(
                $this->refinery->kindlyTo()->int()
            );

        $formElements = [];
        $formElements['object'] = $if->field()->section(
            [
                'title_and_description' => $props->getPropertyTitleAndDescription()->toForm(
                    $this->lng,
                    $if->field(),
                    $this->refinery
                ),
                self::PROP_LSO_MODE => $lso_mode
            ],
            $txt('lso_edit')
        );

        $formElements['online'] = $if->field()->section(
            [
                'online' => $props->getPropertyIsOnline()
                    ->toForm(
                        $this->lng,
                        $if->field(),
                        $this->refinery
                    ),
                'availability_period' => $ref_props->getPropertyAvailabilityPeriod()
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

        // Metadata
        $custom_md = $if->field()->checkbox($this->lng->txt('obj_tool_setting_custom_metadata'))
            ->withValue((bool) ilContainer::_lookupContainerSetting(
                $lso->getId(),
                ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                '0'
            ))
            ->withAdditionalTransformation(
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->bool(),
                    $this->refinery->always(false)
                ])
            );
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

        $section_additional = $if->field()->section(
            [
                self::PROP_GALLERY => $gallery,
                ilObjectServiceSettingsGUI::CUSTOM_METADATA => $custom_md
            ],
            $txt('obj_features')
        );
        $formElements['additional'] = $section_additional;

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
        $ref_props = $lso->getObjectReferenceProperties();

        // --- Adaptive mode start/end object guards -----------------------
        // An adaptive learning sequence requires a start AND an end object.
        // Missing either one has consequences for the online state.
        $old_mode = $lso->getLSSettings()->getMode();
        $new_mode = (int) ($data['object'][self::PROP_LSO_MODE] ?? ilLearningSequenceSettings::MODE_LINEAR);

        $online_property = $data['online']['online'] ?? $obj_props->getPropertyIsOnline()->withOffline();
        $was_online = $obj_props->getPropertyIsOnline()->getIsOnline();
        $wants_online = $online_property->getIsOnline();

        $has_start_and_end = true;
        if ($new_mode === ilLearningSequenceSettings::MODE_ADAPTIVE) {
            global $DIC;
            $boundaries = new \ILIAS\LearningSequence\Content\Adaptive\LSOAdaptiveBoundaries($DIC->database());
            $b = $boundaries->getBoundariesFor($lso->getId());
            $has_start_and_end = ((int) $b['start_ref_id'] > 0 && (int) $b['end_ref_id'] > 0);
        }

        // Invariant for adaptive mode: the learning sequence may only be online
        // if it has BOTH a start AND an end object. Missing either one (or both)
        // forces it offline.
        //
        // When the user actively tries to switch the object online without a
        // start and/or end object, abort the whole save with an error message.
        if ($new_mode === ilLearningSequenceSettings::MODE_ADAPTIVE
            && $wants_online
            && !$was_online
            && !$has_start_and_end
        ) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('lso_adaptive_missing_boundaries_online'),
                true
            );
            $this->ctrl->redirect($this);
            return null;
        }

        // In every other case where the object would (stay) online in adaptive
        // mode without a start and/or end object, force it offline and inform
        // the user (e.g. when switching the mode TO adaptive, or when it was
        // already online but a boundary is missing).
        $forced_offline = false;
        if ($new_mode === ilLearningSequenceSettings::MODE_ADAPTIVE
            && $wants_online
            && !$has_start_and_end
        ) {
            $online_property = $online_property->withOffline();
            $forced_offline = true;
        }

        $title_and_description = $data['object']['title_and_description'] ?? null;
        if ($title_and_description instanceof TitleAndDescription) {
            $obj_props->storePropertyTitleAndDescription($title_and_description);
        }

        ilContainer::_writeContainerSetting(
            $lso->getId(),
            ilObjectServiceSettingsGUI::CUSTOM_METADATA,
            ($data['additional'][ilObjectServiceSettingsGUI::CUSTOM_METADATA] ?? false) ? '1' : '0'
        );

        $obj_props->storePropertyIsOnline($online_property);

        if ($forced_offline) {
            $this->tpl->setOnScreenMessage(
                'info',
                $this->lng->txt('lso_adaptive_offline_due_to_missing_boundaries'),
                true
            );
        }

        $availability_period = $data['online']['availability_period'] ?? null;
        if ($availability_period instanceof AvailabilityPeriod) {
            $lso->storeAvailabilityPeriod(
                $availability_period->withObjectReferenceId($lso->getRefId())
            );
        } elseif ($ref_props !== null) {
            // keep current value (no-op), but ensure it stays bound to current ref id
            $lso->storeAvailabilityPeriod(
                $ref_props->getPropertyAvailabilityPeriod()->withObjectReferenceId($lso->getRefId())
            );
        }

        $settings = $lso->getLSSettings()
            ->withMembersGallery($data['additional'][self::PROP_GALLERY] ?? false)
            ->withLSOMod((int) ($data['object'][self::PROP_LSO_MODE] ?? ilLearningSequenceSettings::MODE_LINEAR));
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
