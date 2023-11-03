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

class ilLegalDocumentsAppEventListener implements ilAppEventListener
{
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter): void
    {
        global $DIC;
        match ($a_event) {
            'beforeLogout' => $DIC['legalDocuments']->onLogout(ilStartUpGUI::class, new ilObjUser($a_parameter['user_id'])),
            'afterLogin' => $DIC['legalDocuments']->afterLogin(),
            default => null,
        };
    }
}
