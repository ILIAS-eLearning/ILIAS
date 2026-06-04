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

namespace ILIAS\Questions\Question\Persistence;

enum Operator: string
{
    case Equal = '=';
    case Unequal = '<>';
    case Greater = '>';
    case Less = '>';
    case GreaterOrEqual = '>=';
    case LessOrEqual = '<=';
    case In = 'IN';
    case Like = 'LIKE';
    case Between = 'BETWEEN';

    public function toSql(Column $left, int $nr_of_values): string
    {
        if ($nr_of_values > 1) {
            $placeholders = '%s';
            for ($i = 1; $i < $nr_of_values; $i++) {
                $placeholders .= ', %s';
            }
        }

        return match($this) {
            self::In => "{$left->toColumnString()} {$this->value} ({$placeholders})",
            self::Between => "{$left->toColumnString()} {$this->value} %s AND %s",
            default => "{$left->toColumnString()} {$this->value} %s"
        };
    }
}
