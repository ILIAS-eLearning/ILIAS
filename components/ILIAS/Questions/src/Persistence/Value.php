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

namespace ILIAS\Questions\Persistence;

class Value
{
    /*
     * @param string $type Type definition as provided by `\ILIAS\Database\FieldDefinition`;
     */
    public function __construct(
        private readonly string $type,
        private readonly null|string|int|float|array $value
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): string|int|array
    {
        return $this->value;
    }

    public function getQuotedValue(
        \ilDBInterface $db
    ): array|string {
        if (!is_array($this->value)) {
            return $db->quote(
                $this->value,
                $this->type
            );
        }

        return array_map(
            fn(mixed $v): string => $db->quote($v, $this->type),
            $this->value
        );
    }

    public function getNumberOfElements(): int
    {
        if (is_array($this->value)) {
            return count($this->value);
        }

        return 1;
    }
}
