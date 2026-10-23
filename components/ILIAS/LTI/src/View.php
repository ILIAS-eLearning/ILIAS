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
 * Whether the current request shows the LTI view of a user who came from a platform. The legacy environment
 * knows it as $DIC['lti']; everything else of the view is internal to the LTI component.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
interface View
{
    /**
     * Whether the current user came from a platform.
     */
    public function isActive(): bool;
}
