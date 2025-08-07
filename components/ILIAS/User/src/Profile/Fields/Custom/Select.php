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
            'options' => $ff->tag($lng->txt('options'), [])
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
        if ($parsed_data['allow_multiple']) {
            $input = new \ilMultiSelectInputGUI($label);
            $input->setOptions($this->parseData($data)['options']);
            $input->setValue($user_value);
            return $input;
        }

        $input = new \ilSelectInputGUI($label);
        $input->setOptions(['' => $lng->txt('please_select')] + $this->parseData($data)['options']);
        $input->setValue($user_value[0] ?? '');
        return $input;
    }

    public function prepareUserInputForStorage(mixed $input): array
    {
        if (is_array($input)) {
            return $input;
        }

        return [$input];
    }

    public function buildPresentationValueFromUserValue(
        array $input,
        ?string $data
    ): string {
        if ($data === null || $input === []) {
            return '';
        }

        $options = $this->parseData($data)['options'];

        return implode(
            ', ',
            array_reduce(
                $input,
                static function (array $c, string|int $v) use ($options): array {
                    if (array_key_exists($v, $options)) {
                        $c[] = $options[$v];
                        return $c;
                    }

                    $value = array_search($v, $options);
                    if ($value === false) {
                        return $c;
                    }

                    $c[] = $v;
                    return $c;
                },
                []
            )
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
