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

namespace ILIAS\File\Icon;

use ILIAS\FileUpload\MimeType;
use ILIAS\HTTP\Wrapper\WrapperFactory;

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
class IconFormUI
{
    public const MODE_CREATE = 'create';
    public const MODE_EDIT = 'edit';
    public const FORM_ICON_CREATION = 'form_icon_creation';
    public const FORM_ICON_UPDATING = 'form_icon_updating';
    public const INPUT_ICON = 'input_icon';
    public const INPUT_DESC_ICON = 'input_desc_icon';
    public const INPUT_ACTIVE = 'input_active';
    public const INPUT_DESC_ACTIVE = 'input_desc_active';
    public const INPUT_SUFFIXES = 'input_suffixes';
    public const INPUT_DESC_SUFFIXES = 'input_desc_suffixes';

    private \ilCtrl $ctrl;
    private \ilLanguage $lng;
    private \ilTabsGUI $tabs;
    private \ILIAS\UI\Factory $ui_factory;
    private WrapperFactory $wrapper;
    private \ILIAS\UI\Component\Input\Container\Form\Standard $icon_form;
    private \ILIAS\Refinery\Factory $refinery;
    private Icon $icon;
    private string $mode;
    private IconRepositoryInterface $icon_repo;

    public function __construct(
        Icon $icon,
        string $mode,
        IconRepositoryInterface $icon_repo
    ) {
        $this->icon = $icon;
        $this->mode = $mode;
        $this->icon_repo = $icon_repo;
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->ui_factory = $DIC->ui()->factory();
        $this->wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();

        $this->initIconForm();
    }

    protected function initIconForm(): void
    {
        $this->tabs->clearSubTabs();
        $this->tabs->clearTargets();
        $back_target = $this->ctrl->getLinkTargetByClass(
            ilObjFileIconsOverviewGUI::class,
            ilObjFileIconsOverviewGUI::CMD_INDEX
        );
        $this->tabs->setBackTarget($this->lng->txt('back'), $back_target);

        if ($this->mode == self::MODE_EDIT) {
            $form_title = $this->lng->txt(self::FORM_ICON_UPDATING);
            $form_action = $this->ctrl->getFormActionByClass(
                ilObjFileIconsOverviewGUI::class,
                ilObjFileIconsOverviewGUI::CMD_UPDATE
            );
        } else {
            $form_title = $this->lng->txt(self::FORM_ICON_CREATION);
            $form_action = $this->ctrl->getFormActionByClass(
                ilObjFileIconsOverviewGUI::class,
                ilObjFileIconsOverviewGUI::CMD_CREATE
            );
        }

        $rid = $this->icon->getRid();
        $this->ctrl->setParameterByClass(ilIconUploadHandlerGUI::class, 'rid', $rid);
        $icon_input = $this->ui_factory->input()->field()->file(
            new ilIconUploadHandlerGUI(),
            $this->lng->txt(self::INPUT_ICON),
            $this->lng->txt(self::INPUT_DESC_ICON)
        )->withAcceptedMimeTypes(
            [MimeType::IMAGE__SVG_XML]
        )->withRequired(
            true
        );
        if ($rid !== "") {
            $icon_input = $icon_input->withValue([$rid]);
        }

        $active_input = $this->ui_factory->input()->field()->checkbox(
            $this->lng->txt(self::INPUT_ACTIVE),
            $this->lng->txt(self::INPUT_DESC_ACTIVE)
        )->withValue(
            $this->icon->isActive()
        );

        $suffix_input = $this->ui_factory->input()->field()->text(
            $this->lng->txt(self::INPUT_SUFFIXES),
            $this->lng->txt(self::INPUT_DESC_SUFFIXES)
        )->withRequired(
            true
        )->withValue(
            $this->icon_repo->turnSuffixesArrayIntoString($this->icon->getSuffixes())
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($suffixes_input): array {
                return $this->icon_repo->turnSuffixesStringIntoArray($suffixes_input);
            })
        )->withAdditionalTransformation(
            $this->refinery->custom()->constraint(function ($suffixes_input): bool {
                return $this->icon_repo->hasSuffixInputOnlyAllowedCharacters($suffixes_input);
            }, $this->lng->txt('msg_error_suffixes_with_forbidden_characters'))
        )->withAdditionalTransformation(
            $this->refinery->custom()->constraint(function ($suffixes_input): bool {
                return $this->icon_repo->hasSuffixInputNoDuplicatesToItsOwnEntries($suffixes_input);
            }, $this->lng->txt('msg_error_duplicate_suffix_entries'))
        )->withAdditionalTransformation(
            $this->refinery->custom()->constraint(function ($suffixes_input): bool {
                //retrieve the value of the active_input as it is needed for the causesNoActiveSuffixesConflict validation
                $section = $this->icon_form->getInputs()[0];
                $inputs = $section->getInputs();
                $input_active = $inputs[self::INPUT_ACTIVE];
                $field_is_active = $input_active->getName();
                $to_bool = $this->refinery->custom()->transformation(function ($checkbox_input_value): bool {
                    return $this->transformCheckboxInputValueToBool($checkbox_input_value);
                });
                $post_is_active_value = $this->wrapper->post()->retrieve($field_is_active, $to_bool);

                return $this->icon_repo->causesNoActiveSuffixesConflict(
                    $suffixes_input,
                    $post_is_active_value,
                    $this->icon
                );
            }, $this->lng->txt('msg_error_active_suffixes_conflict'))
        );

        $section = $this->ui_factory->input()->field()->section([
            self::INPUT_ICON => $icon_input,
            self::INPUT_ACTIVE => $active_input,
            self::INPUT_SUFFIXES => $suffix_input
        ], $form_title);
        $this->icon_form = $this->ui_factory->input()->container()->form()->standard($form_action, [$section]);
    }

    public function getIconForm(): \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        return $this->icon_form;
    }

    public function transformCheckboxInputValueToBool($checkbox_input_value): bool
    {
        if ($checkbox_input_value !== null) {
            $checkbox_input_value_str = $this->refinery->kindlyTo()->string()->transform($checkbox_input_value);
            if ($checkbox_input_value_str == "checked") {
                return true;
            }
        }
        return false;
    }
}
