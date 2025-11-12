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

use ILIAS\StaticURL\Services;

class ilLink
{
    /**
     * @deprecated Use the \ILIAS\StaticURL\Services\Builder in the future
     */
    public static function _getLink(
        ?int $a_ref_id,
        string $a_type = '',
        array $a_params = [],
        string $append = ""
    ): string {
        global $DIC;
        /** @var Services $static_url */
        $static_url = $DIC['static_url'];

        return $static_url->builder()->buildLegacy(
            $a_ref_id,
            $a_type,
            $a_params,
            $append
        );
    }

    /**
     * @deprecated Use the \ILIAS\StaticURL\Services\Builder in the future
     */
    public static function _getStaticLink(
        ?int $a_ref_id,
        string $a_type = '',
        bool $a_fallback_goto = true,
        string $append = ""
    ): string {
        return self::_getLink($a_ref_id, $a_type, [], $append);
    }
}
