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

namespace ILIAS\User\Settings\StartingPoint;

use ILIAS\User\LocalDIC;
use ILIAS\User\Settings\User\SettingConfiguration;
use ILIAS\User\Settings\User\AvailablePages;
use ILIAS\User\Settings\User\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;

class Setting implements SettingConfiguration
{
    private readonly Repository $starting_point_repository;

    public function __construct()
    {
        $this->starting_point_repository = LocalDIC::dic()['settings.starting_point.repository'];
    }

    public function getIdentifier(): string
    {
        return 'starting_point';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getLanguageVariable(): string
    {
        return 'starting_point';
    }

    public function getSettingsPage(): AvailablePages
    {
        return AvailablePages::MainSettings;
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::Additional;
    }

    public function getInput(
        Language $lng,
        \ilObjUser $current_user
    ): \ilFormPropertyGUI {
        $input = new \ilRadioGroupInputGUI($lng->txt('adm_user_starting_point'));
        $input->setInfo($lng->txt('adm_user_starting_point_info'));
        $inherit_starting_point = new \ilRadioOption($lng->txt('adm_user_starting_point_inherit'), '0');
        $inherit_starting_point->setInfo($lng->txt('adm_user_starting_point_inherit_info'));
        $input->addOption($inherit_starting_point);
        foreach ($this->starting_point_repository->getPossibleStartingPoints() as $value => $caption) {
            if ($value === Repository::START_REPOSITORY_OBJ) {
                continue;
            }
            $input->addOption(new \ilRadioOption($lng->txt($caption), (string) $value));
        }
        $input->setValue((string) $this->starting_point_repository->getCurrentUserPersonalStartingPoint());

        $starting_point_repository = new \ilRadioOption(
            $lng->txt('adm_user_starting_point_object'),
            (string) Repository::START_REPOSITORY_OBJ
        );
        $repository_object_id = new \ilTextInputGUI($lng->txt('adm_user_starting_point_ref_id'), 'usr_start_ref_id');
        $repository_object_id->setInfo($lng->txt('adm_user_starting_point_ref_id_info'));
        $repository_object_id->setRequired(true);
        $repository_object_id->setSize(5);
        if ($this->starting_point_repository->getCurrentUserPersonalStartingPoint() === Repository::START_REPOSITORY_OBJ) {
            $start_ref_id = $this->starting_point_repository->getCurrentUserPersonalStartingObject();
            $repository_object_id->setValue($start_ref_id);
            if ($start_ref_id !== null
                && ($start_obj_id = \ilObject::_lookupObjId($start_ref_id)) !== 0) {
                $repository_object_id->setInfo(
                    $lng->txt('obj_' . \ilObject::_lookupType($start_obj_id)) .
                    ': ' . \ilObject::_lookupTitle($start_obj_id)
                );
            }
        }
        $starting_point_repository->addSubItem($repository_object_id);
        $input->addOption($starting_point_repository);
        return $input;
    }

    public function getDefaultValueForDisplay(
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings
    ): string {
        $default_starting_point = $this->starting_point_repository->getSystemDefaultStartingPointType();
        $starting_point = $this->starting_point_repository->getPossibleStartingPoints()[$default_starting_point];
        if ($default_starting_point !== Repository::START_REPOSITORY_OBJ
            || ($ref_id = $this->starting_point_repository->getSystemDefaultStartingObject()) === null
            || ($obj_id = \ilObject::_lookupObjId($ref_id)) === 0) {
            return $starting_point;
        }
        return $lng->txt('obj_' . \ilObject::_lookupType($obj_id)) . ' - ' . \ilObject::_lookupTitle($obj_id);
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $current_user
    ): bool {
        return $this->starting_point_repository->getCurrentUserPersonalStartingPoint() !== 0
            && ($this->starting_point_repository->getCurrentUserPersonalStartingPoint()
                !== $this->starting_point_repository->getSystemDefaultStartingPointType()
            || $this->starting_point_repository->getCurrentUserPersonalStartingObject()
                !== $this->starting_point_repository->getSystemDefaultStartingObject());
    }

    public function storeUserChoice(
        \ilObjUser $current_user,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): void {
        if ($input === null
            || (int) $input === 0) {
            $this->starting_point_repository->setCurrentUserPersonalStartingPoint(0);
        }
        $ref_id = $form->getInput('usr_start_ref_id');
        $this->starting_point_repository->setCurrentUserPersonalStartingPoint(
            (int) $input,
            $ref_id === '' ? null : (int) $ref_id
        );
    }

    public function validateUserChoice(
        \ilGlobalTemplateInterface $tpl,
        Language $lng,
        \ilPropertyFormGUI $form
    ): ?string {
        if ($form->getInput($this->getIdentifier()) !== Repository::START_REPOSITORY_OBJ) {
            return null;
        }

        $ref_id = $form->getInput('usr_start_ref_id');
        if (!is_numeric($ref_id) || !\ilObject::_exists((int) $ref_id, true)) {
            $tpl->setOnScreenMessage('failure', $lng->txt('obj_ref_id_not_exist'), true);
            return false;
        }

        return true;
    }
}
