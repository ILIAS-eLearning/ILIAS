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
use ILIAS\User\Settings\SettingDefinition;
use ILIAS\User\Settings\AvailablePages;
use ILIAS\User\Settings\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;
use ILIAS\Refinery\Custom\Constraint as CustomConstraint;
use ILIAS\Refinery\Factory as Refinery;

class Setting implements SettingDefinition
{
    private readonly Repository $starting_point_repository;

    public function __construct()
    {
        $this->starting_point_repository = LocalDIC::dic()[Repository::class];
    }

    public function getIdentifier(): string
    {
        return 'starting_point';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt($this->getIdentifier());
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
        FieldFactory $field_factory,
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): Input {
        $starting_point_id = null;
        $object_ref_id = null;
        if ($user !== null) {
            [
                'starting_point_id' => $starting_point_id ,
                'object_id' => $object_ref_id
            ] = $this->retrieveValueFromUser($user);
        }
        $possible_starting_points = $this->starting_point_repository->getPossibleStartingPoints();
        return $field_factory->switchableGroup(
            array_reduce(
                array_keys($possible_starting_points),
                static function (array $c, int $v) use (
                    $field_factory,
                    $lng,
                    $possible_starting_points,
                    $object_ref_id
                ): array {
                    $c[$v] = $field_factory->group(
                        $v === Repository::START_REPOSITORY_OBJ
                            ? [
                                'usr_start_ref_id' => $field_factory->numeric(
                                    $lng->txt('adm_user_starting_point_ref_id'),
                                    $object_ref_id === null || ($start_obj_id = \ilObject::_lookupObjId($object_ref_id)) === 0
                                        ? $lng->txt('adm_user_starting_point_ref_id_info')
                                        : $lng->txt('obj_' . \ilObject::_lookupType($start_obj_id))
                                                . ': ' . \ilObject::_lookupTitle($start_obj_id)
                                )->withRequired(true)
                            ] : [],
                        $lng->txt($possible_starting_points[$v])
                    );
                    return $c;
                },
                [
                    0 => $field_factory->group(
                        [],
                        $lng->txt('adm_user_starting_point_inherit'),
                        $lng->txt('adm_user_starting_point_inherit_info')
                    )
                ]
            ),
            $lng->txt('adm_user_starting_point'),
            $lng->txt('adm_user_starting_point_info')
        )->withAdditionalTransformation(
            $this->buildValidateObjectConstraint($refinery, $lng)
        )->withAdditionalTransformation(
            $refinery->custom()->transformation(
                static fn(array $v): array => [
                    'starting_point_id' => $refinery->kindlyTo()->int()->transform($v[0]),
                    'object_id' => $v[1]['usr_start_ref_id'] ?? null
                ]
            )
        )->withValue(
            $this->buildValueSetterArray(
                $starting_point_id,
                $object_ref_id
            )
        );
    }

    public function getLegacyInput(
        Language $lng,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $starting_point_id = null;
        $object_ref_id = null;
        if ($user !== null) {
            ['starting_point_id' => $starting_point_id , 'object_id' => $object_ref_id] = $this->retrieveValueFromUser($user);
        }
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
        $input->setValue((string) $starting_point_id);

        $starting_point_repository = new \ilRadioOption(
            $lng->txt('adm_user_starting_point_object'),
            (string) Repository::START_REPOSITORY_OBJ
        );
        $repository_object_id = new \ilTextInputGUI($lng->txt('adm_user_starting_point_ref_id'), 'usr_start_ref_id');
        $repository_object_id->setInfo($lng->txt('adm_user_starting_point_ref_id_info'));
        $repository_object_id->setRequired(true);
        $repository_object_id->setSize(5);
        if ($object_ref_id !== null) {
            $repository_object_id->setValue($object_ref_id);
            if (($start_obj_id = \ilObject::_lookupObjId($object_ref_id)) !== 0) {
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
        \ilSetting $settings
    ): string {
        $default_starting_point = $this->starting_point_repository->getSystemDefaultStartingPointType();
        $starting_point = $this->starting_point_repository->getPossibleStartingPoints()[$default_starting_point];
        if ($default_starting_point !== Repository::START_REPOSITORY_OBJ
            || ($ref_id = $this->starting_point_repository->getSystemDefaultStartingObject()) === null
            || ($obj_id = \ilObject::_lookupObjId($ref_id)) === 0) {
            return $lng->txt($starting_point);
        }
        return $lng->txt('obj_' . \ilObject::_lookupType($obj_id)) . ' - ' . \ilObject::_lookupTitle($obj_id);
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $user
    ): bool {
        return $this->starting_point_repository->isPersonalStartingPointEnabledForUser($user);
    }

    public function persistUserInput(
        \ilObjUser $user,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): \ilObjUser {
        if ($input === null) {
            $starting_point_id = 0;
            $object_ref_id = null;
        } elseif (is_array($input)) {
            ['starting_point_id' => $starting_point_id , 'object_id' => $object_ref_id] = $input;
        } else {
            $starting_point_id = (int) $input;
            $object_ref_id_input = $form->getInput('usr_start_ref_id');
            $object_ref_id = $object_ref_id_input === '' ? null : (int) $object_ref_id_input;
        }
        $this->starting_point_repository->setPersonalStartingPointForUser(
            $user,
            $starting_point_id,
            $object_ref_id
        );
        return $user;
    }

    public function validateUserChoice(
        \ilGlobalTemplateInterface $tpl,
        Language $lng,
        \ilPropertyFormGUI $form
    ): bool {
        if ((int) $form->getInput($this->getIdentifier()) !== Repository::START_REPOSITORY_OBJ) {
            return true;
        }

        $ref_id = $form->getInput('usr_start_ref_id');
        if (!is_numeric($ref_id) || !\ilObject::_exists((int) $ref_id, true)) {
            $tpl->setOnScreenMessage('failure', $lng->txt('obj_ref_id_not_exist'), true);
            return false;
        }

        return true;
    }

    private function buildValidateObjectConstraint(
        Refinery $refinery,
        Language $lng
    ): CustomConstraint {
        return $refinery->custom()->constraint(
            function (array $v): bool {
                if ((int) $v[0] !== Repository::START_REPOSITORY_OBJ) {
                    return true;
                }
                if (!is_int($v[1]['usr_start_ref_id']) || !\ilObject::_exists($v[1]['usr_start_ref_id'], true)) {
                    return false;
                }
                return true;
            },
            $lng->txt('obj_ref_id_not_exist')
        );
    }

    /**
     * @return array{start: int, ref_id: int|null}
     */
    public function retrieveValueFromUser(\ilObjUser $user): array
    {
        return [
            'starting_point_id' => $this->starting_point_repository->getPersonalStartingPointForUser($user),
            'object_id' => $this->starting_point_repository->getPersonalStartingObjectForUser($user)
        ];
    }

    private function buildValueSetterArray(
        int $starting_point_id,
        ?int $object_ref_id
    ): int|array {
        if ($starting_point_id !== Repository::START_REPOSITORY_OBJ) {
            return $starting_point_id;
        }

        return [
            0 => $starting_point_id,
            1 => [
                'usr_start_ref_id' => $object_ref_id
            ]
        ];
    }
}
