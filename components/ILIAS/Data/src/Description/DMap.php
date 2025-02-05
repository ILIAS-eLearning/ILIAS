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

class DMap extends Description
{
    use ErrorHandling;

    public function __construct(
        Text\SimpleDocumentMarkdown $description,
        protected DValue $key_type,
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

        foreach ($data as $k => $v) {
            $key = $this->key_type->getPrimitiveRepresentation($k);
            $value = $this->value_type->getPrimitiveRepresentation($v);

            $key_is_error = $key instanceof \Closure;
            $value_is_error = $value instanceof \Closure;

            if ($key_is_error) {
                $errors[] = $key;
            }
            if ($value_is_error) {
                $errors[] = $value;
            }

            if (!$key_is_error && !$value_is_error) {
                $repr[$key] = $value;
            }
        }

        if ($errors) {
            return $this->mergeErrors($errors);
        }

        return $repr;
    }

    public function getKeyType(): DValue
    {
        return $this->key_type;
    }

    public function getValueType(): Description
    {
        return $this->value_type;
    }
}
