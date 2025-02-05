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

class DValue extends Description
{
    public function __construct(
        Text\SimpleDocumentMarkdown $description,
        protected ValueType $type,
    ) {
        parent::__construct($description);
    }

    public function getType(): ValueType
    {
        return $this->type;
    }

    public function getPrimitiveRepresentation(mixed $data): mixed
    {
        switch ($this->type) {
            case ValueType::INT:
                if (!is_int($data)) {
                    return fn() => yield "Expected an integer.";
                }
                return $data;

            case ValueType::FLOAT:
                if (!is_float($data)) {
                    return fn() => yield "Expected a float.";
                }
                return $data;

            case ValueType::STRING:
                if (!is_string($data)) {
                    return fn() => yield "Expected a string.";
                }
                return $data;

            case ValueType::DATETIME:
                if (!$data instanceof \DateTimeImmutable) {
                    return fn() => yield "Expected a \\DateTimeImmutable.";
                }
                return $data;

            case ValueType::BOOL:
                if (!is_bool($data)) {
                    return fn() => yield "Expected a bool.";
                }
                return $data;

            case ValueType::NULL:
                if (!is_null($data)) {
                    return fn() => yield "Expected null.";
                }
                return $data;

            default:
                throw new \LogicException("Unmatch type.");
        }
    }
}
