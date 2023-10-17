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

namespace ILIAS\MetaData\Repository\Validation\Data;

use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Data\Type;

class DataValidator implements DataValidatorInterface
{
    protected DataValidatorService $validators;

    public function __construct(
        DataValidatorService $validators
    ) {
        $this->validators = $validators;
    }

    public function isValid(ElementInterface $element, bool $ignore_marker): bool
    {
        return $this->validators->validator($element->getDefinition()->dataType())
                    ->isValid($element, $ignore_marker);
    }

    protected function getValidator(Type $type): DataValidatorInterface
    {
        $validator = $this->validators[$type->value];
        if (isset($validator)) {
            return $validator;
        }
        throw new \ilMDRepositoryException('Unhandled data type');
    }
}
