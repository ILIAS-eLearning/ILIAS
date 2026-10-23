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

namespace ILIAS\LTI;

/**
 * The LTI view of a user who came from a platform, as the LTI component provides it. The legacy
 * environment knows it as $DIC['lti'].
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
interface View
{
    /**
     * Switches the page to the LTI view for a user who came from a platform.
     */
    public function init(): void;

    /**
     * Whether the current user came from a platform.
     */
    public function isActive(): bool;

    public function executeCommand(): void;

    /**
     * The address the user of an accepted launch goes on to: the object the platform launched, or an
     * empty string when the session holds no launch.
     */
    public function getLaunchTarget(): string;

    public function getContextId(): int;

    public function getTitle(): string;

    public function getExitLink(): string;

    /**
     * A page of LTI without any navigation of ILIAS.
     *
     * @param array $content the components of the page
     */
    public function printPage(string $title, array $content): void;
}
