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
 * Cleans up item group references when objects are trashed or deleted.
 */
class ilItemGroupAppEventListener implements ilAppEventListener
{
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter): void
    {
        $ref_id = (int) ($a_parameter['ref_id'] ?? 0);
        if ($a_component !== 'components/ILIAS/ILIASObject' || $ref_id <= 0) {
            return;
        }

        global $DIC;

        match ($a_event) {
            'toTrash', 'delete' => $DIC->itemGroup()->internal()->repo()->itemGroup()->removeItems([$ref_id]),
            default => null,
        };
    }
}
