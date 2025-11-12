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

namespace ILIAS\User\Profile\Fields\Custom;

use ILIAS\User\Context;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\Refinery\Factory as Refinery;

class Select implements Type
{
    public function getLabel(Language $lng): string
    {
        return $lng->txt('udf_type_select');
    }

    public function getAdditionalEditFormInputs(
        Language $lng,
        FieldFactory $ff,
        Refinery $refinery,
        ?string $data
    ): ?FormInput {
        $parsed_data = $this->parseData($data);
        return $ff->group([
            'allow_multiple' => $ff->checkbox($lng->txt('multiple_selection'))
                ->withValue($parsed_data['allow_multiple']),
            'options' => $ff->tag($lng->txt('udf_select_options'), [])
                ->withValue($parsed_data['options'])
        ])->withAdditionalTransformation(
            $refinery->custom()->transformation(
                static fn(array $vs): string => json_encode([
                    'allow_multiple' => $vs['allow_multiple'],
                    'options' => $vs['options']
                ])
            )
        );
    }

    public function getLegacyInput(
        Language $lng,
        Context $context,
        array $user_value,
        string $label,
        ?string $data
    ): \ilFormPropertyGUI {
        $parsed_data = $this->parseData($data);
        if (!$parsed_data['allow_multiple']) {
            $input = new \ilSelectInputGUI($label);
            $input->setOptions(
                ['' => $lng->txt('please_select')]
                + array_combine($parsed_data['options'], $parsed_data['options'])
            );
            $input->setValue($user_value[0] ?? '');
            return $input;
        }

        $input = new \ilMultiSelectInputGUI($label);
        $input->setOptions(
            array_combine($parsed_data['options'], $parsed_data['options'])
        );
        $input->setValue($user_value);
        return $input;


    }

    public function prepareUserInputForStorage(mixed $input, ?string $data): array
    {
        $options = $this->parseData($data)['options'];
        if (!is_array($input)) {
            $input = [$input];
        }

        return array_filter(
            $input,
            fn(string $v): bool => in_array($v, $options)
        );
    }

    private function parseData(?string $data): array
    {
        if ($data === null) {
            return [
                'allow_multiple' => false,
                'options' => []
            ];
        }

        $values = json_decode($data, true) ?? unserialize($data, ['allowed_classes' => false]) ?? [];

        return [
            'allow_multiple' => $values['allow_multiple'] ?? false,
            'options' => $values['options'] ?? $values
        ];
    }
}
