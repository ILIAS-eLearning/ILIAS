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

namespace ILIAS\Data\Description;

use ILIAS\Data\Text;

class DList extends Description
{
    use ErrorHandling;

    public function __construct(
        Text\SimpleDocumentMarkdown $description,
        protected Description $value_type
    ) {
        parent::__construct($description);
    }

    public function getPrimitiveRepresentation(mixed $data): mixed
    {
        if (!is_array($data)) {
            return fn() => yield "Expected an array.";
        }

        $repr = [];
        $errors = [];

        foreach ($data as $v) {
            $value = $this->value_type->getPrimitiveRepresentation($v);

            if ($value instanceof \Closure) {
                $errors[] = $value;
            } else {
                $repr[] = $value;
            }
        }

        if ($errors) {
            return $this->mergeErrors($errors);
        }

        return $repr;
    }

    public function getValueType(): Description
    {
        return $this->value_type;
    }
}
