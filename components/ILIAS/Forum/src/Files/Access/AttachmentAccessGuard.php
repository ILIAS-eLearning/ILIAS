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

namespace ILIAS\Forum\Files\Access;

/**
 * Decides whether an attachment container addressed by a request may be resolved
 * in the context of the forum object the request was routed to.
 *
 * Guards judge object relationships only. Repository permissions (RBAC) remain
 * the responsibility of the calling controller.
 */
interface AttachmentAccessGuard
{
    public function decide(int $routed_obj_id, int $container_id): AccessDecision;
}
