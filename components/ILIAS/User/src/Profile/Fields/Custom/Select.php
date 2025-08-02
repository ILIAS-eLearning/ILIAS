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
        return $ff->group([
            'values' => $ff->tag($lng->txt('options'), [])
                ->withValue($this->parseData($data))
        ])->withAdditionalTransformation(
            $refinery->custom()->transformation(
                static fn(array $vs): string => json_encode($vs['values'])
            )
        );
    }

    public function getInput(
        Language $lng,
        string $user_value,
        string $label,
        ?string $data
    ): \ilFormPropertyGUI {
        $input = new \ilSelectInputGUI($label);
        $input->setOptions(['' => $lng->txt('please_select')] + $this->parseData($data));
        $input->setValue($user_value);
        return $input;
    }

    public function prepareUserInputForStorage(mixed $input): array
    {
        return [$input];
    }

    private function parseData(?string $data): array
    {
        if ($data === null) {
            return [];
        }
        return json_decode($data) ?? unserialize($data, ['allowed_classes' => false]) ?? [];
    }
}
