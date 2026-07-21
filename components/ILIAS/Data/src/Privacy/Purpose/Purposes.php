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

namespace ILIAS\Data\Privacy\Purpose;

use ILIAS\Data\Privacy\Source\DbTarget;

/**
 * Factory for the available purposes. Obtain it via
 * {@see \ILIAS\Data\Privacy\Services::purposes()}, via
 * `$pull[Purposes::class]` in a component bootstrap, or via
 * `$DIC[Purposes::class]` in legacy code.
 */
class Purposes
{
    public function storeInTable(DbTarget $target): StoreInTable
    {
        return new StoreInTable($target);
    }

    /**
     * @param string $ui_context e.g. "public_profile", "profile_form", "mail_header"
     */
    public function displayToUser(string $ui_context): DisplayToUser
    {
        return new DisplayToUser($ui_context);
    }

    /**
     * @param string $component e.g. "Mail", "Notifications"
     * @param string $reason    e.g. "signature", "send_notification"
     */
    public function passToComponent(string $component, string $reason): PassToComponent
    {
        return new PassToComponent($component, $reason);
    }

    /**
     * @param string $operation e.g. "pseudonymisation", "condition_check"
     */
    public function technicalProcessing(string $operation): TechnicalProcessing
    {
        return new TechnicalProcessing($operation);
    }

    /**
     * Never use this in new code — state the real purpose instead.
     */
    public function legacyAccess(string $hint = 'unclassified'): LegacyAccess
    {
        return new LegacyAccess($hint);
    }
}
