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

namespace ILIAS\StaticURL\Legacy;

/**
 * Hands out ilCtrl. Handlers receive the legacy object itself through
 * {@see \ILIAS\StaticURL\Context::ctrl()}, so this cannot narrow it down to the
 * methods they call. Goes away once Ctrl is wired through the component
 * bootstrap.
 *
 * @internal
 */
class CtrlProxy
{
    public function get(): \ilCtrlInterface
    {
        global $DIC;
        return $DIC->ctrl();
    }
}
