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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilCtrlQueryRegexParser implements ilCtrlQueryParserInterface
{
    private const PATTERN = '/([^=&]*)=([^=&]*)/m';

    public function parseQueriesOfURL(string $query_string): array
    {
        preg_match_all(self::PATTERN, $query_string, $matches, PREG_SET_ORDER, 0);
        $query_parameters = [];
        foreach ($matches as $match) {
            $query_parameters[$match[1]] = $match[2];
        }

        return $query_parameters;
    }

}
