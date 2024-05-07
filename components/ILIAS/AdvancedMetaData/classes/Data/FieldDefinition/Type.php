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

namespace ILIAS\AdvancedMetaData\Data\FieldDefinition;

enum Type: int
{
    private const STRING_MAP = [
        'Text' => self::TEXT,
        'Select' => self::SELECT,
        'Date' => self::DATE,
        'DateTime' => self::DATETIME,
        'Float' => self::FLOAT,
        'Location' => self::LOCATION,
        'Integer' => self::INTEGER,
        'SelectMulti' => self::SELECT_MULTI,
        'ExternalLink' => self::EXTERNAL_LINK,
        'InternalLink' => self::INTERNAL_LINK,
        'Address' => self::ADDRESS
    ];

    case SELECT = 1;
    case TEXT = 2;
    case DATE = 3;
    case DATETIME = 4;
    case INTEGER = 5;
    case FLOAT = 6;
    case LOCATION = 7;
    case SELECT_MULTI = 8;
    case ADDRESS = 99;
    case EXTERNAL_LINK = 9;
    case INTERNAL_LINK = 10;

    public static function tryFromString(string $value): ?Type
    {
        return self::STRING_MAP[$value] ?? null;
    }

    public function stringValue(): string
    {
        foreach (self::STRING_MAP as $string => $case) {
            if ($this === $case) {
                return $string;
            }
        }
        return '';
    }
}
